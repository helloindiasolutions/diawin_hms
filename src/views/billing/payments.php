<?php
$pageTitle = "Payments";
ob_start();
?>

<!-- Main Content Header -->
<div class="pos-content-header mb-4 px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="page-title fw-semibold fs-20 mb-1">Receipts & Payments</h1>
            <p class="text-muted mb-0 fs-13">Monitor collection receipts, payment modes, and financial transactions.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12 mb-3">
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-sm px-4 fw-bold uppercase tracking-wider ripple">
                <i class="ri-history-line me-1"></i> Transaction History
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title text-muted fs-11 uppercase fw-bold tracking-widest">Recent Collections</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Receipt ID</th>
                                <th>Date & Time</th>
                                <th>Mode</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="paymentList">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Parent Layout Overrides */
    .main-content.app-content {
        padding-inline: 1.5rem !important;
        margin-block-start: 8.5rem !important;
    }

    .card.custom-card {
        border-radius: 4px;
        border: 1px solid var(--default-border);
    }

    .card-header {
        border-bottom: 2px solid #f2f4f5;
        padding: 0.75rem 1.25rem;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', fetchPayments);

    async function fetchPayments() {
        const list = document.getElementById('paymentList');
        list.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const res = await fetch(`/api/v1/billing/payments`);
            const data = await res.json();
            list.innerHTML = '';

            if (data.success && data.data.payments.length > 0) {
                data.data.payments.forEach(p => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td class="ps-4"><span class="fw-bold">RCP-${String(p.payment_id).padStart(5, '0')}</span></td>
                    <td>${new Date(p.payment_date).toLocaleString()}</td>
                    <td><span class="badge bg-primary-transparent">${p.payment_mode.toUpperCase()}</span></td>
                    <td class="fw-bold text-success">${Number(p.amount).toLocaleString('en-IN', { style: 'currency', currency: 'INR' })}</td>
                    <td class="text-muted fs-12">${p.reference || '-'}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-icon btn-light rounded-pill"><i class="ri-printer-line"></i></button>
                    </td>
                `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No collections recorded yet today.</td></tr>';
            }
        } catch (e) { }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>