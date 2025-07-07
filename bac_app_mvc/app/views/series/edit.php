<?php
// app/views/series/edit.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('edit_serie')); ?>: <?php echo htmlspecialchars($code ?? ''); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/series/update/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST">
        <div class="mb-3">
            <label for="code" class="form-label"><?php echo $tr('series_code'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($code_err)) ? 'is-invalid' : ''; ?>" id="code" name="code" value="<?php echo htmlspecialchars($code ?? ''); ?>" required maxlength="10">
            <div class="invalid-feedback"><?php echo $code_err ?? ''; ?></div>
        </div>
        <div class="mb-3">
            <label for="libelle" class="form-label"><?php echo $tr('series_label'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo (!empty($libelle_err)) ? 'is-invalid' : ''; ?>" id="libelle" name="libelle" value="<?php echo htmlspecialchars($libelle ?? ''); ?>" required maxlength="100">
            <div class="invalid-feedback"><?php echo $libelle_err ?? ''; ?></div>
        </div>

        <!-- Section pour les matières associées (Étape 3 du plan actuel) -->
        <div class="mt-4 card">
            <div class="card-header">
                <h4><?php echo $tr('associated_subjects_config'); ?></h4>
            </div>
            <div class="card-body">
                <div id="matieres-container">
                    <?php if (isset($serie_matieres_details) && !empty($serie_matieres_details)): ?>
                        <?php foreach ($serie_matieres_details as $index => $detail): ?>
                            <div class="row matiere-item mb-2 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label visually-hidden"><?php echo $tr('subject'); // Matière ?></label>
                                    <select name="matieres[<?php echo $index; ?>][matiere_id]" class="form-select form-select-sm">
                                        <option value=""><?php echo $tr('select_subject'); ?></option>
                                        <?php if(isset($all_matieres)) foreach ($all_matieres as $matiere): ?>
                                            <option value="<?php echo $matiere->id; ?>" <?php echo (isset($detail->matiere_id) && $detail->matiere_id == $matiere->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($matiere->nom); ?> (<?php echo htmlspecialchars($matiere->code); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                     <label class="form-label visually-hidden"><?php echo $tr('coefficient'); ?></label>
                                    <input type="number" step="0.1" min="0" name="matieres[<?php echo $index; ?>][coefficient]" class="form-control form-control-sm" placeholder="<?php echo $tr('coefficient'); ?>" value="<?php echo htmlspecialchars($detail->coefficient ?? '1'); ?>">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="matieres[<?php echo $index; ?>][obligatoire]" value="1" class="form-check-input" id="obligatoire_<?php echo $index; ?>" <?php echo (isset($detail->obligatoire) && $detail->obligatoire) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="obligatoire_<?php echo $index; ?>"><?php echo $tr('mandatory_short'); ?></label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-matiere-btn w-100"><?php echo $tr('remove'); ?></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-matiere-btn" class="btn btn-info btn-sm mt-2">
                    <i class="fas fa-plus"></i> <?php echo $tr('add_subject_to_serie'); ?>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-3">
            <i class="fas fa-save"></i> <?php echo $tr('save_changes'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/series" class="btn btn-secondary mt-3">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>

<?php
// JavaScript pour ajouter/supprimer dynamiquement des matières
// Placé ici pour plus de clarté, pourrait être dans un fichier JS séparé et inclus.
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('matieres-container');
    const addBtn = document.getElementById('add-matiere-btn');
    // S'assurer que $all_matieres est disponible et encodé en JSON pour JS
    // Le contrôleur doit passer $all_matieres à la vue.
    const allMatieres = <?php echo json_encode($all_matieres ?? []); ?>;
    let matiereIndex = <?php echo isset($serie_matieres_details) ? count($serie_matieres_details) : 0; ?>;

    if (addBtn && container) {
        addBtn.addEventListener('click', function() {
            const newItem = document.createElement('div');
            newItem.classList.add('row', 'matiere-item', 'mb-2', 'align-items-center');

            let optionsHtml = '<option value=""><?php echo $tr('select_subject'); ?></option>';
            allMatieres.forEach(function(matiere) {
                optionsHtml += `<option value="${matiere.id}">${matiere.nom} (${matiere.code})</option>`;
            });

            newItem.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label visually-hidden"><?php echo $tr('subject'); ?></label>
                    <select name="matieres[${matiereIndex}][matiere_id]" class="form-select form-select-sm" required>
                        ${optionsHtml}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label visually-hidden"><?php echo $tr('coefficient'); ?></label>
                    <input type="number" step="0.1" min="0" name="matieres[${matiereIndex}][coefficient]" class="form-control form-control-sm" placeholder="<?php echo $tr('coefficient'); ?>" value="1" required>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="matieres[${matiereIndex}][obligatoire]" value="1" class="form-check-input" id="obligatoire_new_${matiereIndex}" checked>
                        <label class="form-check-label" for="obligatoire_new_${matiereIndex}"><?php echo $tr('mandatory_short'); ?></label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger remove-matiere-btn w-100"><?php echo $tr('remove'); ?></button>
                </div>
            `;
            container.appendChild(newItem);
            matiereIndex++;
        });
    }

    if (container) {
        container.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-matiere-btn')) {
                e.target.closest('.matiere-item').remove();
            }
        });
    }
});
</script>
```
