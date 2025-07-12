<?php

class AccreditationsController extends Controller {
    private $accreditationModel;

    public function __construct() {
        parent::__construct();
        $this->accreditationModel = $this->model('AccreditationModel');

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'access_denied_pleaselogin');
            $this->redirect('auth/login');
        }
    }

    public function index() {
        if (!$this->userHasPermission('view_accreditations') && !$this->userHasPermission('manage_accreditations')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('dashboard/index');
        }

        $accreditations = $this->accreditationModel->getAll();
        $data = [
            'page_title' => $this->translate('accreditation_list'),
            'accreditations' => $accreditations,
        ];
        $this->view('accreditations/index', $data);
    }

    public function add() {
        if (!$this->userHasPermission('manage_accreditations')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('accreditations/index');
        }

        $data = [
            'page_title' => $this->translate('add_new_accreditation'),
            'libelle_action' => '',
            'libelle_action_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data['libelle_action'] = trim($_POST['libelle_action']);

            if (empty($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('please_enter_accreditation_label');
            } elseif ($this->accreditationModel->findByLibelle($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('accreditation_label_taken');
            }

            if (empty($data['libelle_action_err'])) {
                if ($this->accreditationModel->create($data['libelle_action'])) {
                    $this->setFlashMessage('success', 'accreditation_added_successfully');
                    $this->redirect('accreditations/index');
                } else {
                    $this->setFlashMessage('error', 'error_adding_accreditation');
                    $this->view('accreditations/add', $data);
                }
            } else {
                $this->view('accreditations/add', $data);
            }
        } else {
            $this->view('accreditations/add', $data);
        }
    }

    public function edit($id = null) {
        if (!$this->userHasPermission('manage_accreditations')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('accreditations/index');
        }

        if (is_null($id) || !($accreditation = $this->accreditationModel->getById((int)$id))) {
            $this->setFlashMessage('error', 'accreditation_not_found');
            $this->redirect('accreditations/index');
            return;
        }

        $data = [
            'page_title' => $this->translate('edit_accreditation'),
            'id' => $accreditation->id,
            'libelle_action' => $accreditation->libelle_action,
            'libelle_action_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $new_libelle_action = trim($_POST['libelle_action']);

            if (empty($new_libelle_action)) {
                $data['libelle_action_err'] = $this->translate('please_enter_accreditation_label');
            } else {
                if (strtolower($new_libelle_action) !== strtolower($accreditation->libelle_action)) {
                    if ($this->accreditationModel->findByLibelle($new_libelle_action)) {
                        $data['libelle_action_err'] = $this->translate('accreditation_label_taken');
                    }
                }
            }

            $data['libelle_action'] = $new_libelle_action;

            if (empty($data['libelle_action_err'])) {
                if ($this->accreditationModel->update($data['id'], $new_libelle_action)) {
                    $this->setFlashMessage('success', 'accreditation_updated_successfully');
                    $this->redirect('accreditations/index');
                } else {
                    $this->setFlashMessage('error', 'error_updating_accreditation');
                     $this->view('accreditations/edit', $data);
                }
            } else {
                $this->view('accreditations/edit', $data);
            }
        } else {
            $this->view('accreditations/edit', $data);
        }
    }

    public function delete($id = null) {
        if (!$this->userHasPermission('manage_accreditations')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('accreditations/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $accreditation_id = (int)$_POST['id_to_delete'];

            if (!$this->accreditationModel->getById($accreditation_id)) {
                $this->setFlashMessage('error', 'accreditation_not_found');
                $this->redirect('accreditations/index');
                return;
            }

            if ($this->accreditationModel->delete($accreditation_id)) {
                $this->setFlashMessage('success', 'accreditation_deleted_successfully');
            } else {
                $this->setFlashMessage('error', 'error_deleting_accreditation');
            }
            $this->redirect('accreditations/index');
        } else {
            $this->redirect('accreditations/index');
        }
    }
}
?>
