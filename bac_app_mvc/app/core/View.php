<?php

class View {
    public static function render($viewPath, $data = []) {
        extract($data);
        $viewFile = APP_ROOT . '/app/views/' . $viewPath . '.php';

        if (file_exists($viewFile)) {
            // Possibilité d'inclure un layout global ici
            // require_once APP_ROOT . '/app/views/layouts/header.php';
            require_once $viewFile;
            // require_once APP_ROOT . '/app/views/layouts/footer.php';
        } else {
             self::renderError("Fichier de vue '$viewFile' introuvable.");
        }
    }

    public static function redirect($urlSegment) {
        if (!preg_match('/^https?:\/\//', $urlSegment)) {
            $finalUrl = APP_URL . '/' . ltrim($urlSegment, '/');
        } else {
            $finalUrl = $urlSegment;
        }
        header('Location: ' . $finalUrl);
        exit;
    }

    public static function renderError($message, $statusCode = 500) {
        http_response_code($statusCode);
        echo "<h1>Erreur " . $statusCode . "</h1>";
        echo "<p>" . htmlspecialchars($message) . "</p>";
        if (defined('SHOW_ERRORS') && SHOW_ERRORS) {
            echo "<pre>Trace:\n";
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            echo "</pre>";
        }
        exit();
    }
}
?>
