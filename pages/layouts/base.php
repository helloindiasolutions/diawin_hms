<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="transparent"
    data-width="fullwidth" data-menu-styles="transparent" data-page-style="flat" data-toggled="close"
    data-vertical-style="doublemenu" data-toggled="double-menu-open">

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

    <!-- Start::Styles -->
    <?php include 'layouts/components/styles.php'; ?>
    <!-- End::Styles -->

    <?php echo $styles; ?>

</head>

<body class="">

    <div class="progress-top-bar"></div>

    <!-- Start::main-switcher -->
    <?php include 'layouts/components/switcher.php'; ?>
    <!-- End::main-switcher -->

    <!-- Loader -->
    <div id="loader">
        <img src="<?php echo $baseUrl; ?>/assets/images/media/loader.svg" alt="">
    </div>
    <!-- Loader -->

    <div class="page">

        <!-- Start::main-header -->
        <?php include 'layouts/components/main-header.php'; ?>
        <!-- End::main-header -->

        <!-- Start::main-sidebar -->
        <?php include 'layouts/components/main-sidebar.php'; ?>
        <!-- End::main-sidebar -->

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid page-container main-body-container">

                <?php echo $content; ?>

            </div>
        </div>
        <!-- End::app-content -->

        <!-- Start::main-modal -->
        <?php include 'layouts/components/modal.php'; ?>
        <!-- End::main-modal -->

        <!-- Start::main-footer -->
        <?php include 'layouts/components/footer.php'; ?>
        <!-- End::main-footer -->

    </div>

    <!-- Start::main-scripts -->
    <?php include 'layouts/components/scripts.php'; ?>
    <!-- End::main-scripts -->

    <?php echo $scripts; ?>

    <!-- Sticky JS -->
    <script src="<?php echo $baseUrl; ?>/assets/js/sticky.js"></script>

    <!-- Defaultmenu JS -->
    <script src="<?php echo $baseUrl; ?>/assets/js/defaultmenu.min.js"></script>

    <!-- Custom JS -->
    <script src="<?php echo $baseUrl; ?>/assets/js/custom.js"></script>

    <!-- Custom-Switcher JS -->
    <script src="<?php echo $baseUrl; ?>/assets/js/custom-switcher.min.js"></script>

</body>

</html>