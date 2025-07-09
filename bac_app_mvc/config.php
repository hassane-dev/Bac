<?php

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'bac_app');
define('DB_USER', 'root');
define('DB_PASS', '');

// Racine de l'application
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

$script_path = dirname($_SERVER['SCRIPT_NAME']);
// Si SCRIPT_NAME est /bac_app_mvc/public/index.php, dirname est /bac_app_mvc/public
// Si SCRIPT_NAME est /public/index.php, dirname est /public
// Si SCRIPT_NAME est /index.php (racine du serveur), dirname est /

// On veut que APP_URL soit http://localhost/bac_app_mvc ou http://localhost si à la racine
$app_url_segment = '';
if (basename($script_path) === 'public') {
    $app_url_segment = dirname($script_path);
} else {
    // Cas où l'application pourrait être directement à la racine du docroot et .htaccess redirige
    // ou si la structure est différente, ce cas est moins probable avec la structure cible.
    $app_url_segment = $script_path;
}

// Normaliser le segment pour éviter les doubles slashs ou les slashs de fin inutiles
$app_url_segment = rtrim($app_url_segment, '/');
if ($app_url_segment === '\\') { // Cas où dirname retourne juste '\' sur Windows si à la racine
    $app_url_segment = '';
}


define('APP_URL', $protocol . $host . $app_url_segment);


// Chemin absolu vers la racine du projet sur le serveur (bac_app_mvc)
define('APP_ROOT', __DIR__); // __DIR__ dans config.php pointe vers bac_app_mvc/

// Paramètres linguistiques par défaut
define('DEFAULT_LANG', 'fr');
define('AVAILABLE_LANGS', ['fr', 'ar']);

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
