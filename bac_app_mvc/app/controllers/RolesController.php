<?php

class RolesController extends Controller {
    private $roleModel;
    private $accreditationModel; // Pour charger toutes les accréditations

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Affiner la vérification des permissions, ex:
        // if (!$this->userHasPermission('manage_roles')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->roleModel = $this->model('Role');
        $this->accreditationModel = $this->model('Accreditation'); // Initialiser ici
    }

    public function index() {
        $roles = $this->roleModel->getAll();
        $this->view('roles/index', ['roles' => $roles, 'title' => $this->translate('role_list')]);
    }

    public function create() {
        $data = [
            'title' => $this->translate('add_role'),
            'nom_role' => '',
            'nom_role_err' => ''
        ];
        $this->view('roles/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'nom_role' => trim($_POST['nom_role']),
                'title' => $this->translate('add_role'),
                'nom_role_err' => ''
            ];

            if (empty($data['nom_role'])) {
                $data['nom_role_err'] = $this->translate('please_enter_role_name');
            }
            // TODO: Ajouter vérification unicité du nom de rôle

            if (empty($data['nom_role_err'])) {
                if ($this->roleModel->add($data)) {
                    $_SESSION['message'] = $this->translate('role_added_successfully');
                    $this->redirect('roles');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_role');
                    $this->view('roles/create', $data);
                }
            } else {
                $this->view('roles/create', $data);
            }
        } else {
            $this->redirect('roles/create');
        }
    }

    public function edit($id) {
        $role = $this->roleModel->getById((int)$id);
        if (!$role) {
            $_SESSION['error_message'] = $this->translate('role_not_found');
            $this->redirect('roles');
            return;
        }

        $allAccreditations = $this->accreditationModel->getAll();
        $roleAccreditationsObjects = $this->roleModel->getAccreditations((int)$id);
        $roleAccreditationIds = array_map(function($acc) { return $acc->id; }, $roleAccreditationsObjects);

        $data = [
            'id' => (int)$id,
            'nom_role' => $role->nom_role,
            'title' => $this->translate('edit_role'),
            'all_accreditations' => $allAccreditations ?? [],
            'role_accreditation_ids' => $roleAccreditationIds,
            'nom_role_err' => ''
        ];
        $this->view('roles/edit', $data);
    }

    public function update($id) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $selectedAccreditations = isset($_POST['accreditations']) && is_array($_POST['accreditations']) ? array_map('intval', $_POST['accreditations']) : [];

            $data = [
                'id' => $id,
                'nom_role' => trim($_POST['nom_role']),
                'title' => $this->translate('edit_role'),
                'all_accreditations' => $this->accreditationModel->getAll() ?? [], // Re-passer pour la vue si erreur
                'role_accreditation_ids' => $selectedAccreditations, // Ce que l'utilisateur a coché
                'nom_role_err' => ''
            ];

            if (empty($data['nom_role'])) {
                $data['nom_role_err'] = $this->translate('please_enter_role_name');
            }
            // TODO: Ajouter vérification unicité si nom différent de l'actuel

            if (empty($data['nom_role_err'])) {
                $dbInstance = $this->roleModel->getDbInstance(); // Obtenir l'instance Database
                try {
                    $dbInstance->beginTransaction();
                    $roleUpdateSuccess = $this->roleModel->update($id, ['nom_role' => $data['nom_role']]);
                    $accreditationUpdateSuccess = $this->roleModel->updateAccreditations($id, $selectedAccreditations);

                    if ($roleUpdateSuccess && $accreditationUpdateSuccess) {
                        $dbInstance->commit();
                        $_SESSION['message'] = $this->translate('role_updated_successfully');
                        $this->redirect('roles');
                    } else {
                        $dbInstance->rollBack();
                        $_SESSION['error_message'] = $this->translate('error_updating_role_or_accreditations');
                        $this->view('roles/edit', $data);
                    }
                } catch (Exception $e) {
                    $dbInstance->rollBack();
                    error_log("Erreur transactionnelle RolesController::update : " . $e->getMessage());
                    $_SESSION['error_message'] = $this->translate('error_updating_role_or_accreditations');
                    $this->view('roles/edit', $data);
                }
            } else {
                 // Si validation échoue, on doit recharger les accréditations actuellement associées au rôle, et non celles soumises.
                $roleAccreditationsObjects = $this->roleModel->getAccreditations($id);
                $data['role_accreditation_ids'] = array_map(function($acc) { return $acc->id; }, $roleAccreditationsObjects);
                $this->view('roles/edit', $data);
            }
        } else {
            $this->redirect('roles');
        }
    }

    public function delete($id) {
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Ajouter une vérification CSRF ici serait mieux
            if ($id === 1) { // Supposition: ID 1 est Administrateur
                $_SESSION['error_message'] = $this->translate('cannot_delete_admin_role');
                $this->redirect('roles');
                return;
            }

            // TODO: Vérifier si le rôle est utilisé par des utilisateurs avant la suppression
            // $userModel = $this->model('User');
            // if ($userModel->isRoleInUse($id)) {
            //    $_SESSION['error_message'] = $this->translate('role_in_use_cannot_delete');
            //    $this->redirect('roles');
            //    return;
            // }

            if ($this->roleModel->delete($id)) {
                $_SESSION['message'] = $this->translate('role_deleted_successfully');
            } else {
                // Le message d'erreur spécifique peut avoir été défini dans le modèle
                if(empty($_SESSION['error_message'])) {
                    $_SESSION['error_message'] = $this->translate('error_deleting_role');
                }
            }
            $this->redirect('roles');
        // } else {
        //     $this->redirect('roles');
        // }
    }
}
?>
