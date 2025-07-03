<?php
// app/views/auth/login.php
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($current_lang ?? 'fr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? $tr('login')); ?> - <?php echo $tr('app_name'); ?></title>

    <!-- Bootstrap CSS -->
    <link href="<?php echo $app_url; ?>/assets/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f0f2f5; /* Un gris plus clair et moderne */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 420px;
            width: 100%;
            padding: 2.5rem; /* Plus d'espace */
            background-color: #fff;
            border-radius: 8px; /* Bords plus arrondis */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Ombre plus prononcée */
            border-top: 5px solid #0d6efd; /* Accent de couleur primaire */
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
            font-weight: 600;
        }
        .form-floating label {
            opacity: .85;
        }
        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 0.75rem; /* Bouton plus grand */
            font-size: 1.05rem;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .alert {
            border-radius: 6px;
        }
        /* Style pour les messages d'erreur spécifiques aux champs, s'ils sont affichés en dehors du feedback standard */
        .specific-field-error {
            font-size: 0.875em;
            color: #dc3545; /* Couleur d'erreur Bootstrap */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><?php echo htmlspecialchars($title); ?></h2>

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

        <?php
        // Afficher l'erreur de login_failed une seule fois en haut, si les champs sont vides (première soumission échouée)
        // ou si l'erreur n'est pas spécifique à un champ.
        if (!empty($username_err) && ($username_err === $tr('login_failed') || $username_err === $tr('user_account_inactive')) && empty($_POST['username']) && empty($_POST['mot_de_passe']) ) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($username_err) . '</div>';
            $username_err = ''; // Effacer pour ne pas le réafficher en dessous du champ
        }
        ?>

        <form action="<?php echo $app_url; ?>/auth/authenticate" method="POST">
            <div class="form-floating mb-3">
                <input type="text" class="form-control <?php echo (!empty($username_err) && $username_err !== $tr('login_failed') && $username_err !== $tr('user_account_inactive')) ? 'is-invalid' : ''; ?>"
                       id="username" name="username" placeholder="<?php echo $tr('username'); ?>"
                       value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus>
                <label for="username"><?php echo $tr('username'); ?></label>
                <?php if(!empty($username_err) && $username_err !== $tr('login_failed') && $username_err !== $tr('user_account_inactive')): ?>
                    <div class="invalid-feedback"><?php echo $username_err; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control <?php echo (!empty($mot_de_passe_err)) ? 'is-invalid' : ''; ?>"
                       id="mot_de_passe" name="mot_de_passe" placeholder="<?php echo $tr('password'); ?>" required>
                <label for="mot_de_passe"><?php echo $tr('password'); ?></label>
                 <?php if(!empty($mot_de_passe_err)): ?>
                    <div class="invalid-feedback"><?php echo $mot_de_passe_err; ?></div>
                <?php endif; ?>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-block"><?php echo $tr('login'); ?></button>
            </div>
        </form>
    </div>

    <script src="<?php echo $app_url; ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
