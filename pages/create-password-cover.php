
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
                                <div>
                                    <h4 class="mb-1 fw-semibold">Create Password</h4>
                                    <p class="mb-4 text-muted fw-normal">Set your new password</p>
                                </div>
                                <div class="row gy-3">
                                    <div class="col-xl-12">
                                        <label for="create-password" class="form-label text-default">Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control form-control-lg" id="create-password" placeholder="password">
                                            <a href="javascript:void(0);" class="show-password-button text-muted"  onclick="createpassword('create-password',this)"><i class="ri-eye-off-line align-middle"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label for="create-confirmpassword" class="form-label text-default">Confirm Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control form-control-lg" id="create-confirmpassword" placeholder="password">
                                            <a href="javascript:void(0);" class="show-password-button text-muted" onclick="createpassword('create-confirmpassword',this)" ><i class="ri-eye-off-line align-middle"></i></a>
                                        </div>
                                        <div class="mt-2">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" value="" id="defaultCheck1" checked>
                                                <label class="form-check-label" for="defaultCheck1">
                                                    Remember password ?
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid mt-3">
                                    <a href="index.php" class="btn btn-primary">Create Password</a>
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
	
        <!-- Show Password JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/show-password.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/custom-base.php'; ?>
<!-- This code use for render base file -->
