<?php
// app/views/accreditations/edit.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('edit_accreditation')); ?> : <?php echo htmlspecialchars($libelle_action ?? ''); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/accreditations/update/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST">
        <div class="mb-3">
            <label for="libelle_action" class="form-label"><?php echo $tr('accreditation_label'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($libelle_action_err)) ? 'is-invalid' : ''; ?>" id="libelle_action" name="libelle_action" value="<?php echo htmlspecialchars($libelle_action ?? ''); ?>" required>
            <?php if (!empty($libelle_action_err)): ?>
                <div class="invalid-feedback"><?php echo $libelle_action_err; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_changes'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/accreditations" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>
