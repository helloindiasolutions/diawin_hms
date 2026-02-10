<?php
/**
 * Siddha Receivables & Outstanding
 * Dynamic API Integrated
 */
$pageTitle = "Siddha Outstanding";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Siddha Outstanding Receivables</h2>
        <span class="text-muted fs-12">Track unpaid therapy sessions, medicated oils, and consultancy fees</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-outline-info btn-wave"><i class="ri-printer-line align-middle me-1"></i> Statement
            Batch</button>
        <button class="btn btn-primary btn-wave"><i class="ri-mail-send-line align-middle me-1"></i> Send Siddha
            Reminders</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card bg-danger-transparent border-danger border-opacity-25">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-danger text-white rounded-circle"><i
                                class="ri-alarm-warning-line fs-24"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" id="total-outstanding">...</h4>
                        <p class="text-muted mb-0 fs-13">Total Outstanding (Siddha)</p>
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
                <div class="card-title">Detailed Debtor Registry (Live)</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100" id="outstanding-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Patient / Entity</th>
                                <th>Ref #</th>
                                <th>Type</th>
                                <th>Days Owed</th>
                                <th>Balance Due (₹)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="outstanding-body">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="mt-2 text-muted mb-0">Synchronizing with financial records...</p>
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
    document.addEventListener('DOMContentLoaded', function () {
        loadOutstanding();

        async function loadOutstanding() {
            const body = document.getElementById('outstanding-body');
            try {
                const response = await fetch('<?= baseUrl('/api/v1/billing/outstanding') ?>');
                const result = await response.json();

                if (result.success && result.data.outstanding) {
                    renderOutstanding(result.data.outstanding);
                    updateTotals(result.data.outstanding);
                } else {
                    body.innerHTML = '<tr><td colspan="7" class="text-center py-5">No outstanding Siddha bills found.</td></tr>';
                    document.getElementById('total-outstanding').textContent = '₹ 0';
                }
            } catch (error) {
                body.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-danger">Financial API synchronization failed.</td></tr>';
            }
        }

        function renderOutstanding(items) {
            const body = document.getElementById('outstanding-body');
            body.innerHTML = '';

            items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${item.date || '--'}</td>
                <td>
                    <h6 class="mb-0 fw-bold">${item.patient_name}</h6>
                    <small class="text-muted">#MRN-${item.mrn}</small>
                </td>
                <td><span class="fw-semibold">${item.invoice_no}</span></td>
                <td><span class="badge bg-info-transparent text-info">${item.type || 'Siddha'}</span></td>
                <td class="${item.days_overdue > 30 ? 'text-danger fw-bold' : ''}">${item.days_overdue} Days</td>
                <td class="fw-bold">${parseFloat(item.balance).toLocaleString('en-IN')}</td>
                <td>
                    <button class="btn btn-sm btn-primary-light" onclick="collect('${item.invoice_id}')">Collect</button>
                    <button class="btn btn-sm btn-icon btn-outline-danger ms-1" title="Send WhatsApp"><i class="ri-whatsapp-line"></i></button>
                </td>
            `;
                body.appendChild(row);
            });
        }

        function updateTotals(items) {
            const total = items.reduce((sum, item) => sum + parseFloat(item.balance), 0);
            document.getElementById('total-outstanding').textContent = '₹ ' + total.toLocaleString('en-IN');
        }
    });

    function collect(id) { alert('Opening collection portal for #' + id); }
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>