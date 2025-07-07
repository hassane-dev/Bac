<?php
// app/views/centres/index.php
?>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('centres_list')); ?></h2>

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
        <a href="<?php echo $app_url; ?>/centres/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_centre'); ?>
        </a>
    </p>

    <?php if (empty($centres)): ?>
        <p><?php echo $tr('no_centres_found'); ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('centre_name'); // Nom du Centre ?></th>
                    <th><?php echo $tr('description'); ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($centres as $centre): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($centre->id); ?></td>
                        <td><?php echo htmlspecialchars($centre->nom_centre); ?></td>
                        <td><?php echo nl2br(htmlspecialchars(substr($centre->description ?? '', 0, 100))); echo (strlen($centre->description ?? '') > 100 ? '...' : ''); ?></td>
                        <td>
                            <a href="<?php echo $app_url; ?>/centres/edit/<?php echo $centre->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <form action="<?php echo $app_url; ?>/centres/delete/<?php echo $centre->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_centre'); ?>');">
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
// fr.php:
// 'centres_list' => 'Liste des Centres d\'Examen',
// 'add_new_centre' => 'Ajouter un nouveau Centre',
// 'no_centres_found' => 'Aucun centre d\'examen trouvé.',
// 'centre_name' => 'Nom du Centre',
// 'are_you_sure_delete_centre' => 'Êtes-vous sûr de vouloir supprimer ce centre ?',
// 'centre_added_successfully' => 'Centre ajouté avec succès.',
// 'error_adding_centre' => "Erreur lors de l'ajout du centre.",
// 'centre_updated_successfully' => 'Centre mis à jour avec succès.',
// 'error_updating_centre' => 'Erreur lors de la mise à jour du centre.',
// 'centre_deleted_successfully' => 'Centre supprimé avec succès.',
// 'error_deleting_centre' => 'Erreur lors de la suppression du centre. Vérifiez qu\'il n\'est pas utilisé.',
// 'centre_not_found' => 'Centre non trouvé.',
// 'centre_name_required' => 'Le nom du centre est requis.',
// 'centre_name_taken' => 'Ce nom de centre est déjà utilisé.',
// 'edit_centre' => 'Modifier le Centre',
// 'add_centre' => 'Ajouter un Centre',
?>
