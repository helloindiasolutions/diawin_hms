<?php
$pageTitle = '403 - Forbidden';
ob_start();
?>

<?php $styles = ob_get_clean(); ob_start(); ?>

<!-- Error Page Content -->
<div class="row align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="col-xl-7 col-lg-8 col-md-10">
        <div class="text-center px-4">
            <div class="mb-4">
                <span class="avatar avatar-xxl bg-danger-transparent rounded-circle">
                    <i class="ri-forbid-line fs-1 text-danger"></i>
                </span>
            </div>
            <span class="d-block fs-4 text-danger fw-semibold mb-2">Access Forbidden</span>
            <p class="error-text mb-0 text-danger" style="font-size: 120px; font-weight: 700; line-height: 1;">403</p>
            <p class="fs-16 text-muted mb-4">
                You don't have permission to access this resource.<br>
                If you believe this is an error, please contact your administrator.
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
