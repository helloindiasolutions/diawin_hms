<?php
$pageTitle = "Product Batches";
ob_start();
?>

<div class="batches-container">
    <!-- Main Content Header -->
    <div class="pos-content-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="page-title fw-semibold fs-20 mb-1">Product Batches</h1>
                <p class="text-muted mb-0 fs-13">Manage product batches, expiry dates, and batch-wise stock tracking.
                </p>
            </div>
        </div>
    </div>

    <!-- Filters and Actions Header -->
    <div class="batches-header">
        <div class="header-grid">
            <!-- Search Field -->
            <div class="header-field search-field">
                <label>Find Batch</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-primary">
                        <i class="ri-search-line"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm border-start-0" id="batchSearch"
                        placeholder="Search by batch number or product name..." onkeyup="fetchBatches()">
                </div>
            </div>

            <!-- Product Filter -->
            <div class="header-field">
                <label>Product</label>
                <select class="form-select form-select-sm" id="product_filter" onchange="fetchBatches()">
                    <option value="">All Products</option>
                </select>
            </div>

            <!-- Expiry Status Filter -->
            <div class="header-field">
                <label>Expiry Status</label>
                <select class="form-select form-select-sm" id="expiry_filter" onchange="fetchBatches()">
                    <option value="">All Batches</option>
                    <option value="expired">Expired</option>
                    <option value="expiring_soon">Expiring Soon (30 days)</option>
                    <option value="valid">Valid</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="header-actions">
                <button class="btn btn-action btn-primary" onclick="openAddBatchModal()">
                    <i class="ri-add-line"></i> ADD BATCH
                </button>
                <button class="btn btn-action btn-secondary outline">
                    <i class="ri-file-download-line"></i> EXPORT
                </button>
            </div>
        </div>
    </div>

    <div class="batches-body">
        <div class="batches-main">
            <div class="batches-grid-container">
                <table class="batches-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 200px;">Product Name</th>
                            <th style="width: 150px;">Batch Number</th>
                            <th style="width: 120px;">Manufacturing Date</th>
                            <th style="width: 120px;">Expiry Date</th>
                            <th style="width: 100px;">Quantity</th>
                            <th style="width: 100px;">MRP</th>
                            <th style="width: 100px;">Purchase Price</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 60px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="batchList">
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
    <footer class="batches-footer">
        <div class="footer-stats">
            Showing <span id="count_display">0</span> batches | <span class="text-primary">Total: <span
                    id="header_count">0</span></span>
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
        --primary-blue-dark: #2563eb;
        --border-color: #e5e7eb;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --bg-light: #f9fafb;
        --bg-white: #ffffff;
    }

    body {
        background: var(--bg-light);
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .batches-container {
        height: calc(100vh - 110px);
        display: flex;
        flex-direction: column;
        background: var(--bg-white);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    /* Filters Header Area */
    .batches-header {
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

    .header-field {
        display: flex;
        flex-direction: column;
    }

    .header-field label {
        font-size: 10px;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .form-control-sm,
    .form-select-sm {
        height: 32px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .form-control-sm:focus,
    .form-select-sm:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
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

    .btn-primary:hover {
        background: var(--primary-blue-dark);
    }

    .btn-secondary.outline {
        background: #fff;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary.outline:hover {
        background: var(--bg-light);
    }

    /* Body and Grid */
    .batches-body {
        flex: 1;
        overflow: hidden;
        background: #f9fafb;
    }

    .batches-main {
        height: 100%;
        padding: 0.5rem;
    }

    .batches-grid-container {
        height: 100%;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        overflow-y: auto;
        position: relative;
    }

    /* Table Styles */
    .batches-table {
        width: 100%;
        border-collapse: collapse;
    }

    .batches-table th {
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

    .batches-table td {
        padding: 0.5rem;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
    }

    .batches-table tr:hover {
        background: #eff6ff;
    }

    /* Footer Stats */
    .batches-footer {
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

    .footer-pagination {
        display: flex;
        gap: 0.25rem;
    }

    .btn-page {
        width: 28px;
        height: 28px;
        border: 1px solid var(--border-color);
        background: #fff;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .btn-page:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Parent Layout Overrides */
    .main-content.app-content {
        padding-inline: 1rem !important;
        margin-block-start: 8.5rem !important;
    }

    .main-content.app-content>.container-fluid {
        padding: 0 !important;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    // Initialize immediately - works for both initial load AND SPA navigation
    (function initBatchesPage() {
        const batchList = document.getElementById('batchList');
        if (!batchList) {
            console.log('Batches script skipped - not on batches page');
            return;
        }
        console.log('Batches page: Initializing...');
        fetchBatches();
        loadProducts();
    })();

    async function loadProducts() {
        try {
            const res = await fetch('/api/v1/inventory/products');
            const data = await res.json();

            if (data.success && data.data.products) {
                const select = document.getElementById('product_filter');
                data.data.products.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = p.name;
                    select.appendChild(option);
                });
            }
        } catch (e) {
            console.error('Failed to load products:', e);
        }
    }

    async function fetchBatches() {
        const list = document.getElementById('batchList');
        const search = document.getElementById('batchSearch').value;
        const product = document.getElementById('product_filter').value;
        const expiry = document.getElementById('expiry_filter').value;

        document.getElementById('loading_msg').style.display = 'block';
        list.innerHTML = '';

        try {
            const res = await fetch(`/api/v1/inventory/batches?search=${search}&product_id=${product}&expiry_status=${expiry}`);
            const data = await res.json();
            document.getElementById('loading_msg').style.display = 'none';

            if (data.success && data.data.batches && data.data.batches.length > 0) {
                document.getElementById('header_count').innerText = data.data.batches.length;
                document.getElementById('count_display').innerText = data.data.batches.length;

                data.data.batches.forEach((b, index) => {
                    const expiryDate = new Date(b.expiry_date);
                    const today = new Date();
                    const daysUntilExpiry = Math.floor((expiryDate - today) / (1000 * 60 * 60 * 24));

                    let statusClass = 'text-success';
                    let statusText = 'Valid';

                    if (daysUntilExpiry < 0) {
                        statusClass = 'text-danger';
                        statusText = 'Expired';
                    } else if (daysUntilExpiry <= 30) {
                        statusClass = 'text-warning';
                        statusText = 'Expiring Soon';
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="text-center text-muted">${index + 1}</td>
                        <td class="fw-bold tracking-tight">${b.product_name || 'N/A'}</td>
                        <td class="fw-semibold">${b.batch_number}</td>
                        <td class="text-muted fs-12">${b.manufacturing_date || '-'}</td>
                        <td class="text-muted fs-12">${b.expiry_date || '-'}</td>
                        <td class="text-center">${b.quantity || 0}</td>
                        <td class="text-end">₹${parseFloat(b.mrp || 0).toFixed(2)}</td>
                        <td class="text-end">₹${parseFloat(b.purchase_price || 0).toFixed(2)}</td>
                        <td class="text-center fw-bold ${statusClass}" style="text-transform:uppercase; font-size:10px;">${statusText}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-icon btn-light border" onclick="editBatch(${b.id})"><i class="ri-edit-line"></i></button>
                        </td>
                    `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No batches found.</td></tr>';
            }
        } catch (e) {
            document.getElementById('loading_msg').style.display = 'none';
            list.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Failed to load batches. Please try again.</td></tr>';
        }
    }

    function openAddBatchModal() {
        alert('Add Batch functionality - To be implemented');
    }

    function editBatch(id) {
        alert('Edit Batch ' + id + ' - To be implemented');
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>