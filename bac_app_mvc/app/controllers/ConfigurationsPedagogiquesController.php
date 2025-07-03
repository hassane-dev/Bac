<?php

class ConfigurationsPedagogiquesController extends Controller {
    private $configPedagogiqueModel;
    private $anneeScolaireModel;

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Affiner la vérification des permissions
        // if (!$this->userHasPermission('manage_pedagogical_configs')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->configPedagogiqueModel = $this->model('ConfigurationPedagogique');
        $this->anneeScolaireModel = $this->model('AnneeScolaire');
    }

    public function index() {
        $configs = $this->configPedagogiqueModel->getAllWithAnneeScolaire();
        $this->view('configurations_pedagogiques/index', [
            'configs' => $configs,
            'title' => $this->translate('pedagogical_configs_list')
        ]);
    }

    public function gerer($annee_scolaire_id = null) {
        if ($annee_scolaire_id === null) {
            $annees = $this->anneeScolaireModel->getAll();
             if (empty($annees)) {
                $_SESSION['error_message'] = $this->translate('no_academic_years_create_first');
                $this->redirect('anneesscolaires/create');
                return;
            }
            $this->view('configurations_pedagogiques/select_annee', [
                'annees' => $annees,
                'title' => $this->translate('select_academic_year_for_config')
            ]);
            return;
        }

        $annee_scolaire_id = (int)$annee_scolaire_id;
        $anneeScolaire = $this->anneeScolaireModel->getById($annee_scolaire_id);
        if (!$anneeScolaire) {
            $_SESSION['error_message'] = $this->translate('academic_year_not_found');
            $this->redirect('configurationspedagogiques');
            return;
        }

        $config = $this->configPedagogiqueModel->getByAnneeScolaireId($annee_scolaire_id);

        $data = [
            'title' => $this->translate('manage_pedagogical_config_for') . ' ' . $anneeScolaire->libelle,
            'annee_scolaire_id' => $annee_scolaire_id,
            'annee_scolaire_libelle' => $anneeScolaire->libelle,
            'seuil_admission' => $config->seuil_admission ?? '10.00',
            'seuil_second_tour' => $config->seuil_second_tour ?? '9.50',
            'mention_passable' => $config->mention_passable ?? '10.00',
            'mention_AB' => $config->mention_AB ?? '12.00',
            'mention_bien' => $config->mention_bien ?? '14.00',
            'mention_TB' => $config->mention_TB ?? '16.00',
            'mention_exc' => $config->mention_exc ?? '18.00',
            'seuil_admission_err' => '', 'seuil_second_tour_err' => '', 'mention_passable_err' => '',
            'mention_AB_err' => '', 'mention_bien_err' => '', 'mention_TB_err' => '', 'mention_exc_err' => ''
        ];
        $this->view('configurations_pedagogiques/gerer', $data);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $annee_scolaire_id = isset($_POST['annee_scolaire_id']) ? (int)$_POST['annee_scolaire_id'] : null;
            if(!$annee_scolaire_id) {
                 $_SESSION['error_message'] = $this->translate('academic_year_required');
                 $this->redirect('configurationspedagogiques');
                 return;
            }
            $anneeScolaire = $this->anneeScolaireModel->getById($annee_scolaire_id);
             if (!$anneeScolaire) {
                $_SESSION['error_message'] = $this->translate('academic_year_not_found');
                $this->redirect('configurationspedagogiques');
                return;
            }

            $data = [
                'annee_scolaire_id' => $annee_scolaire_id,
                'annee_scolaire_libelle' => $anneeScolaire->libelle, // Pour réafficher le titre en cas d'erreur
                'seuil_admission' => trim($_POST['seuil_admission']),
                'seuil_second_tour' => trim($_POST['seuil_second_tour']),
                'mention_passable' => trim($_POST['mention_passable']),
                'mention_AB' => trim($_POST['mention_AB']),
                'mention_bien' => trim($_POST['mention_bien']),
                'mention_TB' => trim($_POST['mention_TB']),
                'mention_exc' => trim($_POST['mention_exc']),
                'title' => $this->translate('manage_pedagogical_config_for') . ' ' . $anneeScolaire->libelle,
                'seuil_admission_err' => '', 'seuil_second_tour_err' => '', 'mention_passable_err' => '',
                'mention_AB_err' => '', 'mention_bien_err' => '', 'mention_TB_err' => '', 'mention_exc_err' => ''
            ];

            $all_valid = true;
            $numericFields = [
                'seuil_admission', 'seuil_second_tour', 'mention_passable',
                'mention_AB', 'mention_bien', 'mention_TB', 'mention_exc'
            ];
            foreach ($numericFields as $field) {
                if (!isset($data[$field]) || !is_numeric($data[$field]) || $data[$field] < 0 || $data[$field] > 20) {
                    $data[$field.'_err'] = $this->translate('field_must_be_numeric_between_0_20');
                    $all_valid = false;
                } else {
                    $data[$field] = (float)$data[$field];
                }
            }

            if ($all_valid) {
                if ($data['seuil_second_tour'] >= $data['seuil_admission']) {
                    $data['seuil_second_tour_err'] = $this->translate('second_round_lower_than_admission');
                    $all_valid = false;
                }
                if (!($data['mention_passable'] < $data['mention_AB'] &&
                      $data['mention_AB'] < $data['mention_bien'] &&
                      $data['mention_bien'] < $data['mention_TB'] &&
                      $data['mention_TB'] < $data['mention_exc'])) {
                    $_SESSION['error_message'] = $this->translate('mentions_must_be_increasing');
                    $all_valid = false;
                }
                 if ($data['mention_passable'] < $data['seuil_admission'] && abs($data['mention_passable'] - $data['seuil_admission']) > 0.001) { // abs pour float
                     $data['mention_passable_err'] = $this->translate('mention_passable_ge_admission');
                     $all_valid = false;
                }
            }

            if ($all_valid) {
                if ($this->configPedagogiqueModel->save($data)) {
                    $_SESSION['message'] = $this->translate('pedagogical_config_saved_successfully');
                    $this->redirect('configurationspedagogiques');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_saving_pedagogical_config');
                    $this->view('configurations_pedagogiques/gerer', $data);
                }
            } else {
                $this->view('configurations_pedagogiques/gerer', $data);
            }
        } else {
            $this->redirect('configurationspedagogiques');
        }
    }
}
?>
