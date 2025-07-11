<?php
// Data est fourni par AccreditationsController::edit()
// $data['page_title']
// $data['id']
// $data['libelle_action']
// $data['libelle_action_err']

// Le layout main.php sera utilisé implicitement.
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
            </div>
            <div class="card-body">
                <form action="<?php echo APP_URL . '/accreditations/edit/' . $data['id']; ?>" method="POST">
                    <div class="mb-3">
                        <label for="libelle_action" class="form-label"><?php echo $tr('accreditation_label'); ?> <span class="text-danger">*</span></label>
                        <input type="text"
                               name="libelle_action"
                               id="libelle_action"
                               class="form-control <?php echo (!empty($data['libelle_action_err'])) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($data['libelle_action']); ?>"
                               required>
                        <?php if (!empty($data['libelle_action_err'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['libelle_action_err']); ?></div>
                        <?php endif; ?>
                    </div>

                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL . '/accreditations/index'; ?>" class="btn btn-secondary">
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
