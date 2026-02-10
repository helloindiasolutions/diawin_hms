<?php
$pageTitle = '401 - Unauthorized';
ob_start();
?>

<?php $styles = ob_get_clean(); ob_start(); ?>

<!-- Error Page Content -->
<div class="row align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="col-xl-7 col-lg-8 col-md-10">
        <div class="text-center px-4">
            <div class="mb-4">
                <img src="<?= asset('images/media/backgrounds/11.png') ?>" alt="401" class="img-fluid" style="max-height: 200px;">
            </div>
            <span class="d-block fs-4 text-primary fw-semibold mb-2">Oops! Unauthorized Access</span>
            <p class="error-text mb-0 text-primary" style="font-size: 120px; font-weight: 700; line-height: 1;">401</p>
            <p class="fs-16 text-muted mb-4">
                You don't have permission to access this page.<br>
                Please log in with valid credentials or contact support.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= baseUrl('/login') ?>" class="btn btn-primary">
                    <i class="ri-login-box-line me-1"></i>Login
                </a>
                <a href="<?= baseUrl('/dashboard') ?>" class="btn btn-outline-primary">
                    <i class="ri-home-line me-1"></i>Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ob_start(); ?>

<?php $scripts = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
