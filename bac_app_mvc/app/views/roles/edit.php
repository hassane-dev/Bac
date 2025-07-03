<?php
// app/views/roles/edit.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('edit_role')); ?>: <?php echo htmlspecialchars($nom_role ?? ''); ?></h2>

     <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/roles/update/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST">
        <div class="mb-3">
            <label for="nom_role" class="form-label"><?php echo $tr('role_name'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($nom_role_err)) ? 'is-invalid' : ''; ?>"
                   id="nom_role" name="nom_role" value="<?php echo htmlspecialchars($nom_role ?? ''); ?>" required
                   <?php echo (isset($id) && $id == 1) ? 'readonly' : ''; // Rendre le nom du rôle Admin non modifiable ?> >
            <?php if (!empty($nom_role_err)): ?>
                <div class="invalid-feedback"><?php echo $nom_role_err; ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <h4><?php echo $tr('assign_accreditations'); ?></h4>
            <?php if (empty($all_accreditations)): ?>
                <p><?php echo $tr('no_accreditations_available'); ?></p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($all_accreditations as $accreditation): ?>
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="accreditations[]"
                                       value="<?php echo htmlspecialchars($accreditation->id); ?>"
                                       id="accreditation_<?php echo htmlspecialchars($accreditation->id); ?>"
                                    <?php echo (isset($role_accreditation_ids) && is_array($role_accreditation_ids) && in_array($accreditation->id, $role_accreditation_ids)) ? 'checked' : ''; ?>
                                    <?php echo (isset($id) && $id == 1) ? 'disabled' : ''; // Désactiver pour le rôle Admin ?>
                                >
                                <label class="form-check-label" for="accreditation_<?php echo htmlspecialchars($accreditation->id); ?>">
                                    <?php echo htmlspecialchars($accreditation->libelle_action); ?>
                                </label>
                                <?php if (isset($id) && $id == 1 && isset($role_accreditation_ids) && is_array($role_accreditation_ids) && in_array($accreditation->id, $role_accreditation_ids)): ?>
                                    <!-- Si admin et l'accréditation est cochée, envoyer sa valeur via un champ caché car les champs désactivés ne sont pas soumis -->
                                    <input type="hidden" name="accreditations[]" value="<?php echo htmlspecialchars($accreditation->id); ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
             <?php if (isset($id) && $id == 1): ?>
                <p class="text-muted"><small><?php echo $tr('admin_role_all_permissions_note'); // Le rôle Administrateur a toutes les permissions par défaut et ne peut être modifié ici. ?></small></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_changes'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/roles" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>
