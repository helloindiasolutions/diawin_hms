<?php
// Placeholder for Doctors Create
$pageTitle = 'Create Doctor';
ob_start();
?>
<!-- Redirect to unified user creation for better UX -->
<script>window.location.href = '/users/create?role=doctor';</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>