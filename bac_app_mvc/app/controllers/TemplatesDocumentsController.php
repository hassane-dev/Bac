<?php

class TemplatesDocumentsController extends Controller {
    private $templateModel;
    private $uploadDir = 'uploads/templates_backgrounds/'; // Relatif à APP_ROOT . '/public/'
    private $allowedDocTypes = ['diplome', 'releve', 'carte'];

    public function __construct() {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = $this->translate('access_denied');
            $this->redirect('auth/login');
        }
        // TODO: Affiner la vérification des permissions
        // if (!$this->userHasPermission('manage_document_templates')) {
        //    $_SESSION['error_message'] = $this->translate('access_denied');
        //    $this->redirect('dashboard');
        // }
        $this->templateModel = $this->model('TemplateDocument');

        $fullUploadDir = APP_ROOT . '/public/' . $this->uploadDir;
        if (!is_dir($fullUploadDir)) {
            if (!mkdir($fullUploadDir, 0755, true)) {
                View::renderError("Impossible de créer le dossier d'upload pour les templates: " . $fullUploadDir);
            }
        }
    }

    public function index() {
        $this->view('templates_documents/index', [
            'title' => $this->translate('document_templates_list'),
            'types_documents' => $this->allowedDocTypes
        ]);
    }

    public function gerer($type_document = null) {
        if (!$type_document || !in_array($type_document, $this->allowedDocTypes)) {
            $_SESSION['error_message'] = $this->translate('invalid_document_type');
            $this->redirect('templatesdocuments');
            return;
        }

        $templateData = $this->templateModel->getElementsByType($type_document);

        $data = [
            'title' => $this->translate('configure_template_for') . ' ' . $this->translate('doc_type_' . $type_document),
            'type_document' => $type_document,
            'elements' => $templateData['elements'],
            'fond' => $templateData['fond'], // Sera un objet, même s'il est vide par défaut (créé dans le modèle)
            'new_element' => ['element' => '', 'position_x' => 0, 'position_y' => 0, 'taille_police' => 10, 'police' => 'helvetica', 'couleur' => '#000000', 'langue_affichage' => 'fr_ar', 'visible' => true],
            'element_err' => '' // Pour les erreurs d'ajout d'élément
        ];
        $this->view('templates_documents/gerer', $data);
    }

    public function addElement($type_document) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$type_document || !in_array($type_document, $this->allowedDocTypes)) {
                $_SESSION['error_message'] = $this->translate('invalid_document_type');
                $this->redirect('templatesdocuments');
                return;
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data_add = [
                'type_document' => $type_document,
                'element' => trim($_POST['element']),
                'position_x' => (int)($_POST['position_x'] ?? 0),
                'position_y' => (int)($_POST['position_y'] ?? 0),
                'taille_police' => (int)($_POST['taille_police'] ?? 10),
                'police' => trim($_POST['police'] ?? 'helvetica'),
                'couleur' => trim($_POST['couleur'] ?? '#000000'),
                'langue_affichage' => $_POST['langue_affichage'] ?? 'fr_ar',
                'visible' => isset($_POST['visible']) ? 1 : 0,
                'element_err' => ''
            ];

            if (empty($data_add['element'])) $data_add['element_err'] = $this->translate('element_name_required');
            // TODO: Ajouter plus de validations pour les positions, taille, etc.

            if (empty($data_add['element_err'])) {
                if ($this->templateModel->addElement($data_add)) {
                    $_SESSION['message'] = $this->translate('template_element_added_successfully');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_template_element');
                }
            } else {
                 $_SESSION['error_message'] = $data_add['element_err'];
            }
            $this->redirect('templatesdocuments/gerer/' . $type_document);
        } else {
            $this->redirect('templatesdocuments');
        }
    }

    public function deleteElement($id) {
        $id = (int)$id;
        // TODO: Sécuriser avec une vérification de méthode POST ou token CSRF
        $element = $this->templateModel->getElementById($id);
        if ($element && !$element->est_parametre_fond) { // S'assurer qu'on ne supprime pas le fond par cette méthode
            if ($this->templateModel->deleteElement($id)) {
                $_SESSION['message'] = $this->translate('template_element_deleted_successfully');
            } else {
                $_SESSION['error_message'] = $this->translate('error_deleting_template_element');
            }
            $this->redirect('templatesdocuments/gerer/' . $element->type_document);
        } else {
            $_SESSION['error_message'] = $this->translate('template_element_not_found_or_is_background');
            // Essayer de trouver une page de redirection pertinente si type_document n'est pas dispo
            $this->redirect('templatesdocuments');
        }
    }

    public function saveBackground($type_document) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             if (!$type_document || !in_array($type_document, $this->allowedDocTypes)) {
                $_SESSION['error_message'] = $this->translate('invalid_document_type');
                $this->redirect('templatesdocuments');
                return;
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $currentFond = $this->templateModel->getElementsByType($type_document)['fond'];

            $data_fond = [
                'type_fond' => $_POST['type_fond'] ?? null,
                'valeur_fond' => null, // Sera défini ci-dessous
                'opacite_fond' => isset($_POST['opacite_fond']) ? (float)$_POST['opacite_fond'] : 1.0,
            ];

            if ($data_fond['opacite_fond'] < 0) $data_fond['opacite_fond'] = 0.0;
            if ($data_fond['opacite_fond'] > 1) $data_fond['opacite_fond'] = 1.0;


            if ($data_fond['type_fond'] === 'couleur') {
                $data_fond['valeur_fond'] = trim($_POST['valeur_fond_couleur'] ?? '#FFFFFF');
            } elseif ($data_fond['type_fond'] === 'theme_app') {
                $data_fond['valeur_fond'] = trim($_POST['valeur_fond_theme'] ?? 'default_theme'); // Nom du thème
            } elseif ($data_fond['type_fond'] === 'image_upload') {
                if (isset($_FILES['image_fond_file']) && $_FILES['image_fond_file']['error'] == UPLOAD_ERR_OK) {
                    $oldImagePath = ($currentFond && $currentFond->type_fond === 'image_upload') ? $currentFond->valeur_fond : null;
                    $uploadResult = $this->handleUpload('image_fond_file', 'bg_'.$type_document.'_', $oldImagePath);
                    if ($uploadResult['success']) {
                        $data_fond['valeur_fond'] = $uploadResult['path'];
                    } else {
                        $_SESSION['error_message'] = $this->translate('error_uploading_file', ['field' => $this->translate('background_image')]) . ': ' . $uploadResult['message'];
                        $this->redirect('templatesdocuments/gerer/' . $type_document);
                        return;
                    }
                } elseif ($currentFond && $currentFond->type_fond === 'image_upload') {
                     $data_fond['valeur_fond'] = $currentFond->valeur_fond; // Conserver l'ancienne image si pas de nouveau fichier
                }
            }

            // Si on change de type de fond et que l'ancien était une image, et qu'on ne re-upload pas une image
            if ($currentFond && $currentFond->type_fond === 'image_upload' && $data_fond['type_fond'] !== 'image_upload' && $data_fond['valeur_fond'] !== $currentFond->valeur_fond) {
                if ($currentFond->valeur_fond && file_exists(APP_ROOT . '/public/' . $currentFond->valeur_fond)) {
                     if (strpos($currentFond->valeur_fond, $this->uploadDir) === 0) { // Sécurité
                        unlink(APP_ROOT . '/public/' . $currentFond->valeur_fond);
                     }
                }
            }


            if ($this->templateModel->saveBackgroundSettings($type_document, $data_fond)) {
                $_SESSION['message'] = $this->translate('background_settings_updated_successfully');
            } else {
                $_SESSION['error_message'] = $this->translate('error_updating_background_settings');
            }
            $this->redirect('templatesdocuments/gerer/' . $type_document);

        } else {
            $this->redirect('templatesdocuments');
        }
    }

    private function handleUpload($fileInputName, $prefix, $currentFilePath = null) {
        $targetDir = APP_ROOT . '/public/' . $this->uploadDir;
        $fileExtension = strtolower(pathinfo($_FILES[$fileInputName]["name"], PATHINFO_EXTENSION));
        $fileName = $prefix . time() . '.' . $fileExtension;
        $targetFilePath = $targetDir . $fileName;

        $check = getimagesize($_FILES[$fileInputName]["tmp_name"]);
        if ($check === false) return ['success' => false, 'message' => $this->translate('file_not_image')];
        if ($_FILES[$fileInputName]["size"] > 5000000) return ['success' => false, 'message' => $this->translate('file_too_large', ['size'=>'5MB'])];
        $allowedTypes = ['jpg', 'png', 'jpeg', 'gif']; // SVG pourrait être problématique pour DomPDF sans config spéciale
        if (!in_array($fileExtension, $allowedTypes)) return ['success' => false, 'message' => $this->translate('unsupported_file_type_image_limited')];


        if ($currentFilePath && strpos($currentFilePath, $this->uploadDir) === 0 && file_exists(APP_ROOT . '/public/' . $currentFilePath)) {
           if (basename($currentFilePath) !== $fileName) {
             unlink(APP_ROOT . '/public/' . $currentFilePath);
           }
        }

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
            return ['success' => true, 'path' => $this->uploadDir . $fileName];
        }
        return ['success' => false, 'message' => $this->translate('error_during_upload')];
    }
}
?>
