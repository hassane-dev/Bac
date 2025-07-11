<?php

class RolesController extends Controller {
    private $roleModel;
    private $accreditationModel;

    public function __construct() {
        parent::__construct();
        $this->roleModel = $this->model('RoleModel');
        $this->accreditationModel = $this->model('AccreditationModel'); // Needed for assigning accreditations

        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
        // Permission pour gérer les rôles globalement. Des permissions plus fines peuvent être ajoutées par méthode.
        // if (!$this->userHasPermission('manage_roles')) {
        //     $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
        //     $this->redirect('dashboard/index');
        // }
    }

    public function index() {
        if (!$this->userHasPermission('view_roles') && !$this->userHasPermission('manage_roles')) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
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
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
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
                $data['nom_role_err'] = $this->translate('role_name_taken'); // Assurez-vous que 'role_name_taken' est dans les fichiers de langue
            }

            if (empty($data['nom_role_err'])) {
                $new_role_id = $this->roleModel->create($data['nom_role']);
                if ($new_role_id) {
                    if ($this->roleModel->updateAccreditations($new_role_id, $data['assigned_accreditations'])) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'message' => $this->translate('role_added_successfully')];
                        $this->redirect('roles/index');
                    } else {
                        // Le rôle a été créé, mais les accréditations ont échoué. Situation délicate.
                        // On pourrait supprimer le rôle créé ou laisser l'admin corriger.
                        // Pour l'instant, message d'erreur général.
                         $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('error_adding_role') . ' (accreditations)'];
                         // Il faudrait recharger les données pour le formulaire
                         $this->view('roles/add', $data);
                    }
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('error_adding_role')];
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
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
            $this->redirect('roles/index');
        }

        if (is_null($id) || !($role = $this->roleModel->getById((int)$id))) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('role_not_found')];
            $this->redirect('roles/index');
            return;
        }

        // Admin role (ID 1) cannot be edited for its accreditations here (all perms by default)
        // Name can be edited though.
        $is_admin_role = ($role->id == 1);


        $all_accreditations = $this->accreditationModel->getAll();
        $assigned_accreditations_ids = $is_admin_role ? [] : $this->roleModel->getAccreditationIds($role->id);
        // If admin role, effectively all accreditations are assigned, but we don't manage them via checkboxes here.
        // For display, we can show all checked and disabled if it's admin role.

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

            $data['nom_role'] = $new_nom_role; // Update for re-display
            if (!$is_admin_role) {
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
                $accreditations_updated = true; // Assume true if no changes needed or if successful

                if (!$is_admin_role) { // Accreditations for admin role are not managed here
                    $accreditations_updated = $this->roleModel->updateAccreditations($data['id'], $new_assigned_accreditations);
                }

                if ($role_updated && $accreditations_updated) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => $this->translate('role_updated_successfully')];
                    $this->redirect('roles/index');
                } else {
                     $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('error_updating_role_or_accreditations')];
                     $this->view('roles/edit', $data); // Re-render with current (potentially partially saved) data
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
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
             $this->redirect('roles/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $role_id_to_delete = (int)$_POST['id_to_delete'];

            $role = $this->roleModel->getById($role_id_to_delete);
            if (!$role) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('role_not_found')];
                $this->redirect('roles/index');
                return;
            }

            // Prevent deletion of admin role (ID 1) or other critical roles if needed
            if ($role->id == 1) { // Assuming 1 is the main admin role
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('cannot_delete_admin_role')];
                $this->redirect('roles/index');
                return;
            }

            if ($this->roleModel->delete($role_id_to_delete)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => $this->translate('role_deleted_successfully')];
            } else {
                // RoleModel::delete() returns false if users are assigned or DB error
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('error_deleting_role') . $this->translate('role_may_be_in_use') ]; // Add role_may_be_in_use to lang
            }
            $this->redirect('roles/index');
        } else {
            $this->redirect('roles/index');
        }
    }
}
?>
