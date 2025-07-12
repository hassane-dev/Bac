<?php
// Data: $data['page_title'], $data['configurations'], $data['unconfigured_annees'], $data['all_annees_scolaires']
// Helpers: $tr, $app_url, $this->userHasPermission()
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
</div>

<?php if ($this->userHasPermission('manage_pedagogical_configs')): ?>
<div class="card mb-4">
    <div class="card-header">
        <?php echo $tr('manage_config_for_year'); ?>
    </div>
    <div class="card-body">
        <?php if (!empty($data['unconfigured_annees']) || !empty($data['configurations'])): ?>
            <form method="GET" action="<?php echo APP_URL . '/configurations_pedagogiques/edit'; ?>" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="annee_scolaire_id_select" class="form-label visually-hidden"><?php echo $tr('select_academic_year_for_config'); ?></label>
                    <select name="annee_id" id="annee_scolaire_id_select" class="form-select">
                        <option value=""><?php echo $tr('select_academic_year_for_config'); ?>...</option>
                        <?php $annees_options = [];
                              foreach($data['all_annees_scolaires'] as $annee) $annees_options[$annee->id] = $annee->libelle;
                              uasort($annees_options, function($a, $b) { return $b <=> $a; }); // Trier par libellé descendant
                        ?>
                        <?php foreach ($annees_options as $id => $libelle): ?>
                            <option value="<?php echo $id; ?>">
                                <?php echo htmlspecialchars($libelle); ?>
                                <?php
                                $is_configured = false;
                                foreach($data['configurations'] as $conf) { if ($conf->annee_scolaire_id == $id) {$is_configured = true; break;} }
                                echo $is_configured ? ' (' . $tr('configured_short') . ')' : ' (' . $tr('not_configured_short') . ')'; // Ajouter configured_short et not_configured_short aux lang
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><?php echo $tr('edit') . '/' . $tr('add'); ?></button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-muted"><?php echo $tr('no_academic_years_create_first'); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<?php if (empty($data['configurations'])): ?>
    <?php if ($this->userHasPermission('manage_pedagogical_configs') && empty($data['all_annees_scolaires'])) :?>
        <?php // Ne rien afficher de plus ici si pas d'années scolaires et que le message est déjà dans le card du haut ?>
    <?php else: ?>
        <div class="alert alert-info"><?php echo $tr('no_pedagogical_configs_found'); ?></div>
    <?php endif; ?>
<?php else: ?>
    <h4 class="mt-4"><?php echo $tr('existing_configurations'); ?></h4> <?php // Ajouter 'existing_configurations' ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?php echo $tr('academic_year'); ?></th>
                    <th><?php echo $tr('admission_threshold'); ?></th>
                    <th><?php echo $tr('second_round_threshold'); ?></th>
                    <th><?php echo $tr('mention_P'); ?></th>
                    <th><?php echo $tr('mention_AB_short'); ?></th>
                    <th><?php echo $tr('mention_B_short'); ?></th>
                    <th><?php echo $tr('mention_TB_short'); ?></th>
                    <th><?php echo $tr('mention_Exc_short'); ?></th>
                    <?php if ($this->userHasPermission('manage_pedagogical_configs')): ?>
                        <th><?php echo $tr('actions'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['configurations'] as $config): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($config->annee_scolaire_libelle); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->seuil_admission, 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->seuil_second_tour, 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->mention_passable, 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->mention_AB, 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->mention_bien, 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->mention_TB, 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($config->mention_exc, 2)); ?></td>
                        <?php if ($this->userHasPermission('manage_pedagogical_configs')): ?>
                            <td>
                                <a href="<?php echo APP_URL . '/configurations_pedagogiques/edit/' . $config->annee_scolaire_id; ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                                </a>
                                <!-- La suppression se fait via la suppression de l'année scolaire (ON DELETE CASCADE) -->
                                <!-- ou via une action spécifique si nécessaire, mais pas typiquement ici. -->
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
