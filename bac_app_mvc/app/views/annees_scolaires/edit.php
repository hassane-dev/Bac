<?php
// Data: $data['page_title'], $data['id'], $data['libelle'], $data['date_debut'], $data['date_fin'],
//       $data['est_active'] (valeur actuelle pour la logique du formulaire),
//       $data['libelle_err'], $data['date_debut_err'], $data['date_fin_err']
// Helpers: $tr, $app_url
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
            </div>
            <div class="card-body">
                <form action="<?php echo APP_URL . '/annees_scolaires/edit/' . $data['id']; ?>" method="POST">
                    <div class="mb-3">
                        <label for="libelle" class="form-label"><?php echo $tr('label'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" id="libelle"
                               class="form-control <?php echo (!empty($data['libelle_err'])) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($data['libelle']); ?>"
                               placeholder="Ex: 2023-2024" required>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($data['libelle_err']); ?></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_debut" class="form-label"><?php echo $tr('start_date'); ?> <span class="text-danger">*</span></label>
                            <input type="date" name="date_debut" id="date_debut"
                                   class="form-control <?php echo (!empty($data['date_debut_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['date_debut']); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['date_debut_err']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_fin" class="form-label"><?php echo $tr('end_date'); ?> <span class="text-danger">*</span></label>
                            <input type="date" name="date_fin" id="date_fin"
                                   class="form-control <?php echo (!empty($data['date_fin_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['date_fin']); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['date_fin_err']); ?></div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="est_active" id="est_active" value="1"
                               <?php echo ($data['est_active']) ? 'checked' : '';
                               ?>
                        >
                        <label class="form-check-label" for="est_active">
                            <?php echo $tr('set_as_active_year'); ?>
                        </label>
                        <small class="form-text text-muted d-block">
                            <?php
                                // Utiliser $data['est_active'] qui reflète l'état actuel de l'entité chargée
                                if ($data['est_active']) {
                                    echo $tr('uncheck_not_supported_activate_another');
                                } else {
                                    echo $tr('set_active_year_notice');
                                }
                            ?>
                        </small>
                         <?php if ($data['est_active']): ?>
                            <!-- Si l'année est déjà active, et que la checkbox est désactivée (non fait ici) ou pour s'assurer que la valeur est postée -->
                            <!-- Le contrôleur doit gérer la logique de ne pas désactiver si c'est la seule active. -->
                            <!-- <input type="hidden" name="est_active_current_value" value="1" /> -->
                        <?php endif; ?>
                    </div>

                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo APP_URL . '/annees_scolaires/index'; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?php echo $tr('cancel'); ?>
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> <?php echo $tr('save_changes'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
