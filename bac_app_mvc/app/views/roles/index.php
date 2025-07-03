<?php
// app/views/roles/index.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('role_list')); ?></h2>

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
        <a href="<?php echo $app_url; ?>/roles/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_role'); ?>
        </a>
    </p>

    <?php if (empty($roles)): ?>
        <p><?php echo $tr('no_roles_found'); ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('role_name'); ?></th>
                    <th><?php echo $tr('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($role->id); ?></td>
                        <td><?php echo htmlspecialchars($role->nom_role); ?></td>
                        <td>
                            <a href="<?php echo $app_url; ?>/roles/edit/<?php echo $role->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <?php if ($role->id != 1): // Supposons que 1 est l'ID de l'admin ?>
                                <form action="<?php echo $app_url; ?>/roles/delete/<?php echo $role->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_role'); ?>');">
                                    <?php // Pour plus de sécurité, la méthode DELETE devrait être utilisée, ou un token CSRF pour POST ?>
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-danger disabled" title="<?php echo $tr('cannot_delete_admin_role'); ?>">
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
