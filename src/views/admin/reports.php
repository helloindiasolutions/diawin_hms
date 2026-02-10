<?php
/**
 * Administrative Reports Dashboard
 * Professional Analytics Overview
 */
$pageTitle = "Admin Reports Center";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Administrative Reports</h2>
        <span class="text-muted fs-12">High-level management analytics and operational intelligence</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-outline-primary btn-wave"><i class="ri-settings-line align-middle me-1"></i> Report
            Settings</button>
        <button class="btn btn-primary btn-wave"><i class="ri-file-chart-line align-middle me-1"></i> Generate Monthly
            Audit</button>
    </div>
</div>

<div class="row">
    <!-- Category Groups -->
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title text-primary"><i class="ri-bank-line me-2"></i> Financial Audit Reports</div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="<?= baseUrl('/reports/daily-revenue') ?>"
                        class="list-group-item list-group-item-action p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Daily Collection Summary</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                        <small class="text-muted">Total cash/digital collections per day</small>
                    </a>
                    <a href="<?= baseUrl('/reports/outstanding') ?>" class="list-group-item list-group-item-action p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Receivables Aging (Report)</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                        <small class="text-muted">Detailed aging of unpaid bills & insurance claims</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title text-success"><i class="ri-user-star-line me-2"></i> Clinical Analytics</div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="<?= baseUrl('/reports/patient-counts') ?>"
                        class="list-group-item list-group-item-action p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Patient Traffic Trends</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                        <small class="text-muted">Registration volumes & returning patient rates</small>
                    </a>
                    <a href="<?= baseUrl('/reports/doctor-revenue') ?>"
                        class="list-group-item list-group-item-action p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Physician Performance</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                        <small class="text-muted">Revenue & patient volume per consultant</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title text-warning"><i class="ri-shopping-cart-line me-2"></i> Supply Chain Reports
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="<?= baseUrl('/reports/inventory') ?>" class="list-group-item list-group-item-action p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Stock Valuation Ledger</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                        <small class="text-muted">Current inventory value by landing cost & selling price</small>
                    </a>
                    <a href="<?= baseUrl('/reports/pharmacy-sales') ?>"
                        class="list-group-item list-group-item-action p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Drug Movement Analysis</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                        <small class="text-muted">Fast moving vs expired drug analytics</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>