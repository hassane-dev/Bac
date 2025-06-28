<?php
// Exemple de vue simple pour la page d'accueil

// Le titre et le message sont passés via le tableau $data depuis HomeController
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang ?? 'fr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Bienvenue'); ?></title>
    <!-- Ici, vous pourriez lier votre CSS Bootstrap -->
    <!-- <link rel="stylesheet" href="<?php echo $app_url; ?>/assets/css/bootstrap.min.css"> -->
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        header { background-color: #f4f4f4; padding: 10px; text-align: center; }
        nav a { margin: 0 10px; text-decoration: none; }
        .container { max-width: 800px; margin: auto; padding: 20px; }
        footer { text-align: center; margin-top: 20px; padding: 10px; background-color: #333; color: white; }
    </style>
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($title ?? 'Application Baccalauréat'); ?></h1>
        <nav>
            <!-- Exemple de sélecteur de langue -->
            <a href="?lang=fr">Français</a> |
            <a href="?lang=ar">العربية (Arabe)</a>
        </nav>
    </header>

    <div class="container">
        <p><?php echo htmlspecialchars($message ?? 'Contenu à venir.'); ?></p>

        <p>
            <?php
            // Utilisation de la fonction de traduction passée par le contrôleur
            // echo $tr('welcome_text_example');
            ?>
        </p>
        <p>
            Langue actuelle : <?php echo htmlspecialchars($current_lang ?? 'N/A'); ?>
        </p>
        <p>
            URL de l'application : <?php echo htmlspecialchars($app_url ?? 'N/A'); ?>
        </p>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Gestion du Baccalauréat. Tous droits réservés.</p>
    </footer>

    <!-- Ici, vous pourriez lier votre JS (jQuery, Bootstrap JS) -->
    <!-- <script src="<?php echo $app_url; ?>/assets/js/jquery.min.js"></script> -->
    <!-- <script src="<?php echo $app_url; ?>/assets/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>
