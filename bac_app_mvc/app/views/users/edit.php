<?php
// app/views/users/edit.php
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($title ?? $tr('edit_user')); ?>: <?php echo htmlspecialchars($username ?? ''); ?></h2>

    <?php if (!empty($photo_err)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $photo_err; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $app_url; ?>/users/update/<?php echo htmlspecialchars($id ?? ''); ?>" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="username" class="form-label"><?php echo $tr('username'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    <div class="invalid-feedback"><?php echo $username_err ?? ''; ?></div>
                </div>
                <div class="mb-3">
                    <label for="mot_de_passe" class="form-label"><?php echo $tr('password'); ?> (<?php echo $tr('leave_blank_if_no_change'); ?>)</label>
                    <input type="password" class="form-control <?php echo (!empty($mot_de_passe_err)) ? 'is-invalid' : ''; ?>" id="mot_de_passe" name="mot_de_passe">
                    <div class="invalid-feedback"><?php echo $mot_de_passe_err ?? ''; ?></div>
                </div>
                <div class="mb-3">
                    <label for="confirm_mot_de_passe" class="form-label"><?php echo $tr('confirm_password'); ?></label>
                    <input type="password" class="form-control <?php echo (!empty($confirm_mot_de_passe_err)) ? 'is-invalid' : ''; ?>" id="confirm_mot_de_passe" name="confirm_mot_de_passe">
                    <div class="invalid-feedback"><?php echo $confirm_mot_de_passe_err ?? ''; ?></div>
                </div>
                <div class="mb-3">
                    <label for="role_id" class="form-label"><?php echo $tr('role'); ?> <span class="text-danger">*</span></label>
                    <select class="form-select <?php echo (!empty($role_id_err)) ? 'is-invalid' : ''; ?>" id="role_id" name="role_id" required <?php echo (isset($id) && $id == 1) ? 'disabled' : ''; ?>>
                        <option value=""><?php echo $tr('select_role'); ?></option>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role_item): ?>
                                <option value="<?php echo htmlspecialchars($role_item->id); ?>" <?php echo (isset($role_id) && $role_id == $role_item->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role_item->nom_role); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($id) && $id == 1): ?>
                        <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($role_id); ?>" />
                    <?php endif; ?>
                    <div class="invalid-feedback"><?php echo $role_id_err ?? ''; ?></div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label"><?php echo $tr('email'); ?></label>
                    <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <div class="invalid-feedback"><?php echo $email_err ?? ''; ?></div>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        <?php echo (isset($is_active) && $is_active) ? 'checked' : ''; ?>
                        <?php echo ( (isset($id) && $id == 1) || (isset($_SESSION['user_id']) && isset($id) && $id == $_SESSION['user_id']) ) ? 'disabled' : ''; ?> >
                    <label class="form-check-label" for="is_active"><?php echo $tr('is_active_user'); ?></label>
                     <?php if ( (isset($id) && $id == 1) || (isset($_SESSION['user_id']) && isset($id) && $id == $_SESSION['user_id']) ): ?>
                        <input type="hidden" name="is_active" value="<?php echo isset($is_active) && $is_active ? '1' : '0'; ?>" />
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="nom" class="form-label"><?php echo $tr('lastname'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo (!empty($nom_err)) ? 'is-invalid' : ''; ?>" id="nom" name="nom" value="<?php echo htmlspecialchars($nom ?? ''); ?>" required>
                    <div class="invalid-feedback"><?php echo $nom_err ?? ''; ?></div>
                </div>
                <div class="mb-3">
                    <label for="prenom" class="form-label"><?php echo $tr('firstname'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo (!empty($prenom_err)) ? 'is-invalid' : ''; ?>" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom ?? ''); ?>" required>
                    <div class="invalid-feedback"><?php echo $prenom_err ?? ''; ?></div>
                </div>
                <div class="mb-3">
                    <label for="date_naissance" class="form-label"><?php echo $tr('date_of_birth'); ?> <span class="text-danger">*</span></label>
                    <input type="date" class="form-control <?php echo (!empty($date_naissance_err)) ? 'is-invalid' : ''; ?>" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($date_naissance ?? ''); ?>" required>
                    <div class="invalid-feedback"><?php echo $date_naissance_err ?? ''; ?></div>
                </div>
                <div class="mb-3">
                    <label for="lieu_naissance" class="form-label"><?php echo $tr('place_of_birth'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo (!empty($lieu_naissance_err)) ? 'is-invalid' : ''; ?>" id="lieu_naissance" name="lieu_naissance" value="<?php echo htmlspecialchars($lieu_naissance ?? ''); ?>" required>
                    <div class="invalid-feedback"><?php echo $lieu_naissance_err ?? ''; ?></div>
                </div>
                <div class="mb-3">
                    <label for="sexe" class="form-label"><?php echo $tr('gender'); ?> <span class="text-danger">*</span></label>
                    <select class="form-select <?php echo (!empty($sexe_err)) ? 'is-invalid' : ''; ?>" id="sexe" name="sexe" required>
                        <option value="M" <?php echo (isset($sexe) && $sexe == 'M') ? 'selected' : ''; ?>><?php echo $tr('male'); ?></option>
                        <option value="F" <?php echo (isset($sexe) && $sexe == 'F') ? 'selected' : ''; ?>><?php echo $tr('female'); ?></option>
                    </select>
                    <div class="invalid-feedback"><?php echo $sexe_err ?? ''; ?></div>
                </div>
                 <div class="mb-3">
                    <label for="matricule" class="form-label"><?php echo $tr('matricule'); ?></label>
                    <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo htmlspecialchars($matricule ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="telephone" class="form-label"><?php echo $tr('phone_number'); ?></label>
                    <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="photo" class="form-label"><?php echo $tr('photo'); ?></label>
                    <input type="file" class="form-control <?php echo (!empty($photo_err)) ? 'is-invalid' : ''; ?>" id="photo" name="photo" accept="image/png, image/jpeg, image/gif">
                     <div class="invalid-feedback"><?php echo $photo_err ?? ''; ?></div>
                    <?php if (!empty($photo)): ?>
                        <div class="mt-2">
                            <img src="<?php echo $app_url . '/' . htmlspecialchars($photo); ?>" alt="Photo actuelle" class="img-thumbnail" style="max-height: 100px; max-width: 100px;">
                            <p><small><?php echo $tr('current_photo'); // Photo actuelle ?></small></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> <?php echo $tr('save_changes'); ?>
        </button>
        <a href="<?php echo $app_url; ?>/users" class="btn btn-secondary">
            <i class="fas fa-times"></i> <?php echo $tr('cancel'); ?>
        </a>
    </form>
</div>
