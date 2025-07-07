<?php
// app/views/lycees/index.php
?>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('lycees_list')); ?></h2>

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
        <a href="<?php echo $app_url; ?>/lycees/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_lycee'); ?>
        </a>
    </p>

    <?php if (empty($lycees)): ?>
        <p><?php echo $tr('no_lycees_found'); ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('lycee_name'); // Nom du Lycée ?></th>
                    <th><?php echo $tr('description'); // Description (Optionnel) ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lycees as $lycee): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($lycee->id); ?></td>
                        <td><?php echo htmlspecialchars($lycee->nom_lycee); ?></td>
                        <td><?php echo nl2br(htmlspecialchars(substr($lycee->description ?? '', 0, 100))); echo (strlen($lycee->description ?? '') > 100 ? '...' : ''); ?></td>
                        <td>
                            <a href="<?php echo $app_url; ?>/lycees/edit/<?php echo $lycee->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <form action="<?php echo $app_url; ?>/lycees/delete/<?php echo $lycee->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_lycee'); ?>');">
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
// Traductions à ajouter/vérifier:
// fr.php:
// 'lycees_list' => 'Liste des Lycées',
// 'add_new_lycee' => 'Ajouter un nouveau Lycée',
// 'no_lycees_found' => 'Aucun lycée trouvé.',
// 'lycee_name' => 'Nom du Lycée',
// 'description' => 'Description',
// 'are_you_sure_delete_lycee' => 'Êtes-vous sûr de vouloir supprimer ce lycée ?',
// 'lycee_added_successfully' => 'Lycée ajouté avec succès.',
// 'error_adding_lycee' => "Erreur lors de l'ajout du lycée.",
// 'lycee_updated_successfully' => 'Lycée mis à jour avec succès.',
// 'error_updating_lycee' => 'Erreur lors de la mise à jour du lycée.',
// 'lycee_deleted_successfully' => 'Lycée supprimé avec succès.',
// 'error_deleting_lycee' => 'Erreur lors de la suppression du lycée.',
// 'lycee_not_found' => 'Lycée non trouvé.',
// 'lycee_name_required' => 'Le nom du lycée est requis.',
// 'lycee_name_taken' => 'Ce nom de lycée est déjà utilisé.',
// 'edit_lycee' => 'Modifier le Lycée',
// 'add_lycee' => 'Ajouter un Lycée',
?>
