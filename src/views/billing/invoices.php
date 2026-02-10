<?php
$pageTitle = "Invoices";
ob_start();
?>

<div class="products-container">
    <!-- Main Content Header -->
    <div class="pos-content-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="page-title fw-semibold fs-20 mb-1">Sales & Invoices</h1>
                <p class="text-muted mb-0 fs-13">Track sales receipts, patient billing history, and revenue records.</p>
            </div>
        </div>
    </div>

    <!-- Filters and Actions Header -->
    <div class="products-header">
        <div class="header-grid">
            <!-- Search Field -->
            <div class="header-field search-field">
                <label>Find Invoice</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-primary">
                        <i class="ri-search-line"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm border-start-0" id="invoiceSearch"
                        placeholder="Search by No, MRN, or Patient... [Ctrl+F]" onkeyup="fetchInvoices()">
                </div>
            </div>

            <!-- Invoice Type -->
            <div class="header-field">
                <label>Invoice Type</label>
                <select class="form-select form-select-sm" id="type_filter" onchange="fetchInvoices()">
                    <option value="">All Types</option>
                    <option>Pharmacy</option>
                    <option>Consultation</option>
                    <option>Lab/Diagnostics</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="header-field">
                <label>Payment Status</label>
                <select class="form-select form-select-sm" id="status_filter" onchange="fetchInvoices()">
                    <option value="">All Invoices</option>
                    <option>Paid</option>
                    <option>Credit / Pending</option>
                    <option>Cancelled</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="header-actions">
                <button class="btn btn-action btn-primary" onclick="location.href='/invoices/create'">
                    <i class="ri-add-line"></i> NEW BILL (F2)
                </button>
                <button class="btn btn-action btn-secondary outline">
                    <i class="ri-file-chart-line"></i> DAY REPORT
                </button>
                <button class="btn btn-action btn-secondary outline">
                    <i class="ri-file-upload-line"></i> GSTR-1
                </button>
            </div>
        </div>
    </div>

    <!-- Date Range Row -->
    <div class="filter-strip px-3 py-2 bg-light border-bottom d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <label class="fs-11 fw-bold text-secondary mb-0 uppercase">Filter Date:</label>
            <input type="date" class="form-control form-control-sm" style="width: 150px;" id="date_filter"
                onchange="fetchInvoices()">
        </div>
    </div>

    <div class="products-body">
        <div class="products-main">
            <div class="products-grid-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 120px;">Invoice #</th>
                            <th style="width: 100px;">Date</th>
                            <th>Patient Name</th>
                            <th style="width: 100px;">MRN</th>
                            <th style="width: 120px;">Type</th>
                            <th style="width: 120px;">Total Amt</th>
                            <th style="width: 120px;">Balance</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 60px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceList">
                        <!-- Dynamic Entry Rows -->
                    </tbody>
                </table>
                <div id="loading_msg" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="products-footer">
        <div class="footer-stats">
            Records: <span id="count_display" class="text-primary fw-bold">0</span> |
            Today's Sales: <span class="text-success fw-bold">₹<span id="header_sales_total">0.00</span></span>
        </div>
        <div class="footer-pagination">
            <button class="btn-page" disabled><i class="ri-arrow-left-s-line"></i></button>
            <button class="btn-page"><i class="ri-arrow-right-s-line"></i></button>
        </div>
    </footer>
</div>

<style>
    :root {
        --primary-blue: #3b82f6;
        --border-color: #e5e7eb;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --bg-light: #f9fafb;
        --bg-white: #ffffff;
    }

    body {
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .products-container {
        height: calc(100vh - 110px);
        display: flex;
        flex-direction: column;
        background: var(--bg-white);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    .products-header {
        flex-shrink: 0;
        background: var(--bg-white);
        border-bottom: 2px solid var(--border-color);
        padding: 0.5rem 1rem;
    }

    .header-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 1rem;
        align-items: end;
    }

    .header-field label {
        font-size: 10px;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 0.35rem;
        text-transform: uppercase;
    }

    .form-control-sm,
    .form-select-sm {
        height: 32px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .header-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: end;
    }

    .btn-action {
        height: 32px;
        padding: 0 1rem;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 4px;
        border: none;
    }

    .btn-primary {
        background: var(--primary-blue);
        color: #fff;
    }

    .btn-secondary.outline {
        background: #fff;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .products-body {
        flex: 1;
        overflow: hidden;
        background: #f9fafb;
    }

    .products-main {
        height: 100%;
        padding: 0.5rem;
    }

    .products-grid-container {
        height: 100%;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        overflow-y: auto;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
    }

    .products-table th {
        position: sticky;
        top: 0;
        background: var(--bg-light);
        padding: 0.75rem 0.5rem;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-secondary);
        text-align: left;
        text-transform: uppercase;
        border-bottom: 2px solid var(--border-color);
        z-index: 10;
    }

    .products-table td {
        padding: 0.5rem;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
    }

    .products-table tr:hover {
        background: #eff6ff;
    }

    .products-footer {
        flex-shrink: 0;
        background: var(--bg-white);
        border-top: 1px solid var(--border-color);
        padding: 0.5rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .footer-stats {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-secondary);
    }

    /* Parent Layout Overrides */
    .main-content.app-content {
        padding-inline: 0.5rem !important;
        margin-block-start: 8.5rem !important;
    }

    .main-content.app-content>.container-fluid {
        padding: 0 !important;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetchInvoices();
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F2') { e.preventDefault(); location.href = '/invoices/create'; }
        });
    });

    async function fetchInvoices() {
        const list = document.getElementById('invoiceList');
        const search = document.getElementById('invoiceSearch').value;
        const type = document.getElementById('type_filter').value;
        const status = document.getElementById('status_filter').value;
        const date = document.getElementById('date_filter').value;

        document.getElementById('loading_msg').style.display = 'block';
        list.innerHTML = '';

        try {
            const res = await fetch(`/api/v1/billing/invoices?search=${search}&type=${type}&status=${status}&date=${date}`);
            const data = await res.json();
            document.getElementById('loading_msg').style.display = 'none';

            if (data.success && data.data.invoices.length > 0) {
                document.getElementById('count_display').innerText = data.data.invoices.length;

                let totalSales = 0;
                data.data.invoices.forEach((i, index) => {
                    const balance = i.total_amount - i.paid_amount;
                    totalSales += parseFloat(i.total_amount);

                    const statusClass = i.status == 'paid' ? 'text-success' : 'text-danger';
                    const row = document.createElement('tr');

                    row.innerHTML = `
                        <td class="text-center text-muted">${index + 1}</td>
                        <td><a href="/invoices/view?id=${i.id}" class="fw-bold text-primary text-decoration-none">${i.invoice_no}</a></td>
                        <td class="text-muted">${new Date(i.created_at).toLocaleDateString()}</td>
                        <td class="fw-bold text-navy">${i.first_name} ${i.last_name || ''}</td>
                        <td>${i.mrn || '-'}</td>
                        <td class="fs-10 uppercase">${i.invoice_type || 'General'}</td>
                        <td class="fw-bold">₹${parseFloat(i.total_amount).toFixed(2)}</td>
                        <td class="${balance > 0 ? 'text-danger fw-bold' : 'text-success'}">₹${balance.toFixed(2)}</td>
                        <td class="text-center fw-bold ${statusClass} fs-10 uppercase">${i.status || '-'}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-icon btn-light border"><i class="ri-printer-line"></i></button>
                        </td>
                    `;
                    list.appendChild(row);
                });
                document.getElementById('header_sales_total').innerText = totalSales.toFixed(2);
            } else {
                list.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No invoices found.</td></tr>';
            }
        } catch (e) {
            document.getElementById('loading_msg').style.display = 'none';
        }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>