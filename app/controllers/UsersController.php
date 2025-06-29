<?php

class UsersController extends Controller {
    private $userModel;
    private $roleModel;

    public function __construct() {
        parent::__construct();
        // TODO: Mettre en place la vérification de session et de permissions
        // if (!isset($_SESSION['user_id'])) {
        //     $this->redirect('auth/login');
        // }
        // if (!$this->userHasPermission('manage_users')) { // Méthode à créer
        //    $this->redirect('dashboard');
        // }
        $this->userModel = $this->model('User');
        $this->roleModel = $this->model('Role'); // Nécessaire pour lister les rôles dans les formulaires
    }

    public function index() {
        $users = $this->userModel->getAllWithRoles();
        $this->view('users/index', ['users' => $users, 'title' => $this->translate('user_list')]);
    }

    public function create() {
        $roles = $this->roleModel->getAll();
        $data = [
            'roles' => $roles,
            'title' => $this->translate('add_user'),
            // Initialiser les autres champs pour éviter les erreurs undefined dans la vue
            'username' => '', 'nom' => '', 'prenom' => '', 'date_naissance' => '',
            'lieu_naissance' => '', 'sexe' => 'M', 'role_id' => '', 'matricule' => '',
            'telephone' => '', 'email' => '', 'photo' => '', 'is_active' => true,
            'username_err' => '', 'mot_de_passe_err' => '', 'nom_err' => '', 'prenom_err' => '',
            'date_naissance_err' => '', 'lieu_naissance_err' => '', 'sexe_err' => '', 'role_id_err' => '', 'email_err' => ''
        ];
        $this->view('users/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            // TODO: Gérer l'upload de la photo de profil

            $roles = $this->roleModel->getAll();
            $data = [
                'username' => trim($_POST['username']),
                'mot_de_passe' => trim($_POST['mot_de_passe']),
                'confirm_mot_de_passe' => trim($_POST['confirm_mot_de_passe']),
                'role_id' => $_POST['role_id'],
                'nom' => trim($_POST['nom']),
                'prenom' => trim($_POST['prenom']),
                'date_naissance' => $_POST['date_naissance'],
                'lieu_naissance' => trim($_POST['lieu_naissance']),
                'sexe' => $_POST['sexe'],
                'photo' => null, // À gérer avec l'upload
                'matricule' => trim($_POST['matricule'] ?? null),
                'telephone' => trim($_POST['telephone'] ?? null),
                'email' => trim($_POST['email'] ?? null),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'roles' => $roles,
                'title' => $this->translate('add_user'),
                'username_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => '',
                'role_id_err' => '', 'nom_err' => '', 'prenom_err' => '', 'date_naissance_err' => '',
                'lieu_naissance_err' => '', 'sexe_err' => '', 'email_err' => ''
            ];

            // Validation
            if (empty($data['username'])) $data['username_err'] = $this->translate('username_required');
            elseif ($this->userModel->usernameExists($data['username'])) $data['username_err'] = $this->translate('username_taken');

            if (empty($data['mot_de_passe'])) $data['mot_de_passe_err'] = $this->translate('password_required');
            elseif (strlen($data['mot_de_passe']) < 6) $data['mot_de_passe_err'] = $this->translate('password_min_length', ['length' => 6]);

            if (empty($data['confirm_mot_de_passe'])) $data['confirm_mot_de_passe_err'] = $this->translate('confirm_password_required');
            elseif ($data['mot_de_passe'] != $data['confirm_mot_de_passe']) $data['confirm_mot_de_passe_err'] = $this->translate('passwords_do_not_match');

            if (empty($data['role_id'])) $data['role_id_err'] = $this->translate('role_required');
            if (empty($data['nom'])) $data['nom_err'] = $this->translate('lastname_required');
            if (empty($data['prenom'])) $data['prenom_err'] = $this->translate('firstname_required');
            if (empty($data['date_naissance'])) $data['date_naissance_err'] = $this->translate('dob_required');
            if (empty($data['lieu_naissance'])) $data['lieu_naissance_err'] = $this->translate('pob_required');
            if (empty($data['sexe'])) $data['sexe_err'] = $this->translate('gender_required');

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = $this->translate('invalid_email_format');
            } elseif (!empty($data['email']) && $this->userModel->emailExists($data['email'])) {
                $data['email_err'] = $this->translate('email_taken');
            }

            if (empty($data['username_err']) && empty($data['mot_de_passe_err']) && empty($data['confirm_mot_de_passe_err']) &&
                empty($data['role_id_err']) && empty($data['nom_err']) && empty($data['prenom_err']) &&
                empty($data['date_naissance_err']) && empty($data['lieu_naissance_err']) && empty($data['sexe_err']) && empty($data['email_err'])) {

                // TODO: Gérer l'upload de la photo ici et mettre à jour $data['photo'] avec le chemin du fichier

                if ($this->userModel->add($data)) {
                    $_SESSION['message'] = $this->translate('user_added_successfully');
                    $this->redirect('users');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_user');
                    $this->view('users/create', $data);
                }
            } else {
                $this->view('users/create', $data);
            }
        } else {
            $this->redirect('users/create');
        }
    }

    public function edit($id) {
        $user = $this->userModel->getByIdWithRole($id);
        $roles = $this->roleModel->getAll();

        if (!$user) {
            $_SESSION['error_message'] = $this->translate('user_not_found');
            $this->redirect('users');
            return;
        }

        $data = [
            'id' => $id,
            'username' => $user->username,
            'role_id' => $user->role_id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'date_naissance' => $user->date_naissance,
            'lieu_naissance' => $user->lieu_naissance,
            'sexe' => $user->sexe,
            'photo' => $user->photo,
            'matricule' => $user->matricule,
            'telephone' => $user->telephone,
            'email' => $user->email,
            'is_active' => (bool)$user->is_active,
            'roles' => $roles,
            'title' => $this->translate('edit_user'),
            'username_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => '',
            'role_id_err' => '', 'nom_err' => '', 'prenom_err' => '', 'date_naissance_err' => '',
            'lieu_naissance_err' => '', 'sexe_err' => '', 'email_err' => ''
        ];
        $this->view('users/edit', $data);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            // TODO: Gérer l'upload de la photo

            $user = $this->userModel->getByIdWithRole($id); // Pour la photo actuelle si non changée
            $roles = $this->roleModel->getAll();
            $data = [
                'id' => $id,
                'username' => trim($_POST['username']),
                'mot_de_passe' => trim($_POST['mot_de_passe']), // Peut être vide si non modifié
                'confirm_mot_de_passe' => trim($_POST['confirm_mot_de_passe']),
                'role_id' => $_POST['role_id'],
                'nom' => trim($_POST['nom']),
                'prenom' => trim($_POST['prenom']),
                'date_naissance' => $_POST['date_naissance'],
                'lieu_naissance' => trim($_POST['lieu_naissance']),
                'sexe' => $_POST['sexe'],
                'photo' => $user->photo, // Valeur par défaut, à écraser si nouvelle photo uploadée
                'matricule' => trim($_POST['matricule'] ?? null),
                'telephone' => trim($_POST['telephone'] ?? null),
                'email' => trim($_POST['email'] ?? null),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'roles' => $roles,
                'title' => $this->translate('edit_user'),
                'username_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => '',
                'role_id_err' => '', 'nom_err' => '', 'prenom_err' => '', 'date_naissance_err' => '',
                'lieu_naissance_err' => '', 'sexe_err' => '', 'email_err' => ''
            ];

            // Validation
            if (empty($data['username'])) $data['username_err'] = $this->translate('username_required');
            elseif ($this->userModel->usernameExists($data['username'], $id)) $data['username_err'] = $this->translate('username_taken');

            if (!empty($data['mot_de_passe'])) {
                if (strlen($data['mot_de_passe']) < 6) $data['mot_de_passe_err'] = $this->translate('password_min_length', ['length' => 6]);
                if ($data['mot_de_passe'] != $data['confirm_mot_de_passe']) $data['confirm_mot_de_passe_err'] = $this->translate('passwords_do_not_match');
            } else {
                // Si le mot de passe est vide, on ne le met pas à jour, donc pas besoin de validation de confirmation
                unset($data['mot_de_passe']); // Important pour le modèle
            }

            if (empty($data['role_id'])) $data['role_id_err'] = $this->translate('role_required');
            if (empty($data['nom'])) $data['nom_err'] = $this->translate('lastname_required');
            // ... autres validations ...
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = $this->translate('invalid_email_format');
            } elseif (!empty($data['email']) && $this->userModel->emailExists($data['email'], $id)) {
                $data['email_err'] = $this->translate('email_taken');
            }


            if (empty($data['username_err']) && empty($data['mot_de_passe_err']) && empty($data['confirm_mot_de_passe_err']) &&
                empty($data['role_id_err']) && empty($data['nom_err'])  && empty($data['email_err']) /* ... et autres ... */) {

                // TODO: Gérer l'upload de la photo ici et mettre à jour $data['photo']

                if ($this->userModel->update($id, $data)) {
                    $_SESSION['message'] = $this->translate('user_updated_successfully');
                    $this->redirect('users');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_user');
                    $this->view('users/edit', $data);
                }
            } else {
                $this->view('users/edit', $data);
            }
        } else {
            $this->redirect('users');
        }
    }

    public function delete($id) {
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($id == $_SESSION['user_id']) { // Empêcher l'auto-suppression
                 $_SESSION['error_message'] = $this->translate('cannot_delete_current_user');
                 $this->redirect('users');
                 return;
            }
             if ($id == 1) { // Empêcher la suppression de l'admin principal
                 $_SESSION['error_message'] = $this->translate('cannot_delete_main_admin');
                 $this->redirect('users');
                 return;
            }

            if ($this->userModel->delete($id)) {
                $_SESSION['message'] = $this->translate('user_deleted_successfully');
            } else {
                $_SESSION['error_message'] = $this->translate('error_deleting_user');
            }
            $this->redirect('users');
        // } else {
        //     $this->redirect('users');
        // }
    }
}
?>
