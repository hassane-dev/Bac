<?php

class ElevesController extends Controller {
    private $eleveModel;
    private $anneeScolaireModel;
    private $serieModel;
    private $lyceeModel;
    private $uploadDirPhotos = 'uploads/eleves_photos/';

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Permissions pour 'manage_eleves' ou 'enroll_eleves'
        $this->eleveModel = $this->model('Eleve');
        $this->anneeScolaireModel = $this->model('AnneeScolaire');
        $this->serieModel = $this->model('Serie');
        $this->lyceeModel = $this->model('Lycee');

        $fullUploadDir = APP_ROOT . '/public/' . $this->uploadDirPhotos;
        if (!is_dir($fullUploadDir)) {
            mkdir($fullUploadDir, 0755, true);
        }
    }

    public function index($annee_scolaire_id_filter = null) {
        $anneesScolaires = $this->anneeScolaireModel->getAll();
        $activeYear = $this->anneeScolaireModel->getActiveYear();

        if ($annee_scolaire_id_filter === null && $activeYear) {
            $annee_scolaire_id_filter = $activeYear->id;
        } elseif ($annee_scolaire_id_filter === null && !empty($anneesScolaires)) {
            $annee_scolaire_id_filter = $anneesScolaires[0]->id; // Fallback sur la première si pas d'année active
        } else {
            $annee_scolaire_id_filter = (int)$annee_scolaire_id_filter;
        }

        $eleves = $annee_scolaire_id_filter ? $this->eleveModel->getAll($annee_scolaire_id_filter) : [];

        $this->view('eleves/index', [
            'eleves' => $eleves,
            'annees_scolaires' => $anneesScolaires,
            'selected_annee_id' => $annee_scolaire_id_filter,
            'title' => $this->translate('student_list')
        ]);
    }

    public function create() {
        $activeYear = $this->anneeScolaireModel->getActiveYear();
        if (!$activeYear) {
            $_SESSION['error_message'] = $this->translate('no_active_academic_year_enroll');
            $this->redirect('anneesscolaires'); // Suggérer de définir une année active
            return;
        }

        $data = [
            'title' => $this->translate('add_student'),
            'annee_scolaire_id' => $activeYear->id,
            'annee_scolaire_libelle' => $activeYear->libelle, // Pour affichage
            'series' => $this->serieModel->getAll(),
            'lycees' => $this->lyceeModel->getAll(),
            // Initialiser les champs pour éviter les erreurs undefined dans la vue
            'matricule' => '', 'nom' => '', 'prenom' => '', 'date_naissance' => '', 'sexe' => 'M',
            'serie_id' => '', 'lycee_id' => '', 'photo_path' => null,
            'empreinte1' => '', /* ... */ 'empreinte10' => '',
            'errors' => [] // Pour stocker les erreurs de validation
        ];
        $this->view('eleves/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $activeYear = $this->anneeScolaireModel->getActiveYear();

            $data = [
                'matricule' => trim($_POST['matricule']),
                'nom' => trim($_POST['nom']),
                'prenom' => trim($_POST['prenom']),
                'date_naissance' => $_POST['date_naissance'],
                'sexe' => $_POST['sexe'],
                'serie_id' => (int)$_POST['serie_id'],
                'lycee_id' => (int)$_POST['lycee_id'],
                'annee_scolaire_id' => (int)($_POST['annee_scolaire_id'] ?? ($activeYear ? $activeYear->id : null)),
                'photo_path' => null, // Sera défini par l'upload ou la capture webcam
                'webcam_photo_data' => $_POST['webcam_photo_data'] ?? null, // Données base64 de la webcam
                // Empreintes (à adapter selon la méthode de capture réelle)
                'empreinte1' => $_POST['empreinte1'] ?? null, 'empreinte2' => $_POST['empreinte2'] ?? null,
                'empreinte3' => $_POST['empreinte3'] ?? null, 'empreinte4' => $_POST['empreinte4'] ?? null,
                'empreinte5' => $_POST['empreinte5'] ?? null, 'empreinte6' => $_POST['empreinte6'] ?? null,
                'empreinte7' => $_POST['empreinte7'] ?? null, 'empreinte8' => $_POST['empreinte8'] ?? null,
                'empreinte9' => $_POST['empreinte9'] ?? null, 'empreinte10' => $_POST['empreinte10'] ?? null,
                'errors' => []
            ];

            // --- Validation ---
            if (empty($data['matricule'])) $data['errors']['matricule_err'] = $this->translate('matricule_required');
            elseif ($this->eleveModel->matriculeExists($data['matricule'], $data['annee_scolaire_id'])) $data['errors']['matricule_err'] = $this->translate('matricule_exists_for_year');
            // ... Autres validations pour nom, prenom, date_naissance, sexe, serie_id, lycee_id, annee_scolaire_id ...
            if (empty($data['nom'])) $data['errors']['nom_err'] = $this->translate('lastname_required');
            if (empty($data['prenom'])) $data['errors']['prenom_err'] = $this->translate('firstname_required');
            if (empty($data['date_naissance'])) $data['errors']['date_naissance_err'] = $this->translate('dob_required');
            if (empty($data['sexe'])) $data['errors']['sexe_err'] = $this->translate('gender_required');
            if (empty($data['serie_id'])) $data['errors']['serie_id_err'] = $this->translate('serie_required');
            if (empty($data['lycee_id'])) $data['errors']['lycee_id_err'] = $this->translate('lycee_required');
            if (empty($data['annee_scolaire_id'])) $data['errors']['annee_scolaire_id_err'] = $this->translate('academic_year_required');


            // Gestion Photo (Webcam prioritaire sur Upload)
            if (!empty($data['webcam_photo_data'])) {
                $imgData = str_replace('data:image/jpeg;base64,', '', $data['webcam_photo_data']);
                $imgData = str_replace(' ', '+', $imgData);
                $fileData = base64_decode($imgData);
                $fileName = uniqid('eleve_wc_') . '.jpg';
                $filePath = $this->uploadDirPhotos . $fileName;
                if (file_put_contents(APP_ROOT . '/public/' . $filePath, $fileData)) {
                    $data['photo_path'] = $filePath;
                } else {
                    $data['errors']['photo_err'] = $this->translate('error_saving_webcam_photo');
                }
            } elseif (isset($_FILES['photo_upload']) && $_FILES['photo_upload']['error'] == UPLOAD_ERR_OK) {
                $uploadResult = $this->handlePhotoUpload('photo_upload', 'eleve_up_');
                if ($uploadResult['success']) {
                    $data['photo_path'] = $uploadResult['path'];
                } else {
                    $data['errors']['photo_err'] = $uploadResult['message'];
                }
            }
            $data['photo'] = $data['photo_path']; // Le modèle attend 'photo'

            if (empty($data['errors'])) {
                if ($this->eleveModel->add($data)) {
                    $_SESSION['message'] = $this->translate('student_added_successfully');
                    $this->redirect('eleves/index/' . $data['annee_scolaire_id']);
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_student');
                    // Repopuler les données pour la vue create
                    $data['title'] = $this->translate('add_student');
                    $data['annee_scolaire_libelle'] = $activeYear ? $activeYear->libelle : '';
                    $data['series'] = $this->serieModel->getAll();
                    $data['lycees'] = $this->lyceeModel->getAll();
                    $this->view('eleves/create', $data);
                }
            } else {
                // Repopuler les données pour la vue create
                $data['title'] = $this->translate('add_student');
                $data['annee_scolaire_libelle'] = $activeYear ? $activeYear->libelle : '';
                $data['series'] = $this->serieModel->getAll();
                $data['lycees'] = $this->lyceeModel->getAll();
                $this->view('eleves/create', $data);
            }
        } else {
            $this->redirect('eleves/create');
        }
    }

    public function edit($id) {
        $id = (int)$id;
        $eleve = $this->eleveModel->getById($id);

        if (!$eleve) {
            $_SESSION['error_message'] = $this->translate('student_not_found');
            $this->redirect('eleves');
            return;
        }

        $data = [
            'title' => $this->translate('edit_student') . ' : ' . $eleve->nom . ' ' . $eleve->prenom,
            'id' => $id,
            'matricule' => $eleve->matricule, 'nom' => $eleve->nom, 'prenom' => $eleve->prenom,
            'date_naissance' => $eleve->date_naissance, 'sexe' => $eleve->sexe,
            'serie_id' => $eleve->serie_id, 'lycee_id' => $eleve->lycee_id,
            'annee_scolaire_id' => $eleve->annee_scolaire_id,
            'photo_path' => $eleve->photo,
            'annee_scolaire_libelle' => $eleve->annee_scolaire_libelle, // Vient de la jointure dans getById
            'series' => $this->serieModel->getAll(),
            'lycees' => $this->lyceeModel->getAll(),
            'annees_scolaires' => $this->anneeScolaireModel->getAll(), // Pour pouvoir changer l'année
            'errors' => []
        ];
        for ($i = 1; $i <= 10; $i++) {
            $data["empreinte{$i}"] = $eleve->{"empreinte{$i}"};
        }
        $this->view('eleves/edit', $data);
    }

    public function update($id) {
        $id = (int)$id;
         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $currentEleve = $this->eleveModel->getById($id);
            if (!$currentEleve) {
                 $_SESSION['error_message'] = $this->translate('student_not_found');
                 $this->redirect('eleves');
                 return;
            }

            $data = [
                'id' => $id,
                'matricule' => trim($_POST['matricule']),
                'nom' => trim($_POST['nom']),
                'prenom' => trim($_POST['prenom']),
                'date_naissance' => $_POST['date_naissance'],
                'sexe' => $_POST['sexe'],
                'serie_id' => (int)$_POST['serie_id'],
                'lycee_id' => (int)$_POST['lycee_id'],
                'annee_scolaire_id' => (int)$_POST['annee_scolaire_id'],
                'photo_path' => $currentEleve->photo, // Garder l'ancienne photo par défaut
                'webcam_photo_data' => $_POST['webcam_photo_data'] ?? null,
                'errors' => []
            ];
            for ($i = 1; $i <= 10; $i++) {
                $data["empreinte{$i}"] = $_POST["empreinte{$i}"] ?? $currentEleve->{"empreinte{$i}"};
            }

            // Validations
            if (empty($data['matricule'])) $data['errors']['matricule_err'] = $this->translate('matricule_required');
            elseif ($this->eleveModel->matriculeExists($data['matricule'], $data['annee_scolaire_id'], $id)) $data['errors']['matricule_err'] = $this->translate('matricule_exists_for_year');
            // ... Autres validations ...
             if (empty($data['nom'])) $data['errors']['nom_err'] = $this->translate('lastname_required');


            // Gestion Photo
            if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
                if ($data['photo_path'] && file_exists(APP_ROOT . '/public/' . $data['photo_path'])) {
                    unlink(APP_ROOT . '/public/' . $data['photo_path']);
                }
                $data['photo_path'] = null;
            } elseif (!empty($data['webcam_photo_data'])) {
                // ... (logique similaire à store pour webcam) ...
                $imgData = str_replace('data:image/jpeg;base64,', '', $data['webcam_photo_data']);
                $imgData = str_replace(' ', '+', $imgData);
                $fileData = base64_decode($imgData);
                $fileName = uniqid('eleve_wc_edit_') . '.jpg';
                $newFilePath = $this->uploadDirPhotos . $fileName;
                if (file_put_contents(APP_ROOT . '/public/' . $newFilePath, $fileData)) {
                    // Supprimer l'ancienne photo si elle existe et est différente
                    if ($data['photo_path'] && $data['photo_path'] !== $newFilePath && file_exists(APP_ROOT . '/public/' . $data['photo_path'])) {
                        unlink(APP_ROOT . '/public/' . $data['photo_path']);
                    }
                    $data['photo_path'] = $newFilePath;
                } else {
                    $data['errors']['photo_err'] = $this->translate('error_saving_webcam_photo');
                }
            } elseif (isset($_FILES['photo_upload']) && $_FILES['photo_upload']['error'] == UPLOAD_ERR_OK) {
                $uploadResult = $this->handlePhotoUpload('photo_upload', 'eleve_up_edit_', $currentEleve->photo);
                if ($uploadResult['success']) {
                    $data['photo_path'] = $uploadResult['path'];
                } else {
                    $data['errors']['photo_err'] = $uploadResult['message'];
                }
            }
            $data['photo'] = $data['photo_path'];


            if (empty($data['errors'])) {
                if ($this->eleveModel->update($id, $data)) {
                    $_SESSION['message'] = $this->translate('student_updated_successfully');
                    $this->redirect('eleves/index/' . $data['annee_scolaire_id']);
                } else {
                    $_SESSION['error_message'] = $this->translate('error_updating_student');
                    // Repopulate data for view
                    $data['title'] = $this->translate('edit_student') . ' : ' . $currentEleve->nom . ' ' . $currentEleve->prenom;
                    $data['series'] = $this->serieModel->getAll();
                    $data['lycees'] = $this->lyceeModel->getAll();
                    $data['annees_scolaires'] = $this->anneeScolaireModel->getAll();
                    $anneeInfo = $this->anneeScolaireModel->getById($data['annee_scolaire_id']);
                    $data['annee_scolaire_libelle'] = $anneeInfo ? $anneeInfo->libelle : '';
                    $this->view('eleves/edit', $data);
                }
            } else {
                 // Repopulate data for view
                $data['title'] = $this->translate('edit_student') . ' : ' . $currentEleve->nom . ' ' . $currentEleve->prenom;
                $data['series'] = $this->serieModel->getAll();
                $data['lycees'] = $this->lyceeModel->getAll();
                $data['annees_scolaires'] = $this->anneeScolaireModel->getAll();
                $anneeInfo = $this->anneeScolaireModel->getById($data['annee_scolaire_id']);
                $data['annee_scolaire_libelle'] = $anneeInfo ? $anneeInfo->libelle : '';
                $this->view('eleves/edit', $data);
            }
        } else {
            $this->redirect('eleves');
        }
    }


    public function delete($id) {
        $id = (int)$id;
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $eleve = $this->eleveModel->getById($id);
            if (!$eleve) {
                $_SESSION['error_message'] = $this->translate('student_not_found');
                $this->redirect('eleves');
                return;
            }

            $photo_path_to_delete = $eleve->photo;

            if ($this->eleveModel->delete($id)) {
                if ($photo_path_to_delete && file_exists(APP_ROOT . '/public/' . $photo_path_to_delete)) {
                    unlink(APP_ROOT . '/public/' . $photo_path_to_delete);
                }
                $_SESSION['message'] = $this->translate('student_deleted_successfully');
            } else {
                 if(empty($_SESSION['error_message'])){
                     $_SESSION['error_message'] = $this->translate('error_deleting_student');
                 }
            }
            $this->redirect('eleves/index/' . $eleve->annee_scolaire_id);
        // } else {
        //     $this->redirect('eleves');
        // }
    }

    private function handlePhotoUpload($fileInputName, $prefix, $currentPhotoPath = null) {
        $targetDir = APP_ROOT . '/public/' . $this->uploadDirPhotos;
        $fileName = uniqid($prefix) . '_' . basename($_FILES[$fileInputName]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES[$fileInputName]["tmp_name"]);
        if ($check === false) return ['success' => false, 'message' => $this->translate('file_not_image')];

        if ($_FILES[$fileInputName]["size"] > 2000000) { // 2MB limit
            return ['success' => false, 'message' => $this->translate('file_too_large', ['size' => '2MB'])];
        }

        $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            return ['success' => false, 'message' => $this->translate('unsupported_file_type_image')];
        }

        // Supprimer l'ancienne photo si elle existe et une nouvelle est uploadée
        if ($currentPhotoPath && file_exists(APP_ROOT . '/public/' . $currentPhotoPath)) {
            if (strpos($currentPhotoPath, $this->uploadDirPhotos) === 0) { // Sécurité
                 unlink(APP_ROOT . '/public/' . $currentPhotoPath);
            }
        }

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
            return ['success' => true, 'path' => $this->uploadDirPhotos . $fileName];
        }
        return ['success' => false, 'message' => $this->translate('error_during_upload')];
    }
}
?>
