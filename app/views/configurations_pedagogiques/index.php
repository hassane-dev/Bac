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
            <i class="fas fa-cogs"></i> <?php echo $tr('manage_config_for_year'); // Gérer/Ajouter Config pour une Année ?>
        </a>
    </p>

    <?php if (empty($configs)): ?>
        <p><?php echo $tr('no_pedagogical_configs_found'); // Aucune configuration pédagogique trouvée. ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?php echo $tr('academic_year'); // Année Scolaire ?></th>
                    <th><?php echo $tr('admission_threshold'); // Seuil Admission ?></th>
                    <th><?php echo $tr('second_round_threshold'); // Seuil 2nd Tour ?></th>
                    <th><?php echo $tr('mentions'); // Mentions (Passable / AB / B / TB / Exc) ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configs as $config): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($config->annee_scolaire_libelle); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->seuil_admission, 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->seuil_second_tour, 2)); ?></td>
                        <td>
                            P: <?php echo htmlspecialchars(number_format($config->mention_passable, 2)); ?> |
                            AB: <?php echo htmlspecialchars(number_format($config->mention_AB, 2)); ?> |
                            B: <?php echo htmlspecialchars(number_format($config->mention_bien, 2)); ?> |
                            TB: <?php echo htmlspecialchars(number_format($config->mention_TB, 2)); ?> |
                            Exc: <?php echo htmlspecialchars(number_format($config->mention_exc, 2)); ?>
                        </td>
                        <td>
                            <a href="<?php echo $app_url; ?>/configurationspedagogiques/gerer/<?php echo $config->annee_scolaire_id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <?php /*
                            <form action="<?php echo $app_url; ?>/configurationspedagogiques/delete/<?php echo $config->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_config'); ?>');">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                </button>
                            </form>
                            */ ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// Traductions
// fr: 'pedagogical_configs_list' => 'Liste des Configurations Pédagogiques', 'manage_config_for_year' => 'Gérer/Ajouter Config pour une Année', 'no_pedagogical_configs_found' => 'Aucune configuration pédagogique trouvée.', 'academic_year' => 'Année Scolaire', 'admission_threshold' => 'Seuil Admission', 'second_round_threshold' => 'Seuil 2nd Tour', 'mentions' => 'Mentions', 'are_you_sure_delete_config' => 'Êtes-vous sûr de vouloir supprimer cette configuration ?', 'pedagogical_config_saved_successfully' => 'Configuration pédagogique enregistrée avec succès.', 'error_saving_pedagogical_config' => 'Erreur lors de l\'enregistrement de la configuration pédagogique.', 'field_must_be_numeric_between_0_20' => 'Le champ doit être une valeur numérique entre 0 et 20.', 'manage_pedagogical_config_for' => 'Gérer la Configuration Pédagogique pour', 'select_academic_year_for_config' => "Sélectionner l'Année Scolaire pour la Configuration", 'no_academic_years_create_first' => "Aucune année scolaire n'a été créée. Veuillez d'abord créer une année scolaire.", 'academic_year_required' => "L'année scolaire est requise."
// ar: ...
?>
