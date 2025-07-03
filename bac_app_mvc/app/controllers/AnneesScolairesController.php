<?php

class AnneesScolairesController extends Controller {
    private $anneeScolaireModel;

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Affiner la vérification des permissions
        // if (!$this->userHasPermission('manage_annees_scolaires')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->anneeScolaireModel = $this->model('AnneeScolaire');
    }

    public function index() {
        $annees = $this->anneeScolaireModel->getAll();
        $this->view('annees_scolaires/index', ['annees' => $annees, 'title' => $this->translate('academic_year_list')]);
    }

    public function create() {
        $data = [
            'title' => $this->translate('add_academic_year'),
            'libelle' => '', 'date_debut' => '', 'date_fin' => '', 'est_active' => 0,
            'libelle_err' => '', 'date_debut_err' => '', 'date_fin_err' => ''
        ];
        $this->view('annees_scolaires/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'libelle' => trim($_POST['libelle']),
                'date_debut' => $_POST['date_debut'],
                'date_fin' => $_POST['date_fin'],
                'est_active' => isset($_POST['est_active']) ? 1 : 0,
                'title' => $this->translate('add_academic_year'),
                'libelle_err' => '', 'date_debut_err' => '', 'date_fin_err' => ''
            ];

            if (empty($data['libelle'])) $data['libelle_err'] = $this->translate('label_required');
            elseif ($this->anneeScolaireModel->libelleExists($data['libelle'])) $data['libelle_err'] = $this->translate('label_taken');

            if (!empty($data['date_debut']) && !empty($data['date_fin']) && $data['date_debut'] > $data['date_fin']) {
                $data['date_fin_err'] = $this->translate('end_date_after_start_date');
            }

            if (empty($data['libelle_err']) && empty($data['date_fin_err'])) {
                if ($this->anneeScolaireModel->add($data)) {
                    $_SESSION['message'] = $this->translate('academic_year_added_successfully');
                    $this->redirect('anneesscolaires');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_academic_year');
                    $this->view('annees_scolaires/create', $data);
                }
            } else {
                $this->view('annees_scolaires/create', $data);
            }
        } else {
            $this->redirect('anneesscolaires/create');
        }
    }

    public function edit($id) {
        $id = (int)$id;
        $annee = $this->anneeScolaireModel->getById($id);
        if (!$annee) {
            $_SESSION['error_message'] = $this->translate('academic_year_not_found');
            $this->redirect('anneesscolaires');
            return;
        }
        $data = [
            'id' => $id,
            'libelle' => $annee->libelle,
            'date_debut' => $annee->date_debut,
            'date_fin' => $annee->date_fin,
            'est_active' => (bool)$annee->est_active,
            'title' => $this->translate('edit_academic_year'),
            'libelle_err' => '', 'date_debut_err' => '', 'date_fin_err' => ''
        ];
        $this->view('annees_scolaires/edit', $data);
    }

    public function update($id) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $id,
                'libelle' => trim($_POST['libelle']),
                'date_debut' => $_POST['date_debut'],
                'date_fin' => $_POST['date_fin'],
                'est_active' => isset($_POST['est_active']) ? 1 : 0,
                'title' => $this->translate('edit_academic_year'),
                'libelle_err' => '', 'date_debut_err' => '', 'date_fin_err' => ''
            ];
             // Si on essaie de désactiver l'année active via le formulaire d'édition (ce qui est bloqué par 'disabled' dans la vue),
             // on force est_active à rester à 1. Le seul moyen de désactiver est d'en activer une autre.
            $currentAnnee = $this->anneeScolaireModel->getById($id);
            if ($currentAnnee && $currentAnnee->est_active && $data['est_active'] == 0) {
                $data['est_active'] = 1; // Maintenir active
            }


            if (empty($data['libelle'])) $data['libelle_err'] = $this->translate('label_required');
            elseif ($this->anneeScolaireModel->libelleExists($data['libelle'], $id)) $data['libelle_err'] = $this->translate('label_taken');

            if (!empty($data['date_debut']) && !empty($data['date_fin']) && $data['date_debut'] > $data['date_fin']) {
                $data['date_fin_err'] = $this->translate('end_date_after_start_date');
            }

            if (empty($data['libelle_err']) && empty($data['date_fin_err'])) {
                if ($this->anneeScolaireModel->update($id, $data)) {
                    $_SESSION['message'] = $this->translate('academic_year_updated_successfully');
                    $this->redirect('anneesscolaires');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_academic_year');
                    $this->view('annees_scolaires/edit', $data);
                }
            } else {
                 // Pour s'assurer que 'est_active' est correctement re-populé si la validation échoue
                $annee = $this->anneeScolaireModel->getById($id);
                $data['est_active'] = (bool)$annee->est_active;
                $this->view('annees_scolaires/edit', $data);
            }
        } else {
            $this->redirect('anneesscolaires');
        }
    }

    public function delete($id) {
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Sécurité
            $annee = $this->anneeScolaireModel->getById($id);
            if (!$annee) {
                $_SESSION['error_message'] = $this->translate('academic_year_not_found');
                $this->redirect('anneesscolaires');
                return;
            }
            if ($annee->est_active) {
                 $_SESSION['error_message'] = $this->translate('cannot_delete_active_year');
                 $this->redirect('anneesscolaires');
                 return;
            }
            if ($this->anneeScolaireModel->isUsed($id)) {
                 $_SESSION['error_message'] = $this->translate('cannot_delete_year_with_configs');
                 $this->redirect('anneesscolaires');
                 return;
            }

            if ($this->anneeScolaireModel->delete($id)) {
                $_SESSION['message'] = $this->translate('academic_year_deleted_successfully');
            } else {
                 if(empty($_SESSION['error_message'])) { // Le modèle peut avoir défini un message plus précis
                    $_SESSION['error_message'] = $this->translate('error_deleting_academic_year');
                 }
            }
            $this->redirect('anneesscolaires');
        // } else {
        //     $this->redirect('anneesscolaires');
        // }
    }

    public function activate($id) {
        $id = (int)$id;
        $annee = $this->anneeScolaireModel->getById($id);
        if (!$annee) {
            $_SESSION['error_message'] = $this->translate('academic_year_not_found');
        } elseif ($this->anneeScolaireModel->setActive($id)) {
            $_SESSION['message'] = $this->translate('academic_year_activated_successfully', ['libelle' => $annee->libelle]);
        } else {
            $_SESSION['error_message'] = $this->translate('error_activating_academic_year');
        }
        $this->redirect('anneesscolaires');
    }
}
?>
