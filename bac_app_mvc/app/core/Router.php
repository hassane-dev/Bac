<?php

class Router {
    protected $currentController = 'HomeController';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        // Le constructeur est vide, l'analyse de l'URL et le dispatching se font dans la méthode dispatch.
    }

    protected function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }

    public function dispatch() {
        $url = $this->parseUrl();

        if (!empty($url[0])) {
            $controllerName = ucwords(strtolower($url[0])) . 'Controller';
            $controllerFile = APP_ROOT . '/app/controllers/' . $controllerName . '.php';

            if (file_exists($controllerFile)) {
                $this->currentController = $controllerName;
                unset($url[0]);
            } else {
                $this->serve404("Contrôleur '$controllerName' non trouvé.");
                return;
            }
        } else {
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
        $this->currentController = new $this->currentController;

        if (isset($url[1])) {
            $methodName = strtolower($url[1]);
            if (method_exists($this->currentController, $methodName)) {
                $this->currentMethod = $methodName;
                unset($url[1]);
            } else {
                $controllerClassName = get_class($this->currentController);
                $this->serve404("Méthode '$methodName' non trouvée dans le contrôleur '$controllerClassName'.");
                return;
            }
        }

        $this->params = $url ? array_values($url) : [];

        try {
            call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
        } catch (ArgumentCountError $e) {
            $controllerClassName = get_class($this->currentController);
            $this->serve404("Nombre incorrect de paramètres pour la méthode '{$this->currentMethod}' dans le contrôleur '$controllerClassName'. Erreur: " . $e->getMessage());
        } catch (Error $e) {
            $controllerClassName = get_class($this->currentController);
            $this->serve404("Erreur lors de l'appel de '{$this->currentMethod}' dans '$controllerClassName'. Erreur: " . $e->getMessage());
        }
    }

    protected function serve404($message = "Page non trouvée.") {
        http_response_code(404);
        // Vue d'erreur simple, peut être améliorée
        echo "<h1>Erreur 404</h1>";
        echo "<p>" . htmlspecialchars($message) . "</p>";
        if(defined('SHOW_ERRORS') && SHOW_ERRORS){
            echo "<pre>URL demandée: " . htmlspecialchars($_GET['url'] ?? '/') . "</pre>";
        }
        exit();
    }
}
?>
