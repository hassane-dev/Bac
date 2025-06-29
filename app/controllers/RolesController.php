<?php

class RolesController extends Controller {
    private $roleModel;

    public function __construct() {
        parent::__construct();
        // S'assurer que l'utilisateur est connecté pour accéder à ce contrôleur
        // if (!isset($_SESSION['user_id'])) {
        //     $this->redirect('auth/login'); // Rediriger vers la page de connexion
        // }
        // Ajouter une vérification de rôle/permission ici si nécessaire, ex:
        // if (!$this->userHasPermission('manage_roles')) {
        //    $this->redirect('dashboard'); // Ou afficher une page d'erreur
        // }
        $this->roleModel = $this->model('Role');
    }

    /**
     * Affiche la liste de tous les rôles.
     */
    public function index() {
        $roles = $this->roleModel->getAll();
        $this->view('roles/index', ['roles' => $roles, 'title' => $this->translate('role_list')]);
    }

    /**
     * Affiche le formulaire d'ajout de rôle.
     */
    public function create() {
        $this->view('roles/create', ['title' => $this->translate('add_role')]);
    }

    /**
     * Valide et enregistre le nouveau rôle dans la base de données.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'nom_role' => trim($_POST['nom_role']),
                'nom_role_err' => ''
            ];

            // Validation
            if (empty($data['nom_role'])) {
                $data['nom_role_err'] = $this->translate('please_enter_role_name');
            }
            // Vous pourriez ajouter une vérification pour s'assurer que le nom du rôle est unique.
            // Exemple: if ($this->roleModel->findRoleByName($data['nom_role'])) { ... }


            if (empty($data['nom_role_err'])) {
                if ($this->roleModel->add($data)) {
                    // flash('role_message', 'Rôle ajouté avec succès'); // Fonction flash à implémenter
                    $_SESSION['message'] = $this->translate('role_added_successfully');
                    $this->redirect('roles');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_role');
                    // die('Erreur lors de l\'ajout du rôle');
                    $this->view('roles/create', $data);
                }
            } else {
                // Charger la vue avec les erreurs
                $this->view('roles/create', $data);
            }
        } else {
            $this->redirect('roles');
        }
    }

    /**
     * Affiche le formulaire de modification pour un rôle spécifique.
     * @param int $id
     */
    public function edit($id) {
        $role = $this->roleModel->getById($id);

        if (!$role) {
            $_SESSION['error_message'] = $this->translate('role_not_found');
            $this->redirect('roles');
            return;
        }

        $accreditationModel = $this->model('Accreditation');
        $allAccreditations = $accreditationModel->getAll();
        $roleAccreditations = $this->roleModel->getAccreditations($id);
        $roleAccreditationIds = array_map(function($acc) { return $acc->id; }, $roleAccreditations);

        $data = [
            'id' => $id,
            'nom_role' => $role->nom_role,
            'title' => $this->translate('edit_role'),
            'all_accreditations' => $allAccreditations ?? [],
            'role_accreditation_ids' => $roleAccreditationIds ?? []
        ];
        $this->view('roles/edit', $data);
    }

    /**
     * Valide et met à jour le rôle dans la base de données.
     * @param int $id
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $selectedAccreditations = isset($_POST['accreditations']) ? array_map('intval', $_POST['accreditations']) : [];
            $accreditationModel = $this->model('Accreditation'); // Charger le modèle ici pour le réutiliser

            $data = [
                'id' => $id,
                'nom_role' => trim($_POST['nom_role']),
                'nom_role_err' => '',
                'title' => $this->translate('edit_role'),
                'all_accreditations' => $accreditationModel->getAll() ?? [],
                'role_accreditation_ids' => $selectedAccreditations
            ];

            if (empty($data['nom_role'])) {
                $data['nom_role_err'] = $this->translate('please_enter_role_name');
            }
            // TODO: Ajouter vérification unicité si différent de l'actuel
            // $existingRole = $this->roleModel->findRoleByName($data['nom_role']);
            // if ($existingRole && $existingRole->id != $id) {
            //    $data['nom_role_err'] = $this->translate('role_name_already_exists');
            // }


            if (empty($data['nom_role_err'])) {
                // Gérer la transaction pour s'assurer que les deux opérations (update role, update accreditations) réussissent ou échouent ensemble.
                // Note: $this->db->beginTransaction() etc. suppose que l'instance $db est disponible dans le contrôleur.
                // Cela devrait être initialisé dans le constructeur parent ou directement ici si nécessaire.
                // Pour l'instant, on suppose que l'instance $db du modèle Role est la même.
                // Une meilleure approche serait une classe Service qui gère la transaction.

                // Début de la transaction (si votre classe Database le supporte et est partagée)
                // $this->roleModel->getDbInstance()->beginTransaction(); // Exemple d'accès à l'instance DB via le modèle

                if ($this->roleModel->update($id, ['nom_role' => $data['nom_role']])) {
                    if ($this->roleModel->updateAccreditations($id, $selectedAccreditations)) {
                        // $this->roleModel->getDbInstance()->commit(); // Valider la transaction
                        $_SESSION['message'] = $this->translate('role_updated_successfully');
                        $this->redirect('roles');
                    } else {
                        // $this->roleModel->getDbInstance()->rollBack(); // Annuler la transaction
                        $_SESSION['error_message'] = $this->translate('error_updating_role_accreditations');
                        // Les $data sont déjà prêts pour la vue
                        $this->view('roles/edit', $data);
                    }
                } else {
                    // $this->roleModel->getDbInstance()->rollBack(); // Annuler la transaction
                    $_SESSION['error_message'] = $this->translate('error_updating_role');
                    $this->view('roles/edit', $data); // Les $data sont déjà prêts
                }
            } else {
                // Recharger la vue du formulaire avec les erreurs
                // $data['all_accreditations'] et $data['role_accreditation_ids'] sont déjà remplis
                // $role = $this->roleModel->getById($id); // Pas nécessaire si on ne modifie pas $data['nom_role'] plus haut
                // $data['nom_role'] = $role->nom_role; // Conserver le nom original si la validation échoue
                $this->view('roles/edit', $data);
            }
        } else {
            $this->redirect('roles');
        }
    }

    /**
     * Supprime un rôle.
     * @param int $id
     */
    public function delete($id) {
        // S'assurer que la requête est POST pour la suppression (sécurité)
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Ou utiliser un token CSRF
            $role = $this->roleModel->getById($id);
            if (!$role) {
                $_SESSION['error_message'] = $this->translate('role_not_found');
                $this->redirect('roles');
                return;
            }

            // Vérifier si le rôle est utilisé avant de supprimer (amélioration future)
            // if ($this->roleModel->isRoleInUse($id)) {
            //    $_SESSION['error_message'] = $this->translate('role_in_use_cannot_delete');
            //    $this->redirect('roles');
            //    return;
            // }

            if ($this->roleModel->delete($id)) {
                $_SESSION['message'] = $this->translate('role_deleted_successfully');
            } else {
                $_SESSION['error_message'] = $this->translate('error_deleting_role');
            }
            $this->redirect('roles');
        // } else {
        //     $this->redirect('roles');
        // }
    }
}
?>
