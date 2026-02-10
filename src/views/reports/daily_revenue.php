<?php
/**
 * Siddha Daily Revenue Audit
 * Dynamic API Integrated
 */
$pageTitle = "Daily Revenue Audit";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Siddha Daily Revenue Audit</h2>
        <span class="text-muted fs-12">Consolidated financial overview of all Siddha clinical and pharmacy
            collections</span>
    </div>
    <div class="btn-list">
        <div class="input-group input-group-sm">
            <input type="date" class="form-control" id="audit-date" value="<?= date('Y-m-d') ?>">
            <button class="btn btn-primary" id="refresh-audit"><i class="ri-refresh-line"></i></button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle"><i
                                class="ri-money-dollar-circle-line fs-20"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0" id="total-revenue">...</h4>
                        <p class="text-muted mb-0 fs-12">Total Collection</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-success-transparent text-success rounded-circle"><i
                                class="ri-bank-card-line fs-20"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0" id="digital-revenue">...</h4>
                        <p class="text-muted mb-0 fs-12">Digital (UPI/Card)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-warning-transparent text-warning rounded-circle"><i
                                class="ri-hand-coin-line fs-20"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0" id="cash-revenue">...</h4>
                        <p class="text-muted mb-0 fs-12">Cash Collection</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Audit Ledger Detail</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100" id="audit-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Billing Type</th>
                                <th>Transaction Count</th>
                                <th>Tax (GST)</th>
                                <th>Discounts</th>
                                <th>Net Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody id="audit-body">
                            <tr>
                                <td colspan="5" class="text-center py-4">Syncing with financial engine...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadDailyAudit();
        document.getElementById('refresh-audit').addEventListener('click', loadDailyAudit);

        async function loadDailyAudit() {
            const body = document.getElementById('audit-body');
            const date = document.getElementById('audit-date').value;

            try {
                const resp = await fetch(`<?= baseUrl('/api/v1/billing/dcr') ?>?date=${date}`);
                const result = await resp.json();

                if (result.success && result.data.audit) {
                    renderAudit(result.data.audit);
                    updateSummary(result.data.audit);
                } else {
                    body.innerHTML = '<tr><td colspan="5" class="text-center py-5">No financial transactions recorded for this date.</td></tr>';
                    document.getElementById('total-revenue').textContent = '₹ 0';
                }
            } catch (e) {
                body.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger">Financial API synchronization failed.</td></tr>';
            }
        }

        function renderAudit(audit) {
            const body = document.getElementById('audit-body');
            body.innerHTML = '';
            audit.categories.forEach(cat => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td class="fw-bold">${cat.name}</td>
                <td>${cat.count} Invoices</td>
                <td>₹ ${parseFloat(cat.tax).toLocaleString('en-IN')}</td>
                <td>₹ ${parseFloat(cat.discount).toLocaleString('en-IN')}</td>
                <td class="fw-bold fs-14">₹ ${parseFloat(cat.net).toLocaleString('en-IN')}</td>
            `;
                body.appendChild(tr);
            });
        }

        function updateSummary(audit) {
            document.getElementById('total-revenue').textContent = '₹ ' + parseFloat(audit.summary.total).toLocaleString('en-IN');
            document.getElementById('digital-revenue').textContent = '₹ ' + parseFloat(audit.summary.digital).toLocaleString('en-IN');
            document.getElementById('cash-revenue').textContent = '₹ ' + parseFloat(audit.summary.cash).toLocaleString('en-IN');
        }
    });
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>