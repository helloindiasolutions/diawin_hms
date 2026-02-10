
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
    
    //  Define body class //
    $bodyClass = 'authentication-background';

?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>



<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

        <div class="authentication-basic-background">
            <img src="<?php echo $baseUrl; ?>/assets/images/media/backgrounds/9.png" alt="">
        </div>
	
        <div class="container">
            <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
                <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">
                    <div class="card custom-card border-0 my-4">
                        <div class="card-body p-5">
                            <div class="mb-4"> 
                                <a href="index.php"> 
                                    <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/toggle-logo.png" alt="logo" class="desktop-dark"> 
                                </a> 
                            </div>
                            <div>
                                <h4 class="mb-1 fw-semibold">Hi,Welcome back!</h4>
                                <p class="mb-4 text-muted fw-normal">Please enter your credentials</p>
                            </div>
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="signin-email" class="form-label text-default">Email</label>
                                    <input type="text" class="form-control" id="signin-email" placeholder="Enter Email" value="tomphillip21@gmail.com">
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="signin-password" class="form-label text-default d-block">Password</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="signin-password" placeholder="Enter Password" value="12345678">
                                        <a href="javascript:void(0);" class="show-password-button text-muted" onclick="createpassword('signin-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></a>
                                    </div>
                                    <div class="mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="defaultCheck1" checked>
                                            <label class="form-check-label" for="defaultCheck1">
                                                Remember me
                                            </label>
                                            <a href="reset-password-basic.php" class="float-end link-danger fw-medium fs-12">Forget password ?</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <a href="index.php" class="btn btn-primary">Sign In</a>
                            </div>
                            <div class="text-center my-3 authentication-barrier">
                                <span class="op-4 fs-13">OR</span>
                            </div>
                            <div class="d-grid mb-3">
                                <button class="btn btn-white btn-w-lg border d-flex align-items-center justify-content-center flex-fill mb-3">
                                    <span class="avatar avatar-xs">
                                        <img src="<?php echo $baseUrl; ?>/assets/images/media/apps/google.png" alt="">
                                    </span>
                                    <span class="lh-1 ms-2 fs-13 text-default fw-medium">Signup with Google</span>
                                </button>
                                <button class="btn btn-white btn-w-lg border d-flex align-items-center justify-content-center flex-fill">
                                    <span class="avatar avatar-xs flex-shrink-0">
                                        <img src="<?php echo $baseUrl; ?>/assets/images/media/apps/facebook.png" alt="">
                                    </span>
                                    <span class="lh-1 ms-2 fs-13 text-default fw-medium">Signup with Facebook</span>
                                </button>
                            </div>
                            <div class="text-center mt-3 fw-medium">
                                Dont have an account? <a href="sign-up-basic.php" class="text-primary">Register Here</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- Show Password JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/show-password.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/custom-base.php'; ?>
<!-- This code use for render base file -->
