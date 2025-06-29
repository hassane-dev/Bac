<?php
// app/views/roles/index.php
// Assumer qu'un layout principal (header/footer) sera inclus par la méthode view() du contrôleur.
// Pour l'instant, on se concentre sur le contenu spécifique à cette vue.

// $title est passé par le contrôleur
// $roles est passé par le contrôleur
// $tr est la fonction de traduction
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title); ?></h2>

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
            <i class="fas fa-plus"></i> <?php echo $tr('add_new_role'); // 'Ajouter un nouveau rôle' ?>
        </a>
    </p>

    <?php if (empty($roles)): ?>
        <p><?php echo $tr('no_roles_found'); // 'Aucun rôle trouvé.' ?></p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo $tr('role_name'); // 'Nom du Rôle' ?></th>
                    <th><?php echo $tr('actions'); // 'Actions' ?></th>
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
                            <?php // Ne pas permettre la suppression du rôle Administrateur (ID 1 par exemple) ?>
                            <?php if ($role->id != 1): // Supposons que 1 est l'ID de l'admin et ne peut être supprimé ?>
                                <form action="<?php echo $app_url; ?>/roles/delete/<?php echo $role->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_role'); // Êtes-vous sûr de vouloir supprimer ce rôle ? Les utilisateurs assignés pourraient perdre leurs accès. ?>');">
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
// Ici, on pourrait inclure des scripts JS spécifiques si nécessaire.
// Exemple:
// <script src="<?php echo $app_url; ?>/assets/js/roles_index.js"></script>
?>

<?php
// Mettre à jour les traductions nécessaires dans les fichiers de langue:
// fr.php:
// 'add_new_role' => 'Ajouter un nouveau rôle',
// 'no_roles_found' => 'Aucun rôle trouvé.',
// 'role_name' => 'Nom du Rôle',
// 'are_you_sure_delete_role' => 'Êtes-vous sûr de vouloir supprimer ce rôle ?',
// 'role_added_successfully' => 'Rôle ajouté avec succès.',
// 'error_adding_role' => 'Erreur lors de l\'ajout du rôle.',
// 'role_updated_successfully' => 'Rôle mis à jour avec succès.',
// 'error_updating_role' => 'Erreur lors de la mise à jour du rôle.',
// 'role_deleted_successfully' => 'Rôle supprimé avec succès.',
// 'error_deleting_role' => 'Erreur lors de la suppression du rôle.',
// 'role_not_found' => 'Rôle non trouvé.',
// 'please_enter_role_name' => 'Veuillez saisir le nom du rôle.',

// ar.php:
// 'add_new_role' => 'إضافة دور جديد',
// 'no_roles_found' => 'لم يتم العثور على أدوار.',
// 'role_name' => 'اسم الدور',
// 'are_you_sure_delete_role' => 'هل أنت متأكد أنك تريد حذف هذا الدور؟',
// 'role_added_successfully' => 'تمت إضافة الدور بنجاح.',
// 'error_adding_role' => 'خطأ أثناء إضافة الدور.',
// 'role_updated_successfully' => 'تم تحديث الدور بنجاح.',
// 'error_updating_role' => 'خطأ أثناء تحديث الدور.',
// 'role_deleted_successfully' => 'تم حذف الدور بنجاح.',
// 'error_deleting_role' => 'خطأ أثناء حذف الدور.',
// 'role_not_found' => 'الدور غير موجود.',
// 'please_enter_role_name' => 'الرجاء إدخال اسم الدور.',
?>
