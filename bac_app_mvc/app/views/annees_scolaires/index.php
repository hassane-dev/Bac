<?php
// app/views/annees_scolaires/index.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('academic_year_list')); ?></h2>

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
        <a href="<?php echo $app_url; ?>/anneesscolaires/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_academic_year'); ?>
        </a>
    </p>

    <?php if (empty($annees)): ?>
        <p><?php echo $tr('no_academic_years_found'); ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('label'); ?></th>
                    <th><?php echo $tr('start_date'); ?></th>
                    <th><?php echo $tr('end_date'); ?></th>
                    <th><?php echo $tr('active_status'); ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($annees as $annee): ?>
                    <tr class="<?php echo $annee->est_active ? 'table-success fw-bold' : ''; ?>">
                        <td><?php echo htmlspecialchars($annee->id); ?></td>
                        <td><?php echo htmlspecialchars($annee->libelle); ?></td>
                        <td><?php echo htmlspecialchars($annee->date_debut ? date('d/m/Y', strtotime($annee->date_debut)) : $tr('not_defined')); ?></td>
                        <td><?php echo htmlspecialchars($annee->date_fin ? date('d/m/Y', strtotime($annee->date_fin)) : $tr('not_defined')); ?></td>
                        <td>
                            <?php if ($annee->est_active): ?>
                                <span class="badge bg-success"><?php echo $tr('active'); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo $tr('inactive'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $app_url; ?>/anneesscolaires/edit/<?php echo $annee->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <?php if (!$annee->est_active): ?>
                                <a href="<?php echo $app_url; ?>/anneesscolaires/activate/<?php echo $annee->id; ?>" class="btn btn-sm btn-info" onclick="return confirm('<?php echo $tr('are_you_sure_activate_year'); ?>');">
                                    <i class="fas fa-check-circle"></i> <?php echo $tr('activate'); ?>
                                </a>
                                <form action="<?php echo $app_url; ?>/anneesscolaires/delete/<?php echo $annee->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_year'); ?>');">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
