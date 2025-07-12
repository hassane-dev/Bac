<?php
// Data: $data['page_title']
// Helpers: $tr, $app_url
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
</div>

<p><?php echo $tr('select_document_to_configure'); ?></p>

<div class="list-group">
    <a href="<?php echo APP_URL . '/templates_documents/configure/diplome'; ?>" class="list-group-item list-group-item-action list-group-item-primary d-flex justify-content-between align-items-center">
        <span><i class="fas fa-award me-2"></i> <?php echo $tr('doc_type_diplome'); ?></span>
        <i class="fas fa-chevron-right"></i>
    </a>
    <a href="<?php echo APP_URL . '/templates_documents/configure/releve'; ?>" class="list-group-item list-group-item-action list-group-item-info d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-invoice me-2"></i> <?php echo $tr('doc_type_releve'); ?></span>
        <i class="fas fa-chevron-right"></i>
    </a>
    <a href="<?php echo APP_URL . '/templates_documents/configure/carte'; ?>" class="list-group-item list-group-item-action list-group-item-success d-flex justify-content-between align-items-center">
        <span><i class="fas fa-id-card me-2"></i> <?php echo $tr('doc_type_carte'); ?></span>
        <i class="fas fa-chevron-right"></i>
    </a>
</div>

<p class="mt-4 text-muted">
    <small><?php echo $tr('template_config_note'); // Ex: "La configuration affectera l'apparence des documents PDF générés." ?></small>
</p>
