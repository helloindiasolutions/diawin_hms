
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
    
    //  Define body class //
    $bodyClass = 'bg-white';

?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>



<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
        <div class="row authentication authentication-cover-main mx-0">
            <div class="col-xxl-9 col-xl-9">
                <div class="row justify-content-center align-items-center h-100">
                    <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">
                        <div class="card custom-card border-0 shadow-none my-4">
                            <div class="card-body p-5">
                                <p class="h4 mb-2 fw-semibold">Verify Your Account</p>
                                <p class="mb-4 text-muted fw-normal">Enter the 4 digit code sent to the registered email Id.</p>
                                <div class="row gy-3">
                                    <div class="col-xl-12 mb-2">
                                        <div class="row">
                                            <div class="col-3">
                                                <input type="text" class="form-control form-control-lg text-center" id="one" maxlength="1" onkeyup="clickEvent(this,'two')">
                                            </div>
                                            <div class="col-3">
                                                <input type="text" class="form-control form-control-lg text-center" id="two" maxlength="1" onkeyup="clickEvent(this,'three')">
                                            </div>
                                            <div class="col-3">
                                                <input type="text" class="form-control form-control-lg text-center" id="three" maxlength="1" onkeyup="clickEvent(this,'four')">
                                            </div>
                                            <div class="col-3">
                                                <input type="text" class="form-control form-control-lg text-center" id="four" maxlength="1">
                                            </div>
                                        </div>
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                            <label class="form-check-label" for="defaultCheck1">
                                                Did not recieve a code ?<a href="mail.php" class="text-primary ms-2 d-inline-block fw-medium">Resend</a>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 d-grid mt-3">
                                        <a href="index.php" class="btn btn-lg btn-primary">Verify</a>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <p class="text-danger mt-3 mb-0 fw-medium"><sup><i class="ri-asterisk"></i></sup>Keep the verification code private!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-lg-12 d-xl-block d-none px-0">
                <div class="authentication-cover overflow-hidden">
                    <div class="authentication-cover-logo">
                        <a href="index.php">
                        <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/toggle-logo.png" alt="logo" class="desktop-dark"> 
                        </a>
                    </div>
                    <div class="authentication-cover-background">
                        <img src="<?php echo $baseUrl; ?>/assets/images/media/backgrounds/9.png" alt="">
                    </div>
                    <div class="authentication-cover-content">
                        <div class="p-5">
                            <h3 class="fw-semibold lh-base">Welcome to Dashboard</h3>
                            <p class="mb-0 text-muted fw-medium">Manage your website and content with ease using our powerful admin tools.</p>
                        </div>
                        <div>
                            <img src="<?php echo $baseUrl; ?>/assets/images/media/media-72.png" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- Internal Two Step Verification JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/two-step-verification.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/custom-base.php'; ?>
<!-- This code use for render base file -->
