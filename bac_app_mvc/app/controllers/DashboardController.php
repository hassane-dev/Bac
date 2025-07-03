<?php

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied'); // Message d'accès refusé
            $this->redirect('auth/login');
        }
    }

    public function index() {
        // Vérifier une permission spécifique pour accéder au dashboard si nécessaire
        // if (!$this->userHasPermission('access_dashboard')) { // 'access_dashboard' est un exemple d'accréditation
        //     $_SESSION['error_message'] = $this->translate('access_denied_dashboard');
        //     $this->redirect('auth/login'); // Ou une autre page si déjà loggué mais sans droits
        //     return;
        // }

        $data = [
            'title' => $this->translate('dashboard'),
            'welcome_message' => $this->translate('welcome_to_dashboard', ['username' => $_SESSION['username'] ?? 'Utilisateur'])
        ];
        $this->view('dashboard/index', $data);
    }
}
?>
