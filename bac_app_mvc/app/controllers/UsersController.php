<?php

class UsersController extends Controller {
    private $userModel;
    private $roleModel;
    private $uploadDir = 'uploads/user_photos/'; // Relatif à APP_ROOT/public/

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Affiner la vérification des permissions
        // if (!$this->userHasPermission('manage_users')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->userModel = $this->model('User');
        $this->roleModel = $this->model('Role');

        // Créer le dossier d'upload s'il n'existe pas
        if (!is_dir(APP_ROOT . '/public/' . $this->uploadDir)) {
            mkdir(APP_ROOT . '/public/' . $this->uploadDir, 0755, true);
        }
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
            'username' => '', 'mot_de_passe' => '', 'confirm_mot_de_passe' => '', 'nom' => '', 'prenom' => '',
            'date_naissance' => '', 'lieu_naissance' => '', 'sexe' => 'M', 'role_id' => '',
            'matricule' => '', 'telephone' => '', 'email' => '', 'is_active' => true,
            'username_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => '',
            'nom_err' => '', 'prenom_err' => '', 'date_naissance_err' => '',
            'lieu_naissance_err' => '', 'sexe_err' => '', 'role_id_err' => '', 'email_err' => '', 'photo_err' => ''
        ];
        $this->view('users/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $roles = $this->roleModel->getAll();
            $data = [
                'username' => trim($_POST['username']),
                'mot_de_passe' => trim($_POST['mot_de_passe']),
                'confirm_mot_de_passe' => trim($_POST['confirm_mot_de_passe']),
                'role_id' => $_POST['role_id'] ?? '',
                'nom' => trim($_POST['nom']),
                'prenom' => trim($_POST['prenom']),
                'date_naissance' => $_POST['date_naissance'],
                'lieu_naissance' => trim($_POST['lieu_naissance']),
                'sexe' => $_POST['sexe'] ?? 'M',
                'photo' => null,
                'matricule' => trim($_POST['matricule'] ?? null),
                'telephone' => trim($_POST['telephone'] ?? null),
                'email' => trim($_POST['email'] ?? null),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'roles' => $roles,
                'title' => $this->translate('add_user'),
                'username_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => '',
                'role_id_err' => '', 'nom_err' => '', 'prenom_err' => '', 'date_naissance_err' => '',
                'lieu_naissance_err' => '', 'sexe_err' => '', 'email_err' => '', 'photo_err' => ''
            ];

            // Validations (simplifiées, à étendre)
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

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $data['email_err'] = $this->translate('invalid_email_format');
            elseif (!empty($data['email']) && $this->userModel->emailExists($data['email'])) $data['email_err'] = $this->translate('email_taken');

            // Gestion de l'upload de la photo
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
                $uploadResult = $this->handleUpload('photo', 'user_');
                if ($uploadResult['success']) {
                    $data['photo'] = $uploadResult['path'];
                } else {
                    $data['photo_err'] = $uploadResult['message'];
                }
            }

            if (empty($data['username_err']) && empty($data['mot_de_passe_err']) && empty($data['confirm_mot_de_passe_err']) &&
                empty($data['role_id_err']) && empty($data['nom_err']) && empty($data['prenom_err']) &&
                empty($data['date_naissance_err']) && empty($data['lieu_naissance_err']) && empty($data['email_err']) && empty($data['photo_err'])) {

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
        $id = (int)$id;
        $user = $this->userModel->getByIdWithRole($id);
        $roles = $this->roleModel->getAll();

        if (!$user) {
            $_SESSION['error_message'] = $this->translate('user_not_found');
            $this->redirect('users');
            return;
        }

        $data = [
            'id' => $id,
            'username' => $user->username, 'role_id' => $user->role_id, 'nom' => $user->nom, 'prenom' => $user->prenom,
            'date_naissance' => $user->date_naissance, 'lieu_naissance' => $user->lieu_naissance, 'sexe' => $user->sexe,
            'photo' => $user->photo, 'matricule' => $user->matricule, 'telephone' => $user->telephone, 'email' => $user->email,
            'is_active' => (bool)$user->is_active, 'roles' => $roles, 'title' => $this->translate('edit_user'),
            'username_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => '', 'role_id_err' => '',
            'nom_err' => '', 'prenom_err' => '', 'date_naissance_err' => '', 'lieu_naissance_err' => '', 'sexe_err' => '', 'email_err' => '', 'photo_err' => ''
        ];
        $this->view('users/edit', $data);
    }

    public function update($id) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $currentUserData = $this->userModel->getByIdWithRole($id);
            if (!$currentUserData) {
                $_SESSION['error_message'] = $this->translate('user_not_found');
                $this->redirect('users');
                return;
            }

            $roles = $this->roleModel->getAll();
            $data = [
                'id' => $id,
                'username' => trim($_POST['username']),
                'mot_de_passe' => trim($_POST['mot_de_passe']),
                'confirm_mot_de_passe' => trim($_POST['confirm_mot_de_passe']),
                'role_id' => $_POST['role_id'] ?? '',
                'nom' => trim($_POST['nom']),
                'prenom' => trim($_POST['prenom']),
                'date_naissance' => $_POST['date_naissance'],
                'lieu_naissance' => trim($_POST['lieu_naissance']),
                'sexe' => $_POST['sexe'] ?? 'M',
                'photo' => $currentUserData->photo, // Conserver l'ancienne photo par défaut
                'matricule' => trim($_POST['matricule'] ?? null),
                'telephone' => trim($_POST['telephone'] ?? null),
                'email' => trim($_POST['email'] ?? null),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'roles' => $roles, 'title' => $this->translate('edit_user'),
                'username_err' => '', 'mot_de_passe_err' => '', 'confirm_mot_de_passe_err' => '', 'role_id_err' => '',
                'nom_err' => '', 'prenom_err' => '', 'date_naissance_err' => '', 'lieu_naissance_err' => '', 'sexe_err' => '', 'email_err' => '', 'photo_err' => ''
            ];
             // Empêcher la désactivation de l'admin principal ou de soi-même
            if ($id == 1 || (isset($_SESSION['user_id']) && $id == $_SESSION['user_id'])) {
                $data['is_active'] = 1; // Forcer à actif
            }
            if ($id == 1) { // Empêcher le changement de rôle pour l'admin ID 1
                 $data['role_id'] = $currentUserData->role_id;
            }


            if (empty($data['username'])) $data['username_err'] = $this->translate('username_required');
            elseif ($this->userModel->usernameExists($data['username'], $id)) $data['username_err'] = $this->translate('username_taken');

            if (!empty($data['mot_de_passe'])) {
                if (strlen($data['mot_de_passe']) < 6) $data['mot_de_passe_err'] = $this->translate('password_min_length', ['length' => 6]);
                if ($data['mot_de_passe'] != $data['confirm_mot_de_passe']) $data['confirm_mot_de_passe_err'] = $this->translate('passwords_do_not_match');
            } else {
                unset($data['mot_de_passe']); // Ne pas mettre à jour le mdp s'il est vide
            }

            if (empty($data['role_id'])) $data['role_id_err'] = $this->translate('role_required');
            // ... autres validations ...
             if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $data['email_err'] = $this->translate('invalid_email_format');
            elseif (!empty($data['email']) && $this->userModel->emailExists($data['email'], $id)) $data['email_err'] = $this->translate('email_taken');

            // Gestion de l'upload de la photo
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
                $uploadResult = $this->handleUpload('photo', 'user_', $currentUserData->photo);
                if ($uploadResult['success']) {
                    $data['photo'] = $uploadResult['path'];
                } else {
                    $data['photo_err'] = $uploadResult['message'];
                }
            }


            if (empty($data['username_err']) && empty($data['mot_de_passe_err']) && empty($data['confirm_mot_de_passe_err']) &&
                empty($data['role_id_err']) && empty($data['nom_err']) && empty($data['email_err']) && empty($data['photo_err']) /* ... */) {

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
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Sécurité
            if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
                 $_SESSION['error_message'] = $this->translate('cannot_delete_current_user');
                 $this->redirect('users');
                 return;
            }
             if ($id == 1) { // Admin principal
                 $_SESSION['error_message'] = $this->translate('cannot_delete_main_admin');
                 $this->redirect('users');
                 return;
            }

            $user = $this->userModel->getByIdWithRole($id);
            if (!$user) {
                 $_SESSION['error_message'] = $this->translate('user_not_found');
                 $this->redirect('users');
                 return;
            }

            if ($this->userModel->delete($id)) {
                // Supprimer l'ancienne photo si elle existe
                if ($user->photo && file_exists(APP_ROOT . '/public/' . $user->photo)) {
                    unlink(APP_ROOT . '/public/' . $user->photo);
                }
                $_SESSION['message'] = $this->translate('user_deleted_successfully');
            } else {
                 if(empty($_SESSION['error_message'])){ // Le modèle peut avoir défini un message plus précis
                    $_SESSION['error_message'] = $this->translate('error_deleting_user');
                 }
            }
            $this->redirect('users');
        // } else {
        //     $this->redirect('users');
        // }
    }

    private function handleUpload($fileInputName, $prefix, $currentFilePath = null) {
        $targetDir = APP_ROOT . '/public/' . $this->uploadDir;
        $fileName = uniqid($prefix) . '_' . basename($_FILES[$fileInputName]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES[$fileInputName]["tmp_name"]);
        if ($check === false) return ['success' => false, 'message' => $this->translate('file_not_image')];
        if ($_FILES[$fileInputName]["size"] > 2000000) return ['success' => false, 'message' => $this->translate('file_too_large', ['size' => '2MB'])]; // Limite à 2MB
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) return ['success' => false, 'message' => $this->translate('unsupported_file_type_image')]; // Message plus spécifique

        // Supprimer l'ancien fichier s'il existe et qu'un nouveau est uploadé
        if ($currentFilePath && file_exists(APP_ROOT . '/public/' . $currentFilePath)) {
             if (strpos($currentFilePath, $this->uploadDir) === 0) { // S'assurer qu'on ne supprime que dans le dossier d'upload
                unlink(APP_ROOT . '/public/' . $currentFilePath);
             }
        }

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
            return ['success' => true, 'path' => $this->uploadDir . $fileName]; // Stocker le chemin relatif à public/
        }
        return ['success' => false, 'message' => $this->translate('error_during_upload')];
    }
}
?>
