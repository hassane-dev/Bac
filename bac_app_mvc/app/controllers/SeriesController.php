<?php

class SeriesController extends Controller {
    private $serieModel;
    private $matiereModel; // Pour charger toutes les matières

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Permissions
        // if (!$this->userHasPermission('manage_series')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->serieModel = $this->model('Serie');
        $this->matiereModel = $this->model('Matiere'); // Initialiser le modèle Matiere
    }

    public function index() {
        $series = $this->serieModel->getAll();
        $this->view('series/index', ['series' => $series, 'title' => $this->translate('series_list')]);
    }

    public function create() {
        $data = [
            'title' => $this->translate('add_serie'),
            'code' => '',
            'libelle' => '',
            'code_err' => '',
            'libelle_err' => ''
        ];
        $this->view('series/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'code' => strtoupper(trim($_POST['code'])),
                'libelle' => trim($_POST['libelle']),
                'title' => $this->translate('add_serie'),
                'code_err' => '',
                'libelle_err' => ''
            ];

            if (empty($data['code'])) $data['code_err'] = $this->translate('series_code_required');
            elseif ($this->serieModel->codeExists($data['code'])) $data['code_err'] = $this->translate('series_code_taken');

            if (empty($data['libelle'])) $data['libelle_err'] = $this->translate('series_label_required');

            if (empty($data['code_err']) && empty($data['libelle_err'])) {
                if ($this->serieModel->add($data)) {
                    $_SESSION['message'] = $this->translate('serie_added_successfully');
                    $this->redirect('series');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_serie');
                    $this->view('series/create', $data);
                }
            } else {
                $this->view('series/create', $data);
            }
        } else {
            $this->redirect('series/create');
        }
    }

    public function edit($id) {
        $id = (int)$id;
        $serie = $this->serieModel->getById($id);

        if (!$serie) {
            $_SESSION['error_message'] = $this->translate('serie_not_found');
            $this->redirect('series');
            return;
        }

        $allMatieres = $this->matiereModel->getAll();
        $serieMatieresDetails = $this->serieModel->getMatieresAssociees($id);

        $data = [
            'id' => $id,
            'code' => $serie->code,
            'libelle' => $serie->libelle,
            'title' => $this->translate('edit_serie') . ' : ' . $serie->code,
            'code_err' => '',
            'libelle_err' => '',
            'all_matieres' => $allMatieres,
            'serie_matieres_details' => $serieMatieresDetails
        ];
        $this->view('series/edit', $data);
    }

    public function update($id) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Récupérer les détails des matières soumises
            $matieres_details_input = [];
            if (isset($_POST['matieres']) && is_array($_POST['matieres'])) {
                foreach ($_POST['matieres'] as $matiere_input) {
                    if (!empty($matiere_input['matiere_id']) && isset($matiere_input['coefficient'])) {
                        $matieres_details_input[] = [
                            'matiere_id' => (int)$matiere_input['matiere_id'],
                            'coefficient' => (float)$matiere_input['coefficient'],
                            'obligatoire' => isset($matiere_input['obligatoire']) ? 1 : 0
                        ];
                    }
                }
            }

            $data = [
                'id' => $id,
                'code' => strtoupper(trim($_POST['code'])),
                'libelle' => trim($_POST['libelle']),
                'title' => $this->translate('edit_serie'), // Sera réutilisé si la vue est rechargée
                'code_err' => '',
                'libelle_err' => '',
                'matieres_details_input' => $matieres_details_input // Pour la sauvegarde
            ];

            // Re-populer pour la vue en cas d'erreur
            $serieForView = $this->serieModel->getById($id);
            $data['all_matieres'] = $this->matiereModel->getAll();
            // Si la validation échoue, on veut réafficher les données soumises pour les matières, pas celles de la BDD
            // Donc, nous allons construire $serie_matieres_details pour la vue à partir de $matieres_details_input
            $data['serie_matieres_details'] = [];
            foreach($matieres_details_input as $m_input){
                $matiereInfo = $this->matiereModel->getById($m_input['matiere_id']);
                if($matiereInfo){
                    $data['serie_matieres_details'][] = (object)[
                        'matiere_id' => $m_input['matiere_id'],
                        'matiere_code' => $matiereInfo->code,
                        'matiere_nom' => $matiereInfo->nom,
                        'coefficient' => $m_input['coefficient'],
                        'obligatoire' => $m_input['obligatoire']
                    ];
                }
            }


            if (empty($data['code'])) $data['code_err'] = $this->translate('series_code_required');
            elseif ($this->serieModel->codeExists($data['code'], $id)) $data['code_err'] = $this->translate('series_code_taken');

            if (empty($data['libelle'])) $data['libelle_err'] = $this->translate('series_label_required');

            if (empty($data['code_err']) && empty($data['libelle_err'])) {
                // Gérer la transaction pour s'assurer que les deux opérations (update serie, update series_matieres) réussissent ou échouent ensemble.
                // $this->serieModel->getDbInstance()->beginTransaction(); // Supposant que getDbInstance() retourne l'objet Database qui a beginTransaction()

                $serieUpdateSuccess = $this->serieModel->update($id, ['code' => $data['code'], 'libelle' => $data['libelle']]);

                if ($serieUpdateSuccess) {
                    $matieresAssocieesSuccess = $this->serieModel->updateMatieresAssociees($id, $data['matieres_details_input']);
                    if ($matieresAssocieesSuccess) {
                        // $this->serieModel->getDbInstance()->commit();
                        $_SESSION['message'] = $this->translate('serie_updated_successfully');
                        $this->redirect('series');
                    } else {
                        // $this->serieModel->getDbInstance()->rollBack();
                        $_SESSION['error_message'] = $this->translate('error_updating_associated_subjects');
                        // $data est déjà peuplé avec les valeurs soumises pour réaffichage
                        $this->view('series/edit', $data);
                    }
                } else {
                    // $this->serieModel->getDbInstance()->rollBack(); // Inutile si la première opération échoue avant la transaction
                    $_SESSION['error_message'] = $this->translate('error_updating_serie');
                    $this->view('series/edit', $data);
                }
            } else {
                // $data est déjà peuplé avec les valeurs soumises et les erreurs pour réaffichage
                // $data['serie_matieres_details'] a été reconstruit plus haut avec les valeurs soumises.
                $this->view('series/edit', $data);
            }
        } else {
            $this->redirect('series');
        }
    }

    public function delete($id) {
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Sécurité
            $serie = $this->serieModel->getById($id);
            if (!$serie) {
                $_SESSION['error_message'] = $this->translate('serie_not_found');
                $this->redirect('series');
                return;
            }

            if ($this->serieModel->delete($id)) {
                $_SESSION['message'] = $this->translate('serie_deleted_successfully');
            } else {
                // Le message d'erreur est souvent déjà dans la session via le modèle
                if (empty($_SESSION['error_message'])) {
                     $_SESSION['error_message'] = $this->translate('error_deleting_serie');
                }
            }
            $this->redirect('series');
        // } else {
        //     $this->redirect('series');
        // }
    }
}
?>
