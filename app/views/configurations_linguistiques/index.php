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
                <h4><?php echo $tr('active_languages_interface_docs'); // Langues Actives (Interface et Documents) ?></h4>
            </div>
            <div class="card-body">
                <div class="mb-3 <?php echo (!empty($langues_actives_err)) ? 'is-invalid' : ''; ?>">
                    <?php if (empty($available_languages)): ?>
                        <p><?php echo $tr('no_languages_found_in_lang_dir'); // Aucun fichier de langue trouvé dans /lang ?></p>
                    <?php else: ?>
                        <?php foreach ($available_languages as $code => $name): ?>
                            <div class="form-check form-check-inline form-switch">
                                <input class="form-check-input" type="checkbox" name="langues_actives[]"
                                       value="<?php echo htmlspecialchars($code); ?>"
                                       id="lang_<?php echo htmlspecialchars($code); ?>"
                                       <?php echo in_array($code, $langues_actives_array ?? []) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="lang_<?php echo htmlspecialchars($code); ?>">
                                    <?php echo htmlspecialchars($name); ?>
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
                <h4><?php echo $tr('default_document_languages'); // Langues par défaut et pour Documents ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="langue_principale" class="form-label"><?php echo $tr('main_language'); // Langue Principale ?> <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo (!empty($langue_principale_err)) ? 'is-invalid' : ''; ?>" id="langue_principale" name="langue_principale" required>
                            <option value=""><?php echo $tr('select_main_language'); // Sélectionner... ?></option>
                            <?php foreach ($available_languages as $code => $name): ?>
                                <?php if (in_array($code, $langues_actives_array ?? [])): // Seulement les langues actives ?>
                                <option value="<?php echo htmlspecialchars($code); ?>" <?php echo (isset($langue_principale) && $langue_principale == $code) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?php echo $langue_principale_err ?? ''; ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="langue_secondaire" class="form-label"><?php echo $tr('secondary_language'); // Langue Secondaire (Optionnelle) ?></label>
                        <select class="form-select <?php echo (!empty($langue_secondaire_err)) ? 'is-invalid' : ''; ?>" id="langue_secondaire" name="langue_secondaire">
                            <option value=""><?php echo $tr('select_secondary_language_optional'); // Sélectionner... (Optionnel) ?></option>
                             <?php foreach ($available_languages as $code => $name): ?>
                                <?php if (in_array($code, $langues_actives_array ?? [])): // Seulement les langues actives ?>
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
                    <label for="mode_affichage_documents" class="form-label"><?php echo $tr('document_display_mode'); // Mode d'affichage des documents ?> <span class="text-danger">*</span></label>
                    <select class="form-select <?php echo (!empty($mode_affichage_documents_err)) ? 'is-invalid' : ''; ?>" id="mode_affichage_documents" name="mode_affichage_documents" required>
                        <option value="unilingue" <?php echo (isset($mode_affichage_documents) && $mode_affichage_documents == 'unilingue') ? 'selected' : ''; ?>><?php echo $tr('unilingual_main_lang'); // Unilingue (Langue Principale) ?></option>
                        <option value="bilingue" <?php echo (isset($mode_affichage_documents) && $mode_affichage_documents == 'bilingue') ? 'selected' : ''; ?>><?php echo $tr('bilingual_main_secondary'); // Bilingue (Principale + Secondaire) ?></option>
                    </select>
                     <div class="invalid-feedback"><?php echo $mode_affichage_documents_err ?? ''; ?></div>
                    <small class="form-text text-muted"><?php echo $tr('bilingual_requires_secondary_lang_notice'); // Le mode bilingue nécessite la sélection d'une langue secondaire. ?></small>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_language_settings'); // Enregistrer les Paramètres Linguistiques ?>
        </button>
    </form>
</div>

<?php
// Traductions
// fr.php:
// 'language_settings' => 'Paramètres Linguistiques', 'active_languages_interface_docs' => 'Langues Actives (Interface et Documents)', 'no_languages_found_in_lang_dir' => 'Aucun fichier de langue trouvé dans le dossier /lang.', 'default_document_languages' => 'Langues par défaut et pour Documents', 'main_language' => 'Langue Principale', 'select_main_language' => 'Sélectionner la langue principale...', 'secondary_language' => 'Langue Secondaire (Optionnelle)', 'select_secondary_language_optional' => 'Sélectionner la langue secondaire... (Optionnel)', 'document_display_mode' => "Mode d'affichage des documents", 'unilingual_main_lang' => 'Unilingue (Langue Principale)', 'bilingual_main_secondary' => 'Bilingue (Principale + Secondaire)', 'bilingual_requires_secondary_lang_notice' => 'Le mode bilingue nécessite la sélection d\'une langue secondaire.', 'save_language_settings' => 'Enregistrer les Paramètres Linguistiques', 'language_settings_updated_successfully' => 'Paramètres linguistiques mis à jour avec succès.', 'error_updating_language_settings' => 'Erreur lors de la mise à jour des paramètres linguistiques.', 'at_least_one_language_active' => 'Au moins une langue doit être active.', 'main_language_required' => 'La langue principale est requise.', 'main_language_must_be_active' => 'La langue principale doit être une langue active.', 'secondary_language_must_be_active' => 'La langue secondaire doit être une langue active si sélectionnée.', 'secondary_language_cannot_be_same_as_main' => 'La langue secondaire ne peut pas être identique à la langue principale.', 'invalid_document_display_mode' => "Mode d'affichage des documents invalide.", 'secondary_language_required_for_bilingual' => 'Une langue secondaire est requise pour le mode bilingue.'
// 'language_name_native' => 'Français (Natif)', // Exemple pour fr.php
// 'language_name_in_french' => 'Français', // Exemple pour fr.php
// 'language_name_native' => 'العربية', // Exemple pour ar.php
// 'language_name_in_french' => 'Arabe', // Exemple pour ar.php

// ar.php: ... (traductions similaires)
?>
