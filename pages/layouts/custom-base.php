<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="PHP Bootstrap Responsive Admin Web Dashboard Template">
    <meta name="Author" content="Spruko Technologies Private Limited">
    <meta name="keywords"
        content="admin bootstrap dashboard, admin dashboard in php, admin panel php template, admin panel template, admin panel template bootstrap, admin php, bootstrap admin panels, bootstrap template admin panel, dashboard template bootstrap, panel admin php, php admin panel template, php bootstrap, php dashboard, php dashboard framework, php template, template admin php.">

    <!-- Title -->
    <title> Vyzor - PHP Bootstrap 5 Premium Admin & Dashboard Template </title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180"
        href="<?php echo $baseUrl; ?>/assets/images/brand-logos/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32"
        href="<?php echo $baseUrl; ?>/assets/images/brand-logos/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16"
        href="<?php echo $baseUrl; ?>/assets/images/brand-logos/favicon_io/favicon-16x16.png">
    <link rel="icon" href="<?php echo $baseUrl; ?>/assets/images/brand-logos/favicon_io/favicon.ico"
        type="image/x-icon">
    <link rel="manifest" href="<?php echo $baseUrl; ?>/assets/images/brand-logos/favicon_io/site.webmanifest">

    <!-- Start::custom-styles -->
    <?php include 'layouts/components/custom-styles.php'; ?>
    <!-- End::custom-styles -->

    <?php echo $styles; ?>

</head>

<body class="<?php echo $bodyClass; ?>">

    <!-- Start::custom-switcher -->
    <?php include 'layouts/components/custom-switcher.php'; ?>
    <!-- End::custom-switcher -->

    <?php echo $content; ?>

    <!-- Start::custom-scripts -->
    <?php include 'layouts/components/custom-scripts.php'; ?>
    <!-- End::custom-scripts -->

    <?php echo $scripts; ?>

</body>

</html>