<?php
$pageTitle = "Daily Cash Report";
ob_start();
?>

<!-- Main Content Header -->
<div class="pos-content-header mb-4 px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="page-title fw-semibold fs-20 mb-1">Daily Cash Report (DCR)</h1>
            <p class="text-muted mb-0 fs-13">Daily financial reconciliation, cash handovers, and summary reports.</p>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-xl-12">
        <div class="d-flex justify-content-end align-items-center gap-2">
            <label class="mb-0 fs-11 fw-bold text-secondary uppercase">Report Date:</label>
            <input type="date" class="form-control form-control-sm d-inline-block w-auto" id="dcrDate"
                value="<?= date('Y-m-d') ?>" onchange="fetchDCR()">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title text-muted fs-11 uppercase fw-bold tracking-widest">Daily Summary</div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        Opening Balance
                        <span class="fw-bold" id="opening_cash">₹ 0.00</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        Total Collections
                        <span class="fw-bold text-success" id="total_collections">+ ₹ 0.00</span>
                    </li>
                    <li
                        class="list-group-item d-flex justify-content-between align-items-center bg-light p-3 rounded mt-2">
                        <strong class="uppercase fs-11">Expected Closing</strong>
                        <span class="fw-bold text-primary fs-18" id="expected_closing">₹ 0.00</span>
                    </li>
                </ul>
                <div class="mt-4">
                    <button class="btn btn-primary w-100 fw-bold uppercase tracking-wider" onclick="reconcile()">Verify
                        & Reconcile</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title text-muted fs-11 uppercase fw-bold tracking-widest">Breakdown by Payment Mode
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center g-3">
                    <div class="col-3">
                        <div class="p-4 border rounded bg-light-transparent">
                            <h6 class="text-muted fs-10 text-uppercase mb-2 fw-bold opacity-75">Cash</h6>
                            <h5 class="fw-bold mb-0 text-navy" id="m_cash">₹ 0.00</h5>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-4 border rounded bg-light-transparent">
                            <h6 class="text-muted fs-10 text-uppercase mb-2 fw-bold opacity-75">Card</h6>
                            <h5 class="fw-bold mb-0 text-navy" id="m_card">₹ 0.00</h5>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-4 border rounded bg-light-transparent">
                            <h6 class="text-muted fs-10 text-uppercase mb-2 fw-bold opacity-75">UPI/QR</h6>
                            <h5 class="fw-bold mb-0 text-navy" id="m_upi">₹ 0.00</h5>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-4 border rounded bg-light-transparent">
                            <h6 class="text-muted fs-10 text-uppercase mb-2 fw-bold opacity-75">Other</h6>
                            <h5 class="fw-bold mb-0 text-navy" id="m_other">₹ 0.00</h5>
                        </div>
                    </div>
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
        box-shadow: none !important;
    }

    .card-header {
        border-bottom: 2px solid #f2f4f5;
        padding: 0.75rem 1.25rem;
    }

    .bg-light-transparent {
        background-color: rgba(243, 246, 249, 0.5);
    }

    .text-navy {
        color: #1a233a !important;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', fetchDCR);

    async function fetchDCR() {
        const date = document.getElementById('dcrDate').value;
        try {
            const res = await fetch(`/api/v1/billing/dcr?date=${date}`);
            const data = await res.json();
            const dcr = data.data.dcr || { opening_cash: 0, cash_sales: 0, card_sales: 0, upi_sales: 0, other_sales: 0 };

            document.getElementById('opening_cash').innerText = formatCurrency(dcr.opening_cash);
            document.getElementById('m_cash').innerText = formatCurrency(dcr.cash_sales);
            document.getElementById('m_card').innerText = formatCurrency(dcr.card_sales);
            document.getElementById('m_upi').innerText = formatCurrency(dcr.upi_sales);
            document.getElementById('m_other').innerText = formatCurrency(dcr.other_sales);

            const total = parseFloat(dcr.cash_sales) + parseFloat(dcr.card_sales) + parseFloat(dcr.upi_sales) + parseFloat(dcr.other_sales);
            document.getElementById('total_collections').innerText = '+ ' + formatCurrency(total);
            document.getElementById('expected_closing').innerText = formatCurrency(parseFloat(dcr.opening_cash) + total);

        } catch (e) { }
    }

    function formatCurrency(v) {
        return Number(v).toLocaleString('en-IN', { style: 'currency', currency: 'INR' });
    }

    function reconcile() {
        Swal.fire({
            title: 'Verify Report?',
            text: 'Daily Cash Report for ' + document.getElementById('dcrDate').value + ' will be verified.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6'
        });
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>