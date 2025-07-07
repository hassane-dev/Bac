<?php

class LyceesController extends Controller {
    private $lyceeModel;

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Permissions pour 'manage_lycees'
        $this->lyceeModel = $this->model('Lycee');
    }

    public function index() {
        $lycees = $this->lyceeModel->getAll();
        $this->view('lycees/index', ['lycees' => $lycees, 'title' => $this->translate('lycees_list')]);
    }

    public function create() {
        $data = [
            'title' => $this->translate('add_lycee'),
            'nom_lycee' => '',
            'description' => '',
            'nom_lycee_err' => '',
            'description_err' => '' // Pas d'erreur spécifique pour description pour l'instant
        ];
        $this->view('lycees/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'nom_lycee' => trim($_POST['nom_lycee']),
                'description' => trim($_POST['description'] ?? null),
                'title' => $this->translate('add_lycee'),
                'nom_lycee_err' => ''
            ];

            if (empty($data['nom_lycee'])) {
                $data['nom_lycee_err'] = $this->translate('lycee_name_required');
            } elseif ($this->lyceeModel->nomExists($data['nom_lycee'])) {
                $data['nom_lycee_err'] = $this->translate('lycee_name_taken');
            }

            if (empty($data['nom_lycee_err'])) {
                if ($this->lyceeModel->add($data)) {
                    $_SESSION['message'] = $this->translate('lycee_added_successfully');
                    $this->redirect('lycees');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_lycee');
                    $this->view('lycees/create', $data);
                }
            } else {
                $this->view('lycees/create', $data);
            }
        } else {
            $this->redirect('lycees/create');
        }
    }

    public function edit($id) {
        $id = (int)$id;
        $lycee = $this->lyceeModel->getById($id);

        if (!$lycee) {
            $_SESSION['error_message'] = $this->translate('lycee_not_found');
            $this->redirect('lycees');
            return;
        }

        $data = [
            'id' => $id,
            'nom_lycee' => $lycee->nom_lycee,
            'description' => $lycee->description,
            'title' => $this->translate('edit_lycee'),
            'nom_lycee_err' => ''
        ];
        $this->view('lycees/edit', $data);
    }

    public function update($id) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $id,
                'nom_lycee' => trim($_POST['nom_lycee']),
                'description' => trim($_POST['description'] ?? null),
                'title' => $this->translate('edit_lycee'),
                'nom_lycee_err' => ''
            ];

            if (empty($data['nom_lycee'])) {
                $data['nom_lycee_err'] = $this->translate('lycee_name_required');
            } elseif ($this->lyceeModel->nomExists($data['nom_lycee'], $id)) {
                $data['nom_lycee_err'] = $this->translate('lycee_name_taken');
            }

            if (empty($data['nom_lycee_err'])) {
                if ($this->lyceeModel->update($id, $data)) {
                    $_SESSION['message'] = $this->translate('lycee_updated_successfully');
                    $this->redirect('lycees');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_lycee');
                    $this->view('lycees/edit', $data);
                }
            } else {
                // Re-populate description in case of error and nom_lycee was changed
                $currentLycee = $this->lyceeModel->getById($id);
                $data['description'] = $currentLycee->description; // Keep original if not submitted or if error on other field
                $this->view('lycees/edit', $data);
            }
        } else {
            $this->redirect('lycees');
        }
    }

    public function delete($id) {
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Securité
            $lycee = $this->lyceeModel->getById($id);
            if (!$lycee) {
                $_SESSION['error_message'] = $this->translate('lycee_not_found');
                $this->redirect('lycees');
                return;
            }

            if ($this->lyceeModel->delete($id)) {
                $_SESSION['message'] = $this->translate('lycee_deleted_successfully');
            } else {
                 if(empty($_SESSION['error_message'])){
                     $_SESSION['error_message'] = $this->translate('error_deleting_lycee');
                 }
            }
            $this->redirect('lycees');
        // } else {
        //     $this->redirect('lycees');
        // }
    }
}
?>
