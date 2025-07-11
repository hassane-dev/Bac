<?php
// Data: $data['page_title'], $data['users']
// Helpers: $tr, $app_url, $this->userHasPermission()
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
    <?php if ($this->userHasPermission('manage_users')): ?>
        <a href="<?php echo APP_URL . '/users/add'; ?>" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> <?php echo $tr('add_new_user'); ?>
        </a>
    <?php endif; ?>
</div>

<?php if (empty($data['users'])): ?>
    <div class="alert alert-info"><?php echo $tr('no_users_found'); ?></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th><?php echo $tr('username'); ?></th>
                    <th><?php echo $tr('lastname'); ?></th>
                    <th><?php echo $tr('firstname'); ?></th>
                    <th><?php echo $tr('email'); ?></th>
                    <th><?php echo $tr('role'); ?></th>
                    <th><?php echo $tr('active_status'); ?></th>
                    <?php if ($this->userHasPermission('manage_users')): ?>
                        <th><?php echo $tr('actions'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['users'] as $index => $user): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($user->username); ?></td>
                        <td><?php echo htmlspecialchars($user->nom); ?></td>
                        <td><?php echo htmlspecialchars($user->prenom); ?></td>
                        <td><?php echo htmlspecialchars($user->email ?? $tr('not_defined')); ?></td>
                        <td><?php echo htmlspecialchars($user->nom_role); // nom_role is joined in UserModel::getAll() / getById() ?></td>
                        <td>
                            <?php if ($user->is_active): ?>
                                <span class="badge bg-success"><?php echo $tr('active'); ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?php echo $tr('inactive'); ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if ($this->userHasPermission('manage_users')): ?>
                            <td>
                                <a href="<?php echo APP_URL . '/users/edit/' . $user->id; ?>" class="btn btn-sm btn-outline-primary me-1" title="<?php echo $tr('edit'); ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user->id != 1 && $user->id != ($_SESSION['user_id'] ?? null) ): // Prevent delete for admin ID 1 and self ?>
                                    <form action="<?php echo APP_URL . '/users/delete/' . $user->id; ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_user'); ?>');">
                                        <input type="hidden" name="id_to_delete" value="<?php echo $user->id; ?>">
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
