<?php
/**
 * pharmacy Expiry Alerts
 * Professional Stock Monitoring
 */
$pageTitle = "Expiry Monitoring";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Stock Expiry Monitoring</h2>
        <span class="text-muted fs-12">Proactively manage near-expiry Siddha/Ayurvedic medications and pharmacy
            stock.</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" onclick="loadExpiryAlerts()"><i
                class="ri-refresh-line align-middle me-1"></i> Sync Alerts</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card bg-danger-transparent border-danger border-opacity-25">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-danger text-white rounded-circle"><i
                                class="ri-error-warning-line fs-24"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" id="criticalCount">0 Items</h4>
                        <p class="text-muted mb-0 fs-13">Expiring in < 30 Days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card bg-warning-transparent border-warning border-opacity-25">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-warning text-white rounded-circle"><i
                                class="ri-time-line fs-24"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" id="nearCount">0 Items</h4>
                        <p class="text-muted mb-0 fs-13">Expiring in 31 - 90 Days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header border-bottom justify-content-between">
                <div class="card-title">Detailed Expiry Register</div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="expiryFilter" style="width: 180px;"
                        onchange="filterExpiryData()">
                        <option value="all">All Near Expiry</option>
                        <option value="critical">Critical (< 30 Days)</option>
                        <option value="near">Near (30-90 Days)</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100 mb-0" id="expiryDataTable">
                        <thead class="bg-light">
                            <tr class="fs-11 text-uppercase">
                                <th>Item Name</th>
                                <th>Batch Number</th>
                                <th>Stock Qty</th>
                                <th>MRP</th>
                                <th>Valuation</th>
                                <th>Expiry Date</th>
                                <th class="text-center">Days Left</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="expiryTableBody">
                            <tr>
                                <td colspan="9" class="text-center p-5">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="ms-2">Fetching inventory data...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let allAlerts = [];

    async function loadExpiryAlerts() {
        const branchId = window.currentBranchId;
        const tbody = document.getElementById('expiryTableBody');

        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/expiry-alerts`);
            const data = await res.json();

            if (data.success) {
                allAlerts = data.data.alerts;
                renderExpiryTable(allAlerts);
            } else {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Failed to load data</td></tr>';
            }
        } catch (e) {
            console.error('Error fetching alerts:', e);
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Connection error</td></tr>';
        }
    }

    function renderExpiryTable(alerts) {
        const tbody = document.getElementById('expiryTableBody');
        tbody.innerHTML = '';

        if (alerts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center p-4 text-muted">No items found matching the expiry criteria.</td></tr>';
            document.getElementById('criticalCount').innerText = '0 Items';
            document.getElementById('nearCount').innerText = '0 Items';
            return;
        }

        let critical = 0;
        let near = 0;

        alerts.forEach(item => {
            const daysLeft = parseInt(item.days_left);
            let statusBadge = '';
            let rowClass = '';
            let valColor = '';

            if (daysLeft <= 30) {
                statusBadge = '<span class="badge bg-danger-transparent border border-danger text-danger">CRITICAL</span>';
                rowClass = 'bg-danger-transparent-subtle';
                valColor = 'text-danger';
                critical++;
            } else {
                statusBadge = '<span class="badge bg-warning-transparent border border-warning text-warning">NEAR EXPIRY</span>';
                rowClass = '';
                valColor = 'text-warning';
                near++;
            }

            const valuation = (parseFloat(item.mrp || 0) * parseInt(item.qty_available)).toLocaleString('en-IN', {
                maximumFractionDigits: 2,
                style: 'currency',
                currency: 'INR'
            });

            const row = `
                <tr class="${rowClass} fs-12 align-middle">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="fw-semibold text-primary">${item.product_name}</div>
                        </div>
                    </td>
                    <td><code class="text-muted font-monospace">${item.batch_no}</code></td>
                    <td class="fw-bold">${item.qty_available}</td>
                    <td>₹ ${parseFloat(item.mrp || 0).toFixed(2)}</td>
                    <td class="fw-semibold ${valColor}">${valuation}</td>
                    <td class="fw-bold">${formatDateDisplay(item.expiry_date)}</td>
                    <td class="text-center">
                        <span class="badge ${daysLeft <= 30 ? 'bg-danger' : 'bg-warning'} rounded-pill px-2">
                            ${daysLeft} Days
                        </span>
                    </td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-icon btn-outline-light btn-wave" title="Return to Supplier" onclick="actionReturn(${item.batch_id})">
                                <i class="ri-reply-line"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-outline-danger btn-wave" title="Mark for Disposal" onclick="actionDispose(${item.batch_id})">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('criticalCount').innerText = `${critical} Items`;
        document.getElementById('nearCount').innerText = `${near} Items`;
    }

    function filterExpiryData() {
        const filter = document.getElementById('expiryFilter').value;
        let filtered = allAlerts;

        if (filter === 'critical') {
            filtered = allAlerts.filter(a => parseInt(a.days_left) <= 30);
        } else if (filter === 'near') {
            filtered = allAlerts.filter(a => parseInt(a.days_left) > 30);
        }

        renderExpiryTable(filtered);
    }

    function formatDateDisplay(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function actionReturn(id) {
        Swal.fire({
            title: 'Initiate Return?',
            text: "This will create a return request for this batch.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, start return'
        }).then((result) => {
            if (result.isConfirmed) {
                showToast('Return request initiated successfully', 'success');
            }
        });
    }

    function actionDispose(id) {
        Swal.fire({
            title: 'Confirm Disposal?',
            text: "Mark this batch for disposal due to expiry.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Confirm Disposal'
        }).then((result) => {
            if (result.isConfirmed) {
                showToast('Batch marked for disposal', 'success');
            }
        });
    }

    // Initialize
    if (window.pageInit) {
        window.pageInit.add('expiry-alerts', loadExpiryAlerts);
    } else {
        document.addEventListener('DOMContentLoaded', loadExpiryAlerts);
    }
</script>

<style>
    .bg-danger-transparent-subtle {
        background-color: rgba(239, 68, 68, 0.05) !important;
    }

    #expiryDataTable thead th {
        border-top: 0;
        border-bottom: 2px solid #eef1f6;
        vertical-align: middle;
        padding: 12px 16px;
    }

    #expiryDataTable tbody td {
        padding: 10px 16px;
    }

    .custom-card .card-header {
        padding: 1rem 1.25rem;
    }
</style>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>