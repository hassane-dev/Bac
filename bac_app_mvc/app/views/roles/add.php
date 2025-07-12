<?php
// Data: $data['page_title'], $data['nom_role'], $data['assigned_accreditations'] (array of ids),
//       $data['all_accreditations'] (array of accreditation objects), $data['nom_role_err']
// Helpers: $tr, $app_url
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
            </div>
            <div class="card-body">
                <form action="<?php echo APP_URL . '/roles/add'; ?>" method="POST">
                    <div class="mb-3">
                        <label for="nom_role" class="form-label"><?php echo $tr('role_name'); ?> <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nom_role"
                               id="nom_role"
                               class="form-control <?php echo (!empty($data['nom_role_err'])) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($data['nom_role']); ?>"
                               required>
                        <?php if (!empty($data['nom_role_err'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['nom_role_err']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <h5><?php echo $tr('assign_accreditations'); ?></h5>
                        <?php if (empty($data['all_accreditations'])): ?>
                            <p><?php echo $tr('no_accreditations_available'); ?></p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($data['all_accreditations'] as $accreditation): ?>
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="accreditations[]"
                                                   value="<?php echo $accreditation->id; ?>"
                                                   id="accreditation_<?php echo $accreditation->id; ?>"
                                                   <?php echo in_array($accreditation->id, $data['assigned_accreditations'] ?? []) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="accreditation_<?php echo $accreditation->id; ?>">
                                                <?php echo htmlspecialchars($accreditation->libelle_action); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo APP_URL . '/roles/index'; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?php echo $tr('cancel'); ?>
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> <?php echo $tr('add_role'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
