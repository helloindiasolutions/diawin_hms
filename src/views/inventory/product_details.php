<?php
$pageTitle = "Product Details";
$productId = $product_id ?? 0;
ob_start();
?>

<div class="product-details-container">
    <!-- Header with Back Button -->
    <div class="details-header">
        <button class="btn-back" onclick="history.back()">
            <i class="ri-arrow-left-line"></i> Back to Products
        </button>
        <div class="header-actions">
            <button class="btn-action btn-secondary" onclick="editProduct()">
                <i class="ri-edit-line"></i> Edit
            </button>
            <button class="btn-action btn-secondary" onclick="printDetails()">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>

    <!-- Product Info Card -->
    <div class="product-info-card">
        <div class="info-header">
            <div class="product-title-section">
                <h1 class="product-title" id="productName">Loading...</h1>
                <span class="status-badge" id="productStatus"></span>
            </div>
            <div class="product-meta">
                <div class="meta-item">
                    <span class="meta-label">SKU</span>
                    <span class="meta-value sku-code" id="productSku">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Category</span>
                    <span class="meta-value" id="productCategory">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Unit</span>
                    <span class="meta-value unit-badge" id="productUnit">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">HSN Code</span>
                    <span class="meta-value" id="productHsn">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Tax Rate</span>
                    <span class="meta-value" id="productTax">-</span>
                </div>
            </div>
        </div>
        <div class="info-description" id="productDescription"></div>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" data-tab="overview">Overview</button>
            <button class="tab-btn" data-tab="po-history">Purchase Orders</button>
            <button class="tab-btn" data-tab="grn-history">GRN History</button>
            <button class="tab-btn" data-tab="price-history">Price History</button>
            <button class="tab-btn" data-tab="suppliers">Suppliers</button>
            <button class="tab-btn" data-tab="stock">Stock Details</button>
        </div>

        <!-- Tab Content -->
        <div class="tabs-content">
            <!-- Overview Tab -->
            <div class="tab-pane active" id="overview">
                <div class="overview-grid">
                    <div class="overview-card">
                        <div class="card-icon bg-blue">
                            <i class="ri-shopping-cart-line"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-label">Total Purchased</div>
                            <div class="card-value" id="totalPurchased">0</div>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="card-icon bg-green">
                            <i class="ri-stock-line"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-label">Current Stock</div>
                            <div class="card-value" id="currentStock">0</div>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="card-icon bg-purple">
                            <i class="ri-price-tag-3-line"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-label">Avg. Cost Price</div>
                            <div class="card-value" id="avgCost">₹0.00</div>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="card-icon bg-orange">
                            <i class="ri-group-line"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-label">Suppliers</div>
                            <div class="card-value" id="totalSuppliers">0</div>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h3 class="section-title">Recent Activity</h3>
                    <div class="activity-list" id="recentActivity">
                        <div class="empty-state">No recent activity</div>
                    </div>
                </div>
            </div>

            <!-- PO History Tab -->
            <div class="tab-pane" id="po-history">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="poHistoryList">
                            <tr>
                                <td colspan="7" class="text-center py-4">No purchase orders found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- GRN History Tab -->
            <div class="tab-pane" id="grn-history">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>GRN Number</th>
                                <th>Date</th>
                                <th>Batch Number</th>
                                <th>Expiry Date</th>
                                <th>Quantity</th>
                                <th>Cost Price</th>
                                <th>Total Value</th>
                                <th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody id="grnHistoryList">
                            <tr>
                                <td colspan="8" class="text-center py-4">No GRN records found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Price History Tab -->
            <div class="tab-pane" id="price-history">
                <div class="price-chart-container">
                    <canvas id="priceChart"></canvas>
                </div>
                <div class="table-container mt-3">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Batch</th>
                                <th>Cost Price</th>
                                <th>Supplier</th>
                                <th>Change</th>
                            </tr>
                        </thead>
                        <tbody id="priceHistoryList">
                            <tr>
                                <td colspan="5" class="text-center py-4">No price history available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Suppliers Tab -->
            <div class="tab-pane" id="suppliers">
                <div class="suppliers-grid" id="suppliersList">
                    <div class="empty-state">No suppliers found</div>
                </div>
            </div>

            <!-- Stock Details Tab -->
            <div class="tab-pane" id="stock">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Batch Number</th>
                                <th>Expiry Date</th>
                                <th>Available Qty</th>
                                <th>Cost Price</th>
                                <th>Total Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="stockDetailsList">
                            <tr>
                                <td colspan="6" class="text-center py-4">No stock available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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

    .product-details-container {
        background: var(--bg-light);
        min-height: calc(100vh - 110px);
        padding: 1.5rem;
    }

    /* Header */
    .details-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .btn-back {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-back:hover {
        background: var(--bg-light);
        border-color: var(--primary-blue);
        color: var(--primary-blue);
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        font-size: 12px;
        font-weight: 700;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-action:hover {
        background: var(--primary-blue);
        border-color: var(--primary-blue);
        color: #fff;
    }

    /* Product Info Card */
    .product-info-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .product-title-section {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .product-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .status-badge.active {
        background: #d1fae5;
        color: #065f46;
    }

    .product-meta {
        display: flex;
        gap: 2rem;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .meta-label {
        font-size: 10px;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
    }

    .meta-value {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .sku-code {
        font-family: 'Courier New', monospace;
        color: #6366f1;
        background: #eef2ff;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }

    .unit-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        text-transform: uppercase;
    }

    .info-description {
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        color: var(--text-secondary);
        font-size: 13px;
    }

    /* Tabs */
    .tabs-container {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
    }

    .tabs-nav {
        display: flex;
        border-bottom: 2px solid var(--border-color);
        background: var(--bg-light);
    }

    .tab-btn {
        padding: 1rem 1.5rem;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        color: var(--primary-blue);
        background: rgba(59, 130, 246, 0.05);
    }

    .tab-btn.active {
        color: var(--primary-blue);
        border-bottom-color: var(--primary-blue);
        background: #fff;
    }

    .tabs-content {
        padding: 1.5rem;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    /* Overview Grid */
    .overview-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .overview-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.25rem;
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
    }

    .card-icon.bg-blue {
        background: #3b82f6;
    }

    .card-icon.bg-green {
        background: #10b981;
    }

    .card-icon.bg-purple {
        background: #8b5cf6;
    }

    .card-icon.bg-orange {
        background: #f59e0b;
    }

    .card-content {
        flex: 1;
    }

    .card-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }

    .card-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Tables */
    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: var(--bg-light);
        padding: 0.75rem;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-secondary);
        text-align: left;
        text-transform: uppercase;
        border-bottom: 2px solid var(--border-color);
    }

    .data-table td {
        padding: 0.75rem;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
    }

    .data-table tr:hover {
        background: var(--bg-light);
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-secondary);
        font-size: 13px;
    }

    /* Suppliers Grid */
    .suppliers-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .supplier-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1rem;
    }

    .supplier-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .supplier-info {
        font-size: 12px;
        color: var(--text-secondary);
    }

    /* Parent Layout Overrides */
    .main-content.app-content {
        padding-inline: 0 !important;
        margin-block-start: 8.5rem !important;
    }

    /* Activity Items */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .activity-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-light);
        border-radius: 6px;
        border: 1px solid var(--border-color);
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        background: var(--primary-blue);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .activity-meta {
        font-size: 11px;
        color: var(--text-secondary);
    }

    /* Status Badges */
    .badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge-draft {
        background: #e5e7eb;
        color: #6b7280;
    }

    .badge-ordered {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-received {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Text Colors */
    .text-success {
        color: #10b981;
    }

    .text-danger {
        color: #ef4444;
    }

    .text-center {
        text-align: center;
    }

    .py-4 {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .mt-3 {
        margin-top: 1rem;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    const productId = <?= $productId ?>;

    // Initialize immediately - works for both initial load AND SPA navigation
    (function initProductDetailsPage() {
        const productNameEl = document.getElementById('productName');
        if (!productNameEl) {
            console.log('Product Details script skipped - not on product details page');
            return;
        }
        console.log('Product Details page: Initializing...');
        loadProductDetails();
        setupTabs();
        loadOverview(); // Load overview tab by default
    })();

    function setupTabs() {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.dataset.tab;

                // Update buttons
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Update panes
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');

                // Load tab data
                loadTabData(tabId);
            });
        });
    }

    async function loadProductDetails() {
        try {
            const res = await fetch(`/api/v1/inventory/products/${productId}`);
            const result = await res.json();

            if (result.success) {
                const p = result.data;
                document.getElementById('productName').textContent = p.name;
                document.getElementById('productSku').textContent = p.sku;
                document.getElementById('productCategory').textContent = p.category_name || '-';
                document.getElementById('productUnit').textContent = p.unit || 'nos';
                document.getElementById('productHsn').textContent = p.hsn_code || '-';
                document.getElementById('productTax').textContent = p.tax_percent + '%';
                document.getElementById('productDescription').textContent = p.description || 'No description available';

                const statusBadge = document.getElementById('productStatus');
                statusBadge.textContent = p.is_active ? 'ACTIVE' : 'INACTIVE';
                statusBadge.className = 'status-badge ' + (p.is_active ? 'active' : 'inactive');
            } else {
                console.error('API Error:', result.message);
                document.getElementById('productName').textContent = 'Error loading product';
            }
        } catch (e) {
            console.error('Failed to load product details:', e);
            document.getElementById('productName').textContent = 'Error loading product';
        }
    }

    function loadTabData(tabId) {
        switch (tabId) {
            case 'overview':
                loadOverview();
                break;
            case 'po-history':
                loadPOHistory();
                break;
            case 'grn-history':
                loadGRNHistory();
                break;
            case 'price-history':
                loadPriceHistory();
                break;
            case 'suppliers':
                loadSuppliers();
                break;
            case 'stock':
                loadStockDetails();
                break;
        }
    }

    async function loadOverview() {
        try {
            const res = await fetch(`/api/v1/inventory/products/${productId}/overview`);
            const result = await res.json();

            if (result.success) {
                const data = result.data;
                document.getElementById('totalPurchased').textContent = data.total_purchased;
                document.getElementById('currentStock').textContent = data.current_stock;
                document.getElementById('avgCost').textContent = '₹' + data.avg_cost.toFixed(2);
                document.getElementById('totalSuppliers').textContent = data.total_suppliers;

                // Render recent activity
                const activityList = document.getElementById('recentActivity');
                if (data.recent_activity && data.recent_activity.length > 0) {
                    activityList.innerHTML = data.recent_activity.map(a => `
                        <div class="activity-item">
                            <div class="activity-icon"><i class="ri-file-list-line"></i></div>
                            <div class="activity-content">
                                <div class="activity-title">${a.type} - ${a.reference}</div>
                                <div class="activity-meta">
                                    ${a.quantity} units from ${a.supplier || 'N/A'} • ${new Date(a.date).toLocaleDateString()}
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    activityList.innerHTML = '<div class="empty-state">No recent activity</div>';
                }
            }
        } catch (e) {
            console.error('Failed to load overview:', e);
        }
    }

    async function loadPOHistory() {
        try {
            const res = await fetch(`/api/v1/inventory/products/${productId}/po-history`);
            const result = await res.json();

            if (result.success && result.data.po_history) {
                const tbody = document.getElementById('poHistoryList');
                if (result.data.po_history.length > 0) {
                    tbody.innerHTML = result.data.po_history.map(po => `
                        <tr>
                            <td><strong>${po.po_no}</strong></td>
                            <td>${new Date(po.po_date).toLocaleDateString()}</td>
                            <td>${po.supplier_name || 'N/A'}</td>
                            <td>${po.quantity}</td>
                            <td>₹${parseFloat(po.unit_price).toFixed(2)}</td>
                            <td>₹${parseFloat(po.total_amount).toFixed(2)}</td>
                            <td><span class="badge badge-${po.status}">${po.status}</span></td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No purchase orders found</td></tr>';
                }
            }
        } catch (e) {
            console.error('Failed to load PO history:', e);
        }
    }

    async function loadGRNHistory() {
        try {
            const res = await fetch(`/api/v1/inventory/products/${productId}/grn-history`);
            const result = await res.json();

            if (result.success && result.data.grn_history) {
                const tbody = document.getElementById('grnHistoryList');
                if (result.data.grn_history.length > 0) {
                    tbody.innerHTML = result.data.grn_history.map(grn => `
                        <tr>
                            <td><strong>${grn.grn_no}</strong></td>
                            <td>${new Date(grn.grn_date).toLocaleDateString()}</td>
                            <td>${grn.batch_no || 'N/A'}</td>
                            <td>${grn.expiry_date ? new Date(grn.expiry_date).toLocaleDateString() : 'N/A'}</td>
                            <td>${grn.quantity}</td>
                            <td>₹${parseFloat(grn.cost_price).toFixed(2)}</td>
                            <td>₹${parseFloat(grn.total_value).toFixed(2)}</td>
                            <td>${grn.supplier_name || 'N/A'}</td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No GRN records found</td></tr>';
                }
            }
        } catch (e) {
            console.error('Failed to load GRN history:', e);
        }
    }

    async function loadPriceHistory() {
        try {
            const res = await fetch(`/api/v1/inventory/products/${productId}/price-history`);
            const result = await res.json();

            if (result.success && result.data.price_history) {
                const tbody = document.getElementById('priceHistoryList');
                if (result.data.price_history.length > 0) {
                    tbody.innerHTML = result.data.price_history.map(price => {
                        const change = parseFloat(price.price_change_percent);
                        const changeClass = change > 0 ? 'text-danger' : change < 0 ? 'text-success' : '';
                        const changeIcon = change > 0 ? '↑' : change < 0 ? '↓' : '—';

                        return `
                            <tr>
                                <td>${new Date(price.date).toLocaleDateString()}</td>
                                <td>${price.batch || 'N/A'}</td>
                                <td>₹${parseFloat(price.cost_price).toFixed(2)}</td>
                                <td>${price.supplier_name || 'N/A'}</td>
                                <td class="${changeClass}">
                                    ${change !== 0 ? `${changeIcon} ${Math.abs(change).toFixed(2)}%` : '—'}
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No price history available</td></tr>';
                }
            }
        } catch (e) {
            console.error('Failed to load price history:', e);
        }
    }

    async function loadSuppliers() {
        try {
            const res = await fetch(`/api/v1/inventory/products/${productId}/suppliers`);
            const result = await res.json();

            if (result.success && result.data.suppliers) {
                const container = document.getElementById('suppliersList');
                if (result.data.suppliers.length > 0) {
                    container.innerHTML = result.data.suppliers.map(s => `
                        <div class="supplier-card">
                            <div class="supplier-name">${s.name}</div>
                            <div class="supplier-info">
                                <div><strong>Contact:</strong> ${s.contact_person || 'N/A'}</div>
                                <div><strong>Mobile:</strong> ${s.mobile || 'N/A'}</div>
                                <div><strong>Email:</strong> ${s.email || 'N/A'}</div>
                                <div><strong>Total Orders:</strong> ${s.total_orders}</div>
                                <div><strong>Total Quantity:</strong> ${s.total_quantity}</div>
                                <div><strong>Avg Price:</strong> ₹${parseFloat(s.avg_price).toFixed(2)}</div>
                                <div><strong>Last Order:</strong> ${new Date(s.last_order_date).toLocaleDateString()}</div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div class="empty-state">No suppliers found</div>';
                }
            }
        } catch (e) {
            console.error('Failed to load suppliers:', e);
        }
    }

    async function loadStockDetails() {
        try {
            const res = await fetch(`/api/v1/inventory/products/${productId}/stock`);
            const result = await res.json();

            if (result.success && result.data.stock) {
                const tbody = document.getElementById('stockDetailsList');
                if (result.data.stock.length > 0) {
                    tbody.innerHTML = result.data.stock.map(stock => {
                        const statusClass = stock.status === 'expired' ? 'badge-danger' :
                            stock.status === 'expiring_soon' ? 'badge-warning' : 'badge-success';
                        const statusText = stock.status === 'expired' ? 'Expired' :
                            stock.status === 'expiring_soon' ? 'Expiring Soon' : 'Active';

                        return `
                            <tr>
                                <td>${stock.batch_no || 'N/A'}</td>
                                <td>${stock.expiry_date ? new Date(stock.expiry_date).toLocaleDateString() : 'N/A'}</td>
                                <td>${stock.qty_available}</td>
                                <td>₹${parseFloat(stock.cost_price).toFixed(2)}</td>
                                <td>₹${parseFloat(stock.total_value).toFixed(2)}</td>
                                <td><span class="badge ${statusClass}">${statusText}</span></td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No stock available</td></tr>';
                }
            }
        } catch (e) {
            console.error('Failed to load stock details:', e);
        }
    }

    function editProduct() {
        location.href = `/products/edit/${productId}`;
    }

    function printDetails() {
        window.print();
    }
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>