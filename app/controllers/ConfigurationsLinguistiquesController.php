<?php

class ConfigurationsLinguistiquesController extends Controller {
    private $configLinguistiqueModel;

    public function __construct() {
        parent::__construct();
        // TODO: Vérification de session et permissions
        // if (!$this->isLoggedIn() || !$this->userHasPermission('manage_language_settings')) {
        //     $_SESSION['error_message'] = $this->translate('access_denied');
        //     $this->redirect('dashboard');
        // }
        $this->configLinguistiqueModel = $this->model('ConfigurationLinguistique');
    }

    public function index() {
        $settings = $this->configLinguistiqueModel->getSettings();
        $availableLanguages = $this->configLinguistiqueModel->getAvailableLanguagesFromFiles();

        $data = [
            'title' => $this->translate('language_settings'),
            'settings' => $settings, // L'objet settings complet
            'langues_actives_array' => $settings ? $settings->langues_actives_array : [],
            'langue_principale' => $settings ? $settings->langue_principale : 'fr',
            'langue_secondaire' => $settings ? $settings->langue_secondaire : null,
            'mode_affichage_documents' => $settings ? $settings->mode_affichage_documents : 'bilingue',
            'available_languages' => $availableLanguages,
            // Erreurs (pourraient être définies après une tentative de sauvegarde échouée)
            'langues_actives_err' => '',
            'langue_principale_err' => '',
            'langue_secondaire_err' => '',
            'mode_affichage_documents_err' => ''
        ];
        $this->view('configurations_linguistiques/index', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $selectedLanguesActives = isset($_POST['langues_actives']) && is_array($_POST['langues_actives']) ? $_POST['langues_actives'] : [];

            $data = [
                'langues_actives_json' => json_encode($selectedLanguesActives),
                'langue_principale' => $_POST['langue_principale'] ?? 'fr',
                'langue_secondaire' => isset($_POST['langue_secondaire']) && !empty($_POST['langue_secondaire']) ? $_POST['langue_secondaire'] : null,
                'mode_affichage_documents' => $_POST['mode_affichage_documents'] ?? 'bilingue',
                // Pour réafficher le formulaire en cas d'erreur
                'title' => $this->translate('language_settings'),
                'settings' => $this->configLinguistiqueModel->getSettings(), // Pour les valeurs actuelles
                'langues_actives_array' => $selectedLanguesActives,
                'available_languages' => $this->configLinguistiqueModel->getAvailableLanguagesFromFiles(),
                'langues_actives_err' => '',
                'langue_principale_err' => '',
                'langue_secondaire_err' => '',
                'mode_affichage_documents_err' => ''
            ];

            // Validation
            $all_valid = true;
            if (empty($selectedLanguesActives)) {
                $data['langues_actives_err'] = $this->translate('at_least_one_language_active');
                $all_valid = false;
            }
            if (empty($data['langue_principale'])) {
                $data['langue_principale_err'] = $this->translate('main_language_required');
                $all_valid = false;
            } elseif (!in_array($data['langue_principale'], $selectedLanguesActives)) {
                 $data['langue_principale_err'] = $this->translate('main_language_must_be_active');
                 $all_valid = false;
            }

            if ($data['langue_secondaire'] && !in_array($data['langue_secondaire'], $selectedLanguesActives)) {
                $data['langue_secondaire_err'] = $this->translate('secondary_language_must_be_active');
                $all_valid = false;
            }

            if ($data['langue_principale'] && $data['langue_secondaire'] && $data['langue_principale'] === $data['langue_secondaire']) {
                $data['langue_secondaire_err'] = $this->translate('secondary_language_cannot_be_same_as_main');
                 $all_valid = false;
            }

            if (!in_array($data['mode_affichage_documents'], ['unilingue', 'bilingue'])) {
                $data['mode_affichage_documents_err'] = $this->translate('invalid_document_display_mode');
                $all_valid = false;
            }
             if ($data['mode_affichage_documents'] === 'bilingue' && empty($data['langue_secondaire'])) {
                $data['langue_secondaire_err'] = $this->translate('secondary_language_required_for_bilingual');
                $all_valid = false;
            }


            if ($all_valid) {
                if ($this->configLinguistiqueModel->updateSettings($data)) {
                    $_SESSION['message'] = $this->translate('language_settings_updated_successfully');
                    $this->redirect('configurationslinguistiques');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_language_settings');
                    $this->view('configurations_linguistiques/index', $data);
                }
            } else {
                $this->view('configurations_linguistiques/index', $data);
            }
        } else {
            $this->redirect('configurationslinguistiques');
        }
    }
}
?>
