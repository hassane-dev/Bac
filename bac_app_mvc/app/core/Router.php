<?php

class Router {
    protected $currentController = 'HomeController'; // Contrôleur par défaut
    protected $currentMethod = 'index';       // Méthode par défaut
    protected $params = [];                   // Paramètres de l'URL

    public function __construct() {
        // Le constructeur est vide, l'analyse de l'URL et le dispatching se font dans la méthode dispatch.
    }

    /**
     * Parse l'URL pour extraire le contrôleur, la méthode et les paramètres.
     * L'URL est récupérée via le paramètre 'url' passé par .htaccess depuis public/.htaccess.
     * Exemple: monsite.com/roles/edit/1 -> $url = ['roles', 'edit', '1']
     */
    protected function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }

    /**
     * Dispatch la requête vers le contrôleur et la méthode appropriés.
     * Cette méthode est appelée depuis public/index.php.
     */
    public function dispatch() {
        $url = $this->parseUrl();

        // Déterminer le contrôleur
        // $url[0] correspond au nom du contrôleur (ex: 'roles' pour 'RolesController')
        if (!empty($url[0])) {
            $controllerName = ucwords(strtolower($url[0])) . 'Controller';
            // Utiliser APP_ROOT défini dans config.php pour le chemin des fichiers
            $controllerFile = APP_ROOT . '/app/controllers/' . $controllerName . '.php';

            if (file_exists($controllerFile)) {
                $this->currentController = $controllerName;
                unset($url[0]);
            } else {
                // Contrôleur non trouvé
                $this->serve404("Contrôleur '$controllerName' non trouvé (Fichier: $controllerFile).");
                return;
            }
        } else {
            // Pas de contrôleur dans l'URL, on utilise HomeController par défaut
            $controllerFile = APP_ROOT . '/app/controllers/' . $this->currentController . '.php';
             if (!file_exists($controllerFile)) {
                 $this->serve404("Contrôleur par défaut '{$this->currentController}' non trouvé.");
                 return;
            }
        }

        require_once $controllerFile;

        if (!class_exists($this->currentController)) {
            $this->serve404("Classe contrôleur '{$this->currentController}' non définie dans '$controllerFile'.");
            return;
        }
        // Instancier le contrôleur
        $this->currentController = new $this->currentController;

        // Déterminer la méthode
        // $url[1] correspond à la méthode (ex: 'edit')
        if (isset($url[1])) {
            $methodName = strtolower($url[1]);
            if (method_exists($this->currentController, $methodName)) {
                $this->currentMethod = $methodName;
                unset($url[1]);
            } else {
                // Méthode non trouvée
                $controllerClassName = get_class($this->currentController);
                $this->serve404("Méthode '$methodName' non trouvée dans le contrôleur '$controllerClassName'.");
                return;
            }
        }

        // Récupérer les paramètres restants de l'URL
        $this->params = $url ? array_values($url) : [];

        // Appel de la méthode du contrôleur avec les paramètres
        try {
            call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
        } catch (ArgumentCountError $e) {
            $controllerClassName = get_class($this->currentController);
            $this->serve404("Nombre incorrect de paramètres pour la méthode '{$this->currentMethod}' dans le contrôleur '$controllerClassName'. Erreur: " . $e->getMessage());
        } catch (Error $e) { // Capture plus large pour d'autres types d'erreurs fatales.
            $controllerClassName = get_class($this->currentController);
            $this->serve404("Erreur lors de l'appel de '{$this->currentMethod}' dans '$controllerClassName'. Erreur: " . $e->getMessage());
        }
    }

    /**
     * Affiche une page d'erreur 404 simple.
     * @param string $message Message d'erreur spécifique.
     */
    protected function serve404($message = "Page non trouvée.") {
        http_response_code(404);
        // Idéalement, charger une vue d'erreur dédiée.
        // require_once APP_ROOT . '/app/views/errors/404.php';
        // ou via un ErrorController
        // $errorController = new ErrorController(); // S'assurer qu'il est chargé
        // $errorController->notFound($message);
        echo "<h1>Erreur 404</h1>";
        echo "<p>" . htmlspecialchars($message) . "</p>";
        // exit(); // Important pour arrêter l'exécution du script après une erreur 404
    }
}
?>
