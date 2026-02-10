
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
    
    //  Define body class //
    $bodyClass = '';

?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>



<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
        <div class="page error-bg">
            <div class="error-page-background">
                <img src="<?php echo $baseUrl; ?>/assets/images/media/backgrounds/10.svg" alt="">
            </div>
            <!-- Start::error-page -->
            <div class="row align-items-center justify-content-center h-100 g-0">
                <div class="col-xl-7 col-lg-7 col-md-7 col-12">
                    <div class="text-center">
                        <div class="text-center mb-5">
                            <img src="<?php echo $baseUrl; ?>/assets/images/media/backgrounds/11.png" alt="" class="w-sm-auto w-100 h-100">
                        </div>
                        <span class="d-block fs-4 text-primary fw-semibold">Oops! Something Went Wrong</span>
                        <p class="error-text mb-0">404</p>
                        <p class="fs-5 fw-normal mb-0">There was an issue with the page. Try again <br> later or contact support.</p>
                    </div>
                </div>
            </div>
        </div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	


<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/custom-base.php'; ?>
<!-- This code use for render base file -->
