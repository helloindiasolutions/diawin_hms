<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="<?= e($_ENV['APP_NAME'] ?? 'Melina HMS') ?> - Hospital Management System">
    <?= csrfMeta() ?>

    <!-- Title -->
    <title><?= e($pageTitle ?? 'Authentication') ?> - <?= e($_ENV['APP_NAME'] ?? 'Melina HMS') ?></title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180"
        href="<?= asset('images/brand-logos/favicon_io/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32"
        href="<?= asset('images/brand-logos/favicon_io/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16"
        href="<?= asset('images/brand-logos/favicon_io/favicon-16x16.png') ?>">
    <link rel="icon" href="<?= asset('images/brand-logos/favicon_io/favicon.ico') ?>" type="image/x-icon">
    <link rel="manifest" href="<?= asset('images/brand-logos/favicon_io/site.webmanifest') ?>">

    <!-- Main Theme JS -->
    <script src="<?= asset('js/authentication-main.js') ?>"></script>

    <!-- Bootstrap CSS -->
    <link id="style" href="<?= asset('libs/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">

    <!-- Style CSS -->
    <link href="<?= asset('css/styles.css') ?>" rel="stylesheet">

    <!-- Global Validation CSS -->
    <link href="<?= asset('css/validation.css') ?>" rel="stylesheet">

    <!-- Icons CSS -->
    <link href="<?= asset('css/icons.css') ?>" rel="stylesheet">

    <?= $styles ?? '' ?>
</head>

<body class="authentication-background">

    <?= $content ?? '' ?>

    <!-- Bootstrap JS -->
    <script src="<?= asset('libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Show Password JS -->
    <script src="<?= asset('js/show-password.js') ?>"></script>

    <!-- Global Validation JS -->
    <script src="<?= asset('js/validation.js') ?>"></script>

    <?= $scripts ?? '' ?>
</body>

</html>