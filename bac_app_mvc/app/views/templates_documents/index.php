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

    <p><?php echo $tr('select_document_to_configure'); ?></p>

    <div class="list-group">
        <?php foreach ($types_documents as $type): ?>
            <a href="<?php echo $app_url; ?>/templatesdocuments/gerer/<?php echo htmlspecialchars($type); ?>" class="list-group-item list-group-item-action">
                <?php echo htmlspecialchars($tr('doc_type_' . $type)); ?>
            </a>
        <?php endforeach; ?>
    </div>
     <a href="<?php echo $app_url; ?>/dashboard" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left"></i> <?php echo $tr('back_to_dashboard'); ?>
    </a>
</div>
