<?php

class MatieresController extends Controller {
    private $matiereModel;

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Permissions
        // if (!$this->userHasPermission('manage_matieres')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->matiereModel = $this->model('Matiere');
    }

    public function index() {
        $matieres = $this->matiereModel->getAll();
        $this->view('matieres/index', ['matieres' => $matieres, 'title' => $this->translate('subjects_list')]);
    }

    public function create() {
        $data = [
            'title' => $this->translate('add_subject'),
            'code' => '',
            'nom' => '',
            'code_err' => '',
            'nom_err' => ''
        ];
        $this->view('matieres/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'code' => strtoupper(trim($_POST['code'])),
                'nom' => trim($_POST['nom']),
                'title' => $this->translate('add_subject'),
                'code_err' => '',
                'nom_err' => ''
            ];

            if (empty($data['code'])) $data['code_err'] = $this->translate('subject_code_required');
            elseif ($this->matiereModel->codeExists($data['code'])) $data['code_err'] = $this->translate('subject_code_taken');

            if (empty($data['nom'])) $data['nom_err'] = $this->translate('subject_name_required');

            if (empty($data['code_err']) && empty($data['nom_err'])) {
                if ($this->matiereModel->add($data)) {
                    $_SESSION['message'] = $this->translate('subject_added_successfully');
                    $this->redirect('matieres');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_subject');
                    $this->view('matieres/create', $data);
                }
            } else {
                $this->view('matieres/create', $data);
            }
        } else {
            $this->redirect('matieres/create');
        }
    }

    public function edit($id) {
        $id = (int)$id;
        $matiere = $this->matiereModel->getById($id);

        if (!$matiere) {
            $_SESSION['error_message'] = $this->translate('subject_not_found');
            $this->redirect('matieres');
            return;
        }

        $data = [
            'id' => $id,
            'code' => $matiere->code,
            'nom' => $matiere->nom,
            'title' => $this->translate('edit_subject'),
            'code_err' => '',
            'nom_err' => ''
        ];
        $this->view('matieres/edit', $data);
    }

    public function update($id) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $id,
                'code' => strtoupper(trim($_POST['code'])),
                'nom' => trim($_POST['nom']),
                'title' => $this->translate('edit_subject'),
                'code_err' => '',
                'nom_err' => ''
            ];

            if (empty($data['code'])) $data['code_err'] = $this->translate('subject_code_required');
            elseif ($this->matiereModel->codeExists($data['code'], $id)) $data['code_err'] = $this->translate('subject_code_taken');

            if (empty($data['nom'])) $data['nom_err'] = $this->translate('subject_name_required');

            if (empty($data['code_err']) && empty($data['nom_err'])) {
                if ($this->matiereModel->update($id, $data)) {
                    $_SESSION['message'] = $this->translate('subject_updated_successfully');
                    $this->redirect('matieres');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_subject');
                    $this->view('matieres/edit', $data);
                }
            } else {
                $matiere = $this->matiereModel->getById($id); // Pour garder les valeurs originales si validation échoue
                $data['code'] = $data['code_err'] ? $_POST['code'] : $matiere->code;
                $data['nom'] = $data['nom_err'] ? $_POST['nom'] : $matiere->nom;
                $this->view('matieres/edit', $data);
            }
        } else {
            $this->redirect('matieres');
        }
    }

    public function delete($id) {
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Sécurité
            $matiere = $this->matiereModel->getById($id);
            if (!$matiere) {
                $_SESSION['error_message'] = $this->translate('subject_not_found');
                $this->redirect('matieres');
                return;
            }

            if ($this->matiereModel->delete($id)) {
                $_SESSION['message'] = $this->translate('subject_deleted_successfully');
            } else {
                 if(empty($_SESSION['error_message'])){
                    $_SESSION['error_message'] = $this->translate('error_deleting_subject');
                 }
            }
            $this->redirect('matieres');
        // } else {
        //     $this->redirect('matieres');
        // }
    }
}
?>
