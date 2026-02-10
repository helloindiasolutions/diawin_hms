<?php
$pageTitle = 'WhatsApp Templates';
ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">WhatsApp Templates</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted">Configure your WhatsApp message templates here.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>