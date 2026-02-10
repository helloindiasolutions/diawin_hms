<?php
$pageTitle = "Pharmacy Products";
ob_start();
?>

<div class="products-container">
    <!-- Main Content Header -->
    <div class="pos-content-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="page-title fw-semibold fs-20 mb-1">Pharmacy Products</h1>
                <p class="text-muted mb-0 fs-13">Manage pharmacy inventory, medication list, and stock categories.</p>
            </div>
        </div>
    </div>

    <!-- Filters and Actions Header -->
    <div class="products-header">
        <div class="header-grid">
            <!-- Search Field -->
            <div class="header-field search-field">
                <label>Find Product</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-primary">
                        <i class="ri-search-line"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm border-start-0" id="prodSearch"
                        placeholder="Search by name, generic, or barcode... [Ctrl+F]" onkeyup="fetchProducts()">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="header-field">
                <label>Category</label>
                <select class="form-select form-select-sm" id="category_filter" onchange="fetchProducts()">
                    <option value="">All Categories</option>
                </select>
            </div>

            <!-- Stock Status Filter -->
            <div class="header-field">
                <label>Stock Status</label>
                <select class="form-select form-select-sm" id="stock_status_filter" onchange="fetchProducts()">
                    <option value="">All Items</option>
                    <option>Low Stock</option>
                    <option>Out of Stock</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="header-actions">
                <button class="btn btn-action btn-primary" onclick="location.href='/products/create'">
                    <i class="ri-add-line"></i> ADD NEW (F2)
                </button>
                <button class="btn btn-action btn-secondary outline">
                    <i class="ri-file-excel-2-line"></i> IMPORT
                </button>
                <button class="btn btn-action btn-secondary outline">
                    <i class="ri-file-download-line"></i> EXPORT
                </button>
            </div>
        </div>
    </div>

    <div class="products-body">
        <div class="products-main">
            <div class="products-grid-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="width: 300px;">Product Name</th>
                            <th style="width: 140px;">SKU/Code</th>
                            <th style="width: 150px;">Category</th>
                            <th style="width: 100px;">Unit</th>
                            <th style="width: 120px;" class="text-center">HSN Code</th>
                            <th style="width: 80px;" class="text-center">Tax%</th>
                            <th style="width: 100px;" class="text-center">Status</th>
                            <th style="width: 100px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productList">
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

    .products-container {
        height: calc(100vh - 110px);
        display: flex;
        flex-direction: column;
        background: var(--bg-white);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    /* Filters Header Area */
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
        position: relative;
    }

    /* Table Styles */
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

    /* Zoho-Style Cell Styling */
    .product-name-cell {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .product-name {
        font-weight: 700;
        color: #1f2937;
        font-size: 13px;
    }

    .product-desc {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 500;
    }

    .sku-code {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        font-weight: 700;
        color: #6366f1;
        background: #eef2ff;
        padding: 0.15rem 0.5rem;
        border-radius: 3px;
        display: inline-block;
    }

    .category-badge {
        background: #f3f4f6;
        color: #374151;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-block;
    }

    .unit-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-block;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .status-badge.active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: flex-end;
    }

    .btn-icon {
        width: 28px;
        height: 28px;
        border: 1px solid var(--border-color);
        background: #fff;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        color: var(--text-secondary);
    }

    .btn-icon:hover {
        background: var(--primary-blue);
        border-color: var(--primary-blue);
        color: #fff;
    }

    /* Footer Stats */
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
    let preloadedProducts = [];
    // Initialize immediately - works for both initial load AND SPA navigation
    (function initProductsPage() {
        const productList = document.getElementById('productList');
        if (!productList) {
            console.log('Products script skipped - not on products page');
            return;
        }
        console.log('Products page: Initializing...');
        loadCategories();
        preloadProducts();

        // Add input listener for instant search
        const searchInput = document.getElementById('prodSearch');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                applyInstantFilter();
            });
        }

        // Only add keydown listener once
        if (!window._productsKeydownAdded) {
            document.addEventListener('keydown', (e) => {
                if (!document.getElementById('productList')) return;
                if (e.key === 'F2') { e.preventDefault(); location.href = '/products/create'; }
            });
            window._productsKeydownAdded = true;
        }

        // Event delegation for action buttons - only if element exists
        const productsListEl = document.getElementById('products_list');
        if (productsListEl && !productsListEl.dataset.listenerAdded) {
            productsListEl.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-icon');
                if (btn) {
                    e.stopPropagation();
                    const action = btn.dataset.action;
                    const id = btn.dataset.id;

                    if (action === 'edit') {
                        editProduct(id);
                    } else if (action === 'view') {
                        viewProduct(id);
                    }
                }
            });
            productsListEl.dataset.listenerAdded = 'true';
        }
    })();

    async function preloadProducts() {
        try {
            const res = await fetch('/api/v1/inventory/products?status=active');
            const data = await res.json();
            if (data.success) {
                preloadedProducts = data.data.products || [];
                renderProductsList(preloadedProducts);
            }
        } catch (e) {
            console.error('Product preload failed');
            fetchProducts();
        }
    }

    function applyInstantFilter() {
        const query = document.getElementById('prodSearch').value.toLowerCase().trim();
        const category = document.getElementById('category_filter').value;

        const filtered = preloadedProducts.filter(p => {
            const matchesQuery = !query ||
                p.name.toLowerCase().includes(query) ||
                (p.sku && p.sku.toLowerCase().includes(query));
            const matchesCategory = !category || p.category_id == category;

            return matchesQuery && matchesCategory;
        });

        renderProductsList(filtered);

        // If no results, maybe fetch from server for deep search
        if (filtered.length === 0 && query.length > 2) {
            clearTimeout(window._searchTimer);
            window._searchTimer = setTimeout(fetchProducts, 500);
        }
    }

    async function loadCategories() {
        try {
            const res = await fetch('/api/v1/inventory/categories?status=active');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('category_filter');
                data.data.categories.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    select.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Failed to load categories');
        }
    }

    function renderProductsList(products) {
        const list = document.getElementById('productList');
        if (!list) return;

        list.innerHTML = '';
        document.getElementById('header_count').innerText = products.length;
        document.getElementById('count_display').innerText = products.length;

        if (products.length === 0) {
            list.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No products found.</td></tr>';
            return;
        }

        products.forEach((p, index) => {
            const isActive = p.is_active == 1;
            const statusBadge = isActive
                ? '<span class="status-badge active">ACTIVE</span>'
                : '<span class="status-badge inactive">INACTIVE</span>';

            const row = document.createElement('tr');
            row.style.cursor = 'pointer';
            row.dataset.productId = p.product_id;
            row.addEventListener('click', function (e) {
                if (e.target.closest('.btn-icon')) return;
                viewProduct(this.dataset.productId);
            });

            row.innerHTML = `
                <td class="text-center text-muted fw-semibold">${index + 1}</td>
                <td>
                    <div class="product-name-cell">
                        <div class="product-name">${p.name}</div>
                        ${p.description ? `<div class="product-desc">${p.description}</div>` : ''}
                    </div>
                </td>
                <td><span class="sku-code">${p.sku || 'N/A'}</span></td>
                <td>${p.category_name ? `<span class="category-badge">${p.category_name}</span>` : '<span class="text-muted">-</span>'}</td>
                <td><span class="unit-badge">${p.unit || 'nos'}</span></td>
                <td class="text-center">${p.hsn_code || '-'}</td>
                <td class="text-center fw-bold">${p.tax_percent}%</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-end">
                    <div class="action-buttons">
                        <button class="btn-icon" data-action="edit" data-id="${p.product_id}" title="Edit">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn-icon" data-action="view" data-id="${p.product_id}" title="View">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                </td>
            `;
            list.appendChild(row);
        });
    }

    async function fetchProducts() {
        const search = document.getElementById('prodSearch').value;
        const category = document.getElementById('category_filter').value;
        const status = document.getElementById('stock_status_filter').value;

        document.getElementById('loading_msg').style.display = 'block';

        try {
            const res = await fetch(`/api/v1/inventory/products?search=${search}&category=${category}&status=${status}`);
            const data = await res.json();
            document.getElementById('loading_msg').style.display = 'none';

            if (data.success && data.data.products) {
                renderProductsList(data.data.products);
            }
        } catch (e) {
            document.getElementById('loading_msg').style.display = 'none';
        }
    }

    function editProduct(id) {
        location.href = `/products/edit/${id}`;
    }

    function viewProduct(id) {
        location.href = `/products/${id}`;
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>