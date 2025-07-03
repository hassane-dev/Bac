<?php

/**
 * Contrôleur de base.
 * Tous les autres contrôleurs hériteront de cette classe.
 */
abstract class Controller {
    protected $db; // Instance de la base de données
    protected static $translations = []; // Stockage des traductions chargées

    public function __construct() {
        // S'assurer que config.php est chargé pour les constantes DB_* et APP_ROOT
        if (!defined('APP_ROOT')) {
            // Ceci est une sécurité, config.php devrait être chargé par public/index.php
            $configFile = dirname(__DIR__, 2) . '/config.php'; // Remonte de app/core à bac_app_mvc puis config.php
            if (file_exists($configFile)) {
                require_once $configFile;
            } else {
                die("Fichier de configuration introuvable.");
            }
        }

        $this->db = new Database();

        // Charger les traductions une seule fois par requête (ou par session de langue)
        if (empty(self::$translations)) {
            self::$translations = $this->loadTranslations();
        }
    }

    /**
     * Charge un modèle.
     *
     * @param string $modelLe Le nom du fichier modèle (ex: 'User' pour User.php)
     * @return object|false L'instance du modèle ou false si non trouvé.
     */
    protected function model($modelName) {
        $modelFile = APP_ROOT . '/app/models/' . ucwords($modelName) . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            $modelClass = ucwords($modelName);
            if (class_exists($modelClass)) {
                // Passer l'instance de la base de données au modèle
                return new $modelClass($this->db);
            } else {
                 View::renderError("Classe modèle '$modelClass' non trouvée dans '$modelFile'.");
                 return false;
            }
        }
        View::renderError("Fichier modèle '$modelFile' non trouvé.");
        return false;
    }

    /**
     * Charge et affiche une vue.
     *
     * @param string $viewPath Le chemin vers le fichier de vue depuis app/views/ (ex: 'home/index')
     * @param array $data Les données à passer à la vue
     */
    protected function view($viewPath, $data = []) {
        // Rendre les traductions et autres données globales disponibles pour toutes les vues
        $data['tr'] = function($key, $params = []) {
            return $this->translate($key, $params);
        };
        $data['current_lang'] = $_SESSION['lang'] ?? DEFAULT_LANG;
        $data['app_url'] = APP_URL;
        $data['isLoggedIn'] = $this->isLoggedIn(); // Rendre le statut de connexion disponible aux vues
        if ($this->isLoggedIn()) {
            $data['current_username'] = $_SESSION['username'] ?? '';
            $data['current_user_role_id'] = $_SESSION['user_role_id'] ?? null;
        }


        View::render($viewPath, $data);
    }

    /**
     * Charge les fichiers de langue.
     * @return array
     */
    private function loadTranslations() {
        // Gestion du changement de langue via paramètre GET 'lang'
        if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGS)) {
            $_SESSION['lang'] = $_GET['lang'];
            // Rediriger pour nettoyer l'URL du paramètre lang (optionnel mais propre)
            // Attention: ceci peut causer des boucles si mal géré ou si la page actuelle est POST
            // $currentUrl = strtok($_SERVER["REQUEST_URI"],'?');
            // header("Location: " . $currentUrl);
            // exit;
        }

        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $langFile = APP_ROOT . '/lang/' . $currentLang . '.php';

        if (file_exists($langFile)) {
            return include $langFile;
        }
        // Fallback vers la langue par défaut si le fichier de la langue actuelle n'existe pas
        if ($currentLang !== DEFAULT_LANG) {
            $defaultLangFile = APP_ROOT . '/lang/' . DEFAULT_LANG . '.php';
            if (file_exists($defaultLangFile)) {
                $_SESSION['lang'] = DEFAULT_LANG;
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
     * Redirige vers une URL interne.
     * @param string $urlSegment Segment d'URL (ex: 'users/login').
     */
    protected function redirect($urlSegment) {
        View::redirect($urlSegment);
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

    /**
     * Vérifie si un utilisateur est connecté.
     * @return bool
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Vérifie si l'utilisateur connecté a une accréditation spécifique.
     * @param string $requiredAccreditation Le libellé de l'accréditation requise.
     * @return bool
     */
    protected function userHasPermission($requiredAccreditation) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        // L'admin (role_id 1) a toutes les permissions.
        if (isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 1) {
            return true;
        }

        // Charger les accréditations de l'utilisateur si pas déjà en session
        if (!isset($_SESSION['user_accreditations'])) {
            $roleModel = $this->model('Role');
            if ($roleModel && isset($_SESSION['user_role_id'])) {
                $accreditationsObjects = $roleModel->getAccreditations($_SESSION['user_role_id']);
                $_SESSION['user_accreditations'] = array_map(function($acc) { return $acc->libelle_action; }, $accreditationsObjects);
            } else {
                $_SESSION['user_accreditations'] = [];
            }
        }
        return in_array($requiredAccreditation, $_SESSION['user_accreditations']);
    }
}

// Création d'un HomeController basique si non existant
if (!file_exists(APP_ROOT . '/app/controllers/HomeController.php')) {
    $homeControllerContent = "<?php\n\n";
    $homeControllerContent .= "class HomeController extends Controller {\n";
    $homeControllerContent .= "    public function __construct() {\n";
    $homeControllerContent .= "        parent::__construct();\n";
    $homeControllerContent .= "    }\n\n";
    $homeControllerContent .= "    public function index() {\n";
    $homeControllerContent .= "        if (\$this->isLoggedIn()) {\n";
    $homeControllerContent .= "            \$this->redirect('dashboard');\n";
    $homeControllerContent .= "        } else {\n";
    $homeControllerContent .= "            \$this->redirect('auth/login');\n";
    $homeControllerContent .= "        }\n";
    $homeControllerContent .= "    }\n";
    $homeControllerContent .= "}\n";
    file_put_contents(APP_ROOT . '/app/controllers/HomeController.php', $homeControllerContent);
}
?>
