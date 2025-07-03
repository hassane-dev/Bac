<?php

class AccreditationsController extends Controller {
    private $accreditationModel;

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Affiner la vérification des permissions, ex:
        // if (!$this->userHasPermission('manage_accreditations')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->accreditationModel = $this->model('Accreditation');
    }

    public function index() {
        $accreditations = $this->accreditationModel->getAll();
        $this->view('accreditations/index', [
            'accreditations' => $accreditations,
            'title' => $this->translate('accreditation_list')
        ]);
    }

    public function create() {
        $data = [
            'title' => $this->translate('add_accreditation'),
            'libelle_action' => '',
            'libelle_action_err' => ''
        ];
        $this->view('accreditations/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'libelle_action' => trim($_POST['libelle_action']),
                'title' => $this->translate('add_accreditation'),
                'libelle_action_err' => ''
            ];

            if (empty($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('please_enter_accreditation_label');
            } elseif ($this->accreditationModel->libelleExists($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('accreditation_label_taken');
            }

            if (empty($data['libelle_action_err'])) {
                if ($this->accreditationModel->add($data)) {
                    $_SESSION['message'] = $this->translate('accreditation_added_successfully');
                    $this->redirect('accreditations');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_accreditation');
                    $this->view('accreditations/create', $data);
                }
            } else {
                $this->view('accreditations/create', $data);
            }
        } else {
            $this->redirect('accreditations/create');
        }
    }

    public function edit($id) {
        $id = (int)$id;
        $accreditation = $this->accreditationModel->getById($id);

        if (!$accreditation) {
            $_SESSION['error_message'] = $this->translate('accreditation_not_found');
            $this->redirect('accreditations');
            return;
        }

        $data = [
            'id' => $id,
            'libelle_action' => $accreditation->libelle_action,
            'title' => $this->translate('edit_accreditation'),
            'libelle_action_err' => ''
        ];
        $this->view('accreditations/edit', $data);
    }

    public function update($id) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $id,
                'libelle_action' => trim($_POST['libelle_action']),
                'title' => $this->translate('edit_accreditation'),
                'libelle_action_err' => ''
            ];

            if (empty($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('please_enter_accreditation_label');
            } elseif ($this->accreditationModel->libelleExists($data['libelle_action'], $id)) {
                $data['libelle_action_err'] = $this->translate('accreditation_label_taken');
            }

            if (empty($data['libelle_action_err'])) {
                if ($this->accreditationModel->update($id, $data)) {
                    $_SESSION['message'] = $this->translate('accreditation_updated_successfully');
                    $this->redirect('accreditations');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_accreditation');
                    // Pour que la valeur originale soit réaffichée en cas d'échec et que l'erreur ne soit pas perdue
                    $originalAccreditation = $this->accreditationModel->getById($id);
                    $data['libelle_action'] = $originalAccreditation->libelle_action;
                    $this->view('accreditations/edit', $data);
                }
            } else {
                // Pour que la valeur soumise (et erronée) soit réaffichée avec l'erreur
                // $data['libelle_action'] est déjà ce que l'utilisateur a soumis.
                $this->view('accreditations/edit', $data);
            }
        } else {
            $this->redirect('accreditations');
        }
    }

    public function delete($id) {
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Sécurité
            $accreditation = $this->accreditationModel->getById($id);
            if (!$accreditation) {
                $_SESSION['error_message'] = $this->translate('accreditation_not_found');
                $this->redirect('accreditations');
                return;
            }

            if ($this->accreditationModel->delete($id)) {
                $_SESSION['message'] = $this->translate('accreditation_deleted_successfully');
            } else {
                 // Le message d'erreur spécifique est défini dans le modèle si la suppression échoue à cause de dépendances.
                if(empty($_SESSION['error_message'])){
                    $_SESSION['error_message'] = $this->translate('error_deleting_accreditation');
                }
            }
            $this->redirect('accreditations');
        // } else {
        //     $this->redirect('accreditations');
        // }
    }
}
?>
