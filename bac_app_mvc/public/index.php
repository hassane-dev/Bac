<?php

// Point d'entrée de l'application
session_start();

// Charger la configuration
require_once '../config.php';

// Charger le routeur
require_once '../app/core/Router.php';

// Initialiser le routeur et dispatcher la requête
$router = new Router();
$router->dispatch();

// echo "Bienvenue sur l'application de gestion du Baccalauréat !";
// Le message d'accueil sera géré par HomeController et sa vue.

?>
