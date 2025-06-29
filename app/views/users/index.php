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
            <i class="fas fa-user-plus"></i> <?php echo $tr('add_new_user'); // 'Ajouter un nouvel utilisateur' ?>
        </a>
    </p>

    <?php if (empty($users)): ?>
        <p><?php echo $tr('no_users_found'); // 'Aucun utilisateur trouvé.' ?></p>
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
                    <th><?php echo $tr('active_status'); // Statut ?></th>
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
                        <td><?php echo htmlspecialchars($user->nom_role); ?></td>
                        <td><?php echo htmlspecialchars($user->email ?? ''); ?></td>
                        <td>
                            <?php if ($user->is_active): ?>
                                <span class="badge bg-success"><?php echo $tr('active'); // Actif ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?php echo $tr('inactive'); // Inactif ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $app_url; ?>/users/edit/<?php echo $user->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> <?php echo $tr('edit'); ?>
                            </a>
                            <?php // Empêcher la suppression de l'utilisateur admin principal (ID 1) et de soi-même ?>
                            <?php if ($user->id != 1 && (isset($_SESSION['user_id']) && $user->id != $_SESSION['user_id'])): ?>
                                <form action="<?php echo $app_url; ?>/users/delete/<?php echo $user->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_user'); ?>');">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> <?php echo $tr('delete'); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// Traductions à ajouter/vérifier:
// fr.php:
// 'add_new_user' => 'Ajouter un nouvel utilisateur',
// 'no_users_found' => 'Aucun utilisateur trouvé.',
// 'are_you_sure_delete_user' => 'Êtes-vous sûr de vouloir supprimer cet utilisateur ?',
// 'user_added_successfully' => 'Utilisateur ajouté avec succès.',
// 'error_adding_user' => "Erreur lors de l'ajout de l'utilisateur.",
// 'user_updated_successfully' => 'Utilisateur mis à jour avec succès.',
// 'error_updating_user' => "Erreur lors de la mise à jour de l'utilisateur.",
// 'user_deleted_successfully' => 'Utilisateur supprimé avec succès.',
// 'error_deleting_user' => "Erreur lors de la suppression de l'utilisateur.",
// 'user_not_found' => 'Utilisateur non trouvé.',
// 'username_required' => "Le nom d'utilisateur est requis.",
// 'username_taken' => "Ce nom d'utilisateur est déjà pris.",
// 'password_required' => "Le mot de passe est requis.",
// 'password_min_length' => "Le mot de passe doit contenir au moins :length caractères.",
// 'confirm_password_required' => "La confirmation du mot de passe est requise.",
// 'passwords_do_not_match' => "Les mots de passe ne correspondent pas.",
// 'role_required' => "Le rôle est requis.",
// 'lastname_required' => "Le nom est requis.",
// 'firstname_required' => "Le prénom est requis.",
// 'dob_required' => "La date de naissance est requise.",
// 'pob_required' => "Le lieu de naissance est requis.",
// 'gender_required' => "Le sexe est requis.",
// 'invalid_email_format' => "Format d'email invalide.",
// 'email_taken' => "Cet email est déjà utilisé.",
// 'active_status' => "Statut",
// 'active' => "Actif",
// 'inactive' => "Inactif",
// 'cannot_delete_current_user' => "Vous ne pouvez pas supprimer l'utilisateur actuellement connecté.",
// 'cannot_delete_main_admin' => "L'administrateur principal ne peut pas être supprimé.",

// ar.php: (similaires traductions)
?>
