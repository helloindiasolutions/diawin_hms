<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-nav-style="menu-hover" data-menu-position="fixed"
    data-theme-mode="light">

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
    <?php include 'layouts/components/landingpage/styles.php'; ?>
    <!-- End::Styles -->

    <?php echo $styles; ?>

</head>

<body class="landing-body">

    <!-- Start::main-switcher -->
    <?php include 'layouts/components/landingpage/switcher.php'; ?>
    <!-- End::main-switcher -->

    <div class="landing-page-wrapper">

        <!-- Start::main-header -->
        <?php include 'layouts/components/landingpage/main-header.php'; ?>
        <!-- End::main-header -->

        <!-- Start::main-sidebar -->
        <?php include 'layouts/components/landingpage/main-sidebar.php'; ?>
        <!-- End::main-sidebar -->

        <!-- Start::main-content -->
        <div class="main-content landing-main">

            <?php echo $content; ?>

        </div>
        <!-- End::main-content -->

        <!-- Start::main-footer -->
        <?php include 'layouts/components/landingpage/footer.php'; ?>
        <!-- End::main-footer -->

    </div>
    <!--app-content closed-->

    <!-- Start::main-scripts -->
    <?php include 'layouts/components/landingpage/scripts.php'; ?>
    <!-- End::main-scripts -->

</body>

</html>