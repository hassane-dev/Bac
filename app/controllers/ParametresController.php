<?php

class ParametresController extends Controller {
    private $parametresModel;
    private $uploadDir = APP_ROOT . '/public/uploads/settings/'; // Chemin physique sur le serveur
    private $uploadBaseUrl = APP_URL . '/uploads/settings/';   // URL de base pour y accéder depuis le navigateur

    public function __construct() {
        parent::__construct();
        $this->parametresModel = $this->model('ParametresGenerauxModel');

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'access_denied_pleaselogin');
            $this->redirect('auth/login');
        }
        // Permission globale pour ce contrôleur
        // if (!$this->userHasPermission('manage_general_settings')) { // Ajouter 'manage_general_settings' aux accréditations
        //     $this->setFlashMessage('error', 'access_denied');
        //     $this->redirect('dashboard/index');
        // }
         // S'assurer que le répertoire d'upload existe
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                // Gérer l'erreur si le répertoire ne peut pas être créé
                // Pour l'instant, on pourrait logger une erreur ou afficher un message critique
                error_log("Erreur: Impossible de créer le répertoire d'upload: " . $this->uploadDir);
                // On pourrait set un flash message et rediriger, mais si c'est critique, die() est une option en dev.
                $this->setFlashMessage('error', 'Erreur critique: répertoire d\'upload inaccessible.');
                // $this->redirect('dashboard/index'); // Rediriger vers un endroit sûr
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
            'settings' => (array) $settings, // Convertir en tableau pour un accès plus facile dans la vue
            'upload_base_url' => $this->uploadBaseUrl
        ];

        // Initialiser les erreurs de fichier pour chaque champ d'upload
        $file_fields = ['logo_pays_path', 'armoirie_pays_path', 'drapeau_pays_path', 'signature_directeur_path', 'cachet_office_path'];
        foreach ($file_fields as $field) {
            $data[$field . '_err'] = '';
        }


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $update_data = [
                'republique_de' => trim($_POST['republique_de'] ?? $settings->republique_de),
                'devise_republique' => trim($_POST['devise_republique'] ?? $settings->devise_republique),
                'ministere_nom' => trim($_POST['ministere_nom'] ?? $settings->ministere_nom),
                'office_examen_nom' => trim($_POST['office_examen_nom'] ?? $settings->office_examen_nom),
                'direction_nom' => trim($_POST['direction_nom'] ?? $settings->direction_nom),
                'ville_office' => trim($_POST['ville_office'] ?? $settings->ville_office),
            ];

            $has_file_errors = false;

            foreach ($file_fields as $field_name) {
                // Gestion de la suppression du fichier existant
                if (isset($_POST['delete_' . $field_name]) && !empty($settings->$field_name)) {
                    $old_file_path = $this->uploadDir . basename($settings->$field_name);
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                    $update_data[$field_name] = null; // Chemin NULL dans la DB
                    $data['settings'][$field_name] = null; // Mettre à jour pour la vue
                }

                // Gestion de l'upload d'un nouveau fichier
                if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == UPLOAD_ERR_OK) {
                    $file_upload_result = $this->handleFileUpload($_FILES[$field_name], $field_name, $settings->$field_name ?? null);
                    if ($file_upload_result['success']) {
                        $update_data[$field_name] = $file_upload_result['filename']; // Stocker le nom du fichier (relatif à uploadBaseUrl)
                        $data['settings'][$field_name] = $file_upload_result['filename']; // Mettre à jour pour la vue
                    } else {
                        $data[$field_name . '_err'] = $file_upload_result['error'];
                        $has_file_errors = true;
                    }
                } elseif (isset($settings->$field_name) && !isset($_POST['delete_' . $field_name])) {
                    // Conserver l'ancien chemin si pas de nouveau fichier et pas de suppression demandée
                    $update_data[$field_name] = $settings->$field_name;
                }
            }

            // Fusionner les données postées dans $data pour réafficher le formulaire avec les valeurs actuelles
            $data['settings'] = array_merge($data['settings'], $_POST);


            if (!$has_file_errors) {
                if ($this->parametresModel->updateSettings($update_data)) {
                    $this->setFlashMessage('success', 'settings_updated_successfully');
                    // Recharger les settings pour afficher les dernières modifications
                    $settings = $this->parametresModel->getSettings();
                    $data['settings'] = (array) $settings;
                    // $this->redirect('parametres/index'); // Rediriger pour éviter resoumission et pour voir le message flash
                } else {
                    $this->setFlashMessage('error', 'error_updating_settings');
                }
            } else {
                 $this->setFlashMessage('error', 'error_in_file_uploads'); // Ajouter 'error_in_file_uploads'
            }
        }
        $this->view('parametres/index', $data);
    }

    private function handleFileUpload($file, $field_name_for_error, $old_filename = null) {
        $result = ['success' => false, 'filename' => null, 'error' => ''];
        $target_dir = $this->uploadDir;

        // Vérifier si le répertoire d'upload existe et est accessible en écriture
        if (!is_dir($target_dir) || !is_writable($target_dir)) {
            $result['error'] = $this->translate('upload_directory_not_writable'); // Ajouter aux lang
            error_log("Upload directory not writable: " . $target_dir);
            return $result;
        }

        $filename = uniqid(preg_replace('/[^a-zA-Z0-9_.-]/', '_',pathinfo($file['name'], PATHINFO_FILENAME)) . '_', true)
                  . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $target_file = $target_dir . $filename;
        $max_file_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($file['size'] > $max_file_size) {
            $result['error'] = $this->translate('file_too_large', [':size' => '2MB']);
            return $result;
        }
        if (!in_array($file_type, $allowed_types)) {
            $result['error'] = $this->translate('unsupported_file_type_image');
            return $result;
        }

        // Supprimer l'ancien fichier si un nouveau est uploadé avec succès
        if ($old_filename) {
            $old_file_path = $target_dir . basename($old_filename); // Utiliser basename pour la sécurité
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $result['success'] = true;
            $result['filename'] = $filename; // Retourner seulement le nom du fichier, pas le chemin complet
        } else {
            $result['error'] = $this->translate('error_during_upload');
        }
        return $result;
    }
}
?>
