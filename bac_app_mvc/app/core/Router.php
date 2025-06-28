<?php

class Router {
    protected $controller = 'HomeController'; // Contrôleur par défaut
    protected $method = 'index'; // Méthode par défaut
    protected $params = []; // Paramètres par défaut

    public function __construct() {
        $url = $this->parseUrl();

        // Vérifier si le contrôleur existe
        if (isset($url[0]) && file_exists('../app/controllers/' . ucwords($url[0]) . 'Controller.php')) {
            $this->controller = ucwords($url[0]) . 'Controller';
            unset($url[0]);
        } elseif (isset($url[0])) {
            // Gérer le cas où le contrôleur n'existe pas (ex: page 404)
            // Pour l'instant, on garde le contrôleur par défaut ou on pourrait lancer une erreur.
            // echo "Contrôleur " . ucwords($url[0]) . "Controller non trouvé.<br>";
        }

        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Vérifier si la méthode existe dans le contrôleur
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        } elseif (isset($url[1])) {
            // Gérer le cas où la méthode n'existe pas
            // echo "Méthode " . $url[1] . " non trouvée dans le contrôleur.<br>";
        }

        // Récupérer les paramètres
        $this->params = $url ? array_values($url) : [];

        // Appel de la méthode du contrôleur avec les paramètres
        // call_user_func_array([$this->controller, $this->method], $this->params);
        // Cette ligne est déplacée dans la méthode dispatch() pour une meilleure clarté.
    }

    /**
     * Parse l'URL pour extraire le contrôleur, la méthode et les paramètres.
     * L'URL est récupérée via le paramètre 'url' passé par .htaccess.
     * Exemple: /public/users/show/1 -> ['users', 'show', '1']
     */
    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }

    /**
     * Dispatch la requête vers le contrôleur et la méthode appropriés.
     * Cette méthode sera appelée depuis index.php.
     */
    public function dispatch() {
        // S'assurer que le contrôleur, la méthode et les paramètres sont valides
        // avant d'appeler call_user_func_array.
        // Ceci est une simplification ; une gestion d'erreur plus robuste est nécessaire.
        if (method_exists($this->controller, $this->method)) {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } else {
            // Gérer le cas où la méthode n'existe pas (ex: page 404)
            // require_once '../app/controllers/ErrorController.php';
            // $errorController = new ErrorController();
            // $errorController->notFound();
            echo "Erreur 404: Page non trouvée (Méthode {$this->method} introuvable).";
        }
    }
}

// Pour que le routeur fonctionne, il nous faudra un contrôleur par défaut.
// Créons un placeholder pour HomeController.php qui sera créé plus tard.
if (!file_exists('../app/controllers/HomeController.php')) {
    if (!is_dir('../app/controllers')) {
        mkdir('../app/controllers', 0755, true);
    }
    // Création d'un HomeController basique pour éviter les erreurs fatales au début
    $homeControllerContent = "<?php\n\n";
    $homeControllerContent .= "require_once '../app/core/Controller.php';\n\n";
    $homeControllerContent .= "class HomeController extends Controller {\n";
    $homeControllerContent .= "    public function index() {\n";
    $homeControllerContent .= "        // echo 'Bienvenue depuis HomeController!';\n";
    $homeControllerContent .= "        // $this->view('home/index', ['title' => 'Accueil']);\n";
    $homeControllerContent .= "    }\n";
    $homeControllerContent .= "}\n";
    // // file_put_contents('../app/controllers/HomeController.php', $homeControllerContent);
    // // Ce code est maintenant dans Controller.php pour s'assurer que HomeController est créé après Controller.
}

?>
