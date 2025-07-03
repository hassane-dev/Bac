<?php
// app/views/roles/create.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('add_role')); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/roles/store" method="POST">
        <div class="mb-3">
            <label for="nom_role" class="form-label"><?php echo $tr('role_name'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($nom_role_err)) ? 'is-invalid' : ''; ?>" id="nom_role" name="nom_role" value="<?php echo htmlspecialchars($nom_role ?? ''); ?>" required>
            <?php if (!empty($nom_role_err)): ?>
                <div class="invalid-feedback"><?php echo $nom_role_err; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/roles" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>
