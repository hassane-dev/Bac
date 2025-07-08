<?php
// app/views/eleves/index.php
$uploadBaseUrl = $app_url . '/'; // $app_url est déjà la racine publique
?>

<div class="container-fluid mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('student_list')); ?></h2>

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

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="<?php echo $app_url; ?>/eleves/create" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> <?php echo $tr('add_new_student'); // Ajouter un nouvel élève ?>
            </a>
        </div>
        <div class="col-md-6">
            <form method="GET" action="<?php echo $app_url; ?>/eleves/index" class="row g-3 justify-content-end">
                <div class="col-auto">
                    <label for="annee_scolaire_id_filter" class="col-form-label"><?php echo $tr('filter_by_academic_year'); // Filtrer par année scolaire: ?></label>
                </div>
                <div class="col-auto">
                    <select name="annee_scolaire_id_filter" id="annee_scolaire_id_filter" class="form-select" onchange="this.form.submit()">
                        <option value=""><?php echo $tr('all_years'); // Toutes les années ?></option>
                        <?php if (!empty($annees_scolaires)): ?>
                            <?php foreach ($annees_scolaires as $annee): ?>
                                <option value="<?php echo $annee->id; ?>" <?php echo (isset($selected_annee_id) && $selected_annee_id == $annee->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($annee->libelle); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>


    <?php if (empty($eleves)): ?>
        <p><?php echo $tr('no_students_found_for_year'); // Aucun élève trouvé pour cette année scolaire. ?></p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo $tr('photo_short'); // Photo ?></th>
                        <th><?php echo $tr('matricule'); ?></th>
                        <th><?php echo $tr('lastname'); ?></th>
                        <th><?php echo $tr('firstname'); ?></th>
                        <th><?php echo $tr('date_of_birth_short'); // Né(e) le ?></th>
                        <th><?php echo $tr('gender_short'); // Sexe ?></th>
                        <th><?php echo $tr('serie'); ?></th>
                        <th><?php echo $tr('lycee_origin'); // Lycée d'origine ?></th>
                        <th><?php echo $tr('exam_center'); // Centre d'examen ?></th>
                        <th><?php echo $tr('enrollment_date_short'); // Enrôlé le ?></th>
                        <th><?php echo $tr('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eleves as $eleve): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($eleve->id); ?></td>
                            <td>
                                <?php if (!empty($eleve->photo)): ?>
                                    <img src="<?php echo $uploadBaseUrl . htmlspecialchars($eleve->photo); ?>" alt="Photo de <?php echo htmlspecialchars($eleve->nom); ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user fa-2x text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($eleve->matricule); ?></td>
                            <td><?php echo htmlspecialchars($eleve->nom); ?></td>
                            <td><?php echo htmlspecialchars($eleve->prenom); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($eleve->date_naissance))); ?></td>
                            <td><?php echo htmlspecialchars($eleve->sexe == 'M' ? $tr('male_short') : $tr('female_short')); // M / F ?></td>
                            <td><?php echo htmlspecialchars($eleve->serie_libelle ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($eleve->nom_lycee ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($eleve->nom_centre ?? $tr('not_assigned_short')); // Non assigné ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($eleve->date_enrolement))); ?></td>
                            <td>
                                <a href="<?php echo $app_url; ?>/eleves/edit/<?php echo $eleve->id; ?>" class="btn btn-sm btn-warning mb-1" title="<?php echo $tr('edit'); ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php /* <a href="<?php echo $app_url; ?>/eleves/show/<?php echo $eleve->id; ?>" class="btn btn-sm btn-info mb-1" title="<?php echo $tr('view_details'); ?>">
                                    <i class="fas fa-eye"></i>
                                </a> */ ?>
                                <form action="<?php echo $app_url; ?>/eleves/delete/<?php echo $eleve->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_student'); ?>');">
                                    <button type="submit" class="btn btn-sm btn-danger mb-1" title="<?php echo $tr('delete'); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
     <a href="<?php echo $app_url; ?>/dashboard" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left"></i> <?php echo $tr('back_to_dashboard'); ?>
    </a>
</div>

<?php
// Traductions
// 'filter_by_academic_year' => 'Filtrer par année scolaire:',
// 'all_years' => 'Toutes les années',
// 'no_students_found_for_year' => 'Aucun élève trouvé pour cette année scolaire.',
// 'photo_short' => 'Photo',
// 'date_of_birth_short' => 'Né(e) le',
// 'gender_short' => 'Sexe',
// 'lycee_origin' => "Lycée d'origine",
// 'exam_center' => "Centre d'examen",
// 'enrollment_date_short' => 'Enrôlé le',
// 'male_short' => 'M',
// 'female_short' => 'F',
// 'not_assigned_short' => 'Non assigné',
// 'are_you_sure_delete_student' => 'Êtes-vous sûr de vouloir supprimer cet élève ? Toutes ses notes seront également supprimées.',
// 'student_added_successfully' => 'Élève ajouté avec succès.',
// 'error_adding_student' => "Erreur lors de l'ajout de l'élève.",
// 'student_updated_successfully' => 'Élève mis à jour avec succès.',
// 'error_updating_student' => "Erreur lors de la mise à jour de l'élève.",
// 'student_deleted_successfully' => 'Élève supprimé avec succès.',
// 'error_deleting_student' => "Erreur lors de la suppression de l'élève.",
// 'student_not_found' => 'Élève non trouvé.',
// 'matricule_required' => 'Le matricule est requis.',
// 'matricule_exists_for_year' => 'Ce matricule existe déjà pour cette année scolaire.',
// 'serie_required' => 'La série est requise.',
// 'lycee_required' => 'Le lycée d\'origine est requis.',
// 'no_active_academic_year_enroll' => "Aucune année scolaire n'est active. Veuillez en activer une avant d'enrôler des élèves.",
// 'error_saving_webcam_photo' => 'Erreur lors de la sauvegarde de la photo webcam.',
// 'add_new_student' => 'Ajouter un nouvel élève',
?>
