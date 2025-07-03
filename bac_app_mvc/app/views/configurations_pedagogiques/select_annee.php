<?php
// app/views/configurations_pedagogiques/select_annee.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('select_academic_year_for_config')); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($annees)): ?>
        <p><?php echo $tr('no_academic_years_create_first'); ?></p>
        <a href="<?php echo $app_url; ?>/anneesscolaires/create" class="btn btn-primary"><?php echo $tr('add_new_academic_year'); ?></a>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($annees as $annee): ?>
                <a href="<?php echo $app_url; ?>/configurationspedagogiques/gerer/<?php echo $annee->id; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($annee->libelle); ?>
                    <?php if ($annee->est_active): ?>
                        <span class="badge bg-success rounded-pill"><?php echo $tr('active'); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="mt-3">
            <?php echo $tr('or'); ?> <a href="<?php echo $app_url; ?>/anneesscolaires/create"><?php echo $tr('add_new_academic_year'); ?></a> <?php echo $tr('if_not_listed'); ?>
        </p>
    <?php endif; ?>
     <a href="<?php echo $app_url; ?>/configurationspedagogiques" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left"></i> <?php echo $tr('back_to_configs_list'); // Retour à la liste des configurations ?>
    </a>
</div>
