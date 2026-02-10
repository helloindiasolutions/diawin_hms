<?php
// Audit Logs View (Placeholder for now)
$pageTitle = 'System Audit Logs';
ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">System Audit Logs</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="ri-file-shield-2-line display-4 text-muted"></i>
        </div>
        <h5>Audit Logging is Active</h5>
        <p class="text-muted">Detailed system event logs will be displayed here.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>