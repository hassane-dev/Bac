<?php

class View {
    /**
     * Charge un fichier de vue.
     *
     * @param string $view Le nom du fichier de vue (ex: 'home/index')
     * @param array $data Les données à passer à la vue
     */
    public static function render($view, $data = []) {
        // Extraire les données pour les rendre accessibles par leur nom de clé dans la vue
        extract($data);

        // Construire le chemin vers le fichier de vue
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            // Inclure l'en-tête commun (si nécessaire)
            // if (file_exists(APP_ROOT . '/app/views/layouts/header.php')) {
            //     require_once APP_ROOT . '/app/views/layouts/header.php';
            // }

            require_once $viewFile;

            // Inclure le pied de page commun (si nécessaire)
            // if (file_exists(APP_ROOT . '/app/views/layouts/footer.php')) {
            //     require_once APP_ROOT . '/app/views/layouts/footer.php';
            // }
        } else {
            // Gérer l'erreur si le fichier de vue n'est pas trouvé
            // die("Le fichier de vue '$viewFile' n'existe pas.");
            echo "Erreur: Le fichier de vue '$viewFile' n'existe pas.";
        }
    }

    /**
     * Redirige vers une URL spécifiée.
     *
     * @param string $url L'URL vers laquelle rediriger (peut être interne ou externe)
     */
    public static function redirect($url) {
        // Si l'URL ne commence pas par http, on suppose que c'est une route interne.
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = APP_URL . '/' . ltrim($url, '/');
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Charge les traductions pour la langue actuelle.
     * Les traductions sont stockées dans des fichiers PHP dans le dossier /lang.
     *
     * @return array Les chaînes de traduction.
     */
    public static function loadTranslations() {
        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $langFile = APP_ROOT . '/lang/' . $currentLang . '.php';

        if (file_exists($langFile)) {
            return include $langFile;
        }
        // Si le fichier de langue n'existe pas, retourner un tableau vide ou charger la langue par défaut.
        if ($currentLang !== DEFAULT_LANG) {
            $defaultLangFile = APP_ROOT . '/lang/' . DEFAULT_LANG . '.php';
            if (file_exists($defaultLangFile)) {
                return include $defaultLangFile;
            }
        }
        return [];
    }

    /**
     * Obtient une chaîne de traduction.
     *
     * @param string $key La clé de la chaîne de traduction.
     * @param array $params Paramètres à remplacer dans la chaîne.
     * @return string La chaîne traduite ou la clé si non trouvée.
     */
    public static function translate($key, $params = []) {
        global $translations; // Supposons que les traductions sont chargées globalement ou passées.

        // Charger les traductions si elles ne le sont pas déjà
        // Cela pourrait être fait une seule fois au début de la requête.
        if (empty($translations)) {
            // Note: Pour que cela fonctionne, $translations doit être accessible.
            // Une meilleure approche serait d'utiliser une classe de gestion de langue (I18n).
            // Pour l'instant, on suppose qu'elles sont chargées via une variable globale ou une instance.
            // $translations = self::loadTranslations();
            // Dans un vrai scénario, on initialiserait $translations dans le bootstrap (index.php ou un contrôleur de base).
        }

        $text = $translations[$key] ?? $key;

        foreach ($params as $paramKey => $paramValue) {
            $text = str_replace(':' . $paramKey, $paramValue, $text);
        }
        return $text;
    }
}

// Initialisation des traductions (exemple, devrait être fait de manière plus centralisée)
// $translations = View::loadTranslations();

?>
