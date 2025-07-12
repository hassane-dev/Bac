<?php
// Data est fourni par AccreditationsController::index()
// $data['page_title']
// $data['accreditations']

// Le layout main.php sera utilisé implicitement par la méthode View::render() si elle est mise à jour,
// ou nous devons inclure header/footer manuellement.
// Pour l'instant, on suppose que $this->view() dans le contrôleur gère le layout.
// Les variables comme $tr, $current_lang, $isLoggedIn, $app_url sont injectées par Controller::view()
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
    <?php if ($this->userHasPermission('manage_accreditations')): ?>
        <a href="<?php echo APP_URL . '/accreditations/add'; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_accreditation'); ?>
        </a>
    <?php endif; ?>
</div>

<?php if (empty($data['accreditations'])): ?>
    <div class="alert alert-info"><?php echo $tr('no_accreditations_found'); ?></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th><?php echo $tr('accreditation_label'); ?></th>
                    <?php if ($this->userHasPermission('manage_accreditations')): ?>
                        <th><?php echo $tr('actions'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['accreditations'] as $index => $accreditation): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($accreditation->libelle_action); ?></td>
                        <?php if ($this->userHasPermission('manage_accreditations')): ?>
                            <td>
                                <a href="<?php echo APP_URL . '/accreditations/edit/' . $accreditation->id; ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                                </a>
                                <form action="<?php echo APP_URL . '/accreditations/delete/' . $accreditation->id; ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_accreditation'); ?>');">
                                    <input type="hidden" name="id_to_delete" value="<?php echo $accreditation->id; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
