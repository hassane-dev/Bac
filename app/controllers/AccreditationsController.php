<?php

class AccreditationsController extends Controller {
    private $accreditationModel;

    public function __construct() {
        parent::__construct(); // Important pour initialiser $this->db, charger les traductions, etc.
        $this->accreditationModel = $this->model('AccreditationModel');

        // Sécuriser le contrôleur - Seuls les utilisateurs connectés peuvent y accéder
        // Des permissions plus granulaires peuvent être ajoutées par méthode si nécessaire
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
        // Exemple de vérification de permission plus spécifique (à adapter)
        // if (!$this->userHasPermission('manage_accreditations')) {
        //     $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
        //     $this->redirect('dashboard/index');
        // }
    }

    public function index() {
        // Vérification de permission spécifique pour voir la liste
        if (!$this->userHasPermission('view_accreditations') && !$this->userHasPermission('manage_accreditations')) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
             $this->redirect('dashboard/index');
        }

        $accreditations = $this->accreditationModel->getAll();
        $data = [
            'page_title' => $this->translate('accreditation_list'),
            'accreditations' => $accreditations,
            // Les données nécessaires pour le layout (current_lang, isLoggedIn, etc.) sont déjà ajoutées par $this->view()
        ];
        $this->view('accreditations/index', $data);
    }

    public function add() {
        if (!$this->userHasPermission('manage_accreditations')) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
             $this->redirect('accreditations/index');
        }

        $data = [
            'page_title' => $this->translate('add_new_accreditation'),
            'libelle_action' => '',
            'libelle_action_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data['libelle_action'] = trim($_POST['libelle_action']);

            if (empty($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('please_enter_accreditation_label');
            } elseif ($this->accreditationModel->findByLibelle($data['libelle_action'])) {
                $data['libelle_action_err'] = $this->translate('accreditation_label_taken');
            }

            if (empty($data['libelle_action_err'])) {
                if ($this->accreditationModel->create($data['libelle_action'])) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => $this->translate('accreditation_added_successfully')];
                    $this->redirect('accreditations/index');
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('error_adding_accreditation')];
                    // La vue add sera re-rendue avec les données et le message flash sera affiché par le layout
                    $this->view('accreditations/add', $data);
                }
            } else {
                $this->view('accreditations/add', $data);
            }
        } else {
            // Afficher le formulaire vide
            $this->view('accreditations/add', $data);
        }
    }

    public function edit($id = null) {
        if (!$this->userHasPermission('manage_accreditations')) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
             $this->redirect('accreditations/index');
        }

        if (is_null($id) || !($accreditation = $this->accreditationModel->getById((int)$id))) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('accreditation_not_found')];
            $this->redirect('accreditations/index');
            return;
        }

        $data = [
            'page_title' => $this->translate('edit_accreditation'),
            'id' => $accreditation->id,
            'libelle_action' => $accreditation->libelle_action,
            'libelle_action_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $new_libelle_action = trim($_POST['libelle_action']);

            if (empty($new_libelle_action)) {
                $data['libelle_action_err'] = $this->translate('please_enter_accreditation_label');
            } else {
                // Vérifier l'unicité seulement si le libellé a changé
                if (strtolower($new_libelle_action) !== strtolower($accreditation->libelle_action)) {
                    if ($this->accreditationModel->findByLibelle($new_libelle_action)) {
                        $data['libelle_action_err'] = $this->translate('accreditation_label_taken');
                    }
                }
            }

            $data['libelle_action'] = $new_libelle_action; // Mettre à jour pour l'affichage en cas d'erreur

            if (empty($data['libelle_action_err'])) {
                if ($this->accreditationModel->update($data['id'], $new_libelle_action)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => $this->translate('accreditation_updated_successfully')];
                    $this->redirect('accreditations/index');
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('error_updating_accreditation')];
                     $this->view('accreditations/edit', $data);
                }
            } else {
                $this->view('accreditations/edit', $data);
            }
        } else {
            // Afficher le formulaire pré-rempli
            $this->view('accreditations/edit', $data);
        }
    }

    public function delete($id = null) {
        if (!$this->userHasPermission('manage_accreditations')) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('access_denied')];
             $this->redirect('accreditations/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $accreditation_id = (int)$_POST['id_to_delete']; // S'assurer que l'ID vient du formulaire POST

            if (!$this->accreditationModel->getById($accreditation_id)) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('accreditation_not_found')];
                $this->redirect('accreditations/index');
                return;
            }

            if ($this->accreditationModel->delete($accreditation_id)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => $this->translate('accreditation_deleted_successfully')];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => $this->translate('error_deleting_accreditation')];
            }
            $this->redirect('accreditations/index');
        } else {
            // Si ce n'est pas POST, rediriger. La suppression doit être intentionnelle via un formulaire.
            // On pourrait aussi afficher une page de confirmation ici si $id est passé en GET.
            // Pour l'instant, on s'attend à un POST depuis un formulaire de confirmation.
            $this->redirect('accreditations/index');
        }
    }
}
?>
