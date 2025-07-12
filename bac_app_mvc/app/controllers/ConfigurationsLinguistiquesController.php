<?php

class ConfigurationsLinguistiquesController extends Controller {
    private $configLingModel;

    public function __construct() {
        parent::__construct();
        $this->configLingModel = $this->model('ConfigurationLinguistiqueModel');

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'access_denied_pleaselogin');
            $this->redirect('auth/login');
        }
    }

    public function index() {
        if (!$this->userHasPermission('manage_language_settings')) {
            $this->setFlashMessage('error', 'access_denied');
            $this->redirect('dashboard/index');
            return;
        }

        $settings = $this->configLingModel->getSettings();

        $data = [
            'page_title' => $this->translate('language_settings'),
            'langues_actives_array' => $settings->langues_actives_array ?? [],
            'langue_principale' => $settings->langue_principale ?? DEFAULT_LANG,
            'langue_secondaire' => $settings->langue_secondaire ?? null,
            'mode_affichage_documents' => $settings->mode_affichage_documents ?? 'bilingue',
            'available_langs_from_config' => defined('AVAILABLE_LANGS') ? AVAILABLE_LANGS : ['fr', 'ar'],
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data['langues_actives_array'] = isset($_POST['langues_actives']) && is_array($_POST['langues_actives']) ? $_POST['langues_actives'] : [];
            $data['langue_principale'] = trim($_POST['langue_principale'] ?? '');
            $data['langue_secondaire'] = trim($_POST['langue_secondaire'] ?? '');
            if (empty($data['langue_secondaire'])) $data['langue_secondaire'] = null;
            $data['mode_affichage_documents'] = trim($_POST['mode_affichage_documents'] ?? 'bilingue');

            // Validation
            if (empty($data['langues_actives_array'])) {
                $data['errors']['langues_actives'] = $this->translate('at_least_one_language_active');
            }
            if (empty($data['langue_principale'])) {
                $data['errors']['langue_principale'] = $this->translate('main_language_required');
            } elseif (!in_array($data['langue_principale'], $data['langues_actives_array'])) {
                $data['errors']['langue_principale'] = $this->translate('main_language_must_be_active');
            }

            if ($data['langue_secondaire'] !== null && !in_array($data['langue_secondaire'], $data['langues_actives_array'])) {
                $data['errors']['langue_secondaire'] = $this->translate('secondary_language_must_be_active');
            }
            if ($data['langue_secondaire'] !== null && $data['langue_secondaire'] === $data['langue_principale']) {
                $data['errors']['langue_secondaire'] = $this->translate('secondary_language_cannot_be_same_as_main');
            }

            if (!in_array($data['mode_affichage_documents'], ['unilingue', 'bilingue'])) {
                $data['errors']['mode_affichage_documents'] = $this->translate('invalid_document_display_mode');
            }
            if ($data['mode_affichage_documents'] === 'bilingue' && $data['langue_secondaire'] === null) {
                 $data['errors']['mode_affichage_documents'] = $this->translate('secondary_language_required_for_bilingual');
            }


            if (empty($data['errors'])) {
                $update_data = [
                    'langues_actives_array' => $data['langues_actives_array'],
                    'langue_principale' => $data['langue_principale'],
                    'langue_secondaire' => $data['langue_secondaire'],
                    'mode_affichage_documents' => $data['mode_affichage_documents']
                ];
                if ($this->configLingModel->updateSettings($update_data)) {
                    $this->setFlashMessage('success', 'language_settings_updated_successfully');

                    $new_settings = $this->configLingModel->getSettings();
                    // Mettre à jour la langue de la session si elle n'est plus valide ou si la principale a changé
                    $current_session_lang = $_SESSION['lang'] ?? DEFAULT_LANG;
                    if (!in_array($current_session_lang, $new_settings->langues_actives_array) || empty($current_session_lang)) {
                        $_SESSION['lang'] = $new_settings->langue_principale;
                    }
                    // Forcer la réinitialisation des traductions chargées dans le contrôleur de base pour la requête actuelle
                    self::$translations = []; // Accès à la propriété statique du parent
                    if(method_exists($this, 'loadTranslations')) { // Si la méthode est dans Controller et accessible
                       // $this->loadTranslations(); // Ne fonctionnera pas si c'est private, self::loadTranslations() non plus car pas static
                       // La meilleure solution est que le constructeur de Controller recharge toujours les traductions.
                       // Ou que Controller::translate() vérifie si self::$translations est vide avant de tenter une traduction
                       // et recharge si nécessaire.
                       // Pour l'instant, la redirection forcera un nouveau chargement à la prochaine requête.
                    }


                    $this->redirect('configurations_linguistiques/index');
                } else {
                    $this->setFlashMessage('error', 'error_updating_language_settings');
                    $this->view('configurations_linguistiques/index', $data);
                }
            } else {
                $this->setFlashMessage('error', 'form_has_errors_check_fields');
                $this->view('configurations_linguistiques/index', $data);
            }
        } else {
            $this->view('configurations_linguistiques/index', $data);
        }
    }
}
?>
