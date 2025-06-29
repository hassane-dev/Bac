<?php

class TemplatesDocumentsController extends Controller {
    private $templateModel;
    private $uploadDir = 'uploads/templates_backgrounds/'; // Relatif à APP_ROOT/public/

    public function __construct() {
        parent::__construct();
        // TODO: Vérification de session et permissions
        // if (!$this->isLoggedIn() || !$this->userHasPermission('manage_document_templates')) {
        //     $_SESSION['error_message'] = $this->translate('access_denied');
        //     $this->redirect('dashboard');
        // }
        $this->templateModel = $this->model('TemplateDocument');

        if (!is_dir(APP_ROOT . '/public/' . $this->uploadDir)) {
            mkdir(APP_ROOT . '/public/' . $this->uploadDir, 0755, true);
        }
    }

    public function index() {
        $types_documents = ['diplome', 'releve', 'carte']; // Ou récupérer dynamiquement si besoin
        $this->view('templates_documents/index', [
            'title' => $this->translate('document_templates_list'),
            'types_documents' => $types_documents
        ]);
    }

    /**
     * Gère la configuration d'un type de document spécifique (éléments textuels et fond).
     */
    public function gerer($type_document = null) {
        if (!$type_document || !in_array($type_document, ['diplome', 'releve', 'carte'])) {
            $_SESSION['error_message'] = $this->translate('invalid_document_type');
            $this->redirect('templatesdocuments');
            return;
        }

        $templateData = $this->templateModel->getElementsByType($type_document);

        $data = [
            'title' => $this->translate('configure_template_for') . ' ' . $this->translate('doc_type_' . $type_document),
            'type_document' => $type_document,
            'elements' => $templateData['elements'],
            'fond' => $templateData['fond'],
            // Pour le formulaire d'ajout d'élément
            'new_element' => ['element' => '', 'position_x' => 0, 'position_y' => 0, 'taille_police' => 10, 'police' => 'helvetica', 'couleur' => '#000000', 'langue_affichage' => 'fr_ar', 'visible' => true],
            'element_err' => ''
        ];
        $this->view('templates_documents/gerer', $data);
    }

    public function addElement($type_document) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$type_document || !in_array($type_document, ['diplome', 'releve', 'carte'])) {
                $_SESSION['error_message'] = $this->translate('invalid_document_type');
                $this->redirect('templatesdocuments');
                return;
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data_add = [
                'type_document' => $type_document,
                'element' => trim($_POST['element']),
                'position_x' => (int)$_POST['position_x'],
                'position_y' => (int)$_POST['position_y'],
                'taille_police' => (int)$_POST['taille_police'],
                'police' => trim($_POST['police']),
                'couleur' => trim($_POST['couleur']),
                'langue_affichage' => $_POST['langue_affichage'],
                'visible' => isset($_POST['visible']) ? 1 : 0,
                'element_err' => ''
            ];

            if (empty($data_add['element'])) $data_add['element_err'] = $this->translate('element_name_required');
            // Autres validations...

            if (empty($data_add['element_err'])) {
                if ($this->templateModel->addElement($data_add)) {
                    $_SESSION['message'] = $this->translate('template_element_added_successfully');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_adding_template_element');
                }
            } else {
                 $_SESSION['error_message'] = $data_add['element_err']; // Stocker l'erreur pour l'afficher sur la page gerer
            }
            $this->redirect('templatesdocuments/gerer/' . $type_document);
        } else {
            $this->redirect('templatesdocuments');
        }
    }

    public function updateElement($id) {
        // Similaire à store/add, mais pour la mise à jour d'un élément existant.
        // Nécessite une vue edit_element.php ou un modal dans gerer.php
        // Pour l'instant, on se concentre sur l'ajout et la gestion du fond.
        // Cette fonction est laissée en placeholder.
         $_SESSION['message'] = "Fonctionnalité de mise à jour d'élément à implémenter.";
         // Récupérer type_document à partir de l'élément pour la redirection
         $element = $this->templateModel->getElementById($id);
         if ($element) {
            $this->redirect('templatesdocuments/gerer/' . $element->type_document);
         } else {
            $this->redirect('templatesdocuments');
         }
    }


    public function deleteElement($id) {
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Pour sécurité
            $element = $this->templateModel->getElementById($id);
            if ($element) {
                if ($this->templateModel->deleteElement($id)) {
                    $_SESSION['message'] = $this->translate('template_element_deleted_successfully');
                } else {
                    $_SESSION['error_message'] = $this->translate('error_deleting_template_element');
                }
                $this->redirect('templatesdocuments/gerer/' . $element->type_document);
            } else {
                $_SESSION['error_message'] = $this->translate('template_element_not_found');
                $this->redirect('templatesdocuments');
            }
        // } else {
        //     $this->redirect('templatesdocuments');
        // }
    }

    public function saveBackground($type_document) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             if (!$type_document || !in_array($type_document, ['diplome', 'releve', 'carte'])) {
                $_SESSION['error_message'] = $this->translate('invalid_document_type');
                $this->redirect('templatesdocuments');
                return;
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $currentFond = $this->templateModel->getElementsByType($type_document)['fond'];

            $data_fond = [
                'type_fond' => $_POST['type_fond'] ?? null,
                'valeur_fond' => trim($_POST['valeur_fond'] ?? ($currentFond->valeur_fond ?? '')),
                'opacite_fond' => (float)($_POST['opacite_fond'] ?? 1.0),
            ];

            if (isset($_FILES['image_fond_file']) && $_FILES['image_fond_file']['error'] == UPLOAD_ERR_OK) {
                $uploadResult = $this->handleUpload('image_fond_file', 'bg_'.$type_document, $currentFond->valeur_fond ?? null);
                if ($uploadResult['success']) {
                    $data_fond['valeur_fond'] = $uploadResult['path'];
                } else {
                    $_SESSION['error_message'] = $this->translate('error_uploading_file', ['field' => 'image_fond']) . ': ' . $uploadResult['message'];
                    $this->redirect('templatesdocuments/gerer/' . $type_document);
                    return;
                }
            } elseif ($data_fond['type_fond'] !== 'image_upload' && $currentFond && $currentFond->type_fond === 'image_upload' && !empty($currentFond->valeur_fond)) {
                // Si on change de type de fond et que l'ancien était une image, on supprime l'ancienne image
                if (file_exists(APP_ROOT . '/public/' . $currentFond->valeur_fond)) {
                    unlink(APP_ROOT . '/public/' . $currentFond->valeur_fond);
                }
                 if ($data_fond['type_fond'] === 'couleur') {
                    // Valeur par défaut si on passe d'image à couleur sans spécifier de couleur
                    $data_fond['valeur_fond'] = $_POST['valeur_fond_couleur'] ?? '#FFFFFF';
                 } else {
                    $data_fond['valeur_fond'] = null; // Pour theme_app si pas de valeur spécifique
                 }
            } else if ($data_fond['type_fond'] === 'couleur') {
                 $data_fond['valeur_fond'] = $_POST['valeur_fond_couleur'] ?? '#FFFFFF';
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

    // Méthode d'upload similaire à ParametresController, pourrait être dans un Helper/Controller de base
    private function handleUpload($fileInputName, $prefix, $currentFilePath = null) {
        $targetDir = APP_ROOT . '/public/' . $this->uploadDir;
        // Rendre le nom de fichier un peu plus prédictible pour le même type de document/fond
        $fileName = $prefix . '_' . time() . '_' . basename($_FILES[$fileInputName]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES[$fileInputName]["tmp_name"]);
        if ($check === false) return ['success' => false, 'message' => $this->translate('file_not_image')];
        if ($_FILES[$fileInputName]["size"] > 5000000) return ['success' => false, 'message' => $this->translate('file_too_large')];
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) return ['success' => false, 'message' => $this->translate('unsupported_file_type')];

        // Supprimer l'ancien fichier si un nouveau est uploadé et que l'ancien chemin est celui d'une image uploadée
        if ($currentFilePath && strpos($currentFilePath, $this->uploadDir) === 0 && file_exists(APP_ROOT . '/public/' . $currentFilePath)) {
            unlink(APP_ROOT . '/public/' . $currentFilePath);
        }

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
            return ['success' => true, 'path' => $this->uploadDir . $fileName];
        }
        return ['success' => false, 'message' => $this->translate('error_during_upload')];
    }

}
?>
