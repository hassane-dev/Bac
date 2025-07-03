<?php
// app/views/home/index.php
// Cette vue ne devrait normalement pas être atteinte car HomeController redirige.
// Mais elle est là comme placeholder.
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($current_lang ?? 'fr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? $tr('app_name')); ?></title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($title ?? $tr('app_name')); ?></h1>
    <p><?php echo htmlspecialchars($message ?? $tr('welcome_message', ['appName' => $tr('app_name')])); ?></p>
    <p>
        <?php if (isset($isLoggedIn) && $isLoggedIn) : ?>
            <a href="<?php echo $app_url; ?>/dashboard"><?php echo $tr('dashboard'); ?></a> |
            <a href="<?php echo $app_url; ?>/auth/logout"><?php echo $tr('logout'); ?></a>
        <?php else : ?>
            <a href="<?php echo $app_url; ?>/auth/login"><?php echo $tr('login'); ?></a>
        <?php endif; ?>
    </p>
</body>
</html>
