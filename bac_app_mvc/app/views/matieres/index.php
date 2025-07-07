<?php
// app/views/matieres/index.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('subjects_list')); ?></h2>

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
        <a href="<?php echo $app_url; ?>/matieres/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_subject'); // 'Ajouter une nouvelle matière' ?>
        </a>
    </p>

    <?php if (empty($matieres)): ?>
        <p><?php echo $tr('no_subjects_found'); // 'Aucune matière trouvée.' ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('subject_code'); // 'Code Matière' ?></th>
                    <th><?php echo $tr('subject_name'); // 'Nom Matière' ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matieres as $matiere_item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($matiere_item->id); ?></td>
                        <td><?php echo htmlspecialchars($matiere_item->code); ?></td>
                        <td><?php echo htmlspecialchars($matiere_item->nom); ?></td>
                        <td>
                            <a href="<?php echo $app_url; ?>/matieres/edit/<?php echo $matiere_item->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <form action="<?php echo $app_url; ?>/matieres/delete/<?php echo $matiere_item->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_subject'); ?>');">
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
     <a href="<?php echo $app_url; ?>/dashboard" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left"></i> <?php echo $tr('back_to_dashboard'); ?>
    </a>
</div>

<?php
// Traductions
// fr: 'subjects_list' => 'Liste des Matières', 'add_new_subject' => 'Ajouter une nouvelle Matière', 'no_subjects_found' => 'Aucune matière trouvée.', 'subject_code' => 'Code Matière', 'subject_name' => 'Nom Matière', 'are_you_sure_delete_subject' => 'Êtes-vous sûr de vouloir supprimer cette matière ?', 'subject_added_successfully' => 'Matière ajoutée avec succès.', 'error_adding_subject' => "Erreur lors de l'ajout de la matière.", 'subject_updated_successfully' => 'Matière mise à jour avec succès.', 'error_updating_subject' => 'Erreur lors de la mise à jour de la matière.', 'subject_deleted_successfully' => 'Matière supprimée avec succès.', 'error_deleting_subject' => 'Erreur lors de la suppression de la matière.', 'subject_not_found' => 'Matière non trouvée.', 'subject_code_required' => 'Le code de la matière est requis.', 'subject_code_taken' => 'Ce code de matière est déjà utilisé.', 'subject_name_required' => 'Le nom de la matière est requis.', 'edit_subject' => 'Modifier la Matière', 'add_subject' => 'Ajouter une Matière'
// ar: ...
?>
