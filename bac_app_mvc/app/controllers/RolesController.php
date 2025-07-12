<?php

class RolesController extends Controller {
    private $roleModel;
    private $accreditationModel;

    public function __construct() {
        parent::__construct();
        $this->roleModel = $this->model('RoleModel');
        $this->accreditationModel = $this->model('AccreditationModel');

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'access_denied_pleaselogin');
            $this->redirect('auth/login');
        }
    }

    public function index() {
        if (!$this->userHasPermission('view_roles') && !$this->userHasPermission('manage_roles')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('dashboard/index');
        }

        $roles = $this->roleModel->getAll();
        $data = [
            'page_title' => $this->translate('role_list'),
            'roles' => $roles
        ];
        $this->view('roles/index', $data);
    }

    public function add() {
        if (!$this->userHasPermission('manage_roles')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('roles/index');
        }

        $all_accreditations = $this->accreditationModel->getAll();
        $data = [
            'page_title' => $this->translate('add_new_role'),
            'nom_role' => '',
            'assigned_accreditations' => [],
            'all_accreditations' => $all_accreditations,
            'nom_role_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data['nom_role'] = trim($_POST['nom_role']);
            $data['assigned_accreditations'] = isset($_POST['accreditations']) ? array_map('intval', $_POST['accreditations']) : [];

            if (empty($data['nom_role'])) {
                $data['nom_role_err'] = $this->translate('please_enter_role_name');
            } elseif ($this->roleModel->findByNomRole($data['nom_role'])) {
                $data['nom_role_err'] = $this->translate('role_name_taken');
            }

            if (empty($data['nom_role_err'])) {
                $new_role_id = $this->roleModel->create($data['nom_role']);
                if ($new_role_id) {
                    if ($this->roleModel->updateAccreditations($new_role_id, $data['assigned_accreditations'])) {
                        $this->setFlashMessage('success', 'role_added_successfully');
                        $this->redirect('roles/index');
                    } else {
                         $this->setFlashMessage('error', 'error_adding_role_accreditations'); // Nouvelle clé
                         $this->view('roles/add', $data);
                    }
                } else {
                    $this->setFlashMessage('error', 'error_adding_role');
                    $this->view('roles/add', $data);
                }
            } else {
                $this->view('roles/add', $data);
            }
        } else {
            $this->view('roles/add', $data);
        }
    }

    public function edit($id = null) {
        if (!$this->userHasPermission('manage_roles')) {
            $this->setFlashMessage('error', 'access_denied');
            $this->redirect('roles/index');
        }

        if (is_null($id) || !($role = $this->roleModel->getById((int)$id))) {
            $this->setFlashMessage('error', 'role_not_found');
            $this->redirect('roles/index');
            return;
        }

        $is_admin_role = ($role->id == 1); // ID 1 est l'admin principal

        $all_accreditations = $this->accreditationModel->getAll();
        $assigned_accreditations_ids = $is_admin_role ? array_map(function($acc){ return $acc->id; }, $all_accreditations) : $this->roleModel->getAccreditationIds($role->id);


        $data = [
            'page_title' => $this->translate('edit_role') . ': ' . htmlspecialchars($role->nom_role),
            'id' => $role->id,
            'nom_role' => $role->nom_role,
            'assigned_accreditations' => $assigned_accreditations_ids,
            'all_accreditations' => $all_accreditations,
            'is_admin_role' => $is_admin_role,
            'nom_role_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $new_nom_role = trim($_POST['nom_role']);
            $new_assigned_accreditations = isset($_POST['accreditations']) ? array_map('intval', $_POST['accreditations']) : [];

            $data['nom_role'] = $new_nom_role;
            if (!$is_admin_role) { // Ne pas mettre à jour les accréditations assignées pour l'admin depuis le POST
                $data['assigned_accreditations'] = $new_assigned_accreditations;
            }


            if (empty($new_nom_role)) {
                $data['nom_role_err'] = $this->translate('please_enter_role_name');
            } else {
                if (strtolower($new_nom_role) !== strtolower($role->nom_role)) {
                    if ($this->roleModel->findByNomRole($new_nom_role)) {
                        $data['nom_role_err'] = $this->translate('role_name_taken');
                    }
                }
            }

            if (empty($data['nom_role_err'])) {
                $role_updated = $this->roleModel->update($data['id'], $new_nom_role);
                $accreditations_updated = true;

                if (!$is_admin_role) {
                    $accreditations_updated = $this->roleModel->updateAccreditations($data['id'], $new_assigned_accreditations);
                }

                if ($role_updated && $accreditations_updated) {
                    $this->setFlashMessage('success', 'role_updated_successfully');
                    $this->redirect('roles/index');
                } else {
                     $this->setFlashMessage('error', 'error_updating_role_or_accreditations');
                     $this->view('roles/edit', $data);
                }
            } else {
                $this->view('roles/edit', $data);
            }
        } else {
            $this->view('roles/edit', $data);
        }
    }

    public function delete($id = null) {
        if (!$this->userHasPermission('manage_roles')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('roles/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['id_to_delete'])) {
                 $this->setFlashMessage('error', 'invalid_request');
                 $this->redirect('roles/index');
                 return;
            }
            $role_id_to_delete = (int)$_POST['id_to_delete'];

            $role = $this->roleModel->getById($role_id_to_delete);
            if (!$role) {
                $this->setFlashMessage('error', 'role_not_found');
                $this->redirect('roles/index');
                return;
            }

            if ($role->id == 1) {
                $this->setFlashMessage('error', 'cannot_delete_admin_role');
                $this->redirect('roles/index');
                return;
            }

            if ($this->roleModel->delete($role_id_to_delete)) {
                $this->setFlashMessage('success', 'role_deleted_successfully');
            } else {
                $this->setFlashMessage('error', 'error_deleting_role_in_use'); // Nouvelle clé: error_deleting_role_in_use
            }
            $this->redirect('roles/index');
        } else {
            $this->redirect('roles/index');
        }
    }
}
?>
