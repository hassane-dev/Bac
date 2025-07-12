<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('UserModel');
    }

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard/index');
        }

        $data = [
            'page_title' => $this->translate('login'),
            'username' => '',
            'mot_de_passe' => '',
            'username_err' => '',
            'mot_de_passe_err' => '',
            'login_err' => ''
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
                $loggedInUser = $this->userModel->findByUsername($data['username']);

                if ($loggedInUser) {
                    if (password_verify($data['mot_de_passe'], $loggedInUser->mot_de_passe)) {
                        if ($loggedInUser->is_active) {
                            $this->storeLoginSession($loggedInUser);
                            $this->userModel->updateLastLogin($loggedInUser->id);
                            $this->setFlashMessage('success', 'logged_in_successfully');
                            $this->redirect('dashboard/index');
                        } else {
                            $data['login_err'] = $this->translate('user_account_inactive');
                            $this->view('auth/login', $data);
                        }
                    } else {
                        $data['login_err'] = $this->translate('login_failed');
                        $this->view('auth/login', $data);
                    }
                } else {
                    $data['login_err'] = $this->translate('login_failed');
                    $this->view('auth/login', $data);
                }
            } else {
                $this->view('auth/login', $data);
            }
        } else {
            $this->view('auth/login', $data);
        }
    }

    public function logout() {
        $this->clearLoginSession();
        $this->setFlashMessage('success', 'logged_out_successfully');
        $this->redirect('auth/login');
    }
}
?>
