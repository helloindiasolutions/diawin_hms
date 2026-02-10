<?php
$pageTitle = $title ?? 'Module Under Development';
ob_start();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0"><?= e($title ?? 'Module Under Development') ?></h1>
            <p class="text-muted mb-0">This feature is currently being integrated into the HMS system.</p>
        </div>
        <div class="btn-list mt-md-0 mt-2">
            <button type="button" class="btn btn-primary-light btn-wave" onclick="location.reload()">
                <i class="ri-refresh-line me-2 align-middle"></i>Refresh Status
            </button>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Placeholder Content -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body p-5">
                    <div class="text-center">
                        <div class="mb-4">
                            <span class="avatar avatar-xxl avatar-rounded bg-primary-transparent">
                                <i class="<?= e($icon ?? 'ri-tools-line') ?> fs-48 text-primary"></i>
                            </span>
                        </div>
                        <h4 class="fw-semibold mb-2">Feature Coming Soon</h4>
                        <p class="text-muted fs-14 mb-4">
                            We are working hard to bring you the <strong><?= e($title ?? 'selected module') ?></strong>. 
                            This section will feature advanced clinical tools and streamlined workflows tailored for your branch.
                        </p>
                        <div class="row justify-content-center">
                            <div class="col-xl-6">
                                <div class="alert alert-info border-info-transparent mb-0" role="alert">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <i class="ri-information-line fs-18"></i>
                                        </div>
                                        <div class="text-start">
                                            <p class="mb-0 fs-13">Need this feature urgently? Please contact the system administrator or your technical lead for a progress update.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="<?= baseUrl('/dashboard') ?>" class="btn btn-primary d-inline-flex align-items-center">
                                <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$scripts = ''; 
include SRC_PATH . '/views/layouts/app.php';
?>