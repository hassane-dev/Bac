<?php
// Data: $data['page_title'], $data['annee_scolaire_id'], $data['annee_scolaire_libelle']
//       $data['seuil_admission'], $data['seuil_second_tour'], ... (toutes les mentions)
//       $data['errors'] (array of errors)
// Helpers: $tr, $app_url
?>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
            </div>
            <div class="card-body">
                <form action="<?php echo APP_URL . '/configurations_pedagogiques/edit/' . $data['annee_scolaire_id']; ?>" method="POST">
                    <input type="hidden" name="annee_scolaire_id" value="<?php echo $data['annee_scolaire_id']; ?>">

                    <h5 class="text-primary"><?php echo $tr('general_thresholds'); ?></h5> <?php // Ajouter 'general_thresholds' ?>
                    <hr class="mt-0">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="seuil_admission" class="form-label"><?php echo $tr('admission_threshold'); ?> <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="20" name="seuil_admission" id="seuil_admission"
                                   class="form-control <?php echo (!empty($data['errors']['seuil_admission'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars(number_format($data['seuil_admission'], 2)); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['errors']['seuil_admission'] ?? ''); ?></div>
                        </div>
                        <div class="col-md-6">
                            <label for="seuil_second_tour" class="form-label"><?php echo $tr('second_round_threshold'); ?> <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="20" name="seuil_second_tour" id="seuil_second_tour"
                                   class="form-control <?php echo (!empty($data['errors']['seuil_second_tour'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars(number_format($data['seuil_second_tour'], 2)); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['errors']['seuil_second_tour'] ?? ''); ?></div>
                        </div>
                    </div>

                    <h5 class="mt-4 text-primary"><?php echo $tr('mentions_config'); ?></h5>
                    <hr class="mt-0">
                    <p class="text-muted"><small><?php echo $tr('mentions_must_be_increasing'); ?></small></p>

                    <?php
                    $mentions_fields = [
                        'mention_passable' => $tr('mention_passable_long'),
                        'mention_AB' => $tr('mention_AB_long'),
                        'mention_bien' => $tr('mention_B_long'),
                        'mention_TB' => $tr('mention_TB_long'),
                        'mention_exc' => $tr('mention_Exc_long'),
                    ];
                    ?>

                    <?php foreach ($mentions_fields as $field_name => $label): ?>
                    <div class="mb-3">
                        <label for="<?php echo $field_name; ?>" class="form-label"><?php echo $label; ?> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="20" name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>"
                               class="form-control <?php echo (!empty($data['errors'][$field_name])) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars(number_format($data[$field_name], 2)); ?>" required>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($data['errors'][$field_name] ?? ''); ?></div>
                    </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo APP_URL . '/configurations_pedagogiques/index'; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?php echo $tr('back_to_configs_list'); ?>
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> <?php echo $tr('save_configuration'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
