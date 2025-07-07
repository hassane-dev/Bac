<?php
// app/views/series/create.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('add_serie')); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/series/store" method="POST">
        <div class="mb-3">
            <label for="code" class="form-label"><?php echo $tr('series_code'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($code_err)) ? 'is-invalid' : ''; ?>" id="code" name="code" value="<?php echo htmlspecialchars($code ?? ''); ?>" required maxlength="10">
            <div class="invalid-feedback"><?php echo $code_err ?? ''; ?></div>
        </div>
        <div class="mb-3">
            <label for="libelle" class="form-label"><?php echo $tr('series_label'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($libelle_err)) ? 'is-invalid' : ''; ?>" id="libelle" name="libelle" value="<?php echo htmlspecialchars($libelle ?? ''); ?>" required maxlength="100">
            <div class="invalid-feedback"><?php echo $libelle_err ?? ''; ?></div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/series" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>
