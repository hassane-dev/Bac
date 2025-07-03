<?php
// app/views/configurations_pedagogiques/index.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('pedagogical_configs_list')); ?></h2>

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
        <a href="<?php echo $app_url; ?>/configurationspedagogiques/gerer" class="btn btn-primary">
            <i class="fas fa-cogs"></i> <?php echo $tr('manage_config_for_year'); ?>
        </a>
    </p>

    <?php if (empty($configs)): ?>
        <p><?php echo $tr('no_pedagogical_configs_found'); ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?php echo $tr('academic_year'); ?></th>
                    <th><?php echo $tr('admission_threshold'); ?></th>
                    <th><?php echo $tr('second_round_threshold'); ?></th>
                    <th><?php echo $tr('mentions'); ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configs as $config): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($config->annee_scolaire_libelle); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$config->seuil_admission, 2, '.', '')); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$config->seuil_second_tour, 2, '.', '')); ?></td>
                        <td>
                            <?php echo $tr('mention_P'); ?>: <?php echo htmlspecialchars(number_format((float)$config->mention_passable, 2, '.', '')); ?><br>
                            <?php echo $tr('mention_AB_short'); ?>: <?php echo htmlspecialchars(number_format((float)$config->mention_AB, 2, '.', '')); ?><br>
                            <?php echo $tr('mention_B_short'); ?>: <?php echo htmlspecialchars(number_format((float)$config->mention_bien, 2, '.', '')); ?><br>
                            <?php echo $tr('mention_TB_short'); ?>: <?php echo htmlspecialchars(number_format((float)$config->mention_TB, 2, '.', '')); ?><br>
                            <?php echo $tr('mention_Exc_short'); ?>: <?php echo htmlspecialchars(number_format((float)$config->mention_exc, 2, '.', '')); ?>
                        </td>
                        <td>
                            <a href="<?php echo $app_url; ?>/configurationspedagogiques/gerer/<?php echo $config->annee_scolaire_id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <?php /* La suppression est implicite via la suppression de l'année scolaire ou à ajouter si besoin direct */ ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
