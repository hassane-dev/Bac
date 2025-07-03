<?php
// app/views/dashboard/index.php
// Assumer qu'un layout principal (header/footer avec menu) sera inclus par $this->view()
// Ce layout devrait conditionnellement afficher les liens de menu en fonction des permissions.
?>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('dashboard')); ?></h2>

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

    <?php if (isset($welcome_message)): ?>
        <p class="lead"><?php echo htmlspecialchars($welcome_message); ?></p>
    <?php endif; ?>

    <p><?php echo $tr('logged_in_as'); ?>: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>
       (Rôle ID: <?php echo htmlspecialchars($_SESSION['user_role_id'] ?? 'N/A'); ?>)
    </p>

    <hr>
    <h4><?php echo $tr('quick_navigation'); // Navigation rapide ?></h4>
    <div class="list-group">
        <?php if ($this->userHasPermission('manage_users')): // Exemple de vérification de permission ?>
            <a href="<?php echo $app_url; ?>/users" class="list-group-item list-group-item-action"><?php echo $tr('manage_users'); ?></a>
        <?php endif; ?>

        <?php if ($this->userHasPermission('manage_roles')): // Exemple ?>
            <a href="<?php echo $app_url; ?>/roles" class="list-group-item list-group-item-action"><?php echo $tr('manage_roles'); ?></a>
        <?php endif; ?>

        <?php if ($this->userHasPermission('manage_accreditations')): // Exemple ?>
            <a href="<?php echo $app_url; ?>/accreditations" class="list-group-item list-group-item-action"><?php echo $tr('manage_accreditations'); ?></a>
        <?php endif; ?>

        <?php if ($this->userHasPermission('manage_annees_scolaires')): // Exemple ?>
            <a href="<?php echo $app_url; ?>/anneesscolaires" class="list-group-item list-group-item-action"><?php echo $tr('manage_academic_years'); // Gérer les Années Scolaires ?></a>
        <?php endif; ?>

        <?php if ($this->userHasPermission('manage_general_settings')): // Exemple ?>
            <a href="<?php echo $app_url; ?>/parametres" class="list-group-item list-group-item-action"><?php echo $tr('general_settings'); ?></a>
        <?php endif; ?>

        <?php if ($this->userHasPermission('manage_pedagogical_configs')): // Exemple ?>
            <a href="<?php echo $app_url; ?>/configurationspedagogiques" class="list-group-item list-group-item-action"><?php echo $tr('manage_pedagogical_configs'); // Gérer les Configurations Pédagogiques ?></a>
        <?php endif; ?>

        <?php if ($this->userHasPermission('manage_language_settings')): // Exemple ?>
            <a href="<?php echo $app_url; ?>/configurationslinguistiques" class="list-group-item list-group-item-action"><?php echo $tr('manage_language_settings'); // Gérer les Configurations Linguistiques ?></a>
        <?php endif; ?>

        <?php if ($this->userHasPermission('manage_document_templates')): // Exemple ?>
             <a href="<?php echo $app_url; ?>/templatesdocuments" class="list-group-item list-group-item-action"><?php echo $tr('manage_document_templates'); // Gérer les Modèles de Documents ?></a>
        <?php endif; ?>

        <?php /* Ajouter d'autres liens ici au fur et à mesure */ ?>
    </div>

     <p class="mt-4"><a href="<?php echo $app_url; ?>/auth/logout" class="btn btn-danger"><?php echo $tr('logout'); ?></a></p>

</div>

<?php
// Ajout de traductions pour les liens du dashboard
// fr.php:
// 'quick_navigation' => 'Navigation rapide',
// 'manage_academic_years' => 'Gérer les Années Scolaires',
// 'manage_pedagogical_configs' => 'Gérer les Configurations Pédagogiques',
// 'manage_language_settings' => 'Gérer les Configurations Linguistiques',
// 'manage_document_templates' => 'Gérer les Modèles de Documents',
// 'undefined_role' => 'Rôle non défini',
// 'cannot_delete_this_user' => 'Cet utilisateur ne peut pas être supprimé.',
// 'accreditation_label_taken' => 'Ce libellé d\'accréditation est déjà utilisé.',
// 'admin_role_all_permissions_note' => 'Le rôle Administrateur a toutes les permissions par défaut et ne peut être modifié ici.',
// 'cannot_delete_admin_role' => 'Le rôle Administrateur ne peut pas être supprimé.'

// ar.php: (similaires)
?>
