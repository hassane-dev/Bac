<?php
// app/views/configurations_pedagogiques/select_annee.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('select_academic_year_for_config')); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($annees)): ?>
        <p><?php echo $tr('no_academic_years_create_first'); ?></p>
        <a href="<?php echo $app_url; ?>/anneesscolaires/create" class="btn btn-primary"><?php echo $tr('add_new_academic_year'); ?></a>
    <?php else: ?>
        <form method="GET" action="<?php echo $app_url; ?>/configurationspedagogiques/gerer/">
             <div class="list-group">
                <?php foreach ($annees as $annee): ?>
                    <a href="<?php echo $app_url; ?>/configurationspedagogiques/gerer/<?php echo $annee->id; ?>" class="list-group-item list-group-item-action">
                        <?php echo htmlspecialchars($annee->libelle); ?>
                        <?php if ($annee->est_active): ?>
                            <span class="badge bg-success float-end"><?php echo $tr('active'); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
         <p class="mt-3">
            <?php echo $tr('or'); // Ou ?> <a href="<?php echo $app_url; ?>/anneesscolaires/create"><?php echo $tr('add_new_academic_year'); ?></a> <?php echo $tr('if_not_listed'); // si non listée. ?>
        </p>
    <?php endif; ?>
</div>

<?php
// Traductions
// fr.php:
// 'or' => 'Ou',
// 'if_not_listed' => 'si non listée.',

// ar.php:
// 'or' => 'أو',
// 'if_not_listed' => 'إذا لم تكن مدرجة.',
?>
