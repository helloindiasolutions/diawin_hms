<?php
/**
 * Inventory & Stock Reports
 */
$pageTitle = "Inventory & Stock Reports";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Inventory Reports</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Reports & Analytics</a></li>
            <li class="breadcrumb-item active" aria-current="page">Inventory</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary-light btn-wave"><i class="ri-file-excel-2-fill align-middle me-1"></i> Export
            Excel</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Stock Status Overview</div>
                <div class="d-flex">
                    <button class="btn btn-sm btn-light me-2"><i class="ri-filter-line me-1"></i> Filter</button>
                    <button class="btn btn-sm btn-primary"><i class="ri-refresh-line me-1"></i> Sync Stock</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>On Hand</th>
                                <th>Min Level</th>
                                <th>Value (Cost)</th>
                                <th>Value (Sales)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Paracetamol 500mg</td>
                                <td>Tablets</td>
                                <td class="fw-bold">4,500</td>
                                <td>500</td>
                                <td>₹ 4,500</td>
                                <td>₹ 9,000</td>
                                <td><span class="badge bg-success-transparent text-success">Optimal</span></td>
                            </tr>
                            <tr>
                                <td>Amoxicillin 250mg</td>
                                <td>Tablets</td>
                                <td class="fw-bold text-danger">45</td>
                                <td>100</td>
                                <td>₹ 2,100</td>
                                <td>₹ 4,200</td>
                                <td><span class="badge bg-danger-transparent text-danger">Critical/Low</span></td>
                            </tr>
                            <tr>
                                <td>Siddha Thailam - 100ml</td>
                                <td>External Oils</td>
                                <td class="fw-bold text-warning">120</td>
                                <td>150</td>
                                <td>₹ 12,000</td>
                                <td>₹ 22,000</td>
                                <td><span class="badge bg-warning-transparent text-warning">Near Reorder</span></td>
                            </tr>
                            <tr>
                                <td>Surgical Masks (Box of 50)</td>
                                <td>Consumables</td>
                                <td class="fw-bold">210</td>
                                <td>50</td>
                                <td>₹ 10,500</td>
                                <td>₹ 15,750</td>
                                <td><span class="badge bg-success-transparent text-success">Optimal</span></td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end">Total Inventory Valuation:</td>
                                <td class="text-primary">₹ 14,24,500</td>
                                <td class="text-success">₹ 22,15,800</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Top 5 Fast Moving Items</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="avatar avatar-sm bg-primary-transparent text-primary rounded-circle">P
                                    </div>
                                </td>
                                <td>
                                    <h6 class="mb-0 fs-13">Paracetamol 500mg</h6>
                                    <small class="text-muted">1,240 sold last 7 days</small>
                                </td>
                                <td class="text-end fw-bold">₹ 24,800</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="avatar avatar-sm bg-success-transparent text-success rounded-circle">I
                                    </div>
                                </td>
                                <td>
                                    <h6 class="mb-0 fs-13">Insulin Syringes</h6>
                                    <small class="text-muted">840 sold last 7 days</small>
                                </td>
                                <td class="text-end fw-bold">₹ 12,600</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="avatar avatar-sm bg-warning-transparent text-warning rounded-circle">K
                                    </div>
                                </td>
                                <td>
                                    <h6 class="mb-0 fs-13">Kabhasura Kudineer</h6>
                                    <small class="text-muted">612 sold last 7 days</small>
                                </td>
                                <td class="text-end fw-bold">₹ 18,360</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Stock Adjustment History</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Reason</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>24 Jan</td>
                                <td>Amoxicillin</td>
                                <td>Expiry Disposal</td>
                                <td class="text-danger">-12</td>
                            </tr>
                            <tr>
                                <td>22 Jan</td>
                                <td>Thailam</td>
                                <td>Manual Audit</td>
                                <td class="text-success">+5</td>
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