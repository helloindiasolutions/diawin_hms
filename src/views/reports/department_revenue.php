<?php
$pageTitle = 'Department Revenue Analysis';
ob_start();
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-semibold fs-18 mb-0">Department Revenue</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Department Revenue</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Stats Row -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle">
                            <i class="ri-wallet-3-line fs-18"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-12">Total Revenue (MTD)</p>
                        <h4 class="fw-semibold mb-0">₹ 8,42,500</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-success-transparent text-success rounded-circle">
                            <i class="ri-stethoscope-line fs-18"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-12">OPD Billable</p>
                        <h4 class="fw-semibold mb-0">₹ 4,12,000</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-info-transparent text-info rounded-circle">
                            <i class="ri-capsule-line fs-18"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-12">Pharmacy Sales</p>
                        <h4 class="fw-semibold mb-0">₹ 2,90,500</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-warning-transparent text-warning rounded-circle">
                            <i class="ri-pie-chart-line fs-18"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-12">Others (Lab/Proced.)</p>
                        <h4 class="fw-semibold mb-0">₹ 1,40,000</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Revenue by Department</div>
                <div class="d-flex gap-2">
                    <input type="text" id="reportDateRange" class="form-control form-control-sm"
                        placeholder="Select Date Range">
                    <select class="form-select form-select-sm" style="width: 150px;">
                        <option value="">All Branches</option>
                    </select>
                    <button class="btn btn-sm btn-primary-light btn-wave"><i class="ri-download-2-line"></i>
                        Export</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle text-nowrap mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Department</th>
                                <th>Bill Count</th>
                                <th>Gross Amount</th>
                                <th>Discount</th>
                                <th>Net Revenue</th>
                                <th>Contribution %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-primary-transparent me-2 rounded-circle"><i
                                                class="ri-stethoscope-line"></i></div>
                                        <span class="fw-medium">OPD Consultation</span>
                                    </div>
                                </td>
                                <td>842</td>
                                <td>₹ 4,21,000</td>
                                <td class="text-danger">- ₹ 9,000</td>
                                <td class="fw-bold text-success">₹ 4,12,000</td>
                                <td style="width: 200px;">
                                    <div class="progress progress-xs" style="height: 5px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 48.9%"
                                            aria-valuenow="48.9" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">48.9%</small>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-info-transparent me-2 rounded-circle"><i
                                                class="ri-capsule-line"></i></div>
                                        <span class="fw-medium">Main Pharmacy</span>
                                    </div>
                                </td>
                                <td>1,250</td>
                                <td>₹ 2,95,000</td>
                                <td class="text-danger">- ₹ 4,500</td>
                                <td class="fw-bold text-success">₹ 2,90,500</td>
                                <td>
                                    <div class="progress progress-xs" style="height: 5px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 34.5%"
                                            aria-valuenow="34.5" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">34.5%</small>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-warning-transparent me-2 rounded-circle"><i
                                                class="ri-flask-line"></i></div>
                                        <span class="fw-medium">Laboratory</span>
                                    </div>
                                </td>
                                <td>310</td>
                                <td>₹ 95,000</td>
                                <td class="text-danger">₹ 0</td>
                                <td class="fw-bold text-success">₹ 95,000</td>
                                <td>
                                    <div class="progress progress-xs" style="height: 5px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 11.3%"
                                            aria-valuenow="11.3" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">11.3%</small>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-success-transparent me-2 rounded-circle"><i
                                                class="ri-hospital-line"></i></div>
                                        <span class="fw-medium">IPD & Nursing</span>
                                    </div>
                                </td>
                                <td>45</td>
                                <td>₹ 45,000</td>
                                <td class="text-danger">₹ 0</td>
                                <td class="fw-bold text-success">₹ 45,000</td>
                                <td>
                                    <div class="progress progress-xs" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 5.3%"
                                            aria-valuenow="5.3" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">5.3%</small>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light-transparent">
                            <tr class="fw-bold border-top">
                                <td>TOTAL SUMMARY</td>
                                <td>2,447</td>
                                <td>₹ 8,56,000</td>
                                <td class="text-danger">- ₹ 13,500</td>
                                <td class="text-primary fs-15">₹ 8,42,500</td>
                                <td>100.0%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize date picker
        if (window.flatpickr) {
            flatpickr("#reportDateRange", {
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: [new Date().setDate(1), new Date()]
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>