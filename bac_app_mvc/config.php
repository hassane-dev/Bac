<?php

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'bac_app'); // Nom de la base de données comme spécifié dans bac_app.sql
define('DB_USER', 'root');    // Utilisateur par défaut pour XAMPP/Laragon
define('DB_PASS', '');        // Mot de passe par défaut pour XAMPP/Laragon

// Racine de l'application
// Utile pour construire des URLs absolues et des chemins de fichiers.
// Détecte automatiquement si l'application est dans un sous-dossier.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME']; // ex: /bac_app_mvc/public/index.php

// Retire /public/index.php pour obtenir la base de l'URL de l'application
$app_base_path = str_replace('/public/index.php', '', $script_name);
define('APP_URL', $protocol . $host . $app_base_path);

// Chemin absolu vers la racine du projet sur le serveur
define('APP_ROOT', dirname(__FILE__));

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
