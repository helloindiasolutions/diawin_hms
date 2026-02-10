<?php
// Placeholder for Admin Config
$pageTitle = 'Branch Transfer';
ob_start();
?>
<div class="row">
    <div class="col-12">
        <h4>Branch Transfer</h4>
        <div class="alert alert-info">Module coming soon.</div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>