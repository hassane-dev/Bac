<?php
// app/views/centres/edit.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('edit_centre')); ?>: <?php echo htmlspecialchars($nom_centre ?? ''); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
     <?php if (!empty($_SESSION['message'])) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/centres/update/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nom_centre" class="form-label"><?php echo $tr('centre_name'); ?> <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php echo (!empty($nom_centre_err)) ? 'is-invalid' : ''; ?>" id="nom_centre" name="nom_centre" value="<?php echo htmlspecialchars($nom_centre ?? ''); ?>" required maxlength="100">
                <div class="invalid-feedback"><?php echo $nom_centre_err ?? ''; ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="code_centre" class="form-label"><?php echo $tr('centre_code'); ?></label>
                <input type="text" class="form-control <?php echo (!empty($code_centre_err)) ? 'is-invalid' : ''; ?>" id="code_centre" name="code_centre" value="<?php echo htmlspecialchars($code_centre ?? ''); ?>" maxlength="20">
                <div class="invalid-feedback"><?php echo $code_centre_err ?? ''; ?></div>
                <small class="form-text text-muted"><?php echo $tr('centre_code_desc'); ?></small>
            </div>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label"><?php echo $tr('description'); ?></label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_changes'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/centres" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>

    <hr class="my-4">

    <!-- Section Gestion des Salles (sera implémentée à l'étape suivante) -->
    <div class="mt-4 card">
        <div class="card-header">
            <h4><?php echo $tr('manage_halls'); ?></h4>
        </div>
        <div class="card-body">
            <h5><?php echo $tr('add_new_hall'); // Ajouter une nouvelle salle ?></h5>
            <?php
                // Récupérer les données et erreurs du formulaire de salle depuis la session si elles existent
                $form_data_salle = $_SESSION['form_data_salle'] ?? ['numero_salle' => '', 'capacite' => '', 'salle_description' => ''];
                $form_errors_salle = $_SESSION['form_errors_salle'] ?? [];
                unset($_SESSION['form_data_salle'], $_SESSION['form_errors_salle']);
            ?>
            <form action="<?php echo $app_url; ?>/centres/storeSalle/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST" class="mb-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="numero_salle" class="form-label"><?php echo $tr('hall_number_name'); // Numéro/Nom Salle ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo (!empty($form_errors_salle['salle_numero_err'])) ? 'is-invalid' : ''; ?>" id="numero_salle" name="numero_salle" value="<?php echo htmlspecialchars($form_data_salle['numero_salle']); ?>" required>
                        <div class="invalid-feedback"><?php echo $form_errors_salle['salle_numero_err'] ?? ''; ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="capacite" class="form-label"><?php echo $tr('capacity'); // Capacité ?> <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?php echo (!empty($form_errors_salle['salle_capacite_err'])) ? 'is-invalid' : ''; ?>" id="capacite" name="capacite" value="<?php echo htmlspecialchars($form_data_salle['capacite']); ?>" required min="1">
                        <div class="invalid-feedback"><?php echo $form_errors_salle['salle_capacite_err'] ?? ''; ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="salle_description" class="form-label"><?php echo $tr('description'); ?></label>
                        <input type="text" class="form-control" id="salle_description" name="salle_description" value="<?php echo htmlspecialchars($form_data_salle['salle_description']); ?>">
                    </div>
                    <div class="col-md-2 mb-3 align-self-end">
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus"></i> <?php echo $tr('add_hall'); // Ajouter Salle ?></button>
                    </div>
                </div>
            </form>

            <h5><?php echo $tr('existing_halls'); // Salles existantes ?></h5>
            <?php if (empty($salles)): ?>
                <p><?php echo $tr('no_halls_for_this_center'); // Aucune salle définie pour ce centre. ?></p>
            <?php else: ?>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th><?php echo $tr('hall_number_name'); ?></th>
                            <th><?php echo $tr('capacity'); ?></th>
                            <th><?php echo $tr('description'); ?></th>
                            <th><?php echo $tr('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salles as $salle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($salle->numero_salle); ?></td>
                                <td><?php echo htmlspecialchars($salle->capacite); ?></td>
                                <td><?php echo htmlspecialchars($salle->description ?? ''); ?></td>
                                <td>
                                    <?php /* <a href="<?php echo $app_url; ?>/salles/edit/<?php echo $salle->id; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> */ ?>
                                    <form action="<?php echo $app_url; ?>/centres/deleteSalle/<?php echo $salle->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_hall'); ?>');">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section Gestion des Assignations (sera implémentée plus tard) -->
    <div class="mt-4 card">
        <div class="card-header">
            <h4><?php echo $tr('manage_assignments'); ?></h4>
        </div>
        <div class="card-body">
            <form action="<?php echo $app_url; ?>/centres/loadAssignations/<?php echo htmlspecialchars($id ?? ''); ?>" method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <label for="annee_scolaire_id_select" class="form-label"><?php echo $tr('select_academic_year_for_assignments'); // Sélectionner l'année scolaire pour les assignations ?></label>
                        <select name="annee_scolaire_id_assign_display" id="annee_scolaire_id_select" class="form-select" onchange="this.form.action = '<?php echo $app_url; ?>/centres/loadAssignations/<?php echo htmlspecialchars($id ?? ''); ?>/' + this.value; this.form.submit();">
                            <option value=""><?php echo $tr('select_one'); // Sélectionner... ?></option>
                            <?php if(!empty($annees_scolaires)): ?>
                                <?php foreach($annees_scolaires as $annee): ?>
                                    <option value="<?php echo $annee->id; ?>" <?php echo (isset($selected_annee_scolaire_id) && $selected_annee_scolaire_id == $annee->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($annee->libelle); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </form>

            <?php if(isset($selected_annee_scolaire_id) && $selected_annee_scolaire_id): ?>
                <h5><?php echo $tr('add_assignment_for_year') . ' ' . htmlspecialchars( ($annees_scolaires_map[$selected_annee_scolaire_id] ?? '') ); // Ajouter une assignation pour l'année... ?></h5>
                <form action="<?php echo $app_url; ?>/centres/addAssignation/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST" class="mb-4">
                    <input type="hidden" name="annee_scolaire_id_assign" value="<?php echo htmlspecialchars($selected_annee_scolaire_id); ?>">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label for="lycee_id_assign" class="form-label"><?php echo $tr('lycee'); // Lycée (Optionnel) ?></label>
                            <select name="lycee_id" id="lycee_id_assign" class="form-select">
                                <option value=""><?php echo $tr('select_lycee_optional'); // Sélectionner un lycée (Optionnel) ?></option>
                                <?php if(!empty($lycees)) foreach ($lycees as $lycee): ?>
                                    <option value="<?php echo $lycee->id; ?>"><?php echo htmlspecialchars($lycee->nom_lycee); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="serie_id_assign" class="form-label"><?php echo $tr('serie'); // Série (Optionnel) ?></label>
                            <select name="serie_id" id="serie_id_assign" class="form-select">
                                <option value=""><?php echo $tr('select_serie_optional'); // Sélectionner une série (Optionnel) ?></option>
                                <?php if(!empty($series)) foreach ($series as $serie_item): ?>
                                    <option value="<?php echo $serie_item->id; ?>"><?php echo htmlspecialchars($serie_item->libelle); ?> (<?php echo htmlspecialchars($serie_item->code); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus"></i> <?php echo $tr('add_assignment'); // Ajouter l'assignation ?></button>
                        </div>
                    </div>
                     <small class="form-text text-muted"><?php echo $tr('assign_help_text'); // Vous pouvez assigner un lycée entier, une série spécifique, ou un lycée avec une série spécifique. ?></small>
                </form>

                <h6><?php echo $tr('current_assignments_for_year'); // Assignations actuelles pour l'année sélectionnée ?>:</h6>
                <?php if (empty($assignations)): ?>
                    <p><?php echo $tr('no_assignments_for_this_year_center'); // Aucune assignation pour ce centre et cette année. ?></p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($assignations as $assign): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <?php
                                    $assignText = '';
                                    if ($assign->lycee_id) $assignText .= $tr('lycee') . ': ' . htmlspecialchars($assign->nom_lycee ?? 'N/A');
                                    if ($assign->serie_id) $assignText .= ($assignText ? ' - ' : '') . $tr('serie') . ': ' . htmlspecialchars($assign->serie_libelle ?? $assign->serie_code ?? 'N/A');
                                    echo $assignText ?: $tr('global_assignment'); // Assignation globale (rare)
                                    ?>
                                </span>
                                <form action="<?php echo $app_url; ?>/centres/deleteAssignation/<?php echo $assign->id; ?>" method="POST" onsubmit="return confirm('<?php echo $tr('are_you_sure_delete_assignment'); ?>');">
                                    <?php // Pour une meilleure redirection, on pourrait ajouter centre_id et annee_id ici en hidden ou modifier la route ?>
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php else: ?>
                <p><?php echo $tr('select_year_to_manage_assignments'); // Veuillez sélectionner une année scolaire pour gérer les assignations. ?></p>
            <?php endif; ?>
        </div>
    </div>

</div>
