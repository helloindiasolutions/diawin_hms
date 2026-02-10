<?php
$pageTitle = '404 - Page Not Found';
ob_start();
?>

<?php $styles = ob_get_clean(); ob_start(); ?>

<!-- Error Page Content -->
<div class="row align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="col-xl-7 col-lg-8 col-md-10">
        <div class="text-center px-4">
            <div class="mb-4">
                <span class="avatar avatar-xxl bg-warning-transparent rounded-circle">
                    <i class="ri-file-search-line fs-1 text-warning"></i>
                </span>
            </div>
            <span class="d-block fs-4 text-warning fw-semibold mb-2">Oops! Page Not Found</span>
            <p class="error-text mb-0 text-warning" style="font-size: 120px; font-weight: 700; line-height: 1;">404</p>
            <p class="fs-16 text-muted mb-4">
                The page you're looking for doesn't exist or has been moved.<br>
                Please check the URL or navigate back to the dashboard.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="ri-arrow-left-line me-1"></i>Go Back
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
