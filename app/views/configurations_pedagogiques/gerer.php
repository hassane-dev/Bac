<?php
// app/views/configurations_pedagogiques/gerer.php
// $title, $annee_scolaire_id, $annee_scolaire_libelle, et tous les champs de config + erreurs sont passés
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? ''); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/configurationspedagogiques/save" method="POST">
        <input type="hidden" name="annee_scolaire_id" value="<?php echo htmlspecialchars($annee_scolaire_id); ?>">

        <div class="card mb-3">
            <div class="card-header">
                <?php echo $tr('academic_year'); ?>: <strong><?php echo htmlspecialchars($annee_scolaire_libelle); ?></strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="seuil_admission" class="form-label"><?php echo $tr('admission_threshold'); ?> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control <?php echo (!empty($seuil_admission_err)) ? 'is-invalid' : ''; ?>" id="seuil_admission" name="seuil_admission" value="<?php echo htmlspecialchars($seuil_admission ?? '10.00'); ?>" required>
                        <div class="invalid-feedback"><?php echo $seuil_admission_err ?? ''; ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="seuil_second_tour" class="form-label"><?php echo $tr('second_round_threshold'); ?> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control <?php echo (!empty($seuil_second_tour_err)) ? 'is-invalid' : ''; ?>" id="seuil_second_tour" name="seuil_second_tour" value="<?php echo htmlspecialchars($seuil_second_tour ?? '9.50'); ?>" required>
                        <div class="invalid-feedback"><?php echo $seuil_second_tour_err ?? ''; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <?php echo $tr('mentions_config'); // Configuration des Mentions ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="mention_passable" class="form-label"><?php echo $tr('mention_passable_long'); // Mention Passable (à partir de) ?> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control <?php echo (!empty($mention_passable_err)) ? 'is-invalid' : ''; ?>" id="mention_passable" name="mention_passable" value="<?php echo htmlspecialchars($mention_passable ?? '10.00'); ?>" required>
                        <div class="invalid-feedback"><?php echo $mention_passable_err ?? ''; ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="mention_AB" class="form-label"><?php echo $tr('mention_AB_long'); // Mention Assez Bien (à partir de) ?> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control <?php echo (!empty($mention_AB_err)) ? 'is-invalid' : ''; ?>" id="mention_AB" name="mention_AB" value="<?php echo htmlspecialchars($mention_AB ?? '12.00'); ?>" required>
                        <div class="invalid-feedback"><?php echo $mention_AB_err ?? ''; ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="mention_bien" class="form-label"><?php echo $tr('mention_B_long'); // Mention Bien (à partir de) ?> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control <?php echo (!empty($mention_bien_err)) ? 'is-invalid' : ''; ?>" id="mention_bien" name="mention_bien" value="<?php echo htmlspecialchars($mention_bien ?? '14.00'); ?>" required>
                        <div class="invalid-feedback"><?php echo $mention_bien_err ?? ''; ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="mention_TB" class="form-label"><?php echo $tr('mention_TB_long'); // Mention Très Bien (à partir de) ?> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control <?php echo (!empty($mention_TB_err)) ? 'is-invalid' : ''; ?>" id="mention_TB" name="mention_TB" value="<?php echo htmlspecialchars($mention_TB ?? '16.00'); ?>" required>
                        <div class="invalid-feedback"><?php echo $mention_TB_err ?? ''; ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="mention_exc" class="form-label"><?php echo $tr('mention_Exc_long'); // Mention Excellent (à partir de) ?> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control <?php echo (!empty($mention_exc_err)) ? 'is-invalid' : ''; ?>" id="mention_exc" name="mention_exc" value="<?php echo htmlspecialchars($mention_exc ?? '18.00'); ?>" required>
                        <div class="invalid-feedback"><?php echo $mention_exc_err ?? ''; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_configuration'); // Enregistrer la Configuration ?>
        </button>
        <a href="<?php echo $app_url; ?>/configurationspedagogiques" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>

<?php
// Traductions
// fr.php:
// 'mentions_config' => 'Configuration des Mentions',
// 'mention_passable_long' => 'Mention Passable (à partir de)',
// 'mention_AB_long' => 'Mention Assez Bien (à partir de)',
// 'mention_B_long' => 'Mention Bien (à partir de)',
// 'mention_TB_long' => 'Mention Très Bien (à partir de)',
// 'mention_Exc_long' => 'Mention Excellent (à partir de)',
// 'save_configuration' => 'Enregistrer la Configuration',

// ar.php: ...
?>
