<?php
// app/views/templates_documents/gerer.php
$current_fond = $fond ?? (object)['type_fond' => '', 'valeur_fond' => '', 'opacite_fond' => 1.0];
$uploadBaseUrl = $app_url . '/';
?>

<div class="container mt-5">
    <h2><?php echo htmlspecialchars($title ?? ''); ?></h2>

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
            <h4><?php echo $tr('document_background_settings'); ?></h4>
        </div>
        <div class="card-body">
            <form action="<?php echo $app_url; ?>/templatesdocuments/saveBackground/<?php echo htmlspecialchars($type_document); ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="type_fond" class="form-label"><?php echo $tr('background_type'); ?></label>
                        <select class="form-select" id="type_fond" name="type_fond">
                            <option value="" <?php echo ($current_fond->type_fond == '') ? 'selected' : ''; ?>><?php echo $tr('none'); ?></option>
                            <option value="couleur" <?php echo ($current_fond->type_fond == 'couleur') ? 'selected' : ''; ?>><?php echo $tr('color'); ?></option>
                            <option value="theme_app" <?php echo ($current_fond->type_fond == 'theme_app') ? 'selected' : ''; ?>><?php echo $tr('app_theme'); ?></option>
                            <option value="image_upload" <?php echo ($current_fond->type_fond == 'image_upload') ? 'selected' : ''; ?>><?php echo $tr('uploaded_image'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="valeur_fond_couleur" class="form-label"><?php echo $tr('background_value_color'); ?></label>
                        <input type="color" class="form-control form-control-color" id="valeur_fond_couleur" name="valeur_fond_couleur" value="<?php echo ($current_fond->type_fond == 'couleur' && $current_fond->valeur_fond) ? htmlspecialchars($current_fond->valeur_fond) : '#FFFFFF'; ?>">
                        <small class="form-text text-muted"><?php echo $tr('used_if_color_selected'); ?></small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="valeur_fond_theme" class="form-label"><?php echo $tr('app_theme_name'); // Nom du Thème ?></label>
                        <input type="text" class="form-control" id="valeur_fond_theme" name="valeur_fond_theme" value="<?php echo ($current_fond->type_fond == 'theme_app' && $current_fond->valeur_fond) ? htmlspecialchars($current_fond->valeur_fond) : ''; ?>" placeholder="Ex: theme_bleu">
                        <small class="form-text text-muted"><?php echo $tr('used_if_theme_selected'); // Utilisé si Type = Thème ?></small>
                    </div>
                     <div class="col-md-3 mb-3">
                        <label for="opacite_fond" class="form-label"><?php echo $tr('background_opacity'); ?></label>
                        <input type="number" step="0.01" min="0" max="1" class="form-control" id="opacite_fond" name="opacite_fond" value="<?php echo htmlspecialchars($current_fond->opacite_fond ?? '1.0'); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="image_fond_file" class="form-label"><?php echo $tr('background_image_file'); ?></label>
                    <input type="file" class="form-control" id="image_fond_file" name="image_fond_file" accept="image/*">
                    <small class="form-text text-muted"><?php echo $tr('used_if_image_selected'); ?></small>
                    <?php if (isset($current_fond->type_fond) && $current_fond->type_fond == 'image_upload' && !empty($current_fond->valeur_fond)): ?>
                        <div class="mt-2">
                            <img src="<?php echo $uploadBaseUrl . htmlspecialchars($current_fond->valeur_fond); ?>?t=<?php echo time(); ?>" alt="Fond actuel" class="img-thumbnail" style="max-height: 100px;">
                            <p><small><?php echo $tr('current_background_image'); ?>: <?php echo htmlspecialchars($current_fond->valeur_fond); ?></small></p>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-palette"></i> <?php echo $tr('save_background_settings'); ?></button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4><?php echo $tr('add_text_element'); ?></h4>
        </div>
        <div class="card-body">
            <form action="<?php echo $app_url; ?>/templatesdocuments/addElement/<?php echo htmlspecialchars($type_document); ?>" method="POST">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="element_name" class="form-label"><?php echo $tr('element_name_placeholder'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo (!empty($element_err)) ? 'is-invalid' : ''; ?>" id="element_name" name="element" value="<?php echo htmlspecialchars($new_element['element'] ?? ''); ?>" required>
                        <div class="invalid-feedback"><?php echo $element_err ?? ''; ?></div>
                    </div>
                    <div class="col-md-2 mb-3"><label for="position_x" class="form-label">X (mm) <span class="text-danger">*</span></label><input type="number" class="form-control" id="position_x" name="position_x" value="<?php echo htmlspecialchars($new_element['position_x'] ?? '0'); ?>" required></div>
                    <div class="col-md-2 mb-3"><label for="position_y" class="form-label">Y (mm) <span class="text-danger">*</span></label><input type="number" class="form-control" id="position_y" name="position_y" value="<?php echo htmlspecialchars($new_element['position_y'] ?? '0'); ?>" required></div>
                    <div class="col-md-2 mb-3"><label for="taille_police" class="form-label"><?php echo $tr('font_size_pt'); ?> <span class="text-danger">*</span></label><input type="number" class="form-control" id="taille_police" name="taille_police" value="<?php echo htmlspecialchars($new_element['taille_police'] ?? '10'); ?>" required></div>
                    <div class="col-md-3 mb-3"><label for="police" class="form-label"><?php echo $tr('font_family'); ?></label><input type="text" class="form-control" id="police" name="police" value="<?php echo htmlspecialchars($new_element['police'] ?? 'helvetica'); ?>"></div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3"><label for="couleur" class="form-label"><?php echo $tr('color'); ?></label><input type="color" class="form-control form-control-color" id="couleur" name="couleur" value="<?php echo htmlspecialchars($new_element['couleur'] ?? '#000000'); ?>"></div>
                    <div class="col-md-3 mb-3">
                        <label for="langue_affichage" class="form-label"><?php echo $tr('display_language'); ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="langue_affichage" name="langue_affichage" required>
                            <option value="fr" <?php echo (isset($new_element['langue_affichage']) && $new_element['langue_affichage'] == 'fr') ? 'selected' : ''; ?>>Français</option>
                            <option value="ar" <?php echo (isset($new_element['langue_affichage']) && $new_element['langue_affichage'] == 'ar') ? 'selected' : ''; ?>>Arabe</option>
                            <option value="fr_ar" <?php echo (isset($new_element['langue_affichage']) && $new_element['langue_affichage'] == 'fr_ar') ? 'selected' : ''; ?>>Français & Arabe</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 align-self-center"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="visible" name="visible" value="1" <?php echo (isset($new_element['visible']) && $new_element['visible']) ? 'checked' : 'checked'; ?>><label class="form-check-label" for="visible"><?php echo $tr('visible'); ?></label></div></div>
                    <div class="col-md-3 mb-3 align-self-end"><button type="submit" class="btn btn-success w-100"><i class="fas fa-plus"></i> <?php echo $tr('add_element'); ?></button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h4><?php echo $tr('existing_text_elements'); ?></h4></div>
        <div class="card-body">
            <?php if (empty($elements)): ?>
                <p><?php echo $tr('no_text_elements_for_template'); ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead><tr><th><?php echo $tr('element'); ?></th><th>X</th><th>Y</th><th><?php echo $tr('font_size_short'); ?></th><th><?php echo $tr('font'); ?></th><th><?php echo $tr('color'); ?></th><th><?php echo $tr('language_short'); ?></th><th><?php echo $tr('visible_short'); ?></th><th><?php echo $tr('actions'); ?></th></tr></thead>
                        <tbody>
                            <?php foreach ($elements as $el): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($el->element); ?></td><td><?php echo htmlspecialchars($el->position_x); ?></td><td><?php echo htmlspecialchars($el->position_y); ?></td>
                                    <td><?php echo htmlspecialchars($el->taille_police); ?></td><td><?php echo htmlspecialchars($el->police); ?></td>
                                    <td><span style="padding: 2px 5px; background-color:<?php echo htmlspecialchars($el->couleur); ?>; color: <?php echo (hexdec(substr($el->couleur,1,2)) > 128 || hexdec(substr($el->couleur,3,2)) > 128 || hexdec(substr($el->couleur,5,2)) > 128) ? '#000' : '#FFF'; ?>; border-radius: 3px;"><?php echo htmlspecialchars($el->couleur); ?></span></td>
                                    <td><?php echo htmlspecialchars($el->langue_affichage); ?></td><td><?php echo $el->visible ? $tr('yes') : $tr('no'); ?></td>
                                    <td>
                                        <a href="<?php echo $app_url; ?>/templatesdocuments/editElement/<?php echo $el->id; ?>" class="btn btn-sm btn-warning disabled" title="<?php echo $tr('edit_element_not_implemented'); // TODO ?>"><i class="fas fa-edit"></i></a>
                                        <form action="<?php echo $app_url; ?>/templatesdocuments/deleteElement/<?php echo $el->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_element'); ?>');">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
     <div class="mt-3">
        <a href="<?php echo $app_url; ?>/templatesdocuments" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo $tr('back_to_template_list'); ?>
        </a>
    </div>
</div>

<?php
// 'app_theme_name' => 'Nom du Thème',
// 'used_if_theme_selected' => 'Utilisé si Type = Thème de l\'application',
// 'edit_element_not_implemented' => 'Modification d\'élément non implémentée pour le moment.',
// 'back_to_configs_list' => 'Retour à la liste des configurations',
?>
