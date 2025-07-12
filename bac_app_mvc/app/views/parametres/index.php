<?php
// Data: $data['page_title'], $data['settings'] (array), $data['upload_base_url']
//       $data['logo_pays_path_err'], etc. pour les erreurs de fichier.
// Helpers: $tr, $app_url
?>

<h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
<hr>

<form action="<?php echo APP_URL . '/parametres/index'; ?>" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-university"></i> <?php echo $tr('institutional_information'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="republique_de" class="form-label"><?php echo $tr('republic_of'); ?></label>
                            <input type="text" name="republique_de" id="republique_de" class="form-control"
                                   value="<?php echo htmlspecialchars($data['settings']['republique_de'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="devise_republique" class="form-label"><?php echo $tr('republic_motto'); ?></label>
                            <input type="text" name="devise_republique" id="devise_republique" class="form-control"
                                   value="<?php echo htmlspecialchars($data['settings']['devise_republique'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ministere_nom" class="form-label"><?php echo $tr('ministry_name'); ?></label>
                        <input type="text" name="ministere_nom" id="ministere_nom" class="form-control"
                               value="<?php echo htmlspecialchars($data['settings']['ministere_nom'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="office_examen_nom" class="form-label"><?php echo $tr('exam_office_name'); ?></label>
                        <input type="text" name="office_examen_nom" id="office_examen_nom" class="form-control"
                               value="<?php echo htmlspecialchars($data['settings']['office_examen_nom'] ?? ''); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="direction_nom" class="form-label"><?php echo $tr('directorate_name'); ?></label>
                            <input type="text" name="direction_nom" id="direction_nom" class="form-control"
                                   value="<?php echo htmlspecialchars($data['settings']['direction_nom'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ville_office" class="form-label"><?php echo $tr('office_city'); ?></label>
                            <input type="text" name="ville_office" id="ville_office" class="form-control"
                                   value="<?php echo htmlspecialchars($data['settings']['ville_office'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-image"></i> <?php echo $tr('national_symbols'); ?></h4>
                </div>
                <div class="card-body">
                    <?php
                    $file_fields_config = [
                        'logo_pays_path' => $tr('country_logo'),
                        'armoirie_pays_path' => $tr('country_coat_of_arms'),
                        'drapeau_pays_path' => $tr('country_flag')
                    ];
                    ?>
                    <?php foreach ($file_fields_config as $field => $label): ?>
                    <div class="mb-4 border-bottom pb-3">
                        <label for="<?php echo $field; ?>" class="form-label fw-bold"><?php echo $label; ?></label>
                        <?php if (!empty($data['settings'][$field])): ?>
                            <div class="mb-2">
                                <img src="<?php echo $data['upload_base_url'] . htmlspecialchars($data['settings'][$field]); ?>?t=<?php echo time(); // Cache buster ?>"
                                     alt="<?php echo $label; ?>" style="max-height: 100px; max-width: 200px; background: #f0f0f0; border:1px solid #ddd;">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="delete_<?php echo $field; ?>" id="delete_<?php echo $field; ?>">
                                    <label class="form-check-label text-danger" for="delete_<?php echo $field; ?>">
                                        <?php echo $tr('delete_current_file'); ?> (<?php echo htmlspecialchars(basename($data['settings'][$field])); ?>)
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="<?php echo $field; ?>" id="<?php echo $field; ?>" class="form-control <?php echo (!empty($data[$field.'_err'])) ? 'is-invalid' : ''; ?>">
                        <div class="form-text"><?php echo $tr('max_file_size_note', [':size' => '2MB']); ?> (JPG, PNG, GIF, SVG)</div>
                        <?php if (!empty($data[$field.'_err'])): ?>
                            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data[$field.'_err']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-signature"></i> <?php echo $tr('document_personalization'); ?></h4>
                </div>
                <div class="card-body">
                     <?php
                    $file_fields_perso = [
                        'signature_directeur_path' => $tr('director_signature'),
                        'cachet_office_path' => $tr('office_stamp')
                    ];
                    ?>
                    <?php foreach ($file_fields_perso as $field => $label): ?>
                    <div class="mb-4 border-bottom pb-3">
                        <label for="<?php echo $field; ?>" class="form-label fw-bold"><?php echo $label; ?></label>
                        <?php if (!empty($data['settings'][$field])): ?>
                            <div class="mb-2">
                                <img src="<?php echo $data['upload_base_url'] . htmlspecialchars($data['settings'][$field]); ?>?t=<?php echo time(); ?>"
                                     alt="<?php echo $label; ?>" style="max-height: 100px; max-width: 200px; background: #f0f0f0; border:1px solid #ddd;">
                                 <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="delete_<?php echo $field; ?>" id="delete_<?php echo $field; ?>">
                                    <label class="form-check-label text-danger" for="delete_<?php echo $field; ?>">
                                        <?php echo $tr('delete_current_file'); ?> (<?php echo htmlspecialchars(basename($data['settings'][$field])); ?>)
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="<?php echo $field; ?>" id="<?php echo $field; ?>" class="form-control <?php echo (!empty($data[$field.'_err'])) ? 'is-invalid' : ''; ?>">
                        <div class="form-text"><?php echo $tr('max_file_size_note', [':size' => '2MB']); ?> (JPG, PNG, GIF, SVG)</div>
                        <?php if (!empty($data[$field.'_err'])): ?>
                            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data[$field.'_err']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-lg btn-success">
                    <i class="fas fa-save"></i> <?php echo $tr('save_general_settings'); ?>
                </button>
            </div>

        </div>
    </div>
</form>
