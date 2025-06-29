<?php

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }

    public function index() {
        $data = [
            'title' => $this->translate('dashboard'),
            'welcome_message' => $this->translate('welcome_to_dashboard', ['username' => $_SESSION['username'] ?? 'Utilisateur'])
        ];
        $this->view('dashboard/index', $data);
    }
}

// Traductions à ajouter:
// fr.php: 'welcome_to_dashboard' => 'Bienvenue sur votre tableau de bord, :username !'
// ar.php: 'welcome_to_dashboard' => 'مرحباً بك في لوحة التحكم الخاصة بك، :username!'
?>
