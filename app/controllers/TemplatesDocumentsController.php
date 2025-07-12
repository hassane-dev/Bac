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
        // Permission globale (à affiner)
        // if (!$this->userHasPermission('manage_document_templates')) { // Ajouter 'manage_document_templates'
        //     $this->setFlashMessage('error', 'access_denied');
        //     $this->redirect('dashboard/index');
        // }
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                error_log("Erreur: Impossible de créer le répertoire d'upload pour les fonds de template: " . $this->uploadDir);
                // Gérer cette erreur de manière plus visible si nécessaire
            }
        }
    }

    /**
     * Affiche la page de sélection du type de document à configurer.
     */
    public function index() {
        if (!$this->userHasPermission('manage_document_templates') && !$this->userHasPermission('view_document_templates')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('dashboard/index');
        }
        $data = [
            'page_title' => $this->translate('document_templates_list')
            // On pourrait lister ici les types de documents avec un aperçu ou un statut
        ];
        $this->view('templates_documents/index', $data);
    }

    /**
     * Affiche et gère la configuration d'un type de document spécifique.
     * @param string $type_document 'diplome', 'releve', ou 'carte'
     */
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
            'errors' => [], // Pour les erreurs de formulaire
            'current_bg_image_err' => '', // Erreur spécifique pour l'upload d'image de fond
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $action = $_POST['action'] ?? 'update_background'; // Déterminer quelle partie du formulaire est soumise

            if ($action === 'update_background') {
                $bg_data = [
                    'type_fond' => $_POST['type_fond'] ?? null,
                    'valeur_fond' => trim($_POST['valeur_fond'] ?? ''), // Peut être couleur HEX ou nom de thème
                    'opacite_fond' => filter_var($_POST['opacite_fond'] ?? 1.0, FILTER_VALIDATE_FLOAT),
                    'visible' => isset($_POST['background_visible']) // Le fond est-il visible?
                ];
                if ($bg_data['opacite_fond'] === false || $bg_data['opacite_fond'] < 0 || $bg_data['opacite_fond'] > 1) {
                    $bg_data['opacite_fond'] = 1.0; // Valeur par défaut en cas d'erreur
                }

                // Gestion de l'upload de l'image de fond
                if ($bg_data['type_fond'] === 'image_upload') {
                    if (isset($_FILES['background_image_file']) && $_FILES['background_image_file']['error'] == UPLOAD_ERR_OK) {
                        $old_bg_image = ($background_settings && $background_settings->type_fond === 'image_upload') ? $background_settings->valeur_fond : null;
                        $upload_result = $this->handleBackgroundImageUpload($_FILES['background_image_file'], $old_bg_image);
                        if ($upload_result['success']) {
                            $bg_data['valeur_fond'] = $upload_result['filename'];
                        } else {
                            $data['current_bg_image_err'] = $upload_result['error'];
                        }
                    } elseif ($background_settings && $background_settings->type_fond === 'image_upload' && !empty($background_settings->valeur_fond)) {
                        // Conserver l'image existante si aucune nouvelle n'est fournie et type_fond est image_upload
                        $bg_data['valeur_fond'] = $background_settings->valeur_fond;
                    } else {
                        // Si type_fond est image_upload mais aucun fichier n'est fourni et aucun n'existe, c'est une erreur ou il faut mettre valeur_fond à null.
                        // Pour l'instant, on laisse vide, ce qui peut causer un problème si on ne gère pas la suppression
                    }
                }
                // Gestion de la suppression de l'image de fond
                if (isset($_POST['delete_current_background_image']) && $background_settings && $background_settings->type_fond === 'image_upload' && !empty($background_settings->valeur_fond)) {
                    $this->deleteBackgroundImage($background_settings->valeur_fond);
                    if ($bg_data['type_fond'] === 'image_upload') { // Si on supprime et que le type est toujours image, on met la valeur à null
                         $bg_data['valeur_fond'] = null;
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
                // Logique pour ajouter un élément textuel
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
                // Validation simple
                if (empty($element_data['element'])) {
                    $this->setFlashMessage('error', 'element_name_required');
                } elseif ($this->templateModel->createElement($element_data)) {
                    $this->setFlashMessage('success', 'template_element_added_successfully');
                } else {
                    $this->setFlashMessage('error', 'error_adding_template_element_อาจจะซ้ำ'); // Ajouter 'error_adding_template_element_duplicate'
                }
                $this->redirect('templates_documents/configure/' . $type_document);


            } // Ajouter elseif pour update_element, delete_element si géré via POST ici

            // Recharger les données après modification pour la vue
            // $background_settings = $this->templateModel->getBackgroundSettings($type_document);
            // $text_elements = $this->templateModel->getElementsByType($type_document);
            // $data['background_settings'] = (array) $background_settings;
            // $data['text_elements'] = $text_elements;
            // $this->view('templates_documents/configure', $data); // Normalement géré par redirect

        } else {
            $this->view('templates_documents/configure', $data);
        }
    }

    // Gérer la suppression d'un élément textuel (pourrait être une action AJAX ou un simple POST)
    public function delete_element($element_id = null) {
        if (!$this->userHasPermission('manage_document_templates')) {
             $this->setFlashMessage('error', 'access_denied');
             $this->redirect('templates_documents/index'); // Rediriger vers une page sûre
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['element_id_to_delete'])) {
            $element_id = (int)$_POST['element_id_to_delete'];
            $type_document_redirect = $_POST['type_document_redirect'] ?? 'diplome'; // Fallback

            $element = $this->templateModel->getElementById($element_id);
            if (!$element) {
                $this->setFlashMessage('error', 'template_element_not_found');
            } elseif ($this->templateModel->deleteElement($element_id)) {
                $this->setFlashMessage('success', 'template_element_deleted_successfully');
            } else {
                $this->setFlashMessage('error', 'error_deleting_template_element');
            }
            $this->redirect('templates_documents/configure/' . $type_document_redirect);
        } else {
            // Si ce n'est pas POST ou ID manquant, rediriger
            $this->redirect('templates_documents/index');
        }
    }


    private function handleBackgroundImageUpload($file, $old_filename = null) {
        $result = ['success' => false, 'filename' => null, 'error' => ''];
        // Utiliser $this->uploadDir qui est déjà défini
        if (!is_dir($this->uploadDir) || !is_writable($this->uploadDir)) {
            $result['error'] = $this->translate('upload_directory_not_writable');
            error_log("Upload directory templates_backgrounds not writable: " . $this->uploadDir);
            return $result;
        }

        $filename = uniqid(preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)) . '_bg_', true)
                  . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $target_file = $this->uploadDir . $filename;
        $max_file_size = 5 * 1024 * 1024; // 5MB pour les fonds
        $allowed_types = ['jpg', 'jpeg', 'png']; // Peut-être pas SVG pour un fond complexe via DomPDF

        if ($file['size'] > $max_file_size) {
            $result['error'] = $this->translate('file_too_large', [':size' => '5MB']);
            return $result;
        }
        if (!in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), $allowed_types)) {
            $result['error'] = $this->translate('unsupported_file_type_image_jpg_png'); // Nouvelle clé de langue
            return $result;
        }

        if ($old_filename) {
            $this->deleteBackgroundImage($old_filename);
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $result['success'] = true;
            $result['filename'] = $filename;
        } else {
            $result['error'] = $this->translate('error_during_upload');
        }
        return $result;
    }

    private function deleteBackgroundImage($filename_to_delete) {
        if (empty($filename_to_delete)) return;
        $file_path = $this->uploadDir . basename($filename_to_delete); // Sécurité avec basename
        if (file_exists($file_path) && is_writable($file_path)) { // Vérifier is_writable sur le fichier avant unlink
            unlink($file_path);
        }
    }
}
?>
