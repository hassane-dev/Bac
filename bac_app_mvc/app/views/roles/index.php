<?php
// Data: $data['page_title'], $data['roles']
// Helpers: $tr, $app_url, $this->userHasPermission()
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
    <?php if ($this->userHasPermission('manage_roles')): ?>
        <a href="<?php echo APP_URL . '/roles/add'; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_role'); ?>
        </a>
    <?php endif; ?>
</div>

<?php if (empty($data['roles'])): ?>
    <div class="alert alert-info"><?php echo $tr('no_roles_found'); ?></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th><?php echo $tr('role_name'); ?></th>
                    <?php if ($this->userHasPermission('manage_roles')): ?>
                        <th><?php echo $tr('actions'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['roles'] as $index => $role): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($role->nom_role); ?></td>
                        <?php if ($this->userHasPermission('manage_roles')): ?>
                            <td>
                                <a href="<?php echo APP_URL . '/roles/edit/' . $role->id; ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                                </a>
                                <?php if ($role->id != 1): //  Ne pas permettre la suppression du rôle admin (ID 1) directement ici ?>
                                    <form action="<?php echo APP_URL . '/roles/delete/' . $role->id; ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_role'); ?>');">
                                        <input type="hidden" name="id_to_delete" value="<?php echo $role->id; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled>
                                        <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
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
