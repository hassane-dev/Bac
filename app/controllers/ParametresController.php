<?php

class ParametresController extends Controller {
    private $parametreGeneralModel;
    private $uploadDir = 'uploads/settings/'; // Relatif à APP_ROOT/public/

    public function __construct() {
        parent::__construct();
        // TODO: Ajouter vérification de session et permissions
        // if (!$this->isLoggedIn() || !$this->userHasPermission('manage_settings')) {
        //     $_SESSION['error_message'] = $this->translate('access_denied');
        //     $this->redirect('dashboard');
        // }
        $this->parametreGeneralModel = $this->model('ParametreGeneral');

        // Créer le dossier d'upload s'il n'existe pas
        if (!is_dir(APP_ROOT . '/public/' . $this->uploadDir)) {
            mkdir(APP_ROOT . '/public/' . $this->uploadDir, 0755, true);
        }
    }

    public function index() {
        $settings = $this->parametreGeneralModel->getSettings();
        $data = [
            'title' => $this->translate('general_settings'),
            'settings' => (array) $settings // Convertir en tableau pour un accès plus facile dans la vue
        ];
        $this->view('parametres/index', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $currentSettings = (array) $this->parametreGeneralModel->getSettings();

            $data = [
                'republique_de' => trim($_POST['republique_de'] ?? ''),
                'devise_republique' => trim($_POST['devise_republique'] ?? ''),
                'ministere_nom' => trim($_POST['ministere_nom'] ?? ''),
                'office_examen_nom' => trim($_POST['office_examen_nom'] ?? ''),
                'direction_nom' => trim($_POST['direction_nom'] ?? ''),
                'ville_office' => trim($_POST['ville_office'] ?? ''),
                // Garder les chemins existants par défaut, ils seront écrasés par les nouveaux uploads si présents
                'logo_pays_path' => $currentSettings['logo_pays_path'] ?? null,
                'armoirie_pays_path' => $currentSettings['armoirie_pays_path'] ?? null,
                'drapeau_pays_path' => $currentSettings['drapeau_pays_path'] ?? null,
                'signature_directeur_path' => $currentSettings['signature_directeur_path'] ?? null,
                'cachet_office_path' => $currentSettings['cachet_office_path'] ?? null,
            ];

            // Gestion des uploads
            $fileFields = [
                'logo_pays_path' => 'logo_pays_file',
                'armoirie_pays_path' => 'armoirie_pays_file',
                'drapeau_pays_path' => 'drapeau_pays_file',
                'signature_directeur_path' => 'signature_directeur_file',
                'cachet_office_path' => 'cachet_office_file'
            ];

            foreach ($fileFields as $dbField => $formFieldName) {
                if (isset($_FILES[$formFieldName]) && $_FILES[$formFieldName]['error'] == UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleUpload($formFieldName, $dbField, $currentSettings[$dbField] ?? null);
                    if ($uploadResult['success']) {
                        $data[$dbField] = $uploadResult['path'];
                    } else {
                        $_SESSION['error_message'] = $this->translate('error_uploading_file', ['field' => $dbField]) . ': ' . $uploadResult['message'];
                        // Recharger la vue avec les données actuelles et l'erreur
                        $data['settings'] = $currentSettings;
                        $data['title'] = $this->translate('general_settings');
                        $this->view('parametres/index', $data);
                        return;
                    }
                }
            }

            if ($this->parametreGeneralModel->updateSettings($data)) {
                $_SESSION['message'] = $this->translate('settings_updated_successfully');
            } else {
                $_SESSION['error_message'] = $this->translate('error_updating_settings');
            }
            $this->redirect('parametres');

        } else {
            $this->redirect('parametres');
        }
    }

    private function handleUpload($fileInputName, $dbFieldName, $currentFilePath = null) {
        $targetDir = APP_ROOT . '/public/' . $this->uploadDir;
        $fileName = uniqid($dbFieldName . '_') . '_' . basename($_FILES[$fileInputName]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Vérifier si le fichier est une image réelle
        $check = getimagesize($_FILES[$fileInputName]["tmp_name"]);
        if ($check === false) {
            return ['success' => false, 'message' => $this->translate('file_not_image')];
        }

        // Vérifier la taille du fichier (ex: 5MB)
        if ($_FILES[$fileInputName]["size"] > 5000000) {
            return ['success' => false, 'message' => $this->translate('file_too_large')];
        }

        // Autoriser certains formats de fichiers
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            return ['success' => false, 'message' => $this->translate('unsupported_file_type')];
        }

        // Supprimer l'ancien fichier s'il existe et qu'un nouveau est uploadé
        if ($currentFilePath && file_exists(APP_ROOT . '/public/' . $currentFilePath)) {
            unlink(APP_ROOT . '/public/' . $currentFilePath);
        }

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
            return ['success' => true, 'path' => $this->uploadDir . $fileName];
        } else {
            return ['success' => false, 'message' => $this->translate('error_during_upload')];
        }
    }
}
?>
