<?php
// Data: $data['page_title'], $data['langues_actives_array'], $data['langue_principale'],
//       $data['langue_secondaire'], $data['mode_affichage_documents'],
//       $data['available_langs_from_config'], $data['errors']
// Helpers: $tr, $app_url
?>

<h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
<hr>

<form action="<?php echo APP_URL . '/configurations_linguistiques/index'; ?>" method="POST">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-language"></i> <?php echo $tr('active_languages_interface_docs'); ?></h4>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $tr('select_active_languages_help'); ?></p>
                    <?php if (!empty($data['errors']['langues_actives'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($data['errors']['langues_actives']); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <?php if (empty($data['available_langs_from_config'])): ?>
                            <p class="text-danger"><?php echo $tr('no_languages_found_in_lang_dir'); ?></p>
                        <?php else: ?>
                            <?php foreach ($data['available_langs_from_config'] as $lang_code): ?>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox"
                                               name="langues_actives[]" value="<?php echo $lang_code; ?>"
                                               id="lang_<?php echo $lang_code; ?>"
                                               <?php echo in_array($lang_code, $data['langues_actives_array']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="lang_<?php echo $lang_code; ?>">
                                            <?php echo $tr(strtolower($lang_code)); ?>
                                            (<?php echo strtoupper($lang_code); ?>)
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-file-alt"></i> <?php echo $tr('default_document_languages'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="langue_principale" class="form-label"><?php echo $tr('main_language'); ?> <span class="text-danger">*</span></label>
                        <select name="langue_principale" id="langue_principale" class="form-select <?php echo (!empty($data['errors']['langue_principale'])) ? 'is-invalid' : ''; ?>" required>
                            <option value=""><?php echo $tr('select_main_language'); ?></option>
                            <?php
                            // Afficher seulement les langues actuellement sélectionnées comme actives dans les options.
                            // Si une langue principale sauvegardée n'est plus active, elle sera affichée comme sélectionnée mais avec une note.
                            $options_langues_principales = $data['langues_actives_array'];
                            if (!empty($data['langue_principale']) && !in_array($data['langue_principale'], $options_langues_principales)) {
                                $options_langues_principales[] = $data['langue_principale']; // Ajouter temporairement pour l'affichage
                            }
                            sort($options_langues_principales);
                            ?>
                            <?php foreach ($options_langues_principales as $lang_code): ?>
                                <option value="<?php echo $lang_code; ?>" <?php echo ($data['langue_principale'] == $lang_code) ? 'selected' : ''; ?>>
                                     <?php echo $tr(strtolower($lang_code)); ?> (<?php echo strtoupper($lang_code); ?>)
                                     <?php if (!in_array($lang_code, $data['langues_actives_array'])): ?>
                                        (<?php echo $tr('not_currently_active'); ?>)
                                     <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($data['errors']['langue_principale'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['errors']['langue_principale']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="langue_secondaire" class="form-label"><?php echo $tr('secondary_language'); ?></label>
                        <select name="langue_secondaire" id="langue_secondaire" class="form-select <?php echo (!empty($data['errors']['langue_secondaire'])) ? 'is-invalid' : ''; ?>">
                            <option value=""><?php echo $tr('select_secondary_language_optional'); ?></option>
                             <?php
                             $options_langues_secondaires = $data['langues_actives_array'];
                             if (!empty($data['langue_secondaire']) && !in_array($data['langue_secondaire'], $options_langues_secondaires)) {
                                 $options_langues_secondaires[] = $data['langue_secondaire'];
                             }
                             sort($options_langues_secondaires);
                             ?>
                             <?php foreach ($options_langues_secondaires as $lang_code): ?>
                                <option value="<?php echo $lang_code; ?>" <?php echo ($data['langue_secondaire'] == $lang_code) ? 'selected' : ''; ?>>
                                     <?php echo $tr(strtolower($lang_code)); ?> (<?php echo strtoupper($lang_code); ?>)
                                     <?php if (!in_array($lang_code, $data['langues_actives_array'])): ?>
                                        (<?php echo $tr('not_currently_active'); ?>)
                                     <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($data['errors']['langue_secondaire'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['errors']['langue_secondaire']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="mode_affichage_documents" class="form-label"><?php echo $tr('document_display_mode'); ?> <span class="text-danger">*</span></label>
                        <select name="mode_affichage_documents" id="mode_affichage_documents" class="form-select <?php echo (!empty($data['errors']['mode_affichage_documents'])) ? 'is-invalid' : ''; ?>" required>
                            <option value="unilingue" <?php echo ($data['mode_affichage_documents'] == 'unilingue') ? 'selected' : ''; ?>><?php echo $tr('unilingual_main_lang'); ?></option>
                            <option value="bilingue" <?php echo ($data['mode_affichage_documents'] == 'bilingue') ? 'selected' : ''; ?>><?php echo $tr('bilingual_main_secondary'); ?></option>
                        </select>
                        <?php if (!empty($data['errors']['mode_affichage_documents'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['errors']['mode_affichage_documents']); ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted"><?php echo $tr('bilingual_requires_secondary_lang_notice'); ?></small>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-lg btn-success">
                    <i class="fas fa-save"></i> <?php echo $tr('save_language_settings'); ?>
                </button>
            </div>

        </div>
    </div>
</form>
