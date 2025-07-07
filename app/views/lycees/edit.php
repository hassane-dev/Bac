<?php
// app/views/lycees/edit.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('edit_lycee')); ?>: <?php echo htmlspecialchars($nom_lycee ?? ''); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/lycees/update/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST">
        <div class="mb-3">
            <label for="nom_lycee" class="form-label"><?php echo $tr('lycee_name'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($nom_lycee_err)) ? 'is-invalid' : ''; ?>" id="nom_lycee" name="nom_lycee" value="<?php echo htmlspecialchars($nom_lycee ?? ''); ?>" required maxlength="100">
            <div class="invalid-feedback"><?php echo $nom_lycee_err ?? ''; ?></div>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label"><?php echo $tr('description'); ?></label>
            <textarea class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            <div class="invalid-feedback"><?php echo $description_err ?? ''; ?></div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_changes'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/lycees" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>
