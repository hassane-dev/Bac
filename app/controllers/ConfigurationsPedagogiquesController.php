<?php

class ConfigurationsPedagogiquesController extends Controller {
    private $configPedagogiqueModel;
    private $anneeScolaireModel;

    public function __construct() {
        parent::__construct();
        // TODO: Vérification de session et permissions
        // if (!$this->isLoggedIn() || !$this->userHasPermission('manage_configs_pedagogiques')) {
        //     $_SESSION['error_message'] = $this->translate('access_denied');
        //     $this->redirect('dashboard');
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

    /**
     * Affiche le formulaire pour ajouter ou modifier une configuration pour une année donnée.
     * Si une configuration existe pour l'année, elle est chargée pour modification.
     * Si annee_scolaire_id n'est pas fourni, ou si aucune année scolaire n'existe, redirige ou affiche une erreur.
     */
    public function gerer($annee_scolaire_id = null) {
        if ($annee_scolaire_id === null) {
            // Optionnel: rediriger vers une page de sélection d'année scolaire si aucune n'est passée
            // ou afficher une liste d'années scolaires pour en choisir une.
            // Pour l'instant, on va lister les années scolaires pour choisir.
            $annees = $this->anneeScolaireModel->getAll();
             if (empty($annees)) {
                $_SESSION['error_message'] = $this->translate('no_academic_years_create_first');
                $this->redirect('anneesscolaires/create'); // Proposer de créer une année d'abord
                return;
            }
            $this->view('configurations_pedagogiques/select_annee', [
                'annees' => $annees,
                'title' => $this->translate('select_academic_year_for_config')
            ]);
            return;
        }

        $anneeScolaire = $this->anneeScolaireModel->getById($annee_scolaire_id);
        if (!$anneeScolaire) {
            $_SESSION['error_message'] = $this->translate('academic_year_not_found');
            $this->redirect('configurationspedagogiques'); // Rediriger vers l'index du contrôleur
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
            'seuil_admission_err' => '', // etc. pour les erreurs
        ];
        $this->view('configurations_pedagogiques/gerer', $data);
    }


    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $annee_scolaire_id = $_POST['annee_scolaire_id'] ?? null;
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
                'annee_scolaire_libelle' => $anneeScolaire->libelle,
                'seuil_admission' => trim($_POST['seuil_admission']),
                'seuil_second_tour' => trim($_POST['seuil_second_tour']),
                'mention_passable' => trim($_POST['mention_passable']),
                'mention_AB' => trim($_POST['mention_AB']),
                'mention_bien' => trim($_POST['mention_bien']),
                'mention_TB' => trim($_POST['mention_TB']),
                'mention_exc' => trim($_POST['mention_exc']),
                'title' => $this->translate('manage_pedagogical_config_for') . ' ' . $anneeScolaire->libelle,
                'seuil_admission_err' => '', // etc.
            ];

            // Validation (exemple simple, à compléter)
            $numericFields = ['seuil_admission', 'seuil_second_tour', 'mention_passable', 'mention_AB', 'mention_bien', 'mention_TB', 'mention_exc'];
            $all_valid = true;
            foreach ($numericFields as $field) {
                if (!is_numeric($data[$field]) || $data[$field] < 0 || $data[$field] > 20) {
                    $data[$field.'_err'] = $this->translate('field_must_be_numeric_between_0_20');
                    $all_valid = false;
                }
            }
            // Ajouter d'autres validations (ex: seuil_second_tour < seuil_admission, mentions croissantes)

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

    // La suppression se fait généralement par ID de la config ou par ID de l'année scolaire
    // Si on supprime une année scolaire, sa config pédagogique devrait être supprimée en cascade (géré par la DB)
    // ou via une méthode dans le modèle AnneeScolaire.
    // On pourrait ajouter une méthode delete($config_id) ici si nécessaire.
}
?>
