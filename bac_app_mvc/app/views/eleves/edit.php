<?php
// app/views/eleves/edit.php
$uploadBaseUrl = $app_url . '/';
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('edit_student')); ?></h2>
    <p><?php echo $tr('editing_for_year'); // Modification pour l'année scolaire ?>: <strong><?php echo htmlspecialchars($annee_scolaire_libelle ?? ''); ?></strong></p>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
     <?php if (!empty($errors['photo_err'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $errors['photo_err']; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/eleves/update/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="annee_scolaire_id" value="<?php echo htmlspecialchars($annee_scolaire_id ?? ''); ?>">

        <div class="row">
            <div class="col-md-8">
                <div class="row">
                     <div class="col-md-6 mb-3">
                        <label for="matricule" class="form-label"><?php echo $tr('matricule'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo (!empty($errors['matricule_err'])) ? 'is-invalid' : ''; ?>" id="matricule" name="matricule" value="<?php echo htmlspecialchars($matricule ?? ''); ?>" required>
                        <div class="invalid-feedback"><?php echo $errors['matricule_err'] ?? ''; ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nom" class="form-label"><?php echo $tr('lastname'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo (!empty($errors['nom_err'])) ? 'is-invalid' : ''; ?>" id="nom" name="nom" value="<?php echo htmlspecialchars($nom ?? ''); ?>" required>
                        <div class="invalid-feedback"><?php echo $errors['nom_err'] ?? ''; ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="prenom" class="form-label"><?php echo $tr('firstname'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo (!empty($errors['prenom_err'])) ? 'is-invalid' : ''; ?>" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom ?? ''); ?>" required>
                        <div class="invalid-feedback"><?php echo $errors['prenom_err'] ?? ''; ?></div>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date_naissance" class="form-label"><?php echo $tr('date_of_birth'); ?> <span class="text-danger">*</span></label>
                        <input type="date" class="form-control <?php echo (!empty($errors['date_naissance_err'])) ? 'is-invalid' : ''; ?>" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($date_naissance ?? ''); ?>" required>
                        <div class="invalid-feedback"><?php echo $errors['date_naissance_err'] ?? ''; ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sexe" class="form-label"><?php echo $tr('gender'); ?> <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo (!empty($errors['sexe_err'])) ? 'is-invalid' : ''; ?>" id="sexe" name="sexe" required>
                            <option value="M" <?php echo (isset($sexe) && $sexe == 'M') ? 'selected' : ''; ?>><?php echo $tr('male'); ?></option>
                            <option value="F" <?php echo (isset($sexe) && $sexe == 'F') ? 'selected' : ''; ?>><?php echo $tr('female'); ?></option>
                        </select>
                        <div class="invalid-feedback"><?php echo $errors['sexe_err'] ?? ''; ?></div>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="serie_id" class="form-label"><?php echo $tr('serie'); ?> <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo (!empty($errors['serie_id_err'])) ? 'is-invalid' : ''; ?>" id="serie_id" name="serie_id" required>
                            <option value=""><?php echo $tr('select_serie'); ?></option>
                            <?php if (!empty($series)): ?>
                                <?php foreach ($series as $serie_item): ?>
                                    <option value="<?php echo $serie_item->id; ?>" <?php echo (isset($serie_id) && $serie_id == $serie_item->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($serie_item->libelle); ?> (<?php echo htmlspecialchars($serie_item->code); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback"><?php echo $errors['serie_id_err'] ?? ''; ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lycee_id" class="form-label"><?php echo $tr('lycee_origin'); ?> <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo (!empty($errors['lycee_id_err'])) ? 'is-invalid' : ''; ?>" id="lycee_id" name="lycee_id" required>
                            <option value=""><?php echo $tr('select_lycee'); ?></option>
                             <?php if (!empty($lycees)): ?>
                                <?php foreach ($lycees as $lycee_item): ?>
                                    <option value="<?php echo $lycee_item->id; ?>" <?php echo (isset($lycee_id) && $lycee_id == $lycee_item->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lycee_item->nom_lycee); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback"><?php echo $errors['lycee_id_err'] ?? ''; ?></div>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="annee_scolaire_id_display" class="form-label"><?php echo $tr('academic_year'); ?></label>
                    <input type="text" class="form-control" id="annee_scolaire_id_display" value="<?php echo htmlspecialchars($annee_scolaire_libelle ?? ''); ?>" readonly>
                     <small class="form-text text-muted"><?php echo $tr('academic_year_cannot_be_changed_here'); // L'année scolaire d'un enrôlement ne peut pas être modifiée ici. ?></small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label"><?php echo $tr('student_photo'); ?></label>
                    <div id="my_camera_edit" style="width:320px; height:240px; border:1px solid #ccc; margin:auto; <?php echo !empty($photo_path) ? 'display:none;' : ''; ?>"></div>
                    <div id="results_edit" class="mt-2" style="width:320px; height:240px; border:1px solid #ccc; margin:auto; <?php echo empty($photo_path) ? 'display:none;' : ''; ?>">
                        <?php if (!empty($photo_path)): ?>
                            <img src="<?php echo $uploadBaseUrl . htmlspecialchars($photo_path); ?>" style="width:320px; height:240px; object-fit:contain;"/>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="webcam_photo_data" id="webcam_photo_data_edit">
                    <button type="button" class="btn btn-info mt-2" onClick="take_snapshot_edit()"><i class="fas fa-camera"></i> <?php echo $tr('take_snapshot'); ?></button>
                    <button type="button" class="btn btn-secondary mt-2" onClick="reset_camera_edit()"><i class="fas fa-redo"></i> <?php echo $tr('retake_photo'); ?></button>
                </div>
                <div class="mb-3">
                    <label for="photo_upload" class="form-label"><?php echo $tr('or_upload_photo'); ?></label>
                    <input type="file" class="form-control <?php echo (!empty($errors['photo_err'])) ? 'is-invalid' : ''; ?>" id="photo_upload" name="photo_upload" accept="image/png, image/jpeg, image/gif">
                     <div class="invalid-feedback"><?php echo $errors['photo_err'] ?? ''; ?></div>
                </div>
                 <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="delete_photo" value="1" id="delete_photo">
                    <label class="form-check-label" for="delete_photo"><?php echo $tr('delete_current_photo_if_new'); // Supprimer la photo actuelle si une nouvelle est fournie ou capturée ?></label>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <h4><?php echo $tr('fingerprints_data'); ?></h4>
                <p class="text-muted"><small><?php echo $tr('fingerprints_capture_note'); ?></small></p>
            </div>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <div class="col-md-4 col-lg-2 mb-2">
                    <label for="empreinte<?php echo $i; ?>" class="form-label"><?php echo $tr('fingerprint'); ?> <?php echo $i; ?></label>
                    <input type="text" class="form-control form-control-sm" id="empreinte<?php echo $i; ?>" name="empreinte<?php echo $i; ?>" value="<?php echo htmlspecialchars(${'empreinte'.$i} ?? ''); ?>">
                </div>
            <?php endfor; ?>
        </div>

        <hr>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> <?php echo $tr('save_changes'); ?></button>
        <a href="<?php echo $app_url; ?>/eleves/index/<?php echo htmlspecialchars($annee_scolaire_id ?? ''); ?>" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>

<script src="<?php echo $app_url; ?>/assets/js/webcam.min.js"></script>
<script language="JavaScript">
    // Configuration pour le formulaire d'édition
    Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90
    });

    var cameraAttached = false;

    function setup_camera_edit() {
        if (!cameraAttached && document.getElementById('my_camera_edit').style.display !== 'none') {
            Webcam.attach('#my_camera_edit');
            cameraAttached = true;
        }
    }

    function take_snapshot_edit() {
        if (!cameraAttached) setup_camera_edit(); // S'assurer que la caméra est attachée
        Webcam.snap(function(data_uri) {
            document.getElementById('results_edit').innerHTML = '<img src="'+data_uri+'" style="width:320px; height:240px; object-fit:contain;"/>';
            document.getElementById('results_edit').style.display = 'block';
            document.getElementById('my_camera_edit').style.display = 'none';
            document.getElementById('webcam_photo_data_edit').value = data_uri;
            if(cameraAttached) Webcam.reset(); // Libérer la caméra après capture pour l'upload
            cameraAttached = false;
        });
    }

    function reset_camera_edit() {
        if(cameraAttached) Webcam.reset();
        cameraAttached = false;
        document.getElementById('results_edit').innerHTML = '';
        document.getElementById('results_edit').style.display = 'none';
        document.getElementById('my_camera_edit').style.display = 'block';
        document.getElementById('webcam_photo_data_edit').value = '';
        setup_camera_edit(); // Ré-attacher pour une nouvelle prise
    }

    // Attacher la caméra seulement si la div est visible initialement (pas de photo existante)
    if (document.getElementById('my_camera_edit').style.display !== 'none') {
        setup_camera_edit();
    }
</script>

<?php
// Traductions
// 'editing_for_year' => "Modification pour l'année scolaire",
// 'academic_year_cannot_be_changed_here' => "L'année scolaire d'un enrôlement ne peut pas être modifiée ici.",
// 'delete_current_photo_if_new' => "Supprimer la photo actuelle si une nouvelle est fournie/capturée",
?>
