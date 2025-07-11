<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('UserModel');
    }

    public function login() {
        // Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard/index'); // Assumer que DashboardController::index() sera la page d'accueil post-connexion
        }

        $data = [
            'page_title' => $this->translate('login'),
            'username' => '',
            'mot_de_passe' => '',
            'username_err' => '',
            'mot_de_passe_err' => '',
            'login_err' => '' // Pour les erreurs générales de connexion (ex: compte inactif)
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data['username'] = trim($_POST['username']);
            $data['mot_de_passe'] = trim($_POST['mot_de_passe']);

            // Validation simple
            if (empty($data['username'])) {
                $data['username_err'] = $this->translate('username_required');
            }
            if (empty($data['mot_de_passe'])) {
                $data['mot_de_passe_err'] = $this->translate('password_required');
            }

            if (empty($data['username_err']) && empty($data['mot_de_passe_err'])) {
                $loggedInUser = $this->userModel->findByUsername($data['username']);

                if ($loggedInUser) {
                    if (password_verify($data['mot_de_passe'], $loggedInUser->mot_de_passe)) {
                        if ($loggedInUser->is_active) {
                            // Le mot de passe est correct et l'utilisateur est actif
                            $this->storeLoginSession($loggedInUser);
                            $this->userModel->updateLastLogin($loggedInUser->id); // Mettre à jour la date de dernière connexion
                            $this->setFlashMessage('success', $this->translate('logged_in_successfully')); // Ajouter 'logged_in_successfully' aux lang
                            $this->redirect('dashboard/index'); // Rediriger vers le tableau de bord
                        } else {
                            // L'utilisateur est inactif
                            $data['login_err'] = $this->translate('user_account_inactive');
                            $this->view('auth/login', $data);
                        }
                    } else {
                        // Mot de passe incorrect
                        $data['login_err'] = $this->translate('login_failed');
                        $this->view('auth/login', $data);
                    }
                } else {
                    // Utilisateur non trouvé
                    $data['login_err'] = $this->translate('login_failed');
                    $this->view('auth/login', $data);
                }
            } else {
                // Erreurs de validation des champs
                $this->view('auth/login', $data);
            }
        } else {
            // Afficher le formulaire de connexion (GET request)
            $this->view('auth/login', $data);
        }
    }

    public function logout() {
        $this->clearLoginSession();
        $this->setFlashMessage('success', $this->translate('logged_out_successfully'));
        $this->redirect('auth/login');
    }
}
?>
