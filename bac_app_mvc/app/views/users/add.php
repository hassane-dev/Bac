<?php
// Data: $data['page_title'], $data['roles'], and all form field values + error messages
// Helpers: $tr, $app_url
?>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
            </div>
            <div class="card-body">
                <form action="<?php echo APP_URL . '/users/add'; ?>" method="POST" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label"><?php echo $tr('username'); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="username"
                                   class="form-control <?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['username']); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['username_err']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role_id" class="form-label"><?php echo $tr('role'); ?> <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-select <?php echo (!empty($data['role_id_err'])) ? 'is-invalid' : ''; ?>" required>
                                <option value=""><?php echo $tr('select_role'); ?></option>
                                <?php foreach ($data['roles'] as $role): ?>
                                    <option value="<?php echo $role->id; ?>" <?php echo ($data['role_id'] == $role->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role->nom_role); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['role_id_err']); ?></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label"><?php echo $tr('lastname'); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="nom" id="nom"
                                   class="form-control <?php echo (!empty($data['nom_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['nom']); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['nom_err']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label"><?php echo $tr('firstname'); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" id="prenom"
                                   class="form-control <?php echo (!empty($data['prenom_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['prenom']); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['prenom_err']); ?></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mot_de_passe" class="form-label"><?php echo $tr('password'); ?> <span class="text-danger">*</span></label>
                            <input type="password" name="mot_de_passe" id="mot_de_passe"
                                   class="form-control <?php echo (!empty($data['mot_de_passe_err'])) ? 'is-invalid' : ''; ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['mot_de_passe_err']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_mot_de_passe" class="form-label"><?php echo $tr('confirm_password'); ?> <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_mot_de_passe" id="confirm_mot_de_passe"
                                   class="form-control <?php echo (!empty($data['confirm_mot_de_passe_err'])) ? 'is-invalid' : ''; ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['confirm_mot_de_passe_err']); ?></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label"><?php echo $tr('email'); ?></label>
                            <input type="email" name="email" id="email"
                                   class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['email']); ?>">
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['email_err']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label"><?php echo $tr('phone_number'); ?></label>
                            <input type="tel" name="telephone" id="telephone" class="form-control"
                                   value="<?php echo htmlspecialchars($data['telephone']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_naissance" class="form-label"><?php echo $tr('date_of_birth'); ?> <span class="text-danger">*</span></label>
                            <input type="date" name="date_naissance" id="date_naissance"
                                   class="form-control <?php echo (!empty($data['date_naissance_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['date_naissance']); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['date_naissance_err']); ?></div>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="lieu_naissance" class="form-label"><?php echo $tr('place_of_birth'); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="lieu_naissance" id="lieu_naissance"
                                   class="form-control <?php echo (!empty($data['lieu_naissance_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['lieu_naissance']); ?>" required>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['lieu_naissance_err']); ?></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sexe" class="form-label"><?php echo $tr('gender'); ?> <span class="text-danger">*</span></label>
                            <select name="sexe" id="sexe" class="form-select <?php echo (!empty($data['sexe_err'])) ? 'is-invalid' : ''; ?>" required>
                                <option value="M" <?php echo ($data['sexe'] == 'M') ? 'selected' : ''; ?>><?php echo $tr('male'); ?></option>
                                <option value="F" <?php echo ($data['sexe'] == 'F') ? 'selected' : ''; ?>><?php echo $tr('female'); ?></option>
                            </select>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($data['sexe_err']); ?></div>
                        </div>
                    </div>

                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="matricule" class="form-label"><?php echo $tr('matricule'); ?> (<?php echo $tr('staff_id_optional'); ?>)</label>
                            <input type="text" name="matricule" id="matricule" class="form-control"
                                   value="<?php echo htmlspecialchars($data['matricule']); ?>">
                        </div>
                        <div class="col-md-6 mb-3 align-self-center">
                             <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?php echo ($data['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    <?php echo $tr('is_active_user'); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo APP_URL . '/users/index'; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?php echo $tr('cancel'); ?>
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> <?php echo $tr('add_user'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
