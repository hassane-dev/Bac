<?php
// app/views/templates_documents/gerer.php
// $title, $type_document, $elements, $fond, $new_element, $element_err, $tr sont passés
$current_fond = $fond; // Alias
?>

<div class="container mt-5">
    <h2><?php echo htmlspecialchars($title); ?></h2>

    <?php if (!empty($_SESSION['message'])) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Formulaire pour le fond du document -->
    <div class="card mb-4">
        <div class="card-header">
            <h4><?php echo $tr('document_background_settings'); // Paramètres du fond du document ?></h4>
        </div>
        <div class="card-body">
            <form action="<?php echo $app_url; ?>/templatesdocuments/saveBackground/<?php echo htmlspecialchars($type_document); ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="type_fond" class="form-label"><?php echo $tr('background_type'); // Type de fond ?></label>
                        <select class="form-select" id="type_fond" name="type_fond">
                            <option value=""><?php echo $tr('none'); // Aucun ?></option>
                            <option value="couleur" <?php echo (isset($current_fond->type_fond) && $current_fond->type_fond == 'couleur') ? 'selected' : ''; ?>><?php echo $tr('color'); // Couleur ?></option>
                            <option value="theme_app" <?php echo (isset($current_fond->type_fond) && $current_fond->type_fond == 'theme_app') ? 'selected' : ''; ?>><?php echo $tr('app_theme'); // Thème de l'application ?></option>
                            <option value="image_upload" <?php echo (isset($current_fond->type_fond) && $current_fond->type_fond == 'image_upload') ? 'selected' : ''; ?>><?php echo $tr('uploaded_image'); // Image Téléversée ?></option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valeur_fond_couleur" class="form-label"><?php echo $tr('background_value_color'); // Valeur (Couleur HEX) ?></label>
                        <input type="color" class="form-control form-control-color" id="valeur_fond_couleur" name="valeur_fond_couleur" value="<?php echo (isset($current_fond->type_fond) && $current_fond->type_fond == 'couleur' && $current_fond->valeur_fond) ? htmlspecialchars($current_fond->valeur_fond) : '#FFFFFF'; ?>">
                        <small class="form-text text-muted"><?php echo $tr('used_if_color_selected'); // Utilisé si Type = Couleur ?></small>
                    </div>
                     <div class="col-md-4 mb-3">
                        <label for="opacite_fond" class="form-label"><?php echo $tr('background_opacity'); // Opacité du fond (0.0 - 1.0) ?></label>
                        <input type="number" step="0.01" min="0" max="1" class="form-control" id="opacite_fond" name="opacite_fond" value="<?php echo htmlspecialchars($current_fond->opacite_fond ?? '1.0'); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="image_fond_file" class="form-label"><?php echo $tr('background_image_file'); // Fichier Image de Fond ?></label>
                    <input type="file" class="form-control" id="image_fond_file" name="image_fond_file">
                    <small class="form-text text-muted"><?php echo $tr('used_if_image_selected'); // Utilisé si Type = Image Téléversée. Remplace l'image existante. ?></small>
                    <?php if (isset($current_fond->type_fond) && $current_fond->type_fond == 'image_upload' && !empty($current_fond->valeur_fond)): ?>
                        <div class="mt-2">
                            <img src="<?php echo $app_url . '/' . htmlspecialchars($current_fond->valeur_fond); ?>" alt="Fond actuel" class="img-thumbnail" style="max-height: 100px;">
                            <p><small><?php echo $tr('current_background_image'); // Image de fond actuelle ?>: <?php echo htmlspecialchars($current_fond->valeur_fond); ?></small></p>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $tr('save_background_settings'); // Enregistrer paramètres du fond ?></button>
            </form>
        </div>
    </div>


    <!-- Formulaire pour ajouter un nouvel élément textuel -->
    <div class="card mb-4">
        <div class="card-header">
            <h4><?php echo $tr('add_text_element'); // Ajouter un Élément Textuel ?></h4>
        </div>
        <div class="card-body">
            <form action="<?php echo $app_url; ?>/templatesdocuments/addElement/<?php echo htmlspecialchars($type_document); ?>" method="POST">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="element_name" class="form-label"><?php echo $tr('element_name_placeholder'); // Nom de l'élément (ex: nom_eleve) ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo (!empty($element_err)) ? 'is-invalid' : ''; ?>" id="element_name" name="element" value="<?php echo htmlspecialchars($new_element['element'] ?? ''); ?>" required>
                        <div class="invalid-feedback"><?php echo $element_err ?? ''; ?></div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="position_x" class="form-label">X (mm) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="position_x" name="position_x" value="<?php echo htmlspecialchars($new_element['position_x'] ?? '0'); ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="position_y" class="form-label">Y (mm) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="position_y" name="position_y" value="<?php echo htmlspecialchars($new_element['position_y'] ?? '0'); ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="taille_police" class="form-label"><?php echo $tr('font_size_pt'); // Taille (pt) ?> <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="taille_police" name="taille_police" value="<?php echo htmlspecialchars($new_element['taille_police'] ?? '10'); ?>" required>
                    </div>
                     <div class="col-md-3 mb-3">
                        <label for="police" class="form-label"><?php echo $tr('font_family'); // Police ?></label>
                        <input type="text" class="form-control" id="police" name="police" value="<?php echo htmlspecialchars($new_element['police'] ?? 'helvetica'); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="couleur" class="form-label"><?php echo $tr('color'); // Couleur ?></label>
                        <input type="color" class="form-control form-control-color" id="couleur" name="couleur" value="<?php echo htmlspecialchars($new_element['couleur'] ?? '#000000'); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="langue_affichage" class="form-label"><?php echo $tr('display_language'); // Langue d'affichage ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="langue_affichage" name="langue_affichage" required>
                            <option value="fr" <?php echo (isset($new_element['langue_affichage']) && $new_element['langue_affichage'] == 'fr') ? 'selected' : ''; ?>>Français</option>
                            <option value="ar" <?php echo (isset($new_element['langue_affichage']) && $new_element['langue_affichage'] == 'ar') ? 'selected' : ''; ?>>Arabe</option>
                            <option value="fr_ar" <?php echo (isset($new_element['langue_affichage']) && $new_element['langue_affichage'] == 'fr_ar') ? 'selected' : ''; ?>>Français & Arabe</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 align-self-end">
                        <div class="form-check form-switch">
                             <input class="form-check-input" type="checkbox" id="visible" name="visible" value="1" <?php echo (isset($new_element['visible']) && $new_element['visible']) ? 'checked' : 'checked'; // Visible par défaut ?>>
                            <label class="form-check-label" for="visible"><?php echo $tr('visible'); // Visible ?></label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 align-self-end">
                        <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> <?php echo $tr('add_element'); // Ajouter Élément ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des éléments textuels existants -->
    <div class="card">
        <div class="card-header">
            <h4><?php echo $tr('existing_text_elements'); // Éléments Textuels Existants ?></h4>
        </div>
        <div class="card-body">
            <?php if (empty($elements)): ?>
                <p><?php echo $tr('no_text_elements_for_template'); // Aucun élément textuel défini pour ce modèle. ?></p>
            <?php else: ?>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th><?php echo $tr('element'); // Élément ?></th>
                            <th>X</th>
                            <th>Y</th>
                            <th><?php echo $tr('font_size_short'); // Taille ?></th>
                            <th><?php echo $tr('font'); // Police ?></th>
                            <th><?php echo $tr('color'); ?></th>
                            <th><?php echo $tr('language_short'); // Lang. ?></th>
                            <th><?php echo $tr('visible_short'); // Vis. ?></th>
                            <th><?php echo $tr('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($elements as $el): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($el->element); ?></td>
                                <td><?php echo htmlspecialchars($el->position_x); ?></td>
                                <td><?php echo htmlspecialchars($el->position_y); ?></td>
                                <td><?php echo htmlspecialchars($el->taille_police); ?></td>
                                <td><?php echo htmlspecialchars($el->police); ?></td>
                                <td style="background-color:<?php echo htmlspecialchars($el->couleur); ?>; color: <?php echo (hexdec(substr($el->couleur,1,2)) > 128 || hexdec(substr($el->couleur,3,2)) > 128 || hexdec(substr($el->couleur,5,2)) > 128) ? '#000' : '#FFF'; ?>;">
                                    <?php echo htmlspecialchars($el->couleur); ?>
                                </td>
                                <td><?php echo htmlspecialchars($el->langue_affichage); ?></td>
                                <td><?php echo $el->visible ? $tr('yes') : $tr('no'); ?></td>
                                <td>
                                    <a href="<?php echo $app_url; ?>/templatesdocuments/editElement/<?php echo $el->id; ?>" class="btn btn-sm btn-warning disabled"><i class="fas fa-edit"></i></a> <?php // TODO: Implémenter editElement ?>
                                    <form action="<?php echo $app_url; ?>/templatesdocuments/deleteElement/<?php echo $el->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_element'); ?>');">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
     <div class="mt-3">
        <a href="<?php echo $app_url; ?>/templatesdocuments" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo $tr('back_to_template_list'); // Retour à la liste des modèles ?>
        </a>
    </div>
</div>

<?php
// Traductions
// fr.php: 'document_background_settings' => 'Paramètres du fond du document', 'background_type' => 'Type de fond', 'none' => 'Aucun', 'color' => 'Couleur', 'app_theme' => "Thème de l'application", 'uploaded_image' => 'Image Téléversée', 'background_value_color' => 'Valeur (Couleur HEX)', 'used_if_color_selected' => 'Utilisé si Type = Couleur', 'background_opacity' => 'Opacité du fond (0.0 - 1.0)', 'background_image_file' => 'Fichier Image de Fond', 'used_if_image_selected' => "Utilisé si Type = Image Téléversée. Remplace l'image existante.", 'current_background_image' => 'Image de fond actuelle', 'save_background_settings' => 'Enregistrer paramètres du fond', 'add_text_element' => 'Ajouter un Élément Textuel', 'element_name_placeholder' => "Nom de l'élément (ex: nom_eleve)", 'font_size_pt' => 'Taille (pt)', 'font_family' => 'Police', 'display_language' => "Langue d'affichage", 'visible' => 'Visible', 'add_element' => 'Ajouter Élément', 'existing_text_elements' => 'Éléments Textuels Existants', 'no_text_elements_for_template' => 'Aucun élément textuel défini pour ce modèle.', 'element' => 'Élément', 'font_size_short' => 'Taille', 'font' => 'Police', 'language_short' => 'Lang.', 'visible_short' => 'Vis.', 'are_you_sure_delete_element' => "Êtes-vous sûr de vouloir supprimer cet élément ?", 'back_to_template_list' => 'Retour à la liste des modèles'
// ar.php: ...
?>
