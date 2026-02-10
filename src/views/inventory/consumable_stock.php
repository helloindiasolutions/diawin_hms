<?php
$pageTitle = "Consumable Stock";
ob_start();
?>

<div class="consumables-container">
    <!-- Main Content Header -->
    <div class="pos-content-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="page-title fw-semibold fs-20 mb-1">Consumable Stock</h1>
                <p class="text-muted mb-0 fs-13">Manage medical consumables, supplies, and stock levels.</p>
            </div>
        </div>
    </div>

    <!-- Filters and Actions Header -->
    <div class="consumables-header">
        <div class="header-grid">
            <!-- Search Field -->
            <div class="header-field search-field">
                <label>Find Consumable</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-primary">
                        <i class="ri-search-line"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm border-start-0" id="consumableSearch"
                        placeholder="Search by name or code..." onkeyup="fetchConsumables()">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="header-field">
                <label>Category</label>
                <select class="form-select form-select-sm" id="category_filter" onchange="fetchConsumables()">
                    <option value="">All Categories</option>
                    <option>Surgical Supplies</option>
                    <option>Disposables</option>
                    <option>Lab Supplies</option>
                    <option>Medical Equipment</option>
                </select>
            </div>

            <!-- Stock Status Filter -->
            <div class="header-field">
                <label>Stock Status</label>
                <select class="form-select form-select-sm" id="stock_filter" onchange="fetchConsumables()">
                    <option value="">All Items</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                    <option value="adequate">Adequate</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="header-actions">
                <button class="btn btn-action btn-primary" onclick="openAddConsumableModal()">
                    <i class="ri-add-line"></i> ADD ITEM
                </button>
                <button class="btn btn-action btn-secondary outline">
                    <i class="ri-file-download-line"></i> EXPORT
                </button>
            </div>
        </div>
    </div>

    <div class="consumables-body">
        <div class="consumables-main">
            <div class="consumables-grid-container">
                <table class="consumables-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 250px;">Item Name</th>
                            <th style="width: 120px;">Code</th>
                            <th style="width: 150px;">Category</th>
                            <th style="width: 80px;">Unit</th>
                            <th style="width: 100px;">Current Stock</th>
                            <th style="width: 100px;">Min. Level</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 60px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="consumableList">
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
    <footer class="consumables-footer">
        <div class="footer-stats">
            Showing <span id="count_display">0</span> items | <span class="text-primary">Total: <span
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

    .consumables-container {
        height: calc(100vh - 110px);
        display: flex;
        flex-direction: column;
        background: var(--bg-white);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    .consumables-header {
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

    .consumables-body {
        flex: 1;
        overflow: hidden;
        background: #f9fafb;
    }

    .consumables-main {
        height: 100%;
        padding: 0.5rem;
    }

    .consumables-grid-container {
        height: 100%;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        overflow-y: auto;
        position: relative;
    }

    .consumables-table {
        width: 100%;
        border-collapse: collapse;
    }

    .consumables-table th {
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

    .consumables-table td {
        padding: 0.5rem;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
    }

    .consumables-table tr:hover {
        background: #eff6ff;
    }

    .consumables-footer {
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
    (function initConsumablesPage() {
        const consumableList = document.getElementById('consumableList');
        if (!consumableList) {
            console.log('Consumables script skipped - not on consumables page');
            return;
        }
        console.log('Consumables page: Initializing...');
        fetchConsumables();
    })();

    async function fetchConsumables() {
        const list = document.getElementById('consumableList');
        const search = document.getElementById('consumableSearch').value;
        const category = document.getElementById('category_filter').value;
        const stock = document.getElementById('stock_filter').value;

        document.getElementById('loading_msg').style.display = 'block';
        list.innerHTML = '';

        try {
            const res = await fetch(`/api/v1/inventory/consumables?search=${search}&category=${category}&stock=${stock}`);
            const data = await res.json();
            document.getElementById('loading_msg').style.display = 'none';

            if (data.success && data.data.consumables && data.data.consumables.length > 0) {
                document.getElementById('header_count').innerText = data.data.consumables.length;
                document.getElementById('count_display').innerText = data.data.consumables.length;

                data.data.consumables.forEach((c, index) => {
                    const currentStock = parseInt(c.current_stock) || 0;
                    const minLevel = parseInt(c.min_level) || 0;

                    let statusClass = 'text-success';
                    let statusText = 'Adequate';

                    if (currentStock === 0) {
                        statusClass = 'text-danger';
                        statusText = 'Out of Stock';
                    } else if (currentStock <= minLevel) {
                        statusClass = 'text-warning';
                        statusText = 'Low Stock';
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="text-center text-muted">${index + 1}</td>
                        <td class="fw-bold">${c.name}</td>
                        <td>${c.code || 'N/A'}</td>
                        <td><span class="badge bg-light text-muted border">${c.category || '-'}</span></td>
                        <td>${c.unit || 'pcs'}</td>
                        <td class="text-center fw-bold">${currentStock}</td>
                        <td class="text-center">${minLevel}</td>
                        <td class="text-center fw-bold ${statusClass}" style="text-transform:uppercase; font-size:10px;">${statusText}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-icon btn-light border" onclick="editConsumable(${c.id})"><i class="ri-edit-line"></i></button>
                        </td>
                    `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No consumables found.</td></tr>';
            }
        } catch (e) {
            document.getElementById('loading_msg').style.display = 'none';
            list.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load consumables.</td></tr>';
        }
    }

    function openAddConsumableModal() {
        alert('Add Consumable functionality - To be implemented');
    }

    function editConsumable(id) {
        alert('Edit Consumable ' + id + ' - To be implemented');
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>