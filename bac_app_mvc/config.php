<?php

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'bac_app');
define('DB_USER', 'root');
define('DB_PASS', '');

// Racine de l'application
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
// SCRIPT_NAME est le chemin du script actuel (index.php dans le dossier public)
// Pour obtenir la base de l'URL de l'application, nous devons retirer '/public/index.php' ou juste '/index.php' si à la racine du docroot
// et ensuite retirer '/public' si l'application est servie depuis bac_app_mvc/public/
// La configuration .htaccess à la racine de bac_app_mvc redirige vers public/,
// donc SCRIPT_NAME pourrait être /bac_app_mvc/public/index.php ou /public/index.php si bac_app_mvc est le docroot.

// Chemin de base de l'application URL-wise.
// Si l'application est à la racine du serveur (ex: http://localhost/public/index.php), SCRIPT_NAME est /index.php (après redirection vers public)
// Si l'application est dans un sous-dossier (ex: http://localhost/mon_projet/public/index.php), SCRIPT_NAME est /mon_projet/public/index.php
// Nous voulons que APP_URL soit http://localhost/ ou http://localhost/mon_projet/
$app_path = dirname($_SERVER['SCRIPT_NAME']); // Devrait donner /bac_app_mvc/public ou /public
if ($app_path === '/public' || substr($app_path, -7) === '/public') {
    $app_path = substr($app_path, 0, -6); // Retire /public pour obtenir la base de l'application
}
if ($app_path === '\\' || $app_path === '/') { // Si à la racine du serveur web
    $app_path = '';
}

define('APP_URL', $protocol . $host . $app_path);


// Chemin absolu vers la racine du projet sur le serveur (bac_app_mvc)
define('APP_ROOT', dirname(__DIR__)); // dirname(__FILE__) dans config.php, puis dirname() pour remonter d'un niveau

// Paramètres linguistiques par défaut
define('DEFAULT_LANG', 'fr');
define('AVAILABLE_LANGS', ['fr', 'ar']); // Doit correspondre aux noms de fichiers dans /lang sans .php

// Affichage des erreurs (à mettre à false en production)
define('SHOW_ERRORS', true);

if (SHOW_ERRORS) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

?>
