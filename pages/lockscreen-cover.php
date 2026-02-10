
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
                                <p class="h4 mb-2 fw-semibold">Hello Tom Phillip</p>
                                <p class="mb-3 text-muted fw-normal">Welcome Back</p>
                                <div class="d-flex gap-2 align-items-center mb-3">
                                    <div class="lh-1">
                                        <span class="avatar avatar-sm avatar-rounded">
                                            <img src="<?php echo $baseUrl; ?>/assets/images/faces/12.jpg" alt="">
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-dark fw-medium">tomphillip32@gmail.com</p>
                                    </div>
                                </div>
                                <div class="row gy-3">
                                    <div class="col-xl-12 mb-2">
                                        <label for="lockscreen-password" class="form-label text-default">Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control form-control-lg" id="lockscreen-password" placeholder="password">
                                            <a href="javascript:void(0);" class="show-password-button text-muted" onclick="createpassword('lockscreen-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 d-grid mt-2">
                                        <a href="index.php" class="btn btn-lg btn-primary">Unlock</a>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <p class="fw-medium mt-3 mb-0">Try unlock with <a class="text-success" href="javascript:void(0);"><u>Finger print</u></a> / <a class="text-success" href="javascript:void(0);"><u>Face Id</u></a></p>
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
