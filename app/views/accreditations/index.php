<?php
// app/views/accreditations/index.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('accreditation_list')); ?></h2>

    <?php if (!empty($_SESSION['message'])) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <p>
        <a href="<?php echo $app_url; ?>/accreditations/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_accreditation'); // 'Ajouter une nouvelle accréditation' ?>
        </a>
    </p>

    <?php if (empty($accreditations)): ?>
        <p><?php echo $tr('no_accreditations_found'); // 'Aucune accréditation trouvée.' ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('accreditation_label'); // 'Libellé de l'action' ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accreditations as $accreditation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($accreditation->id); ?></td>
                        <td><?php echo htmlspecialchars($accreditation->libelle_action); ?></td>
                        <td>
                            <a href="<?php echo $app_url; ?>/accreditations/edit/<?php echo $accreditation->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <form action="<?php echo $app_url; ?>/accreditations/delete/<?php echo $accreditation->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_accreditation'); ?>');">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// Traductions à ajouter/vérifier:
// fr.php:
// 'accreditation_list' => 'Liste des Accréditations',
// 'add_new_accreditation' => 'Ajouter une nouvelle accréditation',
// 'no_accreditations_found' => 'Aucune accréditation trouvée.',
// 'accreditation_label' => "Libellé de l'action",
// 'are_you_sure_delete_accreditation' => 'Êtes-vous sûr de vouloir supprimer cette accréditation ?',
// 'accreditation_added_successfully' => 'Accréditation ajoutée avec succès.',
// 'error_adding_accreditation' => "Erreur lors de l'ajout de l'accréditation.",
// 'accreditation_updated_successfully' => 'Accréditation mise à jour avec succès.',
// 'error_updating_accreditation' => "Erreur lors de la mise à jour de l'accréditation.",
// 'accreditation_deleted_successfully' => 'Accréditation supprimée avec succès.',
// 'error_deleting_accreditation' => "Erreur lors de la suppression de l'accréditation.",
// 'accreditation_not_found' => 'Accréditation non trouvée.',
// 'please_enter_accreditation_label' => "Veuillez saisir le libellé de l'accréditation.",
// 'edit_accreditation' => "Modifier l'Accréditation",
// 'add_accreditation' => "Ajouter une Accréditation",


// ar.php:
// 'accreditation_list' => 'قائمة الاعتمادات',
// 'add_new_accreditation' => 'إضافة اعتماد جديد',
// 'no_accreditations_found' => 'لم يتم العثور على اعتمادات.',
// 'accreditation_label' => "تسمية الإجراء",
// 'are_you_sure_delete_accreditation' => 'هل أنت متأكد أنك تريد حذف هذا الاعتماد؟',
// 'accreditation_added_successfully' => 'تمت إضافة الاعتماد بنجاح.',
// 'error_adding_accreditation' => 'خطأ أثناء إضافة الاعتماد.',
// 'accreditation_updated_successfully' => 'تم تحديث الاعتماد بنجاح.',
// 'error_updating_accreditation' => 'خطأ أثناء تحديث الاعتماد.',
// 'accreditation_deleted_successfully' => 'تم حذف الاعتماد بنجاح.',
// 'error_deleting_accreditation' => 'خطأ أثناء حذف الاعتماد.',
// 'accreditation_not_found' => 'الاعتماد غير موجود.',
// 'please_enter_accreditation_label' => 'الرجاء إدخال تسمية الاعتماد.',
// 'edit_accreditation' => "تعديل الاعتماد",
// 'add_accreditation' => "إضافة اعتماد",
?>
