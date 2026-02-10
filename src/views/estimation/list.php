<?php
/**
 * Estimation List
 */
$pageTitle = "Pro-Format Estimates";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Financial Estimates</h2>
        <span class="text-muted fs-12">Search and track all generated patient cost estimates</span>
    </div>
    <div class="btn-list">
        <a href="<?= baseUrl('/estimation/create') ?>" class="btn btn-primary btn-wave"><i
                class="ri-add-line align-middle me-1"></i> Generate Estimate</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Recent Pro-Forma Invoices</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100">
                        <thead class="bg-light">
                            <tr>
                                <th>Ref #</th>
                                <th>Estimate Date</th>
                                <th>Patient Name</th>
                                <th>Procedure / Purpose</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="fw-bold">EST-2026-0042</span></td>
                                <td>24 Jan, 2026</td>
                                <td>Mr. Ramesh Kumar</td>
                                <td>Cataract Surgery - Left Eye</td>
                                <td class="fw-bold">₹ 45,000</td>
                                <td><span class="badge bg-primary-transparent text-primary">Generated</span></td>
                                <td>
                                    <div class="btn-list">
                                        <button class="btn btn-sm btn-icon btn-info-light"><i
                                                class="ri-eye-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-primary-light"><i
                                                class="ri-edit-line"></i></button>
                                        <button class="btn btn-sm btn-success-light" title="Convert to Bill"><i
                                                class="ri-exchange-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="fw-bold">EST-2026-0041</span></td>
                                <td>23 Jan, 2026</td>
                                <td>Ms. Sanjitha A</td>
                                <td>Diagnostic Full Body Checkup</td>
                                <td class="fw-bold">₹ 8,500</td>
                                <td><span class="badge bg-success-transparent text-success">Converted</span></td>
                                <td>
                                    <div class="btn-list">
                                        <button class="btn btn-sm btn-icon btn-info-light"><i
                                                class="ri-eye-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-secondary-light" disabled><i
                                                class="ri-exchange-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>