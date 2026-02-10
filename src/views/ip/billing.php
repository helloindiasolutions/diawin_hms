<?php
/**
 * Siddha IP Billing Management
 * Dynamic API Integrated
 */
$pageTitle = "Siddha IP Billing";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">In-Patient Billing (Siddha)</h2>
        <span class="text-muted fs-12">Manage therapy bundles, ward charges, and cumulative collections for admitted
            patients</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-outline-info btn-wave" id="deposit-history"><i
                class="ri-refund-line align-middle me-1"></i> Deposit Records</button>
        <button class="btn btn-primary btn-wave" id="cumulative-bill"><i
                class="ri-calculator-line align-middle me-1"></i> Cumulative Billing</button>
    </div>
</div>

<div class="row" id="stats-row">
    <!-- Dynamic Stats -->
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle"><i
                                class="ri-money-dollar-box-line fs-20"></i></span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1" id="total-ip-collection">...</h5>
                        <p class="text-muted mb-0 fs-12">MTD IP Collection</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-danger-transparent text-danger rounded-circle"><i
                                class="ri-error-warning-line fs-20"></i></span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1" id="pending-deposits-count">...</h5>
                        <p class="text-muted mb-0 fs-12">Low Deposit Alerts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Billing Table -->
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between border-bottom">
                <div class="card-title">Live Siddha IP Billing Queue</div>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" id="ip-search"
                        placeholder="Patient or Adm #">
                    <button class="btn btn-sm btn-primary" id="refresh-billing">Refresh</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100" id="billing-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Admission ID</th>
                                <th>Patient Details</th>
                                <th>Siddha Ward / Bed</th>
                                <th>Days In</th>
                                <th>Est. Charges (₹)</th>
                                <th>Deposits (₹)</th>
                                <th>Balance (₹)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="billing-queue-body">
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <p class="mt-2 text-muted mb-0">Synchronizing with IP billing API...</p>
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
    function initBillingPage() {
        loadIPBilling();
        document.getElementById('refresh-billing').addEventListener('click', loadIPBilling);
    }

    // SPA Support: Run immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBillingPage);
    } else {
        initBillingPage();
    }

    async function loadIPBilling() {
        const body = document.getElementById('billing-queue-body');
        try {
            // Fetching active admissions with billing data
            const response = await fetch('/api/v1/ipd/admissions?status=Active');
            const result = await response.json();

            if (result.success && result.data.admissions) {
                renderBillingQueue(result.data.admissions);
                updateStats(result.data.admissions);
            } else {
                body.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted">No active Siddha IP admissions found.</td></tr>';
            }
        } catch (error) {
            console.error('IP Billing Load Error:', error);
            body.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger">Failed to connect to billing server.</td></tr>';
        }
    }

    function renderBillingQueue(admissions) {
        const body = document.getElementById('billing-queue-body');
        body.innerHTML = '';

        admissions.forEach(adm => {
            const row = document.createElement('tr');

            const patientName = `${adm.first_name} ${adm.last_name || ''}`;
            const admDate = new Date(adm.admission_date);
            const today = new Date();
            const daysAdmitted = Math.ceil((today - admDate) / (1000 * 60 * 60 * 24));

            // Financial calculations (placeholder - would come from billing items)
            const charges = parseFloat(adm.estimated_bill || 0);
            const deposits = parseFloat(adm.total_deposits || 0);
            const balance = charges - deposits;
            const balanceColor = balance > 0 ? 'danger' : 'success';

            row.className = balance > 10000 ? 'table-warning' : '';

            row.innerHTML = `
                <td><span class="fw-bold text-primary">${adm.admission_number}</span></td>
                <td>
                    <h6 class="mb-0 fw-bold">${patientName}</h6>
                    <small class="text-muted">#MRN-${adm.mrn}</small>
                </td>
                <td>${adm.ward_name || 'General Ward'} / ${adm.bed_number || '--'}</td>
                <td>${daysAdmitted} Days</td>
                <td class="fw-bold">${charges.toLocaleString('en-IN')}</td>
                <td class="text-success fw-bold">${deposits.toLocaleString('en-IN')}</td>
                <td class="text-${balanceColor} fw-bold">${Math.abs(balance).toLocaleString('en-IN')} ${balance > 0 ? 'Due' : 'Credit'}</td>
                <td>
                    <div class="btn-list">
                        <button class="btn btn-sm btn-primary-light" onclick="viewInterim('${adm.admission_id}')"><i class="ri-bill-line me-1"></i> Interim</button>
                        <button class="btn btn-sm btn-success-light" onclick="addCollection('${adm.admission_id}')"><i class="ri-hand-coin-line me-1"></i> Pay</button>
                    </div>
                </td>
            `;
            body.appendChild(row);
        });
    }

    function updateStats(admissions) {
        let totalColl = 0;
        let lowDep = 0;
        admissions.forEach(a => {
            totalColl += parseFloat(a.total_deposits || 0);
            if ((parseFloat(a.estimated_bill || 0) - parseFloat(a.total_deposits || 0)) > 5000) lowDep++;
        });

        document.getElementById('total-ip-collection').textContent = '₹ ' + totalColl.toLocaleString('en-IN');
        document.getElementById('pending-deposits-count').textContent = lowDep;
    }

    function viewInterim(id) { alert('Siddha Therapy Interim Bill #' + id + ' loading...'); }
    function addCollection(id) { alert('Open Siddha IP Deposit Interface for #' + id); }
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>