<?php
// app/views/configurations_linguistiques/index.php
// $title, $settings (objet), $langues_actives_array, $langue_principale, $langue_secondaire,
// $mode_affichage_documents, $available_languages, $tr, et les variables _err sont passées.
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('language_settings')); ?></h2>

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

    <form action="<?php echo $app_url; ?>/configurationslinguistiques/update" method="POST">
        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo $tr('active_languages_interface_docs'); ?></h4>
            </div>
            <div class="card-body">
                <div class="mb-3 <?php echo (!empty($langues_actives_err)) ? 'is-invalid' : ''; ?>">
                    <p><?php echo $tr('select_active_languages_help'); // Cochez les langues que vous souhaitez activer dans l'application. ?></p>
                    <?php if (empty($available_languages)): ?>
                        <p><?php echo $tr('no_languages_found_in_lang_dir'); ?></p>
                    <?php else: ?>
                        <?php foreach ($available_languages as $code => $name): ?>
                            <div class="form-check form-check-inline form-switch">
                                <input class="form-check-input" type="checkbox" name="langues_actives[]"
                                       value="<?php echo htmlspecialchars($code); ?>"
                                       id="lang_<?php echo htmlspecialchars($code); ?>"
                                       <?php echo in_array($code, $langues_actives_array ?? []) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="lang_<?php echo htmlspecialchars($code); ?>">
                                    <?php echo htmlspecialchars($name); ?> (<?php echo htmlspecialchars($code); ?>)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($langues_actives_err)): ?>
                    <div class="invalid-feedback d-block"><?php echo $langues_actives_err; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo $tr('default_document_languages'); ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="langue_principale" class="form-label"><?php echo $tr('main_language'); ?> <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo (!empty($langue_principale_err)) ? 'is-invalid' : ''; ?>" id="langue_principale" name="langue_principale" required>
                            <option value=""><?php echo $tr('select_main_language'); ?></option>
                            <?php foreach ($available_languages as $code => $name): ?>
                                <?php // Afficher uniquement les langues actuellement cochées comme actives dans la liste déroulante ?>
                                <?php if (in_array($code, $langues_actives_array ?? [])): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>" <?php echo (isset($langue_principale) && $langue_principale == $code) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?php echo $langue_principale_err ?? ''; ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="langue_secondaire" class="form-label"><?php echo $tr('secondary_language'); ?></label>
                        <select class="form-select <?php echo (!empty($langue_secondaire_err)) ? 'is-invalid' : ''; ?>" id="langue_secondaire" name="langue_secondaire">
                            <option value=""><?php echo $tr('select_secondary_language_optional'); ?></option>
                             <?php foreach ($available_languages as $code => $name): ?>
                                <?php // Afficher uniquement les langues actuellement cochées comme actives ?>
                                <?php if (in_array($code, $langues_actives_array ?? [])): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>" <?php echo (isset($langue_secondaire) && $langue_secondaire == $code) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?php echo $langue_secondaire_err ?? ''; ?></div>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="mode_affichage_documents" class="form-label"><?php echo $tr('document_display_mode'); ?> <span class="text-danger">*</span></label>
                    <select class="form-select <?php echo (!empty($mode_affichage_documents_err)) ? 'is-invalid' : ''; ?>" id="mode_affichage_documents" name="mode_affichage_documents" required>
                        <option value="unilingue" <?php echo (isset($mode_affichage_documents) && $mode_affichage_documents == 'unilingue') ? 'selected' : ''; ?>><?php echo $tr('unilingual_main_lang'); ?></option>
                        <option value="bilingue" <?php echo (isset($mode_affichage_documents) && $mode_affichage_documents == 'bilingue') ? 'selected' : ''; ?>><?php echo $tr('bilingual_main_secondary'); ?></option>
                    </select>
                     <div class="invalid-feedback"><?php echo $mode_affichage_documents_err ?? ''; ?></div>
                    <small class="form-text text-muted"><?php echo $tr('bilingual_requires_secondary_lang_notice'); ?></small>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_language_settings'); ?>
        </button>
         <a href="<?php echo $app_url; ?>/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo $tr('back_to_dashboard'); ?>
        </a>
    </form>
</div>

<?php
// 'select_active_languages_help' => 'Cochez les langues que vous souhaitez activer dans l\'application.',
// 'back_to_dashboard' => 'Retour au tableau de bord',
?>
