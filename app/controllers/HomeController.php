<?php

class HomeController extends Controller {

    public function __construct() {
        parent::__construct();
        // Aucune restriction d'accès spécifique ici, car la méthode index() gère la redirection.
    }

    public function index() {
        if ($this->isLoggedIn()) {
            // Si l'utilisateur est connecté, rediriger vers le tableau de bord.
            // Supposons que le tableau de bord se trouve à 'dashboard/index'.
            // Il faudra créer un DashboardController plus tard.
            $this->redirect('dashboard/index');
        } else {
            // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion.
            $this->redirect('auth/login');
        }
    }

    // Une méthode 'about' simple comme exemple de page publique si nécessaire.
    // public function about() {
    //     $data = [
    //         'page_title' => $this->translate('about_us_title'), // Ajouter 'about_us_title' aux fichiers de langue
    //         'version' => '1.0.0'
    //     ];
    //     $this->view('home/about', $data); // Nécessiterait une vue app/views/home/about.php
    // }
}
?>
