<?php
// app/views/eleves/select_context.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('select_enrollment_context')); ?></h2>

    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors['context_err'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $errors['context_err']; ?>
        </div>
    <?php endif; ?>


    <form action="<?php echo $app_url; ?>/eleves/selectContext" method="POST">
        <div class="mb-3">
            <label for="annee_scolaire_display" class="form-label"><?php echo $tr('academic_year'); ?></label>
            <input type="text" class="form-control" id="annee_scolaire_display" value="<?php echo htmlspecialchars($annee_scolaire_libelle ?? ''); ?>" readonly>
            <?php // L'ID de l'année scolaire active est géré en session ou dans le contrôleur, pas besoin de champ caché ici si le contrôleur le récupère directement. ?>
        </div>

        <div class="mb-3">
            <label for="lycee_id" class="form-label"><?php echo $tr('lycee'); ?> <span class="text-danger">*</span></label>
            <select class="form-select <?php echo (!empty($errors['lycee_id_err'])) ? 'is-invalid' : ''; ?>" id="lycee_id" name="lycee_id" required>
                <option value=""><?php echo $tr('select_lycee'); ?></option>
                <?php if (!empty($lycees)): ?>
                    <?php foreach ($lycees as $lycee_item): ?>
                        <option value="<?php echo $lycee_item->id; ?>" <?php echo (isset($selected_lycee_id) && $selected_lycee_id == $lycee_item->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lycee_item->nom_lycee); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <div class="invalid-feedback"><?php echo $errors['lycee_id_err'] ?? ''; ?></div>
        </div>

        <?php
        // Le centre sera déterminé par le contrôleur après soumission du lycée.
        // On pourrait ajouter un affichage dynamique du centre ici avec AJAX si souhaité,
        // mais pour une première version, le contrôleur gérera la redirection vers le formulaire d'enrôlement
        // avec le contexte complet (y compris le centre) en session.
        ?>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-arrow-right"></i> <?php echo $tr('continue_to_enrollment'); // Continuer vers l'enrôlement ?>
        </button>
         <a href="<?php echo $app_url; ?>/dashboard" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>

<?php
// Traductions à ajouter/vérifier
// fr.php:
// 'select_enrollment_context' => "Sélectionner le Contexte d'Enrôlement",
// 'continue_to_enrollment' => "Continuer vers l'enrôlement",
// 'center_not_assigned_for_lycee_year' => "Aucun centre d'examen n'est assigné à ce lycée pour l'année scolaire active, ou le lycée/centre n'a pas de code défini. Veuillez vérifier les assignations et les codes des centres.",
// 'enrollment_context_not_set' => "Le contexte d'enrôlement (année, lycée, centre) n'est pas défini. Veuillez recommencer la sélection.",
// 'center_code_missing_in_context' => "Le code du centre est manquant dans le contexte d'enrôlement. Vérifiez la configuration du centre.",
// 'unknown_center' => 'Centre inconnu',

// ar.php:
// 'select_enrollment_context' => "تحديد سياق التسجيل",
// 'continue_to_enrollment' => "متابعة إلى التسجيل",
// 'center_not_assigned_for_lycee_year' => "لم يتم تعيين مركز امتحان لهذه الثانوية للسنة الدراسية النشطة، أو أن الثانوية/المركز ليس له رمز محدد. يرجى التحقق من التعيينات ورموز المراكز.",
// 'enrollment_context_not_set' => "سياق التسجيل (السنة، الثانوية، المركز) غير محدد. يرجى إعادة التحديد.",
// 'center_code_missing_in_context' => "رمز المركز مفقود في سياق التسجيل. تحقق من تكوين المركز.",
// 'unknown_center' => 'مركز غير معروف',
?>
