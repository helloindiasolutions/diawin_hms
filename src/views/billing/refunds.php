<?php
/**
 * Siddha Refunds & Adjustments
 * Dynamic API Integrated
 */
$pageTitle = "Siddha Refunds";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Siddha Refunds & Adjustments</h2>
        <span class="text-muted fs-12">Manage therapy corrections, excess payments, and credit notes</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" id="new-refund"><i class="ri-add-line align-middle me-1"></i> New
            Refund Request</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <p class="mb-1 text-muted">Pending Siddha Refunds</p>
                <h4 class="fw-bold mb-1" id="pending-refunds-val">...</h4>
                <div class="text-warning fs-12 fw-semibold" id="pending-refunds-count">
                    <i class="ri-history-line me-1"></i> Syncing...
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Adjustment Audit Trail (Live)</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100" id="refund-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Ref #</th>
                                <th>Patient Details</th>
                                <th>Original Bill #</th>
                                <th>Refund Amount (₹)</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="refund-body">
                            <tr>
                                <td colspan="7" class="text-center py-5">Connecting to financial ledger...</td>
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
        loadRefunds();

        async function loadRefunds() {
            const body = document.getElementById('refund-body');
            try {
                const resp = await fetch('<?= baseUrl('/api/v1/billing/payments') ?>?type=refund');
                const result = await resp.json();

                if (result.success && result.data.refunds) {
                    renderRefunds(result.data.refunds);
                    updateStats(result.data.refunds);
                } else {
                    body.innerHTML = '<tr><td colspan="7" class="text-center py-5">No refund transactions found.</td></tr>';
                }
            } catch (e) {
                body.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-danger">Cloud sync failed.</td></tr>';
            }
        }

        function renderRefunds(refunds) {
            const body = document.getElementById('refund-body');
            body.innerHTML = '';
            refunds.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td><span class="fw-semibold">${r.refund_no}</span></td>
                <td>
                    <h6 class="mb-0 fw-bold">${r.patient_name}</h6>
                    <small class="text-muted">#MRN-${r.mrn}</small>
                </td>
                <td>${r.invoice_no}</td>
                <td class="fw-bold text-danger">₹ ${parseFloat(r.amount).toLocaleString('en-IN')}</td>
                <td>${r.reason || 'Correction'}</td>
                <td><span class="badge bg-${r.status === 'approved' ? 'success' : 'warning'}-transparent text-${r.status === 'approved' ? 'success' : 'warning'}">${r.status || 'Pending'}</span></td>
                <td>
                    <button class="btn btn-sm btn-icon btn-primary-light" onclick="printRefund('${r.refund_id}')"><i class="ri-printer-line"></i></button>
                    ${r.status === 'pending' ? `<button class="btn btn-sm btn-icon btn-success-light ms-1" onclick="approveRefund('${r.refund_id}')"><i class="ri-check-line"></i></button>` : ''}
                </td>
            `;
                body.appendChild(tr);
            });
        }

        function updateStats(refunds) {
            const pending = refunds.filter(r => r.status === 'pending');
            const totalPendingVal = pending.reduce((sum, r) => sum + parseFloat(r.amount), 0);
            document.getElementById('pending-refunds-val').textContent = '₹ ' + totalPendingVal.toLocaleString('en-IN');
            document.getElementById('pending-refunds-count').innerHTML = `<i class="ri-history-line me-1"></i> ${pending.length} requests pending`;
        }
    });

    function printRefund(id) { alert('Printing Siddha Refund Voucher #' + id); }
    function approveRefund(id) { alert('Authorizing Siddha Therapy Refund #' + id); }
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>