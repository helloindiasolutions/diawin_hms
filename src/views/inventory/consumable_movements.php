<?php
$pageTitle = 'Stock Movement Tracker';
ob_start();
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Consumable Movements</h1>
        <p class="text-muted mb-0 small">Audit trail for all stock inflows, outflows, and transfers.</p>
    </div>
    <div class="ms-md-1 ms-0">
        <button class="btn btn-primary btn-sm btn-wave" data-bs-toggle="modal" data-bs-target="#newMovementModal">
            <i class="ri-add-line me-1"></i> Manual Correction
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Movement Logs</div>
                <div class="d-flex gap-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0"><i class="ri-search-line"></i></span>
                        <input type="text" class="form-control" placeholder="Search item...">
                    </div>
                    <select class="form-select form-select-sm" style="width: 150px;">
                        <option value="">All Types</option>
                        <option value="inflow">In-flow (GRN)</option>
                        <option value="outflow">Out-flow (Sale)</option>
                        <option value="transfer">Inter-Branch</option>
                        <option value="correction">Corrections</option>
                    </select>
                    <button class="btn btn-sm btn-light border"><i class="ri-filter-3-line me-1"></i> Filters</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-nowrap mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Item Particulars</th>
                                <th>Type</th>
                                <th class="text-center">Reference</th>
                                <th class="text-end">Qty Chg</th>
                                <th class="text-end">Bal After</th>
                                <th>Operator</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-muted">24 Jan, 05:42 PM</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-info-transparent me-2 rounded-circle">C</div>
                                        <span class="fw-medium">Cotton Rolls (500g)</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-success-transparent text-success">STOCK IN</span></td>
                                <td class="text-center text-primary">#GRN-84221</td>
                                <td class="text-end text-success fw-bold">+ 50</td>
                                <td class="text-end fw-bold">120</td>
                                <td>System Admin</td>
                            </tr>
                            <tr>
                                <td class="text-muted">24 Jan, 03:15 PM</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-primary-transparent me-2 rounded-circle">S</div>
                                        <span class="fw-medium">Surgical Mask (3-Ply)</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-danger-transparent text-danger">STOCK OUT</span></td>
                                <td class="text-center text-primary">#SALE-9910</td>
                                <td class="text-end text-danger fw-bold">- 10</td>
                                <td class="text-end fw-bold">440</td>
                                <td>Pharmacist.A</td>
                            </tr>
                            <tr>
                                <td class="text-muted">24 Jan, 11:20 AM</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-warning-transparent me-2 rounded-circle">N</div>
                                        <span class="fw-medium">Nitril Gloves (L)</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-warning-transparent text-warning">TRANSFER</span></td>
                                <td class="text-center text-muted">To Branch #2</td>
                                <td class="text-end text-warning fw-bold">- 20</td>
                                <td class="text-end fw-bold">80</td>
                                <td>Store Manager</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>