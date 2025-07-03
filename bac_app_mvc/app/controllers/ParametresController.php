<?php

class ParametresController extends Controller {
    private $parametreGeneralModel;
    private $uploadDir = 'uploads/settings/'; // Relatif à APP_ROOT . '/public/'

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Affiner la vérification des permissions
        // if (!$this->userHasPermission('manage_general_settings')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->parametreGeneralModel = $this->model('ParametreGeneral');

        // S'assurer que le dossier d'upload existe
        $fullUploadDir = APP_ROOT . '/public/' . $this->uploadDir;
        if (!is_dir($fullUploadDir)) {
            if (!mkdir($fullUploadDir, 0755, true)) {
                // Gérer l'erreur si le dossier ne peut pas être créé
                View::renderError("Impossible de créer le dossier d'upload: " . $fullUploadDir);
            }
        }
    }

    public function index() {
        $settings = $this->parametreGeneralModel->getSettings();
        $data = [
            'title' => $this->translate('general_settings'),
            'settings' => (array) $settings
        ];
        $this->view('parametres/index', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $currentSettings = (array) $this->parametreGeneralModel->getSettings();

            $dataToUpdate = [
                'republique_de' => trim($_POST['republique_de'] ?? $currentSettings['republique_de']),
                'devise_republique' => trim($_POST['devise_republique'] ?? $currentSettings['devise_republique']),
                'ministere_nom' => trim($_POST['ministere_nom'] ?? $currentSettings['ministere_nom']),
                'office_examen_nom' => trim($_POST['office_examen_nom'] ?? $currentSettings['office_examen_nom']),
                'direction_nom' => trim($_POST['direction_nom'] ?? $currentSettings['direction_nom']),
                'ville_office' => trim($_POST['ville_office'] ?? $currentSettings['ville_office']),
                // Initialiser avec les valeurs actuelles, seront écrasées si un nouveau fichier est uploadé
                'logo_pays_path' => $currentSettings['logo_pays_path'] ?? null,
                'armoirie_pays_path' => $currentSettings['armoirie_pays_path'] ?? null,
                'drapeau_pays_path' => $currentSettings['drapeau_pays_path'] ?? null,
                'signature_directeur_path' => $currentSettings['signature_directeur_path'] ?? null,
                'cachet_office_path' => $currentSettings['cachet_office_path'] ?? null,
            ];

            $fileFields = [
                'logo_pays_path' => 'logo_pays_file',
                'armoirie_pays_path' => 'armoirie_pays_file',
                'drapeau_pays_path' => 'drapeau_pays_file',
                'signature_directeur_path' => 'signature_directeur_file',
                'cachet_office_path' => 'cachet_office_file'
            ];

            foreach ($fileFields as $dbField => $formFieldName) {
                // Vérifier si une action de suppression est demandée pour ce champ
                if (isset($_POST['delete_' . $dbField]) && $_POST['delete_' . $dbField] == '1') {
                    if (!empty($currentSettings[$dbField]) && file_exists(APP_ROOT . '/public/' . $currentSettings[$dbField])) {
                        unlink(APP_ROOT . '/public/' . $currentSettings[$dbField]);
                    }
                    $dataToUpdate[$dbField] = null;
                }
                // Sinon, vérifier pour un nouvel upload
                elseif (isset($_FILES[$formFieldName]) && $_FILES[$formFieldName]['error'] == UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleUpload($formFieldName, $dbField, $currentSettings[$dbField] ?? null);
                    if ($uploadResult['success']) {
                        $dataToUpdate[$dbField] = $uploadResult['path'];
                    } else {
                        $_SESSION['error_message'] = $this->translate('error_uploading_file', ['field' => $dbField]) . ': ' . $uploadResult['message'];
                        $this->redirect('parametres'); // Rediriger avec message d'erreur
                        return;
                    }
                }
            }

            if ($this->parametreGeneralModel->updateSettings($dataToUpdate)) {
                $_SESSION['message'] = $this->translate('settings_updated_successfully');
            } else {
                $_SESSION['error_message'] = $this->translate('error_updating_settings');
            }
            $this->redirect('parametres');

        } else {
            $this->redirect('parametres');
        }
    }

    private function handleUpload($fileInputName, $dbFieldPrefix, $currentFilePath = null) {
        $targetDir = APP_ROOT . '/public/' . $this->uploadDir;
        // Utiliser un nom de fichier plus simple basé sur le champ, et ajouter un timestamp pour unicité si besoin
        $fileExtension = strtolower(pathinfo($_FILES[$fileInputName]["name"], PATHINFO_EXTENSION));
        $fileName = $dbFieldPrefix . '.' . $fileExtension; // ex: logo_pays_path.png
        $targetFilePath = $targetDir . $fileName;

        // Vérifier si le fichier est une image réelle
        $check = getimagesize($_FILES[$fileInputName]["tmp_name"]);
        if ($check === false) {
            return ['success' => false, 'message' => $this->translate('file_not_image')];
        }

        // Vérifier la taille du fichier (ex: 2MB)
        if ($_FILES[$fileInputName]["size"] > 2000000) {
            return ['success' => false, 'message' => $this->translate('file_too_large', ['size'=>'2MB'])];
        }

        // Autoriser certains formats de fichiers
        $allowedTypes = ['jpg', 'png', 'jpeg', 'gif', 'svg'];
        if (!in_array($fileExtension, $allowedTypes)) {
            return ['success' => false, 'message' => $this->translate('unsupported_file_type_image')];
        }

        // Supprimer l'ancien fichier s'il existe et qu'un nouveau est uploadé
        // S'assurer que le chemin actuel est bien dans le dossier d'uploads avant de supprimer
        if ($currentFilePath && strpos($currentFilePath, $this->uploadDir) === 0 && file_exists(APP_ROOT . '/public/' . $currentFilePath)) {
           if (basename($currentFilePath) !== $fileName) { // Ne pas supprimer si c'est le même nom de fichier (juste écraser)
             unlink(APP_ROOT . '/public/' . $currentFilePath);
           }
        }

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
            return ['success' => true, 'path' => $this->uploadDir . $fileName]; // Stocker le chemin relatif à public/
        } else {
            return ['success' => false, 'message' => $this->translate('error_during_upload')];
        }
    }
}
?>
