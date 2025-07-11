<?php

class UsersController extends Controller {
    private $userModel;
    private $roleModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('UserModel');
        $this->roleModel = $this->model('RoleModel');

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', $this->translate('access_denied_pleaselogin')); //  Nécessite 'access_denied_pleaselogin' dans lang
            $this->redirect('auth/login');
        }
        // Global permission for user management
        // if (!$this->userHasPermission('manage_users')) {
        //     $this->setFlashMessage('error', $this->translate('access_denied'));
        //     $this->redirect('dashboard/index');
        // }
    }

    public function index() {
        if (!$this->userHasPermission('view_users') && !$this->userHasPermission('manage_users')) {
             $this->setFlashMessage('error', $this->translate('access_denied'));
             $this->redirect('dashboard/index');
        }

        $users = $this->userModel->getAll();
        $data = [
            'page_title' => $this->translate('user_list'),
            'users' => $users
        ];
        $this->view('users/index', $data);
    }

    public function add() {
        if (!$this->userHasPermission('manage_users')) {
             $this->setFlashMessage('error', $this->translate('access_denied'));
             $this->redirect('users/index');
        }

        $roles = $this->roleModel->getAll();
        $data = [
            'page_title' => $this->translate('add_new_user'),
            'roles' => $roles,
            'username' => '', 'nom' => '', 'prenom' => '', 'email' => '',
            'date_naissance' => '', 'lieu_naissance' => '', 'sexe' => 'M',
            'role_id' => '', 'mot_de_passe' => '', 'confirm_mot_de_passe' => '',
            'is_active' => true, 'matricule' => '', 'telephone' => '',
            'username_err' => '', 'nom_err' => '', 'prenom_err' => '', 'email_err' => '',
            'date_naissance_err' => '', 'lieu_naissance_err' => '', 'sexe_err' => '',
            'role_id_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = array_merge($data, $_POST);
            $data['is_active'] = isset($_POST['is_active']); // Checkbox

            // Validation
            if (empty($data['username'])) $data['username_err'] = $this->translate('username_required');
            elseif ($this->userModel->findByUsername($data['username'])) $data['username_err'] = $this->translate('username_taken');

            if (empty($data['nom'])) $data['nom_err'] = $this->translate('lastname_required');
            if (empty($data['prenom'])) $data['prenom_err'] = $this->translate('firstname_required');

            if (empty($data['mot_de_passe'])) $data['mot_de_passe_err'] = $this->translate('password_required');
            elseif (strlen($data['mot_de_passe']) < 6) $data['mot_de_passe_err'] = $this->translate('password_min_length', [':length' => 6]);

            if (empty($data['confirm_mot_de_passe'])) $data['confirm_mot_de_passe_err'] = $this->translate('confirm_password_required');
            elseif ($data['mot_de_passe'] != $data['confirm_mot_de_passe']) $data['confirm_mot_de_passe_err'] = $this->translate('passwords_do_not_match');

            if (empty($data['role_id'])) $data['role_id_err'] = $this->translate('role_required');
            if (empty($data['date_naissance'])) $data['date_naissance_err'] = $this->translate('dob_required');
            if (empty($data['lieu_naissance'])) $data['lieu_naissance_err'] = $this->translate('pob_required');
            if (empty($data['sexe'])) $data['sexe_err'] = $this->translate('gender_required');

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = $this->translate('invalid_email_format');
            } elseif (!empty($data['email']) && $this->userModel->findByEmail($data['email'])) {
                $data['email_err'] = $this->translate('email_taken');
            }

            // Check if all errors are empty
            $all_errors = array_filter($data, function($key) { return str_ends_with($key, '_err') && !empty($this_data_val = $GLOBALS['data_temp_holder'][$key]); }, ARRAY_FILTER_USE_KEY);
            // Hack to pass $data to the closure
            $GLOBALS['data_temp_holder'] = $data;
            $all_errors = array_filter($data, function($key) use ($data) {
                 return str_ends_with($key, '_err') && !empty($data[$key]);
            }, ARRAY_FILTER_USE_KEY);
            unset($GLOBALS['data_temp_holder']);


            if (empty($all_errors)) {
                $user_data_to_create = [
                    'username' => $data['username'], 'mot_de_passe' => $data['mot_de_passe'],
                    'role_id' => (int)$data['role_id'], 'nom' => $data['nom'], 'prenom' => $data['prenom'],
                    'date_naissance' => $data['date_naissance'], 'lieu_naissance' => $data['lieu_naissance'],
                    'sexe' => $data['sexe'], 'email' => $data['email'] ?? null,
                    'is_active' => $data['is_active'],
                    'matricule' => $data['matricule'] ?? null, 'telephone' => $data['telephone'] ?? null,
                    'photo' => null // Gérer l'upload de photo plus tard
                ];

                if ($this->userModel->create($user_data_to_create)) {
                    $this->setFlashMessage('success', $this->translate('user_added_successfully'));
                    $this->redirect('users/index');
                } else {
                    $this->setFlashMessage('error', $this->translate('error_adding_user'));
                    $this->view('users/add', $data); // Re-render with data and general error
                }
            } else {
                $this->view('users/add', $data); // Re-render with validation errors
            }
        } else {
            $this->view('users/add', $data); // Display empty form
        }
    }

    public function edit($id = null) {
        if (!$this->userHasPermission('manage_users')) {
             $this->setFlashMessage('error', $this->translate('access_denied'));
             $this->redirect('users/index');
        }

        if (is_null($id) || !($user = $this->userModel->getById((int)$id))) {
            $this->setFlashMessage('error', $this->translate('user_not_found'));
            $this->redirect('users/index');
            return;
        }

        $roles = $this->roleModel->getAll();
        $data = [
            'page_title' => $this->translate('edit_user') . ': ' . htmlspecialchars($user->username),
            'roles' => $roles,
            'id' => $user->id,
            'username' => $user->username, 'nom' => $user->nom, 'prenom' => $user->prenom,
            'email' => $user->email ?? '', 'date_naissance' => $user->date_naissance,
            'lieu_naissance' => $user->lieu_naissance, 'sexe' => $user->sexe,
            'role_id' => $user->role_id, 'is_active' => (bool)$user->is_active,
            'matricule' => $user->matricule ?? '', 'telephone' => $user->telephone ?? '',
            'mot_de_passe' => '', 'confirm_mot_de_passe' => '', // Password fields empty by default for edit
            'username_err' => '', 'nom_err' => '', 'prenom_err' => '', 'email_err' => '',
            'date_naissance_err' => '', 'lieu_naissance_err' => '', 'sexe_err' => '',
            'role_id_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = array_merge($data, $_POST); // Merge POST data, keeping original values for fields not in POST
            $data['id'] = $id; // Ensure ID is preserved
            $data['is_active'] = isset($_POST['is_active']);
            // Password fields are intentionally not sticky from POST for security/UX
            $data['mot_de_passe'] = $_POST['mot_de_passe'] ?? '';
            $data['confirm_mot_de_passe'] = $_POST['confirm_mot_de_passe'] ?? '';


            // Validation (similar to add, but conditional for username/email/password)
            if (empty($data['username'])) $data['username_err'] = $this->translate('username_required');
            elseif (strtolower($data['username']) !== strtolower($user->username) && $this->userModel->findByUsername($data['username'])) {
                $data['username_err'] = $this->translate('username_taken');
            }

            if (empty($data['nom'])) $data['nom_err'] = $this->translate('lastname_required');
            if (empty($data['prenom'])) $data['prenom_err'] = $this->translate('firstname_required');

            if (!empty($data['mot_de_passe'])) { // Validate password only if a new one is entered
                if (strlen($data['mot_de_passe']) < 6) $data['mot_de_passe_err'] = $this->translate('password_min_length', [':length' => 6]);
                if ($data['mot_de_passe'] != $data['confirm_mot_de_passe']) $data['confirm_mot_de_passe_err'] = $this->translate('passwords_do_not_match');
            } elseif (!empty($data['confirm_mot_de_passe']) && empty($data['mot_de_passe'])) {
                 $data['mot_de_passe_err'] = $this->translate('password_required_if_confirm'); // Add to lang
            }


            if (empty($data['role_id'])) $data['role_id_err'] = $this->translate('role_required');
            if (empty($data['date_naissance'])) $data['date_naissance_err'] = $this->translate('dob_required');
            if (empty($data['lieu_naissance'])) $data['lieu_naissance_err'] = $this->translate('pob_required');
            if (empty($data['sexe'])) $data['sexe_err'] = $this->translate('gender_required');

            if (!empty($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $data['email_err'] = $this->translate('invalid_email_format');
                } elseif (strtolower($data['email']) !== strtolower($user->email ?? '') && $this->userModel->findByEmail($data['email'])) {
                    $data['email_err'] = $this->translate('email_taken');
                }
            }

            // Check if all errors are empty
            $GLOBALS['data_temp_holder'] = $data; // Hack for closure scope
            $all_errors = array_filter($data, function($key) use ($data) {
                 return str_ends_with($key, '_err') && !empty($data[$key]);
            }, ARRAY_FILTER_USE_KEY);
            unset($GLOBALS['data_temp_holder']);

            if (empty($all_errors)) {
                $user_data_to_update = [
                    'username' => $data['username'],
                    'role_id' => (int)$data['role_id'], 'nom' => $data['nom'], 'prenom' => $data['prenom'],
                    'date_naissance' => $data['date_naissance'], 'lieu_naissance' => $data['lieu_naissance'],
                    'sexe' => $data['sexe'], 'email' => $data['email'] ?? null,
                    'is_active' => $data['is_active'],
                    'matricule' => $data['matricule'] ?? null, 'telephone' => $data['telephone'] ?? null,
                    'photo' => $user->photo // Photo upload not handled yet, keep existing
                ];
                if (!empty($data['mot_de_passe'])) {
                    $user_data_to_update['mot_de_passe'] = $data['mot_de_passe'];
                }

                if ($this->userModel->update($id, $user_data_to_update)) {
                    $this->setFlashMessage('success', $this->translate('user_updated_successfully'));
                    $this->redirect('users/index');
                } else {
                    $this->setFlashMessage('error', $this->translate('error_updating_user'));
                    // Re-fetch user data in case some partial update occurred or to ensure fresh state
                    $current_user_state = $this->userModel->getById((int)$id);
                    $data['username'] = $current_user_state->username; // etc. for all relevant fields
                    $this->view('users/edit', $data);
                }
            } else {
                $this->view('users/edit', $data); // Re-render with validation errors
            }

        } else {
            $this->view('users/edit', $data); // Display pre-filled form
        }
    }

    public function delete($id = null) {
        if (!$this->userHasPermission('manage_users')) {
             $this->setFlashMessage('error', $this->translate('access_denied'));
             $this->redirect('users/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id_to_delete = (int)$_POST['id_to_delete'];

            $user_to_delete = $this->userModel->getById($user_id_to_delete);
            if (!$user_to_delete) {
                $this->setFlashMessage('error', $this->translate('user_not_found'));
                $this->redirect('users/index');
                return;
            }

            // Prevent deletion of admin user (ID 1) or currently logged-in user
            if ($user_to_delete->id == 1) {
                $this->setFlashMessage('error', $this->translate('cannot_delete_main_admin'));
                $this->redirect('users/index');
                return;
            }
            if ($user_to_delete->id == ($_SESSION['user_id'] ?? null)) {
                 $this->setFlashMessage('error', $this->translate('cannot_delete_current_user'));
                 $this->redirect('users/index');
                 return;
            }


            if ($this->userModel->delete($user_id_to_delete)) {
                $this->setFlashMessage('success', $this->translate('user_deleted_successfully'));
            } else {
                $this->setFlashMessage('error', $this->translate('error_deleting_user'));
            }
            $this->redirect('users/index');
        } else {
            $this->redirect('users/index');
        }
    }
}
?>
