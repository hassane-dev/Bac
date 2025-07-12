<?php
// Data: $data['page_title'], $data['annees']
// Helpers: $tr, $app_url, $this->userHasPermission()
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
    <?php if ($this->userHasPermission('manage_academic_years')): ?>
        <a href="<?php echo APP_URL . '/annees_scolaires/add'; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_academic_year'); ?>
        </a>
    <?php endif; ?>
</div>

<?php if (empty($data['annees'])): ?>
    <div class="alert alert-info"><?php echo $tr('no_academic_years_found'); ?></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?php echo $tr('label'); ?></th>
                    <th><?php echo $tr('start_date'); ?></th>
                    <th><?php echo $tr('end_date'); ?></th>
                    <th><?php echo $tr('active_status'); ?></th>
                    <?php if ($this->userHasPermission('manage_academic_years')): ?>
                        <th><?php echo $tr('actions'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['annees'] as $annee): ?>
                    <tr class="<?php echo $annee->est_active ? 'table-success' : ''; ?>">
                        <td><?php echo htmlspecialchars($annee->libelle); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($annee->date_debut))); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($annee->date_fin))); ?></td>
                        <td>
                            <?php if ($annee->est_active): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> <?php echo $tr('active'); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo $tr('inactive'); ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if ($this->userHasPermission('manage_academic_years')): ?>
                            <td>
                                <?php if (!$annee->est_active): ?>
                                    <a href="<?php echo APP_URL . '/annees_scolaires/activate/' . $annee->id; ?>" class="btn btn-sm btn-outline-success me-1" title="<?php echo $tr('activate'); ?>" onclick="return confirm('<?php echo $tr('are_you_sure_activate_year'); ?>');">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo APP_URL . '/annees_scolaires/edit/' . $annee->id; ?>" class="btn btn-sm btn-outline-primary me-1" title="<?php echo $tr('edit'); ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if (!$annee->est_active): ?>
                                    <form action="<?php echo APP_URL . '/annees_scolaires/delete/' . $annee->id; ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_year'); ?>');">
                                        <input type="hidden" name="id_to_delete" value="<?php echo $annee->id; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="<?php echo $tr('delete'); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                     <button type="button" class="btn btn-sm btn-outline-danger" disabled title="<?php echo $tr('delete'); ?>">
                                        <i class="fas fa-trash"></i>
                                     </button>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
