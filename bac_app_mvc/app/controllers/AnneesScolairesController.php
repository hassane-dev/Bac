<?php

class AnneesScolairesController extends Controller {
    private $anneeScolaireModel;

    public function __construct() {
        parent::__construct();
        $this->anneeScolaireModel = $this->model('AnneeScolaireModel');

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'access_denied_pleaselogin');
            $this->redirect('auth/login');
        }
    }

    public function index() {
        if (!$this->userHasPermission('view_academic_years') && !$this->userHasPermission('manage_academic_years')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('dashboard/index');
        }
        $annees = $this->anneeScolaireModel->getAll();
        $data = [
            'page_title' => $this->translate('academic_year_list'),
            'annees' => $annees
        ];
        $this->view('annees_scolaires/index', $data);
    }

    public function add() {
        if (!$this->userHasPermission('manage_academic_years')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('annees_scolaires/index');
        }

        $data = [
            'page_title' => $this->translate('add_new_academic_year'),
            'libelle' => '',
            'date_debut' => '',
            'date_fin' => '',
            'est_active' => false,
            'libelle_err' => '',
            'date_debut_err' => '',
            'date_fin_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data['libelle'] = trim($_POST['libelle']);
            $data['date_debut'] = trim($_POST['date_debut']);
            $data['date_fin'] = trim($_POST['date_fin']);
            $data['est_active'] = isset($_POST['est_active']);

            // Validation
            if (empty($data['libelle'])) {
                $data['libelle_err'] = $this->translate('label_required');
            } elseif ($this->anneeScolaireModel->findByLibelle($data['libelle'])) {
                $data['libelle_err'] = $this->translate('label_taken');
            }

            if (empty($data['date_debut'])) {
                $data['date_debut_err'] = $this->translate('start_date_required');
            }
            if (empty($data['date_fin'])) {
                $data['date_fin_err'] = $this->translate('end_date_required');
            } elseif (!empty($data['date_debut']) && $data['date_fin'] <= $data['date_debut']) {
                $data['date_fin_err'] = $this->translate('end_date_after_start_date');
            }

            $no_errors = true;
            foreach($data as $key => $value){
                if(str_ends_with($key, '_err') && !empty($value)){
                    $no_errors = false;
                    break;
                }
            }

            if ($no_errors) {
                if ($this->anneeScolaireModel->create($data['libelle'], $data['date_debut'], $data['date_fin'], $data['est_active'])) {
                    $this->setFlashMessage('success', 'academic_year_added_successfully');
                    $this->redirect('annees_scolaires/index');
                } else {
                    $this->setFlashMessage('error', 'error_adding_academic_year');
                    $this->view('annees_scolaires/add', $data);
                }
            } else {
                $this->view('annees_scolaires/add', $data);
            }
        } else {
            $this->view('annees_scolaires/add', $data);
        }
    }

    public function edit($id = null) {
        if (!$this->userHasPermission('manage_academic_years')) {
            $this->setFlashMessage('error', 'access_denied');
            $this->redirect('annees_scolaires/index');
        }

        if (is_null($id) || !($annee = $this->anneeScolaireModel->getById((int)$id))) {
            $this->setFlashMessage('error', 'academic_year_not_found');
            $this->redirect('annees_scolaires/index');
            return;
        }

        $data = [
            'page_title' => $this->translate('edit_academic_year') . ': ' . htmlspecialchars($annee->libelle),
            'id' => $annee->id,
            'libelle' => $annee->libelle,
            'date_debut' => $annee->date_debut,
            'date_fin' => $annee->date_fin,
            'est_active' => (bool)$annee->est_active, // Valeur actuelle pour la logique du formulaire
            'libelle_err' => '',
            'date_debut_err' => '',
            'date_fin_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data['libelle'] = trim($_POST['libelle']);
            $data['date_debut'] = trim($_POST['date_debut']);
            $data['date_fin'] = trim($_POST['date_fin']);

            // La case 'est_active' dans le formulaire d'édition sert à *demander* l'activation.
            // Si elle est cochée, on passe true au modèle.
            // Si elle n'est pas cochée, on passe false. Le modèle gèrera la logique
            // (par ex. si on essaie de désactiver la seule année active, il pourrait l'empêcher ou le contrôleur `activate` est préféré).
            $data['est_active'] = isset($_POST['est_active']);


            if (empty($data['libelle'])) {
                $data['libelle_err'] = $this->translate('label_required');
            } else {
                $existing = $this->anneeScolaireModel->findByLibelle($data['libelle']);
                if ($existing && $existing->id != $id) {
                    $data['libelle_err'] = $this->translate('label_taken');
                }
            }

            if (empty($data['date_debut'])) $data['date_debut_err'] = $this->translate('start_date_required');
            if (empty($data['date_fin'])) $data['date_fin_err'] = $this->translate('end_date_required');
            elseif (!empty($data['date_debut']) && $data['date_fin'] <= $data['date_debut']) {
                $data['date_fin_err'] = $this->translate('end_date_after_start_date');
            }

            $no_errors = true;
            foreach($data as $key => $value){
                if(str_ends_with($key, '_err') && !empty($value)){
                    $no_errors = false;
                    break;
                }
            }

            if ($no_errors) {
                if ($this->anneeScolaireModel->update($id, $data['libelle'], $data['date_debut'], $data['date_fin'], $data['est_active'])) {
                    $this->setFlashMessage('success', 'academic_year_updated_successfully');
                    $this->redirect('annees_scolaires/index');
                } else {
                    $this->setFlashMessage('error', 'error_updating_academic_year');
                    // S'assurer que la vue reçoit la valeur originale de est_active pour la logique d'affichage de la checkbox
                    $original_annee_for_view = $this->anneeScolaireModel->getById((int)$id);
                    $data['est_active'] = (bool)$original_annee_for_view->est_active;
                    $this->view('annees_scolaires/edit', $data);
                }
            } else {
                 $original_annee_for_view = $this->anneeScolaireModel->getById((int)$id);
                 $data['est_active'] = (bool)$original_annee_for_view->est_active; // Pour affichage correct de la checkbox
                $this->view('annees_scolaires/edit', $data);
            }
        } else {
            $this->view('annees_scolaires/edit', $data);
        }
    }

    public function activate($id = null) {
        if (!$this->userHasPermission('manage_academic_years')) {
            $this->setFlashMessage('error', 'access_denied');
            $this->redirect('annees_scolaires/index');
        }

        if (is_null($id) || !($annee = $this->anneeScolaireModel->getById((int)$id))) {
            $this->setFlashMessage('error', 'academic_year_not_found');
            $this->redirect('annees_scolaires/index');
            return;
        }

        if ($this->anneeScolaireModel->setActive($annee->id)) {
            $this->setFlashMessage('success', 'academic_year_activated_successfully', [':libelle' => $annee->libelle]);
        } else {
            $this->setFlashMessage('error', 'error_activating_academic_year');
        }
        $this->redirect('annees_scolaires/index');
    }

    public function delete($id = null) {
        if (!$this->userHasPermission('manage_academic_years')) {
            $this->setFlashMessage('error', 'access_denied');
            $this->redirect('annees_scolaires/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['id_to_delete'])) {
                 $this->setFlashMessage('error', 'invalid_request');
                 $this->redirect('annees_scolaires/index');
                 return;
            }
            $id_to_delete = (int)$_POST['id_to_delete'];
            $annee = $this->anneeScolaireModel->getById($id_to_delete);

            if (!$annee) {
                $this->setFlashMessage('error', 'academic_year_not_found');
            } elseif ($annee->est_active) {
                $this->setFlashMessage('error', 'cannot_delete_active_year');
            } elseif ($this->anneeScolaireModel->delete($id_to_delete)) {
                $this->setFlashMessage('success', 'academic_year_deleted_successfully');
            } else {
                $this->setFlashMessage('error', 'error_deleting_academic_year_dependencies');
            }
        } else {
            $this->setFlashMessage('error', 'invalid_request_type');
        }
        $this->redirect('annees_scolaires/index');
    }
}
?>
