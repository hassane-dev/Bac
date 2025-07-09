<?php
// Point d'entrée de l'application bac_app_mvc/public/index.php

// Démarrer la session au tout début
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Charger la configuration principale
// __DIR__ pointe vers le dossier 'public', donc '../' remonte à 'bac_app_mvc/'
require_once __DIR__ . '/../config.php';

// Charger les classes Core (les chemins sont relatifs à ce fichier ou utilisent APP_ROOT)
// APP_ROOT est défini dans config.php comme étant le dossier bac_app_mvc/
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/View.php';       // View avant Controller car Controller l'utilise
require_once APP_ROOT . '/app/core/Controller.php'; // Controller avant Router car Router l'instancie
require_once APP_ROOT . '/app/core/Router.php';


// Initialiser le routeur et dispatcher la requête
$router = new Router();
$router->dispatch();

?>
