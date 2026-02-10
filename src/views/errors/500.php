<?php
$pageTitle = '500 - Server Error';
ob_start();
?>

<?php $styles = ob_get_clean(); ob_start(); ?>

<!-- Error Page Content -->
<div class="row align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="col-xl-7 col-lg-8 col-md-10">
        <div class="text-center px-4">
            <div class="mb-4">
                <span class="avatar avatar-xxl bg-danger-transparent rounded-circle">
                    <i class="ri-server-line fs-1 text-danger"></i>
                </span>
            </div>
            <span class="d-block fs-4 text-danger fw-semibold mb-2">Internal Server Error</span>
            <p class="error-text mb-0 text-danger" style="font-size: 120px; font-weight: 700; line-height: 1;">500</p>
            <p class="fs-16 text-muted mb-4">
                Something went wrong on our end.<br>
                Our team has been notified. Please try again later.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="javascript:location.reload()" class="btn btn-secondary">
                    <i class="ri-refresh-line me-1"></i>Try Again
                </a>
                <a href="<?= baseUrl('/dashboard') ?>" class="btn btn-primary">
                    <i class="ri-home-line me-1"></i>Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ob_start(); ?>

<?php $scripts = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
