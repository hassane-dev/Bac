<?php
// app/views/dashboard/index.php
?>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('dashboard')); ?></h2>

    <?php if (isset($welcome_message)): ?>
        <p><?php echo htmlspecialchars($welcome_message); ?></p>
    <?php endif; ?>

    <p><?php echo $tr('logged_in_as'); // Vous êtes connecté en tant que ?>: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong></p>

    <p><a href="<?php echo $app_url; ?>/auth/logout" class="btn btn-danger"><?php echo $tr('logout'); ?></a></p>

    <hr>
    <h4>Navigation rapide :</h4>
    <ul>
        <li><a href="<?php echo $app_url; ?>/roles"><?php echo $tr('manage_roles'); // Gérer les rôles ?></a></li>
        <li><a href="<?php echo $app_url; ?>/accreditations"><?php echo $tr('manage_accreditations'); // Gérer les accréditations ?></a></li>
        <li><a href="<?php echo $app_url; ?>/users"><?php echo $tr('manage_users'); // Gérer les utilisateurs ?></a></li>
        <?php /* Ajouter d'autres liens ici au fur et à mesure que les modules sont créés */ ?>
    </ul>

</div>

<?php
// Traductions à ajouter/vérifier
// fr.php:
// 'logged_in_as' => 'Vous êtes connecté en tant que',
// 'manage_roles' => 'Gérer les Rôles',
// 'manage_accreditations' => 'Gérer les Accréditations',
// 'manage_users' => 'Gérer les Utilisateurs',

// ar.php:
// 'logged_in_as' => 'لقد سجلت الدخول باسم',
// 'manage_roles' => 'إدارة الأدوار',
// 'manage_accreditations' => 'إدارة الاعتمادات',
// 'manage_users' => 'إدارة المستخدمين',
?>
