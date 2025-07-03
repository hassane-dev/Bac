<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard/index');
        }

        $data = [
            'username' => '',
            'mot_de_passe' => '',
            'username_err' => '',
            'mot_de_passe_err' => '',
            'title' => $this->translate('login')
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data['username'] = trim($_POST['username']);
            $data['mot_de_passe'] = trim($_POST['mot_de_passe']);

            if (empty($data['username'])) {
                $data['username_err'] = $this->translate('username_required');
            }
            if (empty($data['mot_de_passe'])) {
                $data['mot_de_passe_err'] = $this->translate('password_required');
            }

            if (empty($data['username_err']) && empty($data['mot_de_passe_err'])) {
                $loggedInUser = $this->userModel->getByUsername($data['username']);

                if ($loggedInUser && password_verify($data['mot_de_passe'], $loggedInUser->mot_de_passe)) {
                    if (!$loggedInUser->is_active) {
                        $data['username_err'] = $this->translate('user_account_inactive');
                        $this->view('auth/login', $data);
                    } else {
                        $this->createUserSession($loggedInUser);
                        $this->userModel->updateLastLogin($loggedInUser->id); // Mettre à jour la dernière connexion
                        $this->redirect('dashboard/index');
                    }
                } else {
                    $data['username_err'] = $this->translate('login_failed');
                    $this->view('auth/login', $data);
                }
            } else {
                $this->view('auth/login', $data);
            }
        } else {
            // Afficher le formulaire de connexion (GET request)
            $this->view('auth/login', $data);
        }
    }

    private function createUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['user_role_id'] = $user->role_id;

        // Charger et stocker les accréditations du rôle en session
        $roleModel = $this->model('Role'); // Assurez-vous que le modèle Role est chargé
        if ($roleModel) {
            $accreditationsObjects = $roleModel->getAccreditations($user->role_id);
            $_SESSION['user_accreditations'] = array_map(function($acc) { return $acc->libelle_action; }, $accreditationsObjects);
        } else {
            $_SESSION['user_accreditations'] = [];
        }
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_role_id']);
        unset($_SESSION['user_accreditations']); // Ne pas oublier de supprimer aussi
        session_destroy();
        $this->redirect('auth/login');
    }

    // isLoggedIn() est déjà dans Controller.php
}
?>
