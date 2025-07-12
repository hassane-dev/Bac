<?php

class ParametresController extends Controller {
    private $parametresModel;
    private $uploadDir = APP_ROOT . '/public/uploads/settings/';
    private $uploadBaseUrl = APP_URL . '/uploads/settings/';

    public function __construct() {
        parent::__construct();
        $this->parametresModel = $this->model('ParametresGenerauxModel');

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'access_denied_pleaselogin');
            $this->redirect('auth/login');
        }

        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                error_log("Erreur: Impossible de créer le répertoire d'upload: " . $this->uploadDir);
                $this->setFlashMessage('error', 'Erreur critique: répertoire d\'upload inaccessible.');
            }
        }
    }

    public function index() {
        if (!$this->userHasPermission('manage_general_settings')) {
            $this->setFlashMessage('error', 'access_denied');
            $this->redirect('dashboard/index');
            return;
        }

        $settings = $this->parametresModel->getSettings();
        $data = [
            'page_title' => $this->translate('general_settings'),
            'settings' => (array) $settings,
            'upload_base_url' => $this->uploadBaseUrl
        ];

        $file_fields = ['logo_pays_path', 'armoirie_pays_path', 'drapeau_pays_path', 'signature_directeur_path', 'cachet_office_path'];
        foreach ($file_fields as $field) {
            $data[$field . '_err'] = '';
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $current_settings_array = (array)$settings;
            $update_data = [];

            $allowed_all_fields = [ // Toutes les colonnes de la table parametres_generaux sauf 'id'
                'republique_de', 'devise_republique', 'ministere_nom',
                'office_examen_nom', 'direction_nom', 'logo_pays_path',
                'armoirie_pays_path', 'drapeau_pays_path', 'signature_directeur_path',
                'cachet_office_path', 'ville_office'
            ];

            // Initialiser $update_data avec les valeurs actuelles pour tous les champs
            foreach($allowed_all_fields as $field_key) {
                $update_data[$field_key] = $current_settings_array[$field_key] ?? null;
            }

            // Mettre à jour les champs texte à partir de POST
            $text_fields_from_post = ['republique_de', 'devise_republique', 'ministere_nom', 'office_examen_nom', 'direction_nom', 'ville_office'];
            foreach($text_fields_from_post as $tf) {
                if(isset($_POST[$tf])) {
                    $update_data[$tf] = trim($_POST[$tf]);
                }
            }

            $has_file_errors = false;

            foreach ($file_fields as $field_name) {
                // $update_data[$field_name] est déjà initialisé avec la valeur actuelle
                if (isset($_POST['delete_' . $field_name]) && !empty($current_settings_array[$field_name])) {
                    $old_file_path = $this->uploadDir . basename($current_settings_array[$field_name]);
                    if (file_exists($old_file_path) && is_file($old_file_path)) {
                        unlink($old_file_path);
                    }
                    $update_data[$field_name] = null;
                }

                if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == UPLOAD_ERR_OK) {
                    // Passer l'ancienne valeur de $update_data (qui peut avoir été mise à null par delete)
                    $file_upload_result = $this->handleFileUpload($_FILES[$field_name], $update_data[$field_name]);
                    if ($file_upload_result['success']) {
                        $update_data[$field_name] = $file_upload_result['filename'];
                    } else {
                        $data[$field_name . '_err'] = $file_upload_result['error'];
                        $has_file_errors = true;
                    }
                }
            }

            $data['settings'] = array_merge($current_settings_array, $update_data);

            if (!$has_file_errors) {
                if ($this->parametresModel->updateSettings($update_data)) {
                    $this->setFlashMessage('success', 'settings_updated_successfully');
                    $settings_after_update = $this->parametresModel->getSettings();
                    $data['settings'] = (array) $settings_after_update;
                } else {
                    $this->setFlashMessage('error', 'error_updating_settings');
                }
            } else {
                 $this->setFlashMessage('error', 'error_in_file_uploads');
            }
        }
        $this->view('parametres/index', $data);
    }

    private function handleFileUpload($file, $old_filename = null) {
        $result = ['success' => false, 'filename' => null, 'error' => ''];
        $target_dir = $this->uploadDir;

        if (!is_dir($target_dir) || !is_writable($target_dir)) {
            $result['error'] = $this->translate('upload_directory_not_writable');
            error_log("Upload directory not writable: " . $target_dir);
            return $result;
        }

        $original_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid($original_filename . '_', true) . '.' . $extension;

        $target_file = $target_dir . $filename;
        $max_file_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

        if ($file['size'] > $max_file_size) {
            $result['error'] = $this->translate('file_too_large', [':size' => '2MB']);
            return $result;
        }
        if (!in_array($extension, $allowed_types)) {
            $result['error'] = $this->translate('unsupported_file_type_image');
            return $result;
        }

        // Supprimer l'ancien fichier seulement si un nouveau est uploadé avec succès et qu'un ancien nom de fichier est fourni.
        // La suppression est gérée avant si la case "delete" est cochée.
        // Si un nouveau fichier est uploadé, l'ancien fichier (s'il existe et est différent) doit être supprimé.
        if ($old_filename && $old_filename !== $filename) { // Vérifier si old_filename est non null et différent
            $old_file_path = $target_dir . basename($old_filename);
            if (file_exists($old_file_path) && is_file($old_file_path)) {
                unlink($old_file_path);
            }
        }


        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $result['success'] = true;
            $result['filename'] = $filename;
        } else {
            $result['error'] = $this->translate('error_during_upload');
        }
        return $result;
    }
}
?>
