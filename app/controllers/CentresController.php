<?php

class CentresController extends Controller {
    private $centreModel;

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Permissions pour 'manage_centres'
        $this->centreModel = $this->model('Centre');
        // $this->salleModel = $this->model('Salle'); // Modèle Salle sera chargé au besoin ou ici si fréquemment utilisé
    }

    public function index() {
        $centres = $this->centreModel->getAll();
        $this->view('centres/index', ['centres' => $centres, 'title' => $this->translate('centres_list')]);
    }

    public function create() {
        $data = [
            'title' => $this->translate('add_centre'),
            'nom_centre' => '',
            'code_centre' => '',
            'description' => '',
            'nom_centre_err' => '',
            'code_centre_err' => ''
        ];
        $this->view('centres/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'nom_centre' => trim($_POST['nom_centre']),
                'code_centre' => strtoupper(trim($_POST['code_centre'] ?? null)),
                'description' => trim($_POST['description'] ?? null),
                'title' => $this->translate('add_centre'),
                'nom_centre_err' => '',
                'code_centre_err' => ''
            ];

            if (empty($data['nom_centre'])) $data['nom_centre_err'] = $this->translate('centre_name_required');
            elseif ($this->centreModel->nomExists($data['nom_centre'])) $data['nom_centre_err'] = $this->translate('centre_name_taken');

            if (!empty($data['code_centre']) && $this->centreModel->codeCentreExists($data['code_centre'])) {
                $data['code_centre_err'] = $this->translate('centre_code_taken');
            }
            // Le code centre n'est pas requis pour l'instant, mais s'il est fourni, il doit être unique.

            if (empty($data['nom_centre_err']) && empty($data['code_centre_err'])) {
                if ($this->centreModel->add($data)) {
                    $_SESSION['message'] = $this->translate('centre_added_successfully');
                    $this->redirect('centres');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_centre');
                    $this->view('centres/create', $data);
                }
            } else {
                $this->view('centres/create', $data);
            }
        } else {
            $this->redirect('centres/create');
        }
    }

    public function edit($id) {
        $id = (int)$id;
        $centre = $this->centreModel->getById($id);

        if (!$centre) {
            $_SESSION['error_message'] = $this->translate('centre_not_found');
            $this->redirect('centres');
            return;
        }

        // Pour les étapes suivantes (salles, assignations)
        $salles = $this->centreModel->getSallesByCentreId($id); // Sera utilisé à l'étape "Gestion des Salles"
        // $assignations = $this->centreModel->getAssignations($id, $active_annee_scolaire_id); // Sera utilisé à l'étape "Assignations"

        $data = [
            'id' => $id,
            'nom_centre' => $centre->nom_centre,
            'code_centre' => $centre->code_centre,
            'description' => $centre->description,
            'title' => $this->translate('edit_centre'),
            'nom_centre_err' => '',
            'code_centre_err' => '',
            'salles' => $salles ?? [],
            // 'assignations' => $assignations ?? [],
            // 'annees_scolaires' => $this->model('AnneeScolaire')->getAll(),
            // 'lycees' => $this->model('Lycee')->getAll(),
            // 'series' => $this->model('Serie')->getAll(),
            // Données pour le formulaire d'ajout de salle
            'new_salle' => ['numero_salle' => '', 'capacite' => '', 'description' => ''],
            'salle_numero_err' => '',
            'salle_capacite_err' => '',
            // Données pour les assignations
            'assignations' => [], // Sera chargé en fonction de l'année sélectionnée
            'annees_scolaires' => $this->model('AnneeScolaire')->getAll(),
            'selected_annee_scolaire_id' => $this->model('AnneeScolaire')->getActiveYear()->id ?? null, // Année active par défaut
            'lycees' => $this->model('Lycee')->getAll(),
            'series' => $this->model('Serie')->getAll()
        ];

        // Charger les assignations pour l'année active par défaut (si elle existe)
        if ($data['selected_annee_scolaire_id']) {
            $data['assignations'] = $this->centreModel->getAssignations($id, $data['selected_annee_scolaire_id']);
        }

        $this->view('centres/edit', $data);
    }

    /**
     * Gère l'affichage des assignations pour un centre et une année scolaire spécifique.
     * Cette méthode est appelée via AJAX ou par une soumission de formulaire pour changer l'année visualisée.
     */
    public function loadAssignations($centre_id, $annee_scolaire_id = null) {
        $centre_id = (int)$centre_id;
        $annee_scolaire_id = $annee_scolaire_id ? (int)$annee_scolaire_id : ($this->model('AnneeScolaire')->getActiveYear()->id ?? null);

        if (!$annee_scolaire_id) {
             // Si aucune année active et aucune fournie, on ne peut rien charger.
             // On pourrait retourner un JSON d'erreur ou juste un tableau vide.
             // Pour l'instant, on redirige vers l'edit du centre qui affichera le selecteur d'année.
            $this->redirect('centres/edit/' . $centre_id);
            return;
        }

        $centre = $this->centreModel->getById($centre_id);
        if (!$centre) {
            $_SESSION['error_message'] = $this->translate('centre_not_found');
            $this->redirect('centres');
            return;
        }

        $salles = $this->centreModel->getSallesByCentreId($centre_id);
        $assignations = $this->centreModel->getAssignations($centre_id, $annee_scolaire_id);
        $annees_scolaires = $this->model('AnneeScolaire')->getAll();
        $lycees = $this->model('Lycee')->getAll();
        $series = $this->model('Serie')->getAll();

        $data = [
            'id' => $centre_id,
            'nom_centre' => $centre->nom_centre,
            'description' => $centre->description,
            'title' => $this->translate('edit_centre') . ' - ' . $centre->nom_centre,
            'salles' => $salles ?? [],
            'new_salle' => ['numero_salle' => '', 'capacite' => '', 'description' => ''],
            'salle_numero_err' => '',
            'salle_capacite_err' => '',
            'assignations' => $assignations,
            'annees_scolaires' => $annees_scolaires,
            'selected_annee_scolaire_id' => $annee_scolaire_id,
            'lycees' => $lycees,
            'series' => $series
        ];
        $this->view('centres/edit', $data);
    }


    public function update($id) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $id,
                'nom_centre' => trim($_POST['nom_centre']),
                'code_centre' => strtoupper(trim($_POST['code_centre'] ?? null)),
                'description' => trim($_POST['description'] ?? null),
                'title' => $this->translate('edit_centre'),
                'nom_centre_err' => '',
                'code_centre_err' => ''
            ];

            if (empty($data['nom_centre'])) $data['nom_centre_err'] = $this->translate('centre_name_required');
            elseif ($this->centreModel->nomExists($data['nom_centre'], $id)) $data['nom_centre_err'] = $this->translate('centre_name_taken');

            if (!empty($data['code_centre']) && $this->centreModel->codeCentreExists($data['code_centre'], $id)) {
                $data['code_centre_err'] = $this->translate('centre_code_taken');
            }

            if (empty($data['nom_centre_err']) && empty($data['code_centre_err'])) {
                if ($this->centreModel->update($id, $data)) {
                    $_SESSION['message'] = $this->translate('centre_updated_successfully');
                    $this->redirect('centres');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_centre');
                    // Recharger les données pour la vue
                    $centre = $this->centreModel->getById($id);
                    $data['salles'] = $this->centreModel->getSallesByCentreId($id); // Pour l'étape suivante
                    $this->view('centres/edit', $data);
                }
            } else {
                 // Recharger les données pour la vue
                $centre = $this->centreModel->getById($id);
                $data['nom_centre'] = $data['nom_centre_err'] ? $_POST['nom_centre'] : $centre->nom_centre;
                $data['description'] = $_POST['description'] ?? $centre->description; // Garder la description soumise
                $data['salles'] = $this->centreModel->getSallesByCentreId($id); // Pour l'étape suivante
                $this->view('centres/edit', $data);
            }
        } else {
            $this->redirect('centres');
        }
    }

    public function delete($id) {
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Sécurité
            $centre = $this->centreModel->getById($id);
            if (!$centre) {
                $_SESSION['error_message'] = $this->translate('centre_not_found');
                $this->redirect('centres');
                return;
            }

            if ($this->centreModel->delete($id)) {
                $_SESSION['message'] = $this->translate('centre_deleted_successfully');
            } else {
                if(empty($_SESSION['error_message'])){
                     $_SESSION['error_message'] = $this->translate('error_deleting_centre');
                }
            }
            $this->redirect('centres');
        // } else {
        //     $this->redirect('centres');
        // }
    }

    // Méthodes pour la gestion des salles associées à un centre
    public function storeSalle($centre_id) {
        $centre_id = (int)$centre_id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $salleModel = $this->model('Salle');

            $data_salle = [
                'centre_id' => $centre_id,
                'numero_salle' => trim($_POST['numero_salle']),
                'capacite' => trim($_POST['capacite']),
                'description' => trim($_POST['salle_description'] ?? null), // Nom différent pour éviter conflit
                'salle_numero_err' => '',
                'salle_capacite_err' => ''
            ];

            if (empty($data_salle['numero_salle'])) {
                $data_salle['salle_numero_err'] = $this->translate('salle_numero_required');
            } elseif ($salleModel->numeroSalleExistsInCentre($centre_id, $data_salle['numero_salle'])) {
                $data_salle['salle_numero_err'] = $this->translate('salle_numero_exists_in_centre');
            }

            if (empty($data_salle['capacite']) || !is_numeric($data_salle['capacite']) || (int)$data_salle['capacite'] <= 0) {
                $data_salle['salle_capacite_err'] = $this->translate('salle_capacite_invalid');
            } else {
                $data_salle['capacite'] = (int)$data_salle['capacite'];
            }

            if (empty($data_salle['salle_numero_err']) && empty($data_salle['salle_capacite_err'])) {
                if ($salleModel->add($data_salle)) {
                    $_SESSION['message'] = $this->translate('salle_added_successfully');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_salle');
                }
            } else {
                // Stocker les erreurs et les données soumises en session pour les réafficher sur la page d'édition du centre
                $_SESSION['form_data_salle'] = $_POST;
                $_SESSION['form_errors_salle'] = $data_salle; // Contient les messages d'erreur
            }
            $this->redirect('centres/edit/' . $centre_id);
        } else {
            $this->redirect('centres/edit/' . $centre_id);
        }
    }

    // editSalle, updateSalle, deleteSalle pourraient être ajoutées ici
    // Pour l'instant, la suppression sera un lien direct pour simplifier
    public function deleteSalle($salle_id) {
        $salle_id = (int)$salle_id;
        $salleModel = $this->model('Salle');
        $salle = $salleModel->getById($salle_id);

        if (!$salle) {
            $_SESSION['error_message'] = $this->translate('salle_not_found');
            // Essayer de rediriger vers le centre parent si possible, sinon vers l'index des centres
            $this->redirect('centres');
            return;
        }

        $centre_id_redirect = $salle->centre_id; // Pour la redirection

        // TODO: Vérifier si la salle est utilisée (répartitions d'élèves, etc.) avant de supprimer
        // if ($salleModel->isSalleInUse($salle_id)) {
        //    $_SESSION['error_message'] = $this->translate('salle_in_use_cannot_delete');
        //    $this->redirect('centres/edit/' . $centre_id_redirect);
        //    return;
        // }

        if ($salleModel->delete($salle_id)) {
            $_SESSION['message'] = $this->translate('salle_deleted_successfully');
        } else {
            $_SESSION['error_message'] = $this->translate('error_deleting_salle');
        }
        $this->redirect('centres/edit/' . $centre_id_redirect);
    }

    // Une méthode editSalle($salle_id) pourrait charger une vue séparée ou un modal pour l'édition
    // Pour l'instant, nous n'implémentons pas l'édition de salle via une page/modal séparé.
    // Cela pourrait être ajouté comme amélioration.

    // Méthodes pour la gestion des assignations Lycée/Série à un Centre
    public function addAssignation($centre_id) {
        $centre_id = (int)$centre_id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data_assign = [
                'centre_id' => $centre_id,
                'lycee_id' => !empty($_POST['lycee_id']) ? (int)$_POST['lycee_id'] : null,
                'serie_id' => !empty($_POST['serie_id']) ? (int)$_POST['serie_id'] : null,
                'annee_scolaire_id' => (int)$_POST['annee_scolaire_id_assign'] // Champ différent pour le formulaire d'assignation
            ];

            if (empty($data_assign['annee_scolaire_id'])) {
                $_SESSION['error_message'] = $this->translate('academic_year_required_for_assignment');
            } elseif (empty($data_assign['lycee_id']) && empty($data_assign['serie_id'])) {
                 $_SESSION['error_message'] = $this->translate('lycee_or_serie_required_for_assignment');
            } else {
                if ($this->centreModel->addAssignation($data_assign)) {
                    $_SESSION['message'] = $this->translate('assignment_added_successfully');
                } else {
                    // Le message d'erreur est potentiellement déjà défini dans le modèle (ex: doublon)
                    if (empty($_SESSION['error_message'])) {
                        $_SESSION['error_message'] = $this->translate('error_adding_assignment');
                    }
                }
            }
            // Rediriger vers la page d'édition du centre, en rechargeant les assignations pour l'année concernée
            $this->redirect('centres/loadAssignations/' . $centre_id . '/' . $data_assign['annee_scolaire_id']);
        } else {
            $this->redirect('centres/edit/' . $centre_id);
        }
    }

    public function deleteAssignation($assignation_id) {
        $assignation_id = (int)$assignation_id;
        // Pour la redirection, il nous faut centre_id et annee_scolaire_id
        // On pourrait les passer en GET ou récupérer l'assignation pour les obtenir
        // Pour l'instant, on suppose qu'on les passe en GET pour simplifier, ou on redirige vers une page générique
        // Une meilleure solution serait de récupérer l'assignation pour obtenir centre_id et annee_scolaire_id

        // Exemple temporaire (nécessiterait de récupérer l'assignation pour une meilleure redirection)
        // $assignation = $this->centreModel->getAssignationById($assignation_id); // Méthode à créer dans le modèle
        // if (!$assignation) { ... }
        // $centre_id = $assignation->centre_id;
        // $annee_id = $assignation->annee_scolaire_id;

        if ($this->centreModel->removeAssignation($assignation_id)) {
            $_SESSION['message'] = $this->translate('assignment_deleted_successfully');
        } else {
            $_SESSION['error_message'] = $this->translate('error_deleting_assignment');
        }
        // Redirection générique pour l'instant. Idéalement, rediriger vers la page d'édition du centre avec la bonne année sélectionnée.
        // Ceci nécessiterait de connaître le centre_id et annee_scolaire_id de l'assignation supprimée.
        // Si on n'a pas cette info, on redirige à la liste des centres ou au dashboard.
        // Pour l'instant, on va juste rediriger vers l'index des centres. Une amélioration serait de passer centre_id et annee_id.
        $this->redirect('centres'); // A améliorer pour rediriger vers centres/edit/X/Y
    }
}
?>
