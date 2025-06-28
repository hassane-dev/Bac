<?php

/**
 * Contrôleur de base.
 * Tous les autres contrôleurs hériteront de cette classe.
 */
abstract class Controller {
    protected $db;
    protected static $translations = [];

    public function __construct() {
        // Initialiser la connexion à la base de données pour tous les contrôleurs
        $this->db = new Database();

        // Charger les traductions une seule fois
        if (empty(self::$translations)) {
            self::$translations = $this->loadTranslations();
        }
    }

    /**
     * Charge un modèle.
     *
     * @param string $model Le nom du fichier modèle (ex: 'User')
     * @return object|false L'instance du modèle ou false si non trouvé.
     */
    protected function model($model) {
        $modelFile = APP_ROOT . '/app/models/' . ucwords($model) . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            // Instancier le modèle
            $modelClass = ucwords($model);
            if (class_exists($modelClass)) {
                // Passer l'instance de la base de données au modèle si nécessaire
                return new $modelClass($this->db);
                // return new $modelClass(); // Simplifié pour l'instant
            }
        }
        // Si le modèle n'est pas trouvé, on pourrait lancer une exception ou retourner false.
        // error_log("Modèle '$modelFile' non trouvé.");
        return false;
    }

    /**
     * Charge et affiche une vue.
     *
     * @param string $view Le nom du fichier de vue (ex: 'home/index')
     * @param array $data Les données à passer à la vue
     */
    protected function view($view, $data = []) {
        // Rendre les traductions disponibles pour toutes les vues
        $data['tr'] = function($key, $params = []) {
            return $this->translate($key, $params);
        };
        $data['current_lang'] = $_SESSION['lang'] ?? DEFAULT_LANG;
        $data['app_url'] = APP_URL; // Rendre APP_URL disponible dans les vues

        // Utiliser la classe View pour rendre la vue
        View::render($view, $data);

        // // Temporaire en attendant que View.php soit pleinement fonctionnel et intégré
        // $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        // if (file_exists($viewFile)) {
        //     extract($data);
        //     // require_once APP_ROOT . '/app/views/layouts/header.php'; // Exemple d'inclusion de layout
        //     require_once $viewFile;
        //     // require_once APP_ROOT . '/app/views/layouts/footer.php'; // Exemple d'inclusion de layout
        // } else {
        //      // die("Le fichier de vue '$viewFile' n'existe pas.");
        //      echo "Erreur: Le fichier de vue '$viewFile' n'existe pas.";
        // }
    }

    /**
     * Charge les fichiers de langue.
     * @return array
     */
    private function loadTranslations() {
        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $langFile = APP_ROOT . '/lang/' . $currentLang . '.php';

        if (file_exists($langFile)) {
            return include $langFile;
        }
        // Fallback vers la langue par défaut si le fichier de la langue actuelle n'existe pas
        if ($currentLang !== DEFAULT_LANG) {
            $defaultLangFile = APP_ROOT . '/lang/' . DEFAULT_LANG . '.php';
            if (file_exists($defaultLangFile)) {
                $_SESSION['lang'] = DEFAULT_LANG; // Mettre à jour la session avec la langue de fallback
                return include $defaultLangFile;
            }
        }
        return []; // Retourner un tableau vide si aucun fichier de langue n'est trouvé
    }

    /**
     * Obtient une chaîne de traduction.
     *
     * @param string $key La clé de la chaîne de traduction.
     * @param array $params Paramètres à remplacer dans la chaîne (ex: ['name' => 'John']).
     * @return string La chaîne traduite ou la clé si non trouvée.
     */
    protected function translate($key, $params = []) {
        $text = self::$translations[$key] ?? $key;

        foreach ($params as $paramKey => $paramValue) {
            $text = str_replace(':' . $paramKey, $paramValue, $text);
        }
        return $text;
    }

    /**
     * Redirige vers une URL.
     * @param string $url L'URL de redirection.
     */
    protected function redirect($url) {
        View::redirect($url); // Utiliser la méthode de redirection de la classe View
        // // Temporaire :
        // if (!preg_match('/^https?:\/\//', $url)) {
        //     $url = APP_URL . '/' . ltrim($url, '/');
        // }
        // header('Location: ' . $url);
        // exit;
    }

    /**
     * Récupère les données JSON envoyées dans le corps d'une requête POST.
     * @return mixed Les données décodées ou null si invalide.
     */
    protected function getJsonInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }

    /**
     * Envoie une réponse JSON.
     * @param mixed $data Les données à envoyer.
     * @param int $statusCode Le code de statut HTTP.
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}

// Création d'un HomeController basique maintenant que Controller.php existe.
// Ceci est nécessaire pour que le Router.php ne cause pas d'erreur fatale.
if (!file_exists(APP_ROOT . '/app/controllers/HomeController.php')) {
    $homeControllerContent = "<?php\n\n";
    // require_once APP_ROOT . '/app/core/Controller.php'; // Controller.php est déjà requis par Router ou autoloader
    $homeControllerContent .= "class HomeController extends Controller {\n";
    $homeControllerContent .= "    public function __construct() {\n";
    $homeControllerContent .= "        parent::__construct();\n";
    $homeControllerContent .= "        // Exemple: Charger un modèle si nécessaire\n";
    $homeControllerContent .= "        // \$this->userModel = \$this->model('User');\n";
    $homeControllerContent .= "    }\n\n";
    $homeControllerContent .= "    public function index() {\n";
    $homeControllerContent .= "        \$data = [\n";
    $homeControllerContent .= "            'title' => \$this->translate('welcome_title'),\n";
    $homeControllerContent .= "            'message' => \$this->translate('welcome_message', ['appName' => 'BacAppMVC'])\n";
    $homeControllerContent .= "        ];\n";
    $homeControllerContent .= "        \$this->view('home/index', \$data); // Sera utilisé quand la vue home/index existera\n";
    $homeControllerContent .= "        // Pour l'instant, affichage direct pour test\n";
    $homeControllerContent .= "        // echo \"<h1>{\$data['title']}</h1><p>{\$data['message']}</p>\";\n";
    $homeControllerContent .= "        // echo \"<p>Langue actuelle: {$_SESSION['lang']}</p>\";\n";
    $homeControllerContent .= "        // echo '<p><a href=\"?lang=fr\">Français</a> | <a href=\"?lang=ar\">Arabe</a></p>';\n";
    $homeControllerContent .= "    }\n";
    $homeControllerContent .= "}\n";

    file_put_contents(APP_ROOT . '/app/controllers/HomeController.php', $homeControllerContent);
}

?>
