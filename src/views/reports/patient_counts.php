<?php
/**
 * Patient Statistics Report
 */
$pageTitle = "Patient Statistics Report";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Patient Statistics</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Reports & Analytics</a></li>
            <li class="breadcrumb-item active" aria-current="page">Patient Stats</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave"><i class="ri-bar-chart-fill align-middle me-1"></i> Generate
            Analytics</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <p class="mb-1 text-muted">Total Registrations</p>
                        <h4 class="fw-bold mb-1">1,284</h4>
                        <div class="text-success fs-12 fw-semibold">
                            <i class="ri-arrow-up-s-fill me-1"></i>+8.5% <span class="text-muted fw-normal ms-1">this
                                month</span>
                        </div>
                    </div>
                    <div class="avatar avatar-md bg-primary-transparent text-primary ms-auto">
                        <i class="ri-user-add-line fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <p class="mb-1 text-muted">New Outpatients</p>
                        <h4 class="fw-bold mb-1">456</h4>
                        <div class="text-success fs-12 fw-semibold">
                            <i class="ri-arrow-up-s-fill me-1"></i>+12.3% <span class="text-muted fw-normal ms-1">this
                                month</span>
                        </div>
                    </div>
                    <div class="avatar avatar-md bg-success-transparent text-success ms-auto">
                        <i class="ri-customer-service-2-line fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <p class="mb-1 text-muted">IPD Admissions</p>
                        <h4 class="fw-bold mb-1">82</h4>
                        <div class="text-danger fs-12 fw-semibold">
                            <i class="ri-arrow-down-s-fill me-1"></i>-3.2% <span class="text-muted fw-normal ms-1">this
                                month</span>
                        </div>
                    </div>
                    <div class="avatar avatar-md bg-warning-transparent text-warning ms-auto">
                        <i class="ri-hotel-bed-line fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Registration Trends (Last 30 Days)</div>
            </div>
            <div class="card-body">
                <!-- Placeholder for chart -->
                <div style="height: 350px;"
                    class="d-flex align-items-center justify-content-center bg-light rounded bg-opacity-50">
                    <div class="text-center">
                        <i class="ri-bar-chart-grouped-line fs-40 text-muted mb-2"></i>
                        <p class="text-muted mb-0">Analytics Charts are being processed...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Demographics Summary</div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Male Patients
                        <span class="badge bg-primary">540 (42%)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Female Patients
                        <span class="badge bg-danger">712 (55%)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Other/Infant
                        <span class="badge bg-info">32 (3%)</span>
                    </li>
                    <li class="list-group-item py-4">
                        <p class="mb-2 fs-12 fw-semibold">Age Distribution</p>
                        <div class="progress progress-xs mb-3">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 15%"
                                aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-info" role="progressbar" style="width: 35%" aria-valuenow="35"
                                aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 40%"
                                aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 10%" aria-valuenow="10"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between fs-10 text-muted">
                            <span>0-18</span>
                            <span>19-45</span>
                            <span>46-60</span>
                            <span>60+</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>