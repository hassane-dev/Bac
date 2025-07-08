<?php
// app/views/eleves/create.php
// $title, $annee_scolaire_id, $annee_scolaire_libelle, $series, $lycees, $tr, $errors, et les champs pré-remplis
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('add_student')); ?></h2>
    <p><?php echo $tr('enrolling_for_year'); // Enrôlement pour l'année scolaire ?>: <strong><?php echo htmlspecialchars($annee_scolaire_libelle ?? ''); ?></strong></p>

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


    <form action="<?php echo $app_url; ?>/eleves/store" method="POST" enctype="multipart/form-data">
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
                            <option value=""><?php echo $tr('select_serie'); // Sélectionner une série ?></option>
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
                            <option value=""><?php echo $tr('select_lycee'); // Sélectionner un lycée ?></option>
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
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label"><?php echo $tr('student_photo'); // Photo de l'élève ?></label>
                    <div id="my_camera" style="width:320px; height:240px; border:1px solid #ccc; margin:auto;"></div>
                    <div id="results" class="mt-2" style="width:320px; height:240px; border:1px solid #ccc; margin:auto; display:none;"></div>
                    <input type="hidden" name="webcam_photo_data" id="webcam_photo_data">
                    <button type="button" class="btn btn-info mt-2" onClick="take_snapshot()"><i class="fas fa-camera"></i> <?php echo $tr('take_snapshot'); // Prendre la photo ?></button>
                    <button type="button" class="btn btn-secondary mt-2" onClick="reset_camera()"><i class="fas fa-redo"></i> <?php echo $tr('retake_photo'); // Reprendre ?></button>
                </div>
                <div class="mb-3">
                    <label for="photo_upload" class="form-label"><?php echo $tr('or_upload_photo'); // Ou téléverser une photo ?></label>
                    <input type="file" class="form-control <?php echo (!empty($errors['photo_err'])) ? 'is-invalid' : ''; ?>" id="photo_upload" name="photo_upload" accept="image/png, image/jpeg, image/gif">
                     <div class="invalid-feedback"><?php echo $errors['photo_err'] ?? ''; ?></div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <h4><?php echo $tr('fingerprints_data'); // Données d'empreintes (placeholders) ?></h4>
                <p class="text-muted"><small><?php echo $tr('fingerprints_capture_note'); // La capture réelle des empreintes nécessitera un dispositif et une intégration spécifiques. Entrez des identifiants textuels pour l'instant. ?></small></p>
            </div>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <div class="col-md-4 col-lg-2 mb-2">
                    <label for="empreinte<?php echo $i; ?>" class="form-label"><?php echo $tr('fingerprint'); // Empreinte ?> <?php echo $i; ?></label>
                    <input type="text" class="form-control form-control-sm" id="empreinte<?php echo $i; ?>" name="empreinte<?php echo $i; ?>" value="<?php echo htmlspecialchars(${'empreinte'.$i} ?? ''); ?>">
                </div>
            <?php endfor; ?>
        </div>

        <hr>
        <button type="submit" class="btn btn-success"><i class="fas fa-user-plus"></i> <?php echo $tr('add_student'); ?></button>
        <a href="<?php echo $app_url; ?>/eleves/index/<?php echo $annee_scolaire_id; ?>" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>

<script src="<?php echo $app_url; ?>/assets/js/webcam.min.js"></script>
<script language="JavaScript">
    Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90,
        // force_flash: true, // Décommenter si HTML5 ne fonctionne pas et que Flash est installé
    });
    Webcam.attach('#my_camera');

    function take_snapshot() {
        Webcam.snap(function(data_uri) {
            document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
            document.getElementById('results').style.display = 'block';
            document.getElementById('my_camera').style.display = 'none';
            document.getElementById('webcam_photo_data').value = data_uri;
        });
    }
    function reset_camera() {
        document.getElementById('results').innerHTML = '';
        document.getElementById('results').style.display = 'none';
        document.getElementById('my_camera').style.display = 'block';
        document.getElementById('webcam_photo_data').value = '';
        // Webcam.reset(); // Peut être nécessaire si la caméra ne se réinitialise pas bien
    }
</script>

<?php
// Traductions
// 'enrolling_for_year' => "Enrôlement pour l'année scolaire",
// 'select_serie' => 'Sélectionner une série',
// 'select_lycee' => 'Sélectionner un lycée',
// 'student_photo' => "Photo de l'élève",
// 'take_snapshot' => 'Prendre la photo',
// 'retake_photo' => 'Reprendre',
// 'or_upload_photo' => 'Ou téléverser une photo (max 2MB)',
// 'fingerprints_data' => "Données d'empreintes (placeholders)",
// 'fingerprints_capture_note' => "La capture réelle des empreintes nécessitera un dispositif et une intégration spécifiques. Entrez des identifiants textuels pour l'instant.",
// 'fingerprint' => 'Empreinte',
// 'matricule_required' => 'Le matricule est requis.',
// 'matricule_exists_for_year' => 'Ce matricule existe déjà pour cette année scolaire.',
// 'serie_required' => 'La série est requise.',
// 'lycee_required' => "Le lycée d'origine est requis.",
// 'no_active_academic_year_enroll' => "Aucune année scolaire n'est active. Veuillez en activer une avant d'enrôler des élèves.",
// 'error_saving_webcam_photo' => 'Erreur lors de la sauvegarde de la photo webcam.',
// 'student_added_successfully' => 'Élève ajouté avec succès.',
// 'error_adding_student' => "Erreur lors de l'ajout de l'élève.",
?>
