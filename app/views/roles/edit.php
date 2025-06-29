<?php
// app/views/roles/edit.php
// $title est passé par le contrôleur
// $tr est la fonction de traduction
// $id, $nom_role sont passés dans $data
// $nom_role_err peut exister si validation échoue
// Pour l'étape 3, $all_accreditations et $role_accreditation_ids seront aussi passés
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title); ?> : <?php echo htmlspecialchars($nom_role ?? ''); ?></h2>

    <form action="<?php echo $app_url; ?>/roles/update/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST">
        <div class="mb-3">
            <label for="nom_role" class="form-label"><?php echo $tr('role_name'); // 'Nom du Rôle' ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($nom_role_err)) ? 'is-invalid' : ''; ?>" id="nom_role" name="nom_role" value="<?php echo htmlspecialchars($nom_role ?? ''); ?>" required <?php echo ($id == 1) ? 'readonly' : ''; // Rendre le nom du rôle Admin non modifiable ?>>
            <?php if (!empty($nom_role_err)): ?>
                <div class="invalid-feedback"><?php echo $nom_role_err; ?></div>
            <?php endif; ?>
        </div>

        <!-- Section pour les Accréditations -->
        <div class="mb-3">
            <h4><?php echo $tr('assign_accreditations'); // 'Assigner les Accréditations' ?></h4>
            <?php if (empty($all_accreditations)): ?>
                <p><?php echo $tr('no_accreditations_available'); // 'Aucune accréditation disponible.' ?></p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($all_accreditations as $accreditation): ?>
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="accreditations[]" value="<?php echo htmlspecialchars($accreditation->id); ?>" id="accreditation_<?php echo htmlspecialchars($accreditation->id); ?>"
                                    <?php echo (isset($role_accreditation_ids) && is_array($role_accreditation_ids) && in_array($accreditation->id, $role_accreditation_ids)) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="accreditation_<?php echo htmlspecialchars($accreditation->id); ?>">
                                    <?php echo htmlspecialchars($accreditation->libelle_action); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_changes'); // 'Enregistrer les modifications' ?>
        </button>
        <a href="<?php echo $app_url; ?>/roles" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); // 'Annuler' ?>
        </a>
    </form>
</div>

<?php
// Mettre à jour les traductions nécessaires:
// fr.php:
// 'assign_accreditations' => 'Assigner les Accréditations',
// 'no_accreditations_available' => 'Aucune accréditation disponible.',
// ar.php:
// 'assign_accreditations' => 'تعيين الاعتمادات',
// 'no_accreditations_available' => 'لا توجد اعتمادات متاحة.',
?>
