<?php

abstract class Controller {
    protected $db;
    protected static $translations = [];

    public function __construct() {
        if (!defined('APP_ROOT')) {
            $configFile = dirname(__DIR__, 2) . '/config.php';
            if (file_exists($configFile)) {
                require_once $configFile;
            } else {
                die("Fichier de configuration principal introuvable.");
            }
        }

        $this->db = new Database();

        if (empty(self::$translations)) {
            self::$translations = $this->loadTranslations();
        }
    }

    protected function model($modelName) {
        $modelFile = APP_ROOT . '/app/models/' . ucwords($modelName) . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            $modelClass = ucwords($modelName);
            if (class_exists($modelClass)) {
                return new $modelClass($this->db);
            } else {
                 View::renderError("Classe modèle '$modelClass' non trouvée dans '$modelFile'.");
                 return false; // Ou throw exception
            }
        }
        View::renderError("Fichier modèle '$modelFile' non trouvé.");
        return false; // Ou throw exception
    }

    protected function view($viewPath, $data = []) {
        $data['tr'] = function($key, $params = []) {
            return $this->translate($key, $params);
        };
        $data['current_lang'] = $_SESSION['lang'] ?? DEFAULT_LANG;
        $data['app_url'] = APP_URL;
        $data['isLoggedIn'] = $this->isLoggedIn();
        if ($this->isLoggedIn()) {
            $data['current_username'] = $_SESSION['username'] ?? '';
            $data['current_user_role_id'] = $_SESSION['user_role_id'] ?? null;
            // Pourrait être utile de passer le nom du rôle aussi
            // $data['current_user_role_name'] = $_SESSION['user_role_name'] ?? '';
        }

        View::render($viewPath, $data);
    }

    private function loadTranslations() {
        if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGS)) {
            $_SESSION['lang'] = $_GET['lang'];
            // Redirection pour nettoyer l'URL. Attention si la page est POST.
            // $currentUrl = strtok($_SERVER["REQUEST_URI"],'?');
            // header("Location: " . APP_URL . $currentUrl); // Construire URL complète
            // exit;
        }

        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $langFile = APP_ROOT . '/lang/' . $currentLang . '.php';

        if (file_exists($langFile)) {
            return include $langFile;
        }
        if ($currentLang !== DEFAULT_LANG) {
            $defaultLangFile = APP_ROOT . '/lang/' . DEFAULT_LANG . '.php';
            if (file_exists($defaultLangFile)) {
                $_SESSION['lang'] = DEFAULT_LANG;
                return include $defaultLangFile;
            }
        }
        return [];
    }

    protected function translate($key, $params = []) {
        $text = self::$translations[$key] ?? $key;
        foreach ($params as $paramKey => $paramValue) {
            $text = str_replace(':' . $paramKey, $paramValue, $text);
        }
        return $text;
    }

    protected function redirect($urlSegment) {
        View::redirect($urlSegment);
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    protected function userHasPermission($requiredAccreditation) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        if (isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 1) { // Admin (ID 1) a tout
            return true;
        }
        if (!isset($_SESSION['user_accreditations'])) { // Charger si pas en session
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

// Création HomeController et sa vue par défaut
if (!file_exists(APP_ROOT . '/app/controllers/HomeController.php')) {
    $homeControllerContent = "<?php\n\n";
    $homeControllerContent .= "class HomeController extends Controller {\n";
    $homeControllerContent .= "    public function __construct() {\n";
    $homeControllerContent .= "        parent::__construct();\n";
    $homeControllerContent .= "    }\n\n";
    $homeControllerContent .= "    public function index() {\n";
    $homeControllerContent .= "        if (\$this->isLoggedIn()) {\n";
    $homeControllerContent .= "            \$this->redirect('dashboard/index');\n"; // Assurez-vous que DashboardController::index existe
    $homeControllerContent .= "        } else {\n";
    $homeControllerContent .= "            \$this->redirect('auth/login');\n";
    $homeControllerContent .= "        }\n";
    $homeControllerContent .= "    }\n";
    $homeControllerContent .= "}\n";
    file_put_contents(APP_ROOT . '/app/controllers/HomeController.php', $homeControllerContent);
}

if (!file_exists(APP_ROOT . '/app/views/home/index.php')) {
    $homeViewContent = "<?php // Minimal home view - user should be redirected by HomeController ?>\n";
    $homeViewContent .= "<h1><?php echo \$tr('welcome_title'); ?></h1>\n";
    $homeViewContent .= "<p><?php echo \$tr('app_name'); ?></p>\n";
    if (!is_dir(APP_ROOT . '/app/views/home')) {
        mkdir(APP_ROOT . '/app/views/home', 0755, true);
    }
    file_put_contents(APP_ROOT . '/app/views/home/index.php', $homeViewContent);
}
?>
