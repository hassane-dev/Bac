<?php
// Data: $data['page_title'], $data['username'], $data['username_err'], $data['mot_de_passe_err'], $data['login_err']
// Helpers: $tr, $app_url
// Le layout main.php sera utilisé.
// Note: Pour la page de login, le layout pourrait avoir moins d'éléments (pas de navbar d'utilisateur connecté).
// On peut passer une variable $data['use_minimal_layout'] = true; au besoin et adapter main.php
// ou créer un layout auth_layout.php. Pour l'instant, on utilise main.php.
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3><?php echo htmlspecialchars($data['page_title']); ?></h3>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($data['login_err'])): ?>
                    <div class="alert alert-danger text-center"><?php echo htmlspecialchars($data['login_err']); ?></div>
                <?php endif; ?>

                <form action="<?php echo APP_URL . '/auth/login'; ?>" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label"><i class="fas fa-user"></i> <?php echo $tr('username'); ?></label>
                        <input type="text" name="username" id="username"
                               class="form-control form-control-lg <?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($data['username']); ?>" required autofocus>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($data['username_err']); ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label"><i class="fas fa-lock"></i> <?php echo $tr('password'); ?></label>
                        <input type="password" name="mot_de_passe" id="mot_de_passe"
                               class="form-control form-control-lg <?php echo (!empty($data['mot_de_passe_err'])) ? 'is-invalid' : ''; ?>"
                               required>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($data['mot_de_passe_err']); ?></div>
                    </div>

                    <!-- Optionnel: Se souvenir de moi -->
                    <!-- <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me"><?php echo $tr('remember_me'); ?></label>
                    </div> -->

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> <?php echo $tr('login'); ?>
                        </button>
                    </div>
                </form>

                <!-- Optionnel: Lien mot de passe oublié -->
                <!-- <div class="text-center mt-3">
                    <a href="<?php echo APP_URL . '/auth/forgot_password'; ?>"><?php echo $tr('forgot_password'); ?></a>
                </div> -->
            </div>
        </div>
    </div>
</div>
