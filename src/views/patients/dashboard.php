<?php
$pageTitle = 'My Health Dashboard';
ob_start();
?>

<div class="row">
    <!-- Welcome Section -->
    <div class="col-xl-12">
        <div class="card crm-highlight-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-semibold mb-2 text-white">Welcome back,
                            <?= e($_SESSION['user']['full_name'] ?? 'Patient') ?>! 👋
                        </h6>
                        <span class="d-block text-white-50 fs-12">Here's your health overview for today.</span>
                    </div>
                    <div>
                        <a href="<?= baseUrl('/appointments/book') ?>"
                            class="btn btn-white btn-sm text-primary fw-semibold shadow-sm">
                            <i class="ri-calendar-check-line me-1 align-middle"></i> Book Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="lh-1">
                        <span class="avatar avatar-rounded bg-light text-primary">
                            <i class="ri-stethoscope-line fs-18"></i>
                        </span>
                    </div>
                    <span class="badge bg-primary-transparent">Next Visit</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block fs-16 fw-semibold">Standard Checkup</span>
                        <span class="d-block text-muted fs-12">Tomorrow, 10:00 AM</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="lh-1">
                        <span class="avatar avatar-rounded bg-light text-secondary">
                            <i class="ri-file-list-3-line fs-18"></i>
                        </span>
                    </div>
                    <span class="badge bg-secondary-transparent">Prescriptions</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block fs-16 fw-semibold">2 Active</span>
                        <span class="d-block text-muted fs-12">Last updated: 2 days ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="lh-1">
                        <span class="avatar avatar-rounded bg-light text-success">
                            <i class="ri-flask-line fs-18"></i>
                        </span>
                    </div>
                    <span class="badge bg-success-transparent">Lab Reports</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block fs-16 fw-semibold">All Clear</span>
                        <span class="d-block text-muted fs-12">Latest report prepared</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="lh-1">
                        <span class="avatar avatar-rounded bg-light text-warning">
                            <i class="ri-wallet-3-line fs-18"></i>
                        </span>
                    </div>
                    <span class="badge bg-warning-transparent">Outstanding</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block fs-16 fw-semibold">₹ 0.00</span>
                        <span class="d-block text-muted fs-12">No pending bills</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State for Appointments -->
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">My Appointments</div>
                <a href="javascript:void(0);" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <span class="avatar avatar-xxl bg-light text-muted rounded-circle">
                        <i class="ri-calendar-event-line fs-32"></i>
                    </span>
                </div>
                <h6 class="fw-semibold">No appointments scheduled</h6>
                <p class="text-muted mb-4">Book a consultation with our specialists easily.</p>
                <a href="javascript:void(0);" class="btn btn-primary">Book Now</a>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Vitals History</div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Blood Pressure</span>
                            <span class="fw-semibold">120/80 <span
                                    class="badge bg-success-transparent ms-2">Normal</span></span>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Heart Rate</span>
                            <span class="fw-semibold">72 bpm <span
                                    class="badge bg-success-transparent ms-2">Normal</span></span>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Weight</span>
                            <span class="fw-semibold">70 kg</span>
                        </div>
                    </li>
                    <li>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Height</span>
                            <span class="fw-semibold">175 cm</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .crm-highlight-card {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-rgb));
        background-color: var(--primary-color);
        border: none;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>