<?php
// Data: $data['page_title'], $data['username'], $data['username_err'], $data['mot_de_passe_err'], $data['login_err']
// Helpers: $tr, $app_url
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3><?php echo htmlspecialchars($data['page_title']); ?></h3>
            </div>
            <div class="card-body p-4">
                <?php
                // Affichage des messages flash globaux (si la variable $data['flash_message'] est passée par le layout ou directement)
                // Ou si on utilise la session directement ici (moins propre mais possible)
                if (isset($_SESSION['flash_message']) && !empty($_SESSION['flash_message'])) {
                    $flash = $_SESSION['flash_message'];
                    $alert_type = $flash['type'] === 'error' ? 'danger' : $flash['type'];
                    echo '<div class="alert alert-' . htmlspecialchars($alert_type) . ' alert-dismissible fade show" role="alert">';
                    echo htmlspecialchars($flash['message']);
                    // echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'; // Le layout s'en charge
                    echo '</div>';
                    // unset($_SESSION['flash_message']); // Le layout devrait s'en charger après affichage
                }
                ?>

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

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> <?php echo $tr('login'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
