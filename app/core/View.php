<?php

class View {
    /**
     * Rend une vue, potentiellement en utilisant un layout.
     * @param string $viewPath Chemin vers la vue depuis le dossier app/views/ (ex: 'users/index').
     * @param array $data Données à extraire et à rendre disponibles pour la vue et le layout.
     */
    public static function render($viewPath, $data = []) {
        // Extraire les données pour les rendre accessibles par leur nom de clé dans la vue et le layout.
        extract($data);

        $viewFile = APP_ROOT . '/app/views/' . $viewPath . '.php';
        $layoutFile = APP_ROOT . '/app/views/layouts/main.php'; // Layout par défaut

        if (file_exists($viewFile)) {
            if (file_exists($layoutFile) && (!isset($data['use_layout']) || $data['use_layout'] !== false)) {
                // Si un layout est utilisé:
                // 1. Capturer le contenu de la vue spécifique.
                ob_start();
                require $viewFile;
                $content_for_layout = ob_get_clean();

                // 2. Inclure le layout, qui utilisera $content_for_layout et $data.
                require $layoutFile;
            } else {
                // Si pas de layout (soit non trouvé, soit $data['use_layout'] est false), rendre la vue directement.
                require $viewFile;
            }
        } else {
            // Gérer l'erreur : le fichier de vue n'existe pas.
            // Ne pas appeler self::renderError ici pour éviter une boucle infinie si la vue d'erreur elle-même est manquante.
            $show_errors = (defined('SHOW_ERRORS') && SHOW_ERRORS);
            $errorMessage = "Fichier de vue introuvable: " . htmlspecialchars($viewFile);
            error_log($errorMessage); // Toujours logger
            if ($show_errors) {
                die($errorMessage);
            } else {
                // En production, on pourrait vouloir afficher une page 404 plus générique ou rediriger.
                // Pour l'instant, si SHOW_ERRORS est false, on ne montre rien ou on pourrait appeler une fonction d'erreur très basique.
                http_response_code(404);
                die("Page non trouvée.");
            }
        }
    }

    /**
     * Redirige vers une autre URL au sein de l'application ou une URL absolue.
     * @param string $urlSegment Segment d'URL (ex: 'users/index') ou URL complète.
     */
    public static function redirect($urlSegment) {
        if (!preg_match('/^https?:\/\//', $urlSegment)) {
            // S'assurer que APP_URL est défini.
            $app_url_base = defined('APP_URL') ? APP_URL : '';
            $finalUrl = $app_url_base . '/' . ltrim($urlSegment, '/');
            // Nettoyer les doubles slashes potentiels, sauf pour le http(s)://
            $finalUrl = preg_replace_callback('/^([^:]+:\/\/)|(\/+)/', function ($matches) {
                return $matches[1] ?? '/'; // Conserve le protocole ou remplace les slashes multiples par un seul
            }, $finalUrl);
        } else {
            $finalUrl = $urlSegment;
        }

        header('Location: ' . $finalUrl);
        exit;
    }

    /**
     * Affiche une page d'erreur standardisée.
     * @param string $message Message d'erreur principal.
     * @param int $statusCode Code de statut HTTP (par défaut 500).
     * @param string $detailsMessage Message de détails techniques (affiché si SHOW_ERRORS est true).
     */
    public static function renderError($message, $statusCode = 500, $detailsMessage = '') {
        http_response_code($statusCode);

        $show_details = (defined('SHOW_ERRORS') && SHOW_ERRORS);
        $page_title = "Erreur " . $statusCode;

        // Essayer de rendre une vue d'erreur via le système de layout si possible
        // Cela nécessite que le layout et les constantes de base soient disponibles.
        $errorViewPath = APP_ROOT . '/app/views/errors/error_page.php'; // Vue d'erreur générique
        $layoutFile = APP_ROOT . '/app/views/layouts/main.php';

        if (file_exists($errorViewPath) && file_exists($layoutFile) && defined('APP_ROOT') && defined('APP_URL') && defined('DEFAULT_LANG')) {
            $error_data = [
                'page_title' => $page_title,
                'error_code' => $statusCode,
                'error_message_user' => $message, // Message pour l'utilisateur
                'error_message_dev' => $show_details ? ($detailsMessage ?: $message) : '', // Message pour le dev
                'show_details' => $show_details,
                // Simuler les données que le layout attendrait
                'current_lang' => $_SESSION['lang'] ?? DEFAULT_LANG,
                'isLoggedIn' => isset($_SESSION['user_id']), // Peut ne pas être fiable si la session est le problème
                'current_username' => $_SESSION['username'] ?? '',
                'tr' => function($key, $params = []) use ($message, $statusCode) {
                    if ($key === 'error_message_default') return $message;
                    if ($key === 'error_code_text') return "Erreur " . $statusCode;
                    return $key;
                },
                'use_layout' => true // Forcer l'utilisation du layout si on passe par View::render
            ];
            // Pour utiliser le layout, on doit appeler View::render
            // Mais attention à ne pas créer une boucle si View::render appelle View::renderError
            // Donc, on va faire une inclusion directe du layout ici, en simplifiant.
            extract($error_data);
            ob_start();
            require $errorViewPath; // La vue error_page.php utilisera les variables $error_code, $error_message_user, etc.
            $content_for_layout = ob_get_clean();
            require $layoutFile;

        } else {
            // Fallback vers un affichage HTML simple si le template d'erreur n'est pas disponible
            echo "<!DOCTYPE html><html lang=\"fr\"><head><meta charset=\"UTF-8\"><title>" . htmlspecialchars($page_title) . "</title>";
            echo "<style>body { font-family: sans-serif; padding: 20px; } .error-container { max-width: 700px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; background: #f9f9f9; } h1 { color: #d9534f; }</style>";
            echo "</head><body>";
            echo "<div class='error-container'>";
            echo "<h1>" . htmlspecialchars($page_title) . "</h1>";
            echo "<p>" . htmlspecialchars($message) . "</p>";
            if ($show_details && !empty($detailsMessage)) {
                echo "<hr><h4>Détails techniques :</h4><pre>" . htmlspecialchars($detailsMessage) . "</pre>";
            } elseif ($show_details && empty($detailsMessage)) {
                 echo "<hr><h4>Détails techniques :</h4><pre>" . htmlspecialchars($message) . "</pre>"; // Si pas de detailsMessage, afficher message original
            }
            echo "</div></body></html>";
        }
        exit(); // Arrêter l'exécution après avoir affiché l'erreur.
    }
}
?>
