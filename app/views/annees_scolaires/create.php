<?php
// app/views/annees_scolaires/create.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('add_academic_year')); ?></h2>

    <form action="<?php echo $app_url; ?>/anneesscolaires/store" method="POST">
        <div class="mb-3">
            <label for="libelle" class="form-label"><?php echo $tr('label'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($libelle_err)) ? 'is-invalid' : ''; ?>" id="libelle" name="libelle" value="<?php echo htmlspecialchars($libelle ?? ''); ?>" required placeholder="Ex: 2023-2024">
            <div class="invalid-feedback"><?php echo $libelle_err ?? ''; ?></div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="date_debut" class="form-label"><?php echo $tr('start_date'); ?></label>
                <input type="date" class="form-control <?php echo (!empty($date_debut_err)) ? 'is-invalid' : ''; ?>" id="date_debut" name="date_debut" value="<?php echo htmlspecialchars($date_debut ?? ''); ?>">
                <div class="invalid-feedback"><?php echo $date_debut_err ?? ''; ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="date_fin" class="form-label"><?php echo $tr('end_date'); ?></label>
                <input type="date" class="form-control <?php echo (!empty($date_fin_err)) ? 'is-invalid' : ''; ?>" id="date_fin" name="date_fin" value="<?php echo htmlspecialchars($date_fin ?? ''); ?>">
                <div class="invalid-feedback"><?php echo $date_fin_err ?? ''; ?></div>
            </div>
        </div>

        <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="est_active" name="est_active" value="1" <?php echo (isset($est_active) && $est_active) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="est_active"><?php echo $tr('set_as_active_year'); // 'Définir comme année scolaire active' ?></label>
            <small class="form-text text-muted"><?php echo $tr('set_active_year_notice'); // 'Cocher cette case désactivera toute autre année précédemment active.' ?></small>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/anneesscolaires" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>

<?php
// Traductions
// fr.php:
// 'set_as_active_year' => 'Définir comme année scolaire active',
// 'set_active_year_notice' => 'Cocher cette case désactivera toute autre année précédemment active.',
// ar.php
// 'set_as_active_year' => 'تعيين كالسنة الدراسية النشطة',
// 'set_active_year_notice' => 'تحديد هذا المربع سيؤدي إلى إلغاء تنشيط أي سنة أخرى كانت نشطة سابقًا.',
?>
