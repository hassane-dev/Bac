<?php
// app/views/templates_documents/index.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('document_templates_list')); ?></h2>

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

    <p><?php echo $tr('select_document_to_configure'); // Sélectionnez un type de document à configurer : ?></p>

    <div class="list-group">
        <?php foreach ($types_documents as $type): ?>
            <a href="<?php echo $app_url; ?>/templatesdocuments/gerer/<?php echo htmlspecialchars($type); ?>" class="list-group-item list-group-item-action">
                <?php echo htmlspecialchars($tr('doc_type_' . $type)); // Ex: $tr('doc_type_diplome') ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Traductions à ajouter/vérifier:
// fr.php:
// 'document_templates_list' => 'Modèles de Documents',
// 'select_document_to_configure' => 'Sélectionnez un type de document à configurer :',
// 'doc_type_diplome' => 'Diplôme',
// 'doc_type_releve' => 'Relevé de Notes',
// 'doc_type_carte' => "Carte d'Étudiant/Candidat",
// 'invalid_document_type' => 'Type de document invalide.',
// 'configure_template_for' => 'Configurer le modèle pour : ', // (espace à la fin)
// 'template_element_added_successfully' => "Élément de modèle ajouté avec succès.",
// 'error_adding_template_element' => "Erreur lors de l'ajout de l'élément de modèle.",
// 'element_name_required' => "Le nom de l'élément est requis.",
// 'template_element_deleted_successfully' => "Élément de modèle supprimé avec succès.",
// 'error_deleting_template_element' => "Erreur lors de la suppression de l'élément de modèle.",
// 'template_element_not_found' => "Élément de modèle non trouvé.",
// 'background_settings_updated_successfully' => "Paramètres d'arrière-plan mis à jour avec succès.",
// 'error_updating_background_settings' => "Erreur lors de la mise à jour des paramètres d'arrière-plan."

// ar.php: (similaires)
?>
