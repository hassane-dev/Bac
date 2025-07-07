<?php
// app/views/series/index.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('series_list')); ?></h2>

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
        <a href="<?php echo $app_url; ?>/series/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_serie'); // 'Ajouter une nouvelle série' ?>
        </a>
    </p>

    <?php if (empty($series)): ?>
        <p><?php echo $tr('no_series_found'); // 'Aucune série trouvée.' ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('series_code'); // 'Code Série' ?></th>
                    <th><?php echo $tr('series_label'); // 'Libellé Série' ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($series as $serie_item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($serie_item->id); ?></td>
                        <td><?php echo htmlspecialchars($serie_item->code); ?></td>
                        <td><?php echo htmlspecialchars($serie_item->libelle); ?></td>
                        <td>
                            <a href="<?php echo $app_url; ?>/series/edit/<?php echo $serie_item->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <form action="<?php echo $app_url; ?>/series/delete/<?php echo $serie_item->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_serie'); ?>');">
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
// Traductions à ajouter:
// fr.php:
// 'series_list' => 'Liste des Séries',
// 'add_new_serie' => 'Ajouter une nouvelle Série',
// 'no_series_found' => 'Aucune série trouvée.',
// 'series_code' => 'Code Série',
// 'series_label' => 'Libellé Série',
// 'are_you_sure_delete_serie' => 'Êtes-vous sûr de vouloir supprimer cette série ?',
// 'serie_added_successfully' => 'Série ajoutée avec succès.',
// 'error_adding_serie' => "Erreur lors de l'ajout de la série.",
// 'serie_updated_successfully' => 'Série mise à jour avec succès.',
// 'error_updating_serie' => 'Erreur lors de la mise à jour de la série.',
// 'serie_deleted_successfully' => 'Série supprimée avec succès.',
// 'error_deleting_serie' => 'Erreur lors de la suppression de la série.',
// 'serie_not_found' => 'Série non trouvée.',
// 'series_code_required' => 'Le code de la série est requis.',
// 'series_code_taken' => 'Ce code de série est déjà utilisé.',
// 'series_label_required' => 'Le libellé de la série est requis.',
// 'edit_serie' => 'Modifier la Série',
// 'add_serie' => 'Ajouter une Série',
// 'back_to_dashboard' => 'Retour au tableau de bord',

// ar.php: ...
?>
