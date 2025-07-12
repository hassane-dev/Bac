<?php

class TemplatesDocumentsController extends Controller {
    private $templateModel;
    private $uploadDir = APP_ROOT . '/public/uploads/templates_backgrounds/';
    private $uploadBaseUrl = APP_URL . '/uploads/templates_backgrounds/';

    public function __construct() {
        parent::__construct();
        $this->templateModel = $this->model('TemplateDocumentModel');

        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'access_denied_pleaselogin');
            $this->redirect('auth/login');
        }

        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                error_log("Erreur: Impossible de créer le répertoire d'upload pour les fonds de template: " . $this->uploadDir);
            }
        }
    }

    public function index() {
        if (!$this->userHasPermission('manage_document_templates') && !$this->userHasPermission('view_document_templates')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('dashboard/index');
        }
        $data = [
            'page_title' => $this->translate('document_templates_list')
        ];
        $this->view('templates_documents/index', $data);
    }

    public function configure($type_document = null) {
        if (!$this->userHasPermission('manage_document_templates')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('templates_documents/index');
        }

        $allowed_types = ['diplome', 'releve', 'carte'];
        if (is_null($type_document) || !in_array($type_document, $allowed_types)) {
            $this->setFlashMessage('error', 'invalid_document_type');
            $this->redirect('templates_documents/index');
            return;
        }

        $background_settings = $this->templateModel->getBackgroundSettings($type_document);
        $text_elements = $this->templateModel->getElementsByType($type_document);

        $data = [
            'page_title' => $this->translate('configure_template_for') . $this->translate('doc_type_' . $type_document),
            'type_document' => $type_document,
            'background_settings' => (array) $background_settings,
            'text_elements' => $text_elements,
            'upload_base_url' => $this->uploadBaseUrl,
            'errors' => [],
            'current_bg_image_err' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $action = $_POST['action'] ?? 'update_background';

            if ($action === 'update_background') {
                $bg_data = [
                    'type_fond' => $_POST['type_fond'] ?? null,
                    'valeur_fond' => trim($_POST['valeur_fond_hidden'] ?? ($_POST['valeur_fond_couleur_hex'] ?? '')), // Priorité au champ caché rempli par JS
                    'opacite_fond' => filter_var($_POST['opacite_fond'] ?? 1.0, FILTER_VALIDATE_FLOAT),
                    'visible' => isset($_POST['background_visible'])
                ];
                if ($bg_data['opacite_fond'] === false || $bg_data['opacite_fond'] < 0 || $bg_data['opacite_fond'] > 1) {
                    $bg_data['opacite_fond'] = 1.0;
                }

                $current_bg_image_path = ($background_settings && $background_settings->type_fond === 'image_upload') ? $background_settings->valeur_fond : null;

                if (isset($_POST['delete_current_background_image']) && !empty($current_bg_image_path)) {
                    $this->deleteBackgroundImage($current_bg_image_path);
                    $current_bg_image_path = null; // Marquer comme supprimé pour la logique suivante
                    if ($bg_data['type_fond'] === 'image_upload') {
                         $bg_data['valeur_fond'] = null; // Si on supprime et que le type est toujours image, valeur_fond devient null
                    }
                }

                if ($bg_data['type_fond'] === 'image_upload') {
                    if (isset($_FILES['background_image_file']) && $_FILES['background_image_file']['error'] == UPLOAD_ERR_OK) {
                        $upload_result = $this->handleBackgroundImageUpload($_FILES['background_image_file'], $current_bg_image_path);
                        if ($upload_result['success']) {
                            $bg_data['valeur_fond'] = $upload_result['filename'];
                        } else {
                            $data['current_bg_image_err'] = $upload_result['error'];
                        }
                    } elseif ($current_bg_image_path && !isset($_POST['delete_current_background_image'])) {
                        // Conserver l'image existante si aucune nouvelle n'est fournie et pas de suppression
                        $bg_data['valeur_fond'] = $current_bg_image_path;
                    } elseif (!isset($_FILES['background_image_file']) || $_FILES['background_image_file']['error'] != UPLOAD_ERR_OK) {
                        // Pas de nouveau fichier, pas d'ancien, et pas de suppression -> valeur_fond reste ce qu'elle était (peut-être null)
                        if ($bg_data['type_fond'] === 'image_upload' && empty($bg_data['valeur_fond'])) {
                            // Si le type est image mais pas de valeur, on ne fait rien ou on met à null explicitement
                            // $bg_data['valeur_fond'] = null;
                        }
                    }
                }


                if (empty($data['current_bg_image_err'])) {
                    if ($this->templateModel->createOrUpdateBackgroundSettings($type_document, $bg_data)) {
                        $this->setFlashMessage('success', 'background_settings_updated_successfully');
                    } else {
                        $this->setFlashMessage('error', 'error_updating_background_settings');
                    }
                } else {
                     $this->setFlashMessage('error', $data['current_bg_image_err']);
                }
                $this->redirect('templates_documents/configure/' . $type_document);

            } elseif ($action === 'add_element') {
                $element_data = [
                    'type_document' => $type_document,
                    'element' => trim($_POST['element_name']),
                    'position_x' => (int)($_POST['position_x'] ?? 0),
                    'position_y' => (int)($_POST['position_y'] ?? 0),
                    'taille_police' => (int)($_POST['taille_police'] ?? 10),
                    'police' => trim($_POST['police'] ?? 'helvetica'),
                    'couleur' => trim($_POST['couleur'] ?? '#000000'),
                    'langue_affichage' => $_POST['langue_affichage'] ?? 'fr_ar',
                    'visible' => isset($_POST['element_visible'])
                ];
                if (empty($element_data['element'])) {
                    $this->setFlashMessage('error', 'element_name_required');
                } elseif ($this->templateModel->createElement($element_data)) {
                    $this->setFlashMessage('success', 'template_element_added_successfully');
                } else {
                    $this->setFlashMessage('error', 'error_adding_template_element_duplicate');
                }
                $this->redirect('templates_documents/configure/' . $type_document);
            }
            // La modification d'élément n'est pas gérée par ce POST principal, elle aura sa propre action/formulaire.
        } else {
            $this->view('templates_documents/configure', $data);
        }
    }

    public function delete_element($element_id = null) {
        if (!$this->userHasPermission('manage_document_templates')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('dashboard/index');
        }

        // L'ID de l'élément et le type de document pour la redirection sont attendus en POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['element_id_to_delete']) && isset($_POST['type_document_redirect'])) {
            $element_id_to_delete = (int)$_POST['element_id_to_delete'];
            $type_document_redirect = $_POST['type_document_redirect'];

            $allowed_types = ['diplome', 'releve', 'carte'];
            if (!in_array($type_document_redirect, $allowed_types)) {
                 $this->setFlashMessage('error', 'invalid_document_type');
                 $this->redirect('templates_documents/index');
                 return;
            }

            $element = $this->templateModel->getElementById($element_id_to_delete);
            if (!$element) {
                $this->setFlashMessage('error', 'template_element_not_found_or_is_background');
            } elseif ($this->templateModel->deleteElement($element_id_to_delete)) {
                $this->setFlashMessage('success', 'template_element_deleted_successfully');
            } else {
                $this->setFlashMessage('error', 'error_deleting_template_element');
            }
            $this->redirect('templates_documents/configure/' . $type_document_redirect);
        } else {
            $this->setFlashMessage('error', 'invalid_request');
            $this->redirect('templates_documents/index');
        }
    }


    private function handleBackgroundImageUpload($file, $old_filename_to_delete_if_new_succeeds = null) {
        $result = ['success' => false, 'filename' => null, 'error' => ''];

        if (!is_dir($this->uploadDir) || !is_writable($this->uploadDir)) {
            $result['error'] = $this->translate('upload_directory_not_writable');
            error_log("Upload directory templates_backgrounds not writable: " . $this->uploadDir);
            return $result;
        }

        $original_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename_for_storage = uniqid($original_filename . '_bg_', true) . '.' . $extension;

        $target_file = $this->uploadDir . $filename_for_storage;
        $max_file_size = 5 * 1024 * 1024;
        $allowed_types = ['jpg', 'jpeg', 'png'];

        if ($file['size'] > $max_file_size) {
            $result['error'] = $this->translate('file_too_large', [':size' => '5MB']);
            return $result;
        }
        if (!in_array($extension, $allowed_types)) {
            $result['error'] = $this->translate('unsupported_file_type_image_jpg_png');
            return $result;
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            if ($old_filename_to_delete_if_new_succeeds) {
                $this->deleteBackgroundImage($old_filename_to_delete_if_new_succeeds);
            }
            $result['success'] = true;
            $result['filename'] = $filename_for_storage;
        } else {
            $result['error'] = $this->translate('error_during_upload');
        }
        return $result;
    }

    private function deleteBackgroundImage($filename_to_delete) {
        if (empty($filename_to_delete)) return false;
        $file_path = $this->uploadDir . basename($filename_to_delete);
        if (file_exists($file_path) && is_file($file_path)) {
            return unlink($file_path);
        }
        return false;
    }
}
?>
