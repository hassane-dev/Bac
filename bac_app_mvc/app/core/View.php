<?php

class View {
    /**
     * Charge un fichier de vue.
     *
     * @param string $view Le nom du fichier de vue (ex: 'home/index')
     * @param array $data Les données à passer à la vue
     */
    public static function render($viewPath, $data = []) {
        // Extraire les données pour les rendre accessibles par leur nom de clé dans la vue
        extract($data);

        // Construire le chemin vers le fichier de vue
        // APP_ROOT est défini dans config.php et pointe vers la racine du projet (bac_app_mvc)
        $viewFile = APP_ROOT . '/app/views/' . $viewPath . '.php';

        if (file_exists($viewFile)) {
            // Optionnel: Inclure un layout/header commun
            // $headerFile = APP_ROOT . '/app/views/layouts/header.php';
            // if (file_exists($headerFile)) {
            //     require_once $headerFile;
            // }

            require_once $viewFile;

            // Optionnel: Inclure un layout/footer commun
            // $footerFile = APP_ROOT . '/app/views/layouts/footer.php';
            // if (file_exists($footerFile)) {
            //     require_once $footerFile;
            // }
        } else {
            // Gérer l'erreur si le fichier de vue n'est pas trouvé
             self::renderError("Le fichier de vue '$viewFile' n'existe pas.");
        }
    }

    /**
     * Redirige vers une URL spécifiée.
     *
     * @param string $urlSegment Le segment d'URL interne (ex: 'users/login') ou URL complète.
     */
    public static function redirect($urlSegment) {
        // Si l'URL ne commence pas par http, on suppose que c'est une route interne.
        if (!preg_match('/^https?:\/\//', $urlSegment)) {
            $finalUrl = APP_URL . '/' . ltrim($urlSegment, '/');
        } else {
            $finalUrl = $urlSegment;
        }
        header('Location: ' . $finalUrl);
        exit;
    }

    /**
     * Affiche une page d'erreur simple.
     * @param string $message
     * @param int $statusCode
     */
    public static function renderError($message, $statusCode = 500) {
        http_response_code($statusCode);
        // Vous pourriez vouloir une vue d'erreur plus élaborée ici
        // require_once APP_ROOT . '/app/views/errors/general_error.php';
        echo "<h1>Erreur</h1>";
        echo "<p>" . htmlspecialchars($message) . "</p>";
        if (SHOW_ERRORS) {
            // Afficher plus de détails en mode développement
            echo "<pre>";
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            echo "</pre>";
        }
        exit();
    }

    // La gestion des traductions a été déplacée dans la classe Controller de base
    // pour un accès plus facile depuis les contrôleurs et pour la passer aux vues.
}
?>
