<?php

// Point d'entrée de l'application
session_start();

// Charger la configuration
require_once '../config.php';

// Charger les classes Core
require_once '../app/core/Database.php';
require_once '../app/core/Controller.php';
require_once '../app/core/View.php';
require_once '../app/core/Router.php';


// Initialiser le routeur et dispatcher la requête
$router = new Router();
$router->dispatch();

?>
