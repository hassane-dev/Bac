<?php

class ElevesController extends Controller {
    private $eleveModel;
    private $anneeScolaireModel;
    private $serieModel;
    private $lyceeModel;
    private $centreModel; // Ajout du modèle Centre
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
        $this->centreModel = $this->model('Centre'); // Initialiser le modèle Centre

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
            'annee_scolaire_libelle' => $activeYear->libelle,
            'series' => $this->serieModel->getAll(),
            'lycees' => $this->lyceeModel->getAll(),
            'selected_lycee_id' => null, // Pour la présélection
            'selected_centre_id' => null, // Pour la présélection
            'selected_centre_nom' => null,
            'matricule' => '', 'nom' => '', 'prenom' => '', 'date_naissance' => '', 'sexe' => 'M',
            'serie_id' => '', 'lycee_id' => '', 'photo_path' => null,
            'empreinte1' => '', /* ... */ 'empreinte10' => '',
            'errors' => []
        ];

        // Si un lycée et un centre ont été "pré-sélectionnés" (par un formulaire précédent ou une action)
        if (isset($_SESSION['enroll_context']['lycee_id']) && isset($_SESSION['enroll_context']['centre_id'])) {
            $data['selected_lycee_id'] = $_SESSION['enroll_context']['lycee_id'];
            $data['selected_centre_id'] = $_SESSION['enroll_context']['centre_id'];
            $centreInfo = $this->centreModel->getById($data['selected_centre_id']);
            $data['selected_centre_nom'] = $centreInfo ? $centreInfo->nom_centre : '';
            // On pourrait vouloir nettoyer la session ici ou après la première utilisation
            // unset($_SESSION['enroll_context']);
        }

        $this->view('eleves/create', $data);
    }

    /**
     * Étape 1 de l'enrôlement : sélection du lycée et détermination du centre.
     */
    public function selectContext() {
        if (!$this->isLoggedIn()) { $this->redirect('auth/login'); return; }

        $activeYear = $this->anneeScolaireModel->getActiveYear();
        if (!$activeYear) {
            $_SESSION['error_message'] = $this->translate('no_active_academic_year_enroll');
            $this->redirect('anneesscolaires');
            return;
        }

        $data = [
            'title' => $this->translate('select_enrollment_context'), // 'Sélectionner le contexte d'enrôlement'
            'lycees' => $this->lyceeModel->getAll(),
            'annee_scolaire_id' => $activeYear->id,
            'annee_scolaire_libelle' => $activeYear->libelle,
            'selected_lycee_id' => null,
            'selected_centre_id' => null,
            'selected_centre_nom' => null,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $lycee_id = (int)$_POST['lycee_id'];
            // Logique pour trouver le centre_id basé sur lycee_id et annee_scolaire_id (activeYear->id)
            // Cela suppose une fonction dans un modèle (par ex. CentreModel ou un nouveau AssignationModel)
            // $assignation = $this->centreModel->getAssignationForLyceeAnnee($lycee_id, $activeYear->id); // Exemple

            // Logique simplifiée pour l'instant : on suppose que l'assignation est directe
            // Dans une vraie application, il faudrait interroger centres_assignations
            $centre_id_found = null;
            $centre_nom_found = null;

            // --- Début de la logique de recherche du centre (à améliorer avec le modèle) ---
            // Cette logique devrait être dans un modèle, ex: $this->centreModel->findCentreForLyceeInYear($lycee_id, $activeYear->id);
            // Pour l'instant, simulation ou placeholder.
            // On va chercher la première assignation pour ce lycée et cette année.
            $this->db->query("SELECT centre_id FROM centres_assignations WHERE lycee_id = :lycee_id AND annee_scolaire_id = :annee_id LIMIT 1");
            $this->db->bind(':lycee_id', $lycee_id);
            $this->db->bind(':annee_id', $activeYear->id);
            $assign_result = $this->db->single();

            if ($assign_result) {
                $centre_id_found = $assign_result->centre_id;
                $centreInfo = $this->centreModel->getById($centre_id_found);
                $centre_nom_found = $centreInfo ? $centreInfo->nom_centre : $this->translate('unknown_center');
            }
            // --- Fin de la logique de recherche du centre ---


            if ($lycee_id && $centre_id_found) {
                $_SESSION['enroll_context'] = [
                    'annee_scolaire_id' => $activeYear->id,
                    'annee_scolaire_libelle' => $activeYear->libelle,
                    'lycee_id' => $lycee_id,
                    'centre_id' => $centre_id_found,
                    'centre_code' => $centreInfo->code_centre ?? '', // Important pour le matricule
                    'lycee_nom' => $this->lyceeModel->getById($lycee_id)->nom_lycee ?? '',
                    'centre_nom' => $centre_nom_found
                ];
                $this->redirect('eleves/create'); // Rediriger vers le formulaire d'enrôlement
                return;
            } else {
                $data['errors']['context_err'] = $this->translate('center_not_assigned_for_lycee_year');
            }
        }
        $this->view('eleves/select_context', $data);
    }


    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Récupérer le contexte depuis la session
            if (!isset($_SESSION['enroll_context'])) {
                $_SESSION['error_message'] = $this->translate('enrollment_context_not_set');
                $this->redirect('eleves/selectContext'); // Rediriger pour définir le contexte
                return;
            }
            $context = $_SESSION['enroll_context'];

            $data = [
                'matricule' => '', // Sera généré
                'nom' => trim($_POST['nom']),
                'prenom' => trim($_POST['prenom']),
                'date_naissance' => $_POST['date_naissance'],
                'sexe' => $_POST['sexe'],
                'serie_id' => (int)$_POST['serie_id'],
                'lycee_id' => (int)$context['lycee_id'], // Depuis le contexte
                'annee_scolaire_id' => (int)$context['annee_scolaire_id'], // Depuis le contexte
                'centre_id' => (int)$context['centre_id'], // Depuis le contexte
                'code_centre' => $context['centre_code'], // Pour génération matricule
                'code_serie' => '', // Sera récupéré pour génération matricule
                'photo_path' => null,
                'webcam_photo_data' => $_POST['webcam_photo_data'] ?? null,
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
            // ... Autres validations pour nom, prenom, date_naissance, sexe, serie_id ...
            if (empty($data['nom'])) $data['errors']['nom_err'] = $this->translate('lastname_required');
            if (empty($data['prenom'])) $data['errors']['prenom_err'] = $this->translate('firstname_required');
            if (empty($data['date_naissance'])) $data['errors']['date_naissance_err'] = $this->translate('dob_required');
            if (empty($data['sexe'])) $data['errors']['sexe_err'] = $this->translate('gender_required');
            if (empty($data['serie_id'])) $data['errors']['serie_id_err'] = $this->translate('serie_required');
            // lycee_id, annee_scolaire_id, centre_id viennent du contexte, pas besoin de les revalider ici sauf pour existence.

            // Récupérer le code_serie pour la génération du matricule
            if (!empty($data['serie_id'])) {
                $serieInfo = $this->serieModel->getById($data['serie_id']);
                if ($serieInfo) {
                    $data['code_serie'] = $serieInfo->code;
                } else {
                    $data['errors']['serie_id_err'] = $this->translate('serie_not_found');
                }
            }
            if (empty($data['code_centre'])) { // Vérifier si le code centre est bien dans le contexte
                 $data['errors']['context_err'] = $this->translate('center_code_missing_in_context');
            }


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
                    // Repopuler les données pour la vue create, y compris le contexte
                    $data['title'] = $this->translate('add_student');
                    $data['annee_scolaire_id'] = $context['annee_scolaire_id'];
                    $data['annee_scolaire_libelle'] = $context['annee_scolaire_libelle'];
                    $data['selected_lycee_id'] = $context['lycee_id'];
                    $data['selected_centre_id'] = $context['centre_id'];
                    $data['selected_centre_nom'] = $context['centre_nom'];
                    $data['series'] = $this->serieModel->getAll();
                    $data['lycees'] = $this->lyceeModel->getAll(); // Bien que le lycée soit en contexte, on pourrait le garder pour l'affichage
                    $this->view('eleves/create', $data);
                }
            } else {
                // Repopuler les données pour la vue create, y compris le contexte
                $data['title'] = $this->translate('add_student');
                $data['annee_scolaire_id'] = $context['annee_scolaire_id'];
                $data['annee_scolaire_libelle'] = $context['annee_scolaire_libelle'];
                $data['selected_lycee_id'] = $context['lycee_id'];
                $data['selected_centre_id'] = $context['centre_id'];
                $data['selected_centre_nom'] = $context['centre_nom'];
                $data['series'] = $this->serieModel->getAll();
                $data['lycees'] = $this->lyceeModel->getAll();
                $this->view('eleves/create', $data);
            }
        } else {
            // Si ce n'est pas POST, rediriger vers la sélection de contexte si le contexte n'est pas défini
            if (!isset($_SESSION['enroll_context'])) {
                $this->redirect('eleves/selectContext');
            } else {
                $this->redirect('eleves/create'); // ou afficher le formulaire create vide avec le contexte
            }
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
