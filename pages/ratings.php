
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>



<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Ratings</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Advanced Ui</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Ratings</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->

                    <!-- Start::row-1 -->
                    <div class="row">
                        <div class="col-xxl-12 col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                <div class="card-title">
                                        Basic Rater
                                </div> 
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <p class="fs-14 mb-0 fw-medium">Show Some <span class="text-danger">&#10084;</span> with rating :</p>
                                        <div id="rater-basic"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        5 star rater with steps
                                    </div> 
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <p class="fs-14 mb-0 fw-medium">Dont forget to rate the product :</p>
                                        <div id="rater-steps"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Custom messages
                                    </div> 
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <p class="fs-14 mb-0 fw-medium">Your rating is much appreciated&#128079; :</p>
                                        <div id="custom-messages"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Unlimited number of stars readOnly
                                    </div> 
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <p class="fs-14 mb-0 fw-medium">Thanks for rating :</p>
                                        <div id="stars-unlimited"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        5 Star rater with custom isBusyText and simulated backend
                                    </div> 
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <p class="fs-14 mb-0 fw-medium">Thanks for rating :</p>
                                        <div id="stars-busytext"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        On hover event
                                    </div> 
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <p class="fs-14 mb-0 fw-medium">Please give your valuable rating :</p>
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div id="stars-hover"></div>
                                            <span class="live-rating badge bg-success-transparent ms-3">
                                                1
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Clear/reset rater
                                    </div> 
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <p class="fs-14 mb-0 fw-medium">Thank You so much for your support :</p>
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div id="rater-reset"></div>
                                            <button class="btn btn-icon btn-sm btn-danger-light ms-3" id="rater-reset-button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Reset">
                                                <i class="ri-restart-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- Rater JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/rater-js/index.js"></script>

        <!-- Internal Ratings JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/ratings.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
