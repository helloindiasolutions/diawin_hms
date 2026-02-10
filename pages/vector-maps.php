
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- Jsvector Maps -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/jsvectormap/jsvectormap.min.css">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Vector Maps</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Maps</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Vector Maps</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->

                    <!-- Start::row-1 -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Basic Vector Map</div>
                                </div>
                                <div class="card-body">
                                    <div id="vector-map"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Map With Markers</div>
                                </div>
                                <div class="card-body">
                                    <div id="marker-map"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Map With Image Markers</div>
                                </div>
                                <div class="card-body px-1 px-sm-3">
                                    <div id="marker-image-map"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Map With Lines</div>
                                </div>
                                <div class="card-body">
                                    <div id="lines-map"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">US Vector Map</div>
                                </div>
                                <div class="card-body">
                                    <div id="us-map"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Russia Vector Map</div>
                                </div>
                                <div class="card-body">
                                    <div id="russia-map"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Spain Vector Map</div>
                                </div>
                                <div class="card-body">
                                    <div id="spain-map"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Canada Vector Map</div>
                                </div>
                                <div class="card-body">
                                    <div id="canada-map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- JSVector Maps JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/jsvectormap/jsvectormap.min.js"></script>

        <!-- JSVector Maps MapsJS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/jsvectormap/maps/world-merc.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/us-merc-en.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/russia.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/spain.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/canada.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/jsvectormap.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
