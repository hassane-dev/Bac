<?php

class AccreditationsController extends Controller {
    private $accreditationModel;

    public function __construct() {
        parent::__construct();
        // TODO: Ajouter vérification de session et permissions comme pour RolesController
        // if (!isset($_SESSION['user_id'])) {
        //     $this->redirect('auth/login');
        // }
        // if (!$this->userHasPermission('manage_accreditations')) {
        //    $this->redirect('dashboard');
        // }
        $this->accreditationModel = $this->model('Accreditation');
    }

    /**
     * Affiche la liste de toutes les accréditations.
     */
    public function index() {
        $accreditations = $this->accreditationModel->getAll();
        $this->view('accreditations/index', [
            'accreditations' => $accreditations,
            'title' => $this->translate('accreditation_list')
        ]);
    }

    /**
     * Affiche le formulaire d'ajout d'accréditation.
     */
    public function create() {
        $this->view('accreditations/create', ['title' => $this->translate('add_accreditation')]);
    }

    /**
     * Valide et enregistre la nouvelle accréditation.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'libelle_action' => trim($_POST['libelle_action']),
                'libelle_action_err' => ''
            ];

            if (empty($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('please_enter_accreditation_label');
            }
            // TODO: Vérifier l'unicité du libellé_action

            if (empty($data['libelle_action_err'])) {
                if ($this->accreditationModel->add($data)) {
                    $_SESSION['message'] = $this->translate('accreditation_added_successfully');
                    $this->redirect('accreditations');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_accreditation');
                    $this->view('accreditations/create', $data);
                }
            } else {
                $this->view('accreditations/create', $data);
            }
        } else {
            $this->redirect('accreditations');
        }
    }

    /**
     * Affiche le formulaire de modification pour une accréditation.
     * @param int $id
     */
    public function edit($id) {
        $accreditation = $this->accreditationModel->getById($id);

        if (!$accreditation) {
            $_SESSION['error_message'] = $this->translate('accreditation_not_found');
            $this->redirect('accreditations');
            return;
        }

        $data = [
            'id' => $id,
            'libelle_action' => $accreditation->libelle_action,
            'title' => $this->translate('edit_accreditation')
        ];
        $this->view('accreditations/edit', $data);
    }

    /**
     * Valide et met à jour une accréditation.
     * @param int $id
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'libelle_action' => trim($_POST['libelle_action']),
                'libelle_action_err' => '',
                'title' => $this->translate('edit_accreditation')
            ];

            if (empty($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('please_enter_accreditation_label');
            }
            // TODO: Vérifier unicité si différent de l'actuel

            if (empty($data['libelle_action_err'])) {
                if ($this->accreditationModel->update($id, $data)) {
                    $_SESSION['message'] = $this->translate('accreditation_updated_successfully');
                    $this->redirect('accreditations');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_accreditation');
                    // Re-populate data for the view if update fails
                    $accreditation = $this->accreditationModel->getById($id);
                    $data['libelle_action'] = $accreditation ? $accreditation->libelle_action : $data['libelle_action'];
                    $this->view('accreditations/edit', $data);
                }
            } else {
                 // Re-populate data for the view if validation fails
                $accreditation = $this->accreditationModel->getById($id);
                $data['libelle_action'] = $accreditation ? $accreditation->libelle_action : $data['libelle_action'];
                $this->view('accreditations/edit', $data);
            }
        } else {
            $this->redirect('accreditations');
        }
    }

    /**
     * Supprime une accréditation.
     * @param int $id
     */
    public function delete($id) {
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Sécurité : s'assurer que c'est une requête POST
            $accreditation = $this->accreditationModel->getById($id);
            if (!$accreditation) {
                $_SESSION['error_message'] = $this->translate('accreditation_not_found');
                $this->redirect('accreditations');
                return;
            }

            // TODO: Vérifier si l'accréditation est utilisée dans roles_accreditations
            // if ($this->accreditationModel->isAccreditationInUse($id)) {
            // $_SESSION['error_message'] = $this->translate('accreditation_in_use_cannot_delete');
            // $this->redirect('accreditations');
            // return;
            // }

            if ($this->accreditationModel->delete($id)) {
                $_SESSION['message'] = $this->translate('accreditation_deleted_successfully');
            } else {
                $_SESSION['error_message'] = $this->translate('error_deleting_accreditation');
            }
            $this->redirect('accreditations');
        // } else {
        //     $this->redirect('accreditations');
        // }
    }
}
?>
