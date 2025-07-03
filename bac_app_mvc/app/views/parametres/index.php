<?php
// app/views/parametres/index.php
// $title, $settings (array), $tr sont passés
$s = $settings; // Alias plus court
$uploadBaseUrl = $app_url . '/'; // Assumant que APP_URL est la racine web de bac_app_mvc
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('general_settings')); ?></h2>

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

    <form action="<?php echo $app_url; ?>/parametres/update" method="POST" enctype="multipart/form-data">

        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo $tr('institutional_information'); ?></h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="republique_de" class="form-label"><?php echo $tr('republic_of'); ?></label>
                    <input type="text" class="form-control" id="republique_de" name="republique_de" value="<?php echo htmlspecialchars($s['republique_de'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="devise_republique" class="form-label"><?php echo $tr('republic_motto'); ?></label>
                    <input type="text" class="form-control" id="devise_republique" name="devise_republique" value="<?php echo htmlspecialchars($s['devise_republique'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="ministere_nom" class="form-label"><?php echo $tr('ministry_name'); ?></label>
                    <input type="text" class="form-control" id="ministere_nom" name="ministere_nom" value="<?php echo htmlspecialchars($s['ministere_nom'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="office_examen_nom" class="form-label"><?php echo $tr('exam_office_name'); ?></label>
                    <input type="text" class="form-control" id="office_examen_nom" name="office_examen_nom" value="<?php echo htmlspecialchars($s['office_examen_nom'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="direction_nom" class="form-label"><?php echo $tr('directorate_name'); ?></label>
                    <input type="text" class="form-control" id="direction_nom" name="direction_nom" value="<?php echo htmlspecialchars($s['direction_nom'] ?? ''); ?>">
                </div>
                 <div class="mb-3">
                    <label for="ville_office" class="form-label"><?php echo $tr('office_city'); ?></label>
                    <input type="text" class="form-control" id="ville_office" name="ville_office" value="<?php echo htmlspecialchars($s['ville_office'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo $tr('national_symbols'); ?></h4>
            </div>
            <div class="card-body">
                <?php
                $symbolFields = [
                    'logo_pays_path' => ['label' => $tr('country_logo'), 'name' => 'logo_pays_file'],
                    'armoirie_pays_path' => ['label' => $tr('country_coat_of_arms'), 'name' => 'armoirie_pays_file'],
                    'drapeau_pays_path' => ['label' => $tr('country_flag'), 'name' => 'drapeau_pays_file'],
                ];
                ?>
                <div class="row">
                    <?php foreach ($symbolFields as $dbField => $fieldInfo): ?>
                    <div class="col-md-4 mb-3">
                        <label for="<?php echo $fieldInfo['name']; ?>" class="form-label"><?php echo $fieldInfo['label']; ?></label>
                        <input type="file" class="form-control" id="<?php echo $fieldInfo['name']; ?>" name="<?php echo $fieldInfo['name']; ?>" accept="image/*">
                        <?php if (!empty($s[$dbField])): ?>
                            <div class="mt-2">
                                <img src="<?php echo $uploadBaseUrl . htmlspecialchars($s[$dbField]); ?>?t=<?php echo time(); // Cache buster ?>" alt="<?php echo $fieldInfo['label']; ?>" class="img-thumbnail" style="max-height: 80px;">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="delete_<?php echo $dbField; ?>" value="1" id="delete_<?php echo $dbField; ?>">
                                    <label class="form-check-label" for="delete_<?php echo $dbField; ?>"><?php echo $tr('delete_current_file'); // Supprimer le fichier actuel ?></label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo $tr('document_personalization'); ?></h4>
            </div>
            <div class="card-body">
                 <?php
                $documentFields = [
                    'signature_directeur_path' => ['label' => $tr('director_signature'), 'name' => 'signature_directeur_file'],
                    'cachet_office_path' => ['label' => $tr('office_stamp'), 'name' => 'cachet_office_file'],
                ];
                ?>
                <div class="row">
                     <?php foreach ($documentFields as $dbField => $fieldInfo): ?>
                    <div class="col-md-6 mb-3">
                        <label for="<?php echo $fieldInfo['name']; ?>" class="form-label"><?php echo $fieldInfo['label']; ?></label>
                        <input type="file" class="form-control" id="<?php echo $fieldInfo['name']; ?>" name="<?php echo $fieldInfo['name']; ?>" accept="image/*">
                        <?php if (!empty($s[$dbField])): ?>
                             <div class="mt-2">
                                <img src="<?php echo $uploadBaseUrl . htmlspecialchars($s[$dbField]); ?>?t=<?php echo time(); ?>" alt="<?php echo $fieldInfo['label']; ?>" class="img-thumbnail" style="max-height: 80px;">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="delete_<?php echo $dbField; ?>" value="1" id="delete_<?php echo $dbField; ?>">
                                    <label class="form-check-label" for="delete_<?php echo $dbField; ?>"><?php echo $tr('delete_current_file'); ?></label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-save"></i> <?php echo $tr('save_general_settings'); ?>
        </button>
    </form>
</div>

<?php
// Ajouter 'delete_current_file' => 'Supprimer le fichier actuel' aux traductions
// 'unsupported_file_type_image' => "Type de fichier non supporté (images JPG, PNG, GIF, SVG uniquement)."
?>
