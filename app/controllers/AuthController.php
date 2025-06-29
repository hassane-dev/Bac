<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    /**
     * Affiche le formulaire de connexion.
     * Si l'utilisateur est déjà connecté, redirige vers le tableau de bord.
     */
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard'); // 'dashboard' sera une route/contrôleur à créer
        }

        $data = [
            'username' => '',
            'mot_de_passe' => '',
            'username_err' => '',
            'mot_de_passe_err' => '',
            'title' => $this->translate('login')
        ];

        $this->view('auth/login', $data);
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function authenticate() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'username' => trim($_POST['username']),
                'mot_de_passe' => trim($_POST['mot_de_passe']),
                'username_err' => '',
                'mot_de_passe_err' => '',
                'title' => $this->translate('login')
            ];

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
                        $this->redirect('dashboard'); // Rediriger vers le tableau de bord après connexion
                    }
                } else {
                    $data['username_err'] = $this->translate('login_failed'); // Erreur générique
                    $this->view('auth/login', $data);
                }
            } else {
                $this->view('auth/login', $data);
            }
        } else {
            $this->redirect('auth/login');
        }
    }

    /**
     * Crée la session utilisateur.
     * @param object $user L'objet utilisateur
     */
    private function createUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['user_role_id'] = $user->role_id;
        // Vous pourriez vouloir charger le nom du rôle et les accréditations ici aussi
        // $role = $this->model('Role')->getById($user->role_id);
        // $_SESSION['user_role_name'] = $role ? $role->nom_role : 'Unknown';
        // $_SESSION['user_accreditations'] = array_map(function($acc){ return $acc->libelle_action; }, $this->model('Role')->getAccreditations($user->role_id));

        // Mettre à jour la dernière connexion
        // $this->userModel->updateLastLogin($user->id); // Méthode à ajouter dans UserModel
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_role_id']);
        // unset($_SESSION['user_role_name']);
        // unset($_SESSION['user_accreditations']);
        session_destroy();
        $this->redirect('auth/login');
    }

    /**
     * Vérifie si l'utilisateur est connecté.
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?>
