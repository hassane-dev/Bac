<?php

class ConfigurationsPedagogiquesController extends Controller {
    private $configPedaModel;
    private $anneeScolaireModel;

    public function __construct() {
        parent::__construct();
        $this->configPedaModel = $this->model('ConfigurationPedagogiqueModel');
        $this->anneeScolaireModel = $this->model('AnneeScolaireModel'); // Pour lister les années scolaires

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'access_denied_pleaselogin');
            $this->redirect('auth/login');
        }
        // Permission globale (à affiner si nécessaire)
        // if (!$this->userHasPermission('manage_pedagogical_configs')) { // Ajouter 'manage_pedagogical_configs'
        //     $this->setFlashMessage('error', 'access_denied');
        //     $this->redirect('dashboard/index');
        // }
    }

    /**
     * Affiche la liste des configurations existantes ou un sélecteur d'année pour en ajouter/modifier une.
     */
    public function index() {
        if (!$this->userHasPermission('view_pedagogical_configs') && !$this->userHasPermission('manage_pedagogical_configs')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('dashboard/index');
        }

        $all_configs = $this->configPedaModel->getAllWithAnneeLibelle();
        $annees_scolaires = $this->anneeScolaireModel->getAll(); // Pour le sélecteur d'ajout/modification

        // Identifier les années qui n'ont pas encore de configuration
        $configured_annee_ids = array_map(function($config){ return $config->annee_scolaire_id; }, $all_configs);
        $unconfigured_annees = array_filter($annees_scolaires, function($annee) use ($configured_annee_ids) {
            return !in_array($annee->id, $configured_annee_ids);
        });


        $data = [
            'page_title' => $this->translate('pedagogical_configs_list'),
            'configurations' => $all_configs,
            'unconfigured_annees' => $unconfigured_annees, // Années sans config, pour le select "Ajouter pour..."
            'all_annees_scolaires' => $annees_scolaires // Toutes les années pour un éventuel filtre ou sélection
        ];
        $this->view('configurations_pedagogiques/index', $data);
    }

    /**
     * Affiche le formulaire pour éditer (ou créer si non existante) la configuration
     * pour une année scolaire donnée.
     * @param int $annee_scolaire_id L'ID de l'année scolaire.
     */
    public function edit($annee_scolaire_id = null) {
        if (!$this->userHasPermission('manage_pedagogical_configs')) {
            $this->setFlashMessage('error', 'access_denied');
            $this->redirect('configurations_pedagogiques/index');
        }

        if (is_null($annee_scolaire_id) || !($annee = $this->anneeScolaireModel->getById((int)$annee_scolaire_id))) {
            $this->setFlashMessage('error', 'academic_year_not_found');
            $this->redirect('configurations_pedagogiques/index');
            return;
        }

        $config = $this->configPedaModel->getByAnneeScolaireId($annee->id);

        $data = [
            'page_title' => $this->translate('manage_pedagogical_config_for') . ' ' . htmlspecialchars($annee->libelle),
            'annee_scolaire_id' => $annee->id,
            'annee_scolaire_libelle' => $annee->libelle,
            'seuil_admission' => $config->seuil_admission ?? 10.00,
            'seuil_second_tour' => $config->seuil_second_tour ?? 9.50,
            'mention_passable' => $config->mention_passable ?? 10.00,
            'mention_AB' => $config->mention_AB ?? 12.00,
            'mention_bien' => $config->mention_bien ?? 14.00,
            'mention_TB' => $config->mention_TB ?? 16.00,
            'mention_exc' => $config->mention_exc ?? 18.00,
            'errors' => [] // Pour stocker les erreurs de validation
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data['seuil_admission'] = filter_var(trim($_POST['seuil_admission']), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            $data['seuil_second_tour'] = filter_var(trim($_POST['seuil_second_tour']), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            $data['mention_passable'] = filter_var(trim($_POST['mention_passable']), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            $data['mention_AB'] = filter_var(trim($_POST['mention_AB']), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            $data['mention_bien'] = filter_var(trim($_POST['mention_bien']), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            $data['mention_TB'] = filter_var(trim($_POST['mention_TB']), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            $data['mention_exc'] = filter_var(trim($_POST['mention_exc']), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

            // Validation
            $numeric_fields = ['seuil_admission', 'seuil_second_tour', 'mention_passable', 'mention_AB', 'mention_bien', 'mention_TB', 'mention_exc'];
            foreach ($numeric_fields as $field) {
                if ($data[$field] === null || $data[$field] < 0 || $data[$field] > 20) {
                    $data['errors'][$field] = $this->translate('field_must_be_numeric_between_0_20');
                }
            }
            // Validations de cohérence
            if (empty($data['errors']['seuil_admission']) && empty($data['errors']['seuil_second_tour']) && $data['seuil_second_tour'] >= $data['seuil_admission']) {
                $data['errors']['seuil_second_tour'] = $this->translate('second_round_lower_than_admission');
            }
            if (empty($data['errors']['seuil_admission']) && empty($data['errors']['mention_passable']) && $data['mention_passable'] < $data['seuil_admission']) {
                $data['errors']['mention_passable'] = $this->translate('mention_passable_ge_admission');
            }
            // Vérifier que les mentions sont croissantes
            $mentions_order = ['mention_passable', 'mention_AB', 'mention_bien', 'mention_TB', 'mention_exc'];
            for ($i = 0; $i < count($mentions_order) - 1; $i++) {
                $current_mention_field = $mentions_order[$i];
                $next_mention_field = $mentions_order[$i+1];
                if (empty($data['errors'][$current_mention_field]) && empty($data['errors'][$next_mention_field]) && $data[$current_mention_field] > $data[$next_mention_field]) {
                    $data['errors'][$next_mention_field] = $this->translate('mentions_must_be_increasing');
                    break;
                }
            }


            if (empty($data['errors'])) {
                $config_data = [
                    'annee_scolaire_id' => $annee->id,
                    'seuil_admission' => $data['seuil_admission'],
                    'seuil_second_tour' => $data['seuil_second_tour'],
                    'mention_passable' => $data['mention_passable'],
                    'mention_AB' => $data['mention_AB'],
                    'mention_bien' => $data['mention_bien'],
                    'mention_TB' => $data['mention_TB'],
                    'mention_exc' => $data['mention_exc']
                ];

                if ($this->configPedaModel->createOrUpdate($config_data)) {
                    $this->setFlashMessage('success', 'pedagogical_config_saved_successfully');
                    $this->redirect('configurations_pedagogiques/index');
                } else {
                    $this->setFlashMessage('error', 'error_saving_pedagogical_config');
                    $this->view('configurations_pedagogiques/edit', $data);
                }
            } else {
                $this->setFlashMessage('error', 'form_has_errors_check_fields'); // Ajouter aux lang
                $this->view('configurations_pedagogiques/edit', $data);
            }
        } else {
            $this->view('configurations_pedagogiques/edit', $data);
        }
    }
}
?>
