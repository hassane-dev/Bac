<?php
// app/views/parametres/index.php
// $title, $settings (array), $tr sont passés
$s = $settings; // Alias plus court
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

    <form action="<?php echo $app_url; ?>/parametres/update" method="POST" enctype="multipart/form-data">

        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo $tr('institutional_information'); // Informations Institutionnelles ?></h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="republique_de" class="form-label"><?php echo $tr('republic_of'); // République de ?></label>
                    <input type="text" class="form-control" id="republique_de" name="republique_de" value="<?php echo htmlspecialchars($s['republique_de'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="devise_republique" class="form-label"><?php echo $tr('republic_motto'); // Devise de la République ?></label>
                    <input type="text" class="form-control" id="devise_republique" name="devise_republique" value="<?php echo htmlspecialchars($s['devise_republique'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="ministere_nom" class="form-label"><?php echo $tr('ministry_name'); // Nom du Ministère ?></label>
                    <input type="text" class="form-control" id="ministere_nom" name="ministere_nom" value="<?php echo htmlspecialchars($s['ministere_nom'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="office_examen_nom" class="form-label"><?php echo $tr('exam_office_name'); // Nom de l'Office d'Examen ?></label>
                    <input type="text" class="form-control" id="office_examen_nom" name="office_examen_nom" value="<?php echo htmlspecialchars($s['office_examen_nom'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="direction_nom" class="form-label"><?php echo $tr('directorate_name'); // Nom de la Direction ?></label>
                    <input type="text" class="form-control" id="direction_nom" name="direction_nom" value="<?php echo htmlspecialchars($s['direction_nom'] ?? ''); ?>">
                </div>
                 <div class="mb-3">
                    <label for="ville_office" class="form-label"><?php echo $tr('office_city'); // Ville de l'Office ?></label>
                    <input type="text" class="form-control" id="ville_office" name="ville_office" value="<?php echo htmlspecialchars($s['ville_office'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo $tr('national_symbols'); // Symboles Nationaux ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="logo_pays_file" class="form-label"><?php echo $tr('country_logo'); // Logo du Pays ?></label>
                        <input type="file" class="form-control" id="logo_pays_file" name="logo_pays_file">
                        <?php if (!empty($s['logo_pays_path'])): ?>
                            <img src="<?php echo $app_url . '/' . htmlspecialchars($s['logo_pays_path']); ?>" alt="Logo Pays" class="img-thumbnail mt-2" style="max-height: 100px;">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="armoirie_pays_file" class="form-label"><?php echo $tr('country_coat_of_arms'); // Armoirie du Pays ?></label>
                        <input type="file" class="form-control" id="armoirie_pays_file" name="armoirie_pays_file">
                        <?php if (!empty($s['armoirie_pays_path'])): ?>
                            <img src="<?php echo $app_url . '/' . htmlspecialchars($s['armoirie_pays_path']); ?>" alt="Armoirie Pays" class="img-thumbnail mt-2" style="max-height: 100px;">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="drapeau_pays_file" class="form-label"><?php echo $tr('country_flag'); // Drapeau du Pays ?></label>
                        <input type="file" class="form-control" id="drapeau_pays_file" name="drapeau_pays_file">
                        <?php if (!empty($s['drapeau_pays_path'])): ?>
                            <img src="<?php echo $app_url . '/' . htmlspecialchars($s['drapeau_pays_path']); ?>" alt="Drapeau Pays" class="img-thumbnail mt-2" style="max-height: 100px;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo $tr('document_personalization'); // Personnalisation des Documents (Facultatif) ?></h4>
            </div>
            <div class="card-body">
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="signature_directeur_file" class="form-label"><?php echo $tr('director_signature'); // Signature du Directeur ?></label>
                        <input type="file" class="form-control" id="signature_directeur_file" name="signature_directeur_file">
                        <?php if (!empty($s['signature_directeur_path'])): ?>
                            <img src="<?php echo $app_url . '/' . htmlspecialchars($s['signature_directeur_path']); ?>" alt="Signature Directeur" class="img-thumbnail mt-2" style="max-height: 100px;">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cachet_office_file" class="form-label"><?php echo $tr('office_stamp'); // Cachet de l'Office ?></label>
                        <input type="file" class="form-control" id="cachet_office_file" name="cachet_office_file">
                        <?php if (!empty($s['cachet_office_path'])): ?>
                            <img src="<?php echo $app_url . '/' . htmlspecialchars($s['cachet_office_path']); ?>" alt="Cachet Office" class="img-thumbnail mt-2" style="max-height: 100px;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_general_settings'); // Enregistrer les Paramètres Généraux ?>
        </button>
    </form>
</div>

<?php
// Traductions à ajouter
// fr: 'general_settings' => 'Paramètres Généraux', 'institutional_information' => 'Informations Institutionnelles', 'republic_of' => 'République de', 'republic_motto' => 'Devise de la République', 'ministry_name' => 'Nom du Ministère', 'exam_office_name' => "Nom de l'Office d'Examen", 'directorate_name' => 'Nom de la Direction', 'office_city' => "Ville de l'Office", 'national_symbols' => 'Symboles Nationaux', 'country_logo' => 'Logo du Pays', 'country_coat_of_arms' => 'Armoirie du Pays', 'country_flag' => 'Drapeau du Pays', 'document_personalization' => 'Personnalisation des Documents (Facultatif)', 'director_signature' => 'Signature du Directeur', 'office_stamp' => "Cachet de l'Office", 'save_general_settings' => 'Enregistrer les Paramètres Généraux', 'settings_updated_successfully' => 'Paramètres mis à jour avec succès.', 'error_updating_settings' => 'Erreur lors de la mise à jour des paramètres.', 'error_uploading_file' => 'Erreur lors du téléversement du fichier pour :field', 'file_not_image' => "Le fichier n'est pas une image.", 'file_too_large' => "Le fichier est trop volumineux (max 5MB).", 'unsupported_file_type' => "Type de fichier non supporté (JPG, PNG, GIF uniquement).", 'error_during_upload' => "Une erreur s'est produite lors du téléversement de votre fichier."
// ar: ...
?>
