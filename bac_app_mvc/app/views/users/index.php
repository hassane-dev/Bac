<?php
// app/views/users/index.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('user_list')); ?></h2>

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
        <a href="<?php echo $app_url; ?>/users/create" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> <?php echo $tr('add_new_user'); ?>
        </a>
    </p>

    <?php if (empty($users)): ?>
        <p><?php echo $tr('no_users_found'); ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('username'); ?></th>
                    <th><?php echo $tr('lastname'); ?></th>
                    <th><?php echo $tr('firstname'); ?></th>
                    <th><?php echo $tr('role'); ?></th>
                    <th><?php echo $tr('email'); ?></th>
                    <th><?php echo $tr('active_status'); ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user->id); ?></td>
                        <td><?php echo htmlspecialchars($user->username); ?></td>
                        <td><?php echo htmlspecialchars($user->nom); ?></td>
                        <td><?php echo htmlspecialchars($user->prenom); ?></td>
                        <td><?php echo htmlspecialchars($user->nom_role ?? $tr('undefined_role')); // Afficher si nom_role est null ?></td>
                        <td><?php echo htmlspecialchars($user->email ?? ''); ?></td>
                        <td>
                            <?php if ($user->is_active): ?>
                                <span class="badge bg-success"><?php echo $tr('active'); ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?php echo $tr('inactive'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $app_url; ?>/users/edit/<?php echo $user->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <?php
                            $canDelete = true;
                            if ($user->id == 1) $canDelete = false; // Admin principal
                            if (isset($_SESSION['user_id']) && $user->id == $_SESSION['user_id']) $canDelete = false; // Soi-même
                            ?>
                            <?php if ($canDelete): ?>
                                <form action="<?php echo $app_url; ?>/users/delete/<?php echo $user->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_user'); ?>');">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                 <button type="button" class="btn btn-sm btn-danger disabled" title="<?php echo $tr('cannot_delete_this_user'); ?>">
                                    <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
