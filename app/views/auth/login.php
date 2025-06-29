<?php
// app/views/auth/login.php
// $title, $username, $mot_de_passe, $username_err, $mot_de_passe_err, $tr sont passés
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang ?? 'fr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? $tr('login')); ?> - <?php echo $tr('app_name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <!-- Favicon -->
    <!-- <link rel="icon" type="image/x-icon" href="<?php echo $app_url; ?>/assets/images/favicon.ico"> -->
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #343a40;
        }
        .form-floating label {
            opacity: .65; /* Bootstrap 5 default opacity for floating labels */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><?php echo htmlspecialchars($title); ?></h2>

        <?php if (!empty($_SESSION['message'])) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
         <?php if (!empty($username_err) && empty($_POST['username']) && empty($_POST['mot_de_passe']) ): // Cas spécifique pour login_failed ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $username_err; ?>
            </div>
        <?php endif; ?>


        <form action="<?php echo $app_url; ?>/auth/authenticate" method="POST">
            <div class="form-floating mb-3">
                <input type="text" class="form-control <?php echo (!empty($username_err) && !empty($_POST['username'])) ? 'is-invalid' : ''; ?>" id="username" name="username" placeholder="<?php echo $tr('username'); ?>" value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus>
                <label for="username"><?php echo $tr('username'); ?></label>
                <?php if(!empty($username_err) && !empty($_POST['username'])): // Afficher seulement si l'erreur n'est pas générique et que le champ a été rempli ?>
                    <div class="invalid-feedback"><?php echo $username_err; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control <?php echo (!empty($mot_de_passe_err)) ? 'is-invalid' : ''; ?>" id="mot_de_passe" name="mot_de_passe" placeholder="<?php echo $tr('password'); ?>" required>
                <label for="mot_de_passe"><?php echo $tr('password'); ?></label>
                 <?php if(!empty($mot_de_passe_err)): ?>
                    <div class="invalid-feedback"><?php echo $mot_de_passe_err; ?></div>
                <?php endif; ?>
            </div>

            <?php /* Optionnel: Se souvenir de moi
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                <label class="form-check-label" for="remember_me"><?php echo $tr('remember_me'); ?></label>
            </div>
            */ ?>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg"><?php echo $tr('login'); ?></button>
            </div>
        </form>

        <?php /* Optionnel: Liens supplémentaires
        <div class="text-center mt-3">
            <a href="#"><?php echo $tr('forgot_password'); // Mot de passe oublié ? ?></a>
        </div>
        */ ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Traductions à ajouter/vérifier:
// fr.php:
// 'user_account_inactive' => "Votre compte utilisateur est inactif. Veuillez contacter l'administrateur.",
// 'forgot_password' => "Mot de passe oublié ?",

// ar.php:
// 'user_account_inactive' => "حساب المستخدم الخاص بك غير نشط. يرجى الاتصال بالمسؤول.",
// 'forgot_password' => "هل نسيت كلمة المرور؟",
?>
