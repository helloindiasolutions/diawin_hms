
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- Sweetalerts CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.css">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Sweet Alerts</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Sweet Alerts</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->
                    
                    <!-- Start:: row-1 -->
                    <div class="row">
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Basic Alert
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="basic-alert">Basic Alert</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Title With Text Under
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-text">Title With Text</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        With Text,Error Icon & Footer
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-footer">Alert Footer</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Alert With Long Window
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="long-window">Long Window Here</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Custom HTML Description
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-description">Custom HTML Alert</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Alert With Multiple Buttons
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="three-buttons">Multiple Buttons</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Custom Positioned Dialog Alert
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-dialog">Alert Dialog</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Confirm Alert
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-confirm">Confirm Alert</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Alert With Parameters
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-parameter">Alert Parameters</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Alert With Image
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-image">Image Alert</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Alert With Image
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-custom-bg">Custom Alert</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Auto Close Alert
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-auto-close">Auto Close</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Ajax Request Alert
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary" id="alert-ajax">Ajax Request</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-1 -->

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- Sweetalerts JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/sweet-alerts.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
