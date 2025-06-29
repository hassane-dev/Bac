<?php
// app/views/roles/create.php
// $title est passé par le contrôleur
// $tr est la fonction de traduction
// $data['nom_role_err'] peut exister si validation échoue
// $data['nom_role'] pour la valeur précédente en cas d'erreur
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title); ?></h2>

    <form action="<?php echo $app_url; ?>/roles/store" method="POST">
        <div class="mb-3">
            <label for="nom_role" class="form-label"><?php echo $tr('role_name'); // 'Nom du Rôle' ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($nom_role_err)) ? 'is-invalid' : ''; ?>" id="nom_role" name="nom_role" value="<?php echo htmlspecialchars($nom_role ?? ''); ?>" required>
            <?php if (!empty($nom_role_err)): ?>
                <div class="invalid-feedback"><?php echo $nom_role_err; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save'); // 'Enregistrer' ?>
        </button>
        <a href="<?php echo $app_url; ?>/roles" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); // 'Annuler' ?>
        </a>
    </form>
</div>

<?php
// Mettre à jour les traductions nécessaires:
// fr.php:
// 'save' => 'Enregistrer',
// ar.php:
// 'save' => 'حفظ',
?>
