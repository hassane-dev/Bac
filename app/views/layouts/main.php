<?php
// Déterminer la langue et la direction du texte pour le HTML
$current_lang = $data['current_lang'] ?? DEFAULT_LANG;
$html_lang = $current_lang;
$html_dir = ($current_lang === 'ar') ? 'rtl' : 'ltr';

// Base URL pour les assets - Assurez-vous que APP_URL est défini et correct
// Si APP_URL est http://localhost/bac_app_mvc, alors $base_url sera http://localhost/bac_app_mvc/public
// Cependant, les assets sont généralement servis directement depuis /public, donc APP_URL devrait suffire
// ou nous avons besoin d'une constante spécifique pour les assets si la structure de l'URL est complexe.
// Pour l'instant, on va supposer que APP_URL est la base pour accéder à public.
// $assets_base_url = APP_URL . '/public/assets'; // Ceci serait incorrect si APP_URL inclut déjà /public
// Si APP_URL est la racine web de bac_app_mvc (ex: http://localhost/bac_app_mvc), alors:
$assets_path_prefix = APP_URL . '/assets'; // En supposant que .htaccess dans public/ gère bien les chemins

// Récupérer le titre de la page, avec une valeur par défaut
$page_title = $data['page_title'] ?? $tr('app_name');
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($html_lang); ?>" dir="<?php echo htmlspecialchars($html_dir); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo $assets_path_prefix; ?>/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="<?php echo $assets_path_prefix; ?>/fontawesome/css/all.min.css">
    <!-- Custom App CSS -->
    <link rel="stylesheet" href="<?php echo $assets_path_prefix; ?>/css/style.css">

    <?php if ($html_dir === 'rtl'): ?>
        <!-- Bootstrap RTL (si vous utilisez une version de Bootstrap qui le supporte nativement ou un add-on) -->
        <!-- Pour Bootstrap 5, la prise en charge RTL est généralement intégrée et activée via l'attribut dir="rtl" -->
        <!-- Si un fichier CSS RTL spécifique pour Bootstrap est nécessaire et disponible : -->
        <!-- <link rel="stylesheet" href="<?php echo $assets_path_prefix; ?>/bootstrap/css/bootstrap.rtl.min.css"> -->
    <?php endif; ?>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>"><?php echo $tr('app_name'); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if ($data['isLoggedIn']): ?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="<?php echo APP_URL . '/dashboard/index'; ?>"><?php echo $tr('dashboard'); ?></a>
                        </li>
                        <!-- Plus de liens pour utilisateurs connectés ici -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $tr('settings'); ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                                <li><a class="dropdown-item" href="<?php echo APP_URL . '/accreditations/index'; ?>"><?php echo $tr('manage_accreditations'); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL . '/roles/index'; ?>"><?php echo $tr('manage_roles'); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL . '/users/index'; ?>"><?php echo $tr('manage_users'); ?></a></li>
                                <!-- Autres paramètres -->
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if ($data['isLoggedIn']): ?>
                        <li class="nav-item">
                            <span class="navbar-text">
                                <?php echo $tr('logged_in_as'); ?>: <?php echo htmlspecialchars($data['current_username'] ?? ''); ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL . '/auth/logout'; ?>"><?php echo $tr('logout'); ?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL . '/auth/login'; ?>"><?php echo $tr('login'); ?></a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-language"></i> <?php echo htmlspecialchars(strtoupper($current_lang)); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <?php foreach (AVAILABLE_LANGS as $lang_code): ?>
                                <?php if ($lang_code !== $current_lang): ?>
                                    <?php
                                        // Conserver les paramètres GET existants sauf 'lang'
                                        $query_params = $_GET;
                                        unset($query_params['url']); // 'url' est géré par le routeur, pas comme query param standard ici
                                        $query_params['lang'] = $lang_code;
                                        $queryString = http_build_query($query_params);
                                        $current_path = strtok($_SERVER["REQUEST_URI"],'?'); // Obtient le chemin sans query string
                                        if (defined('APP_URL_REWRITTEN') && APP_URL_REWRITTEN) { // si on utilise des URLs réécrites sans index.php?url=
                                            $switch_url_base = APP_URL . '/' . ($_GET['url'] ?? '');
                                        } else { // si on utilise index.php?url=
                                            $switch_url_base = APP_URL . '/index.php?url=' . ($_GET['url'] ?? '');
                                        }
                                        // Nettoyer les doubles slashes potentiels dans $switch_url_base
                                        $switch_url_base = preg_replace('#/+#', '/', $switch_url_base);
                                        $switch_url = rtrim($switch_url_base, '/') . '&lang=' . $lang_code;
                                        // Cas simple si pas de `url` (page d'accueil)
                                        if (!isset($_GET['url']) || empty($_GET['url'])) {
                                            $switch_url = APP_URL . '?lang=' . $lang_code;
                                        } else {
                                             // Assurer que `url` est préservé et `lang` est ajouté ou mis à jour
                                            $url_param = $_GET['url'];
                                            $other_params = array_filter($_GET, function($key) { return $key !== 'url' && $key !== 'lang'; }, ARRAY_FILTER_USE_KEY);
                                            $other_params_query = http_build_query($other_params);
                                            $final_query = 'url=' . $url_param . '&lang=' . $lang_code;
                                            if(!empty($other_params_query)) {
                                                $final_query .= '&' . $other_params_query;
                                            }
                                            $switch_url = APP_URL . '/index.php?' . $final_query;
                                        }

                                    ?>
                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($switch_url); ?>"><?php echo $tr(strtolower($lang_code)); // Assumes 'fr', 'ar' keys exist in lang files ?></a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php
        // Affichage des messages flash (si implémenté)
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            $alert_type = $flash['type'] === 'error' ? 'danger' : $flash['type']; // Bootstrap 'error' is 'danger'
            echo '<div class="alert alert-' . htmlspecialchars($alert_type) . ' alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($flash['message']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['flash_message']);
        }
        ?>

        <!-- Contenu spécifique de la page sera injecté ici par View::render() -->
        <?php echo $content_for_layout ?? ''; // La vue spécifique sera rendue et son contenu stocké dans $content_for_layout ?>
    </div>

    <footer class="mt-5 mb-3 text-center text-muted">
        <p>&copy; <?php echo date('Y'); ?> <?php echo $tr('app_name'); ?>. Tous droits réservés.</p>
    </footer>

    <!-- jQuery -->
    <script src="<?php echo $assets_path_prefix; ?>/jquery/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="<?php echo $assets_path_prefix; ?>/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Custom App JS -->
    <script src="<?php echo $assets_path_prefix; ?>/js/script.js"></script>
</body>
</html>
