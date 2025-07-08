<?php
// app/views/centres/create.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('add_centre')); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/centres/store" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nom_centre" class="form-label"><?php echo $tr('centre_name'); ?> <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php echo (!empty($nom_centre_err)) ? 'is-invalid' : ''; ?>" id="nom_centre" name="nom_centre" value="<?php echo htmlspecialchars($nom_centre ?? ''); ?>" required maxlength="100">
                <div class="invalid-feedback"><?php echo $nom_centre_err ?? ''; ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="code_centre" class="form-label"><?php echo $tr('centre_code'); // Code Centre ?></label>
                <input type="text" class="form-control <?php echo (!empty($code_centre_err)) ? 'is-invalid' : ''; ?>" id="code_centre" name="code_centre" value="<?php echo htmlspecialchars($code_centre ?? ''); ?>" maxlength="20">
                <div class="invalid-feedback"><?php echo $code_centre_err ?? ''; ?></div>
                 <small class="form-text text-muted"><?php echo $tr('centre_code_desc'); // Ex: NDJ, ABE, etc. Pour le matricule. ?></small>
            </div>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label"><?php echo $tr('description'); ?></label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/centres" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>
