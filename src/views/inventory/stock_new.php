<?php
$pageTitle = "Inventory & Stock Management";
ob_start();
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-1">Inventory & Stock Management</h1>
        <p class="text-muted mb-0 fs-12">Product-wise stock tracking with batch history</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-primary" onclick="window.location.href='/purchase-orders'">
            <i class="ri-add-line me-1"></i>New PO
        </button>
        <button class="btn btn-sm btn-success" onclick="window.location.href='/grn'">
            <i class="ri-inbox-line me-1"></i>GRN
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body p-2">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" id="searchProduct" placeholder="Search product...">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filterSupplier">
                    <option value="">All Suppliers</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100" onclick="loadProducts()">
                    <i class="ri-search-line me-1"></i>Search
                </button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-secondary w-100" onclick="exportStock()">
                    <i class="ri-download-line me-1"></i>Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-3 g-2" id="summaryCards">
    <div class="col-xl-3 col-md-6">
        <div class="card mb-0 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 fs-11 fw-semibold">Total Products</p>
                        <h4 class="mb-0 fs-20 fw-bold" id="totalProducts">0</h4>
                    </div>
                    <div class="avatar avatar-md bg-primary-transparent rounded-circle">
                        <i class="ri-medicine-bottle-line fs-20 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mb-0 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 fs-11 fw-semibold">Total Batches</p>
                        <h4 class="mb-0 fs-20 fw-bold" id="totalBatches">0</h4>
                    </div>
                    <div class="avatar avatar-md bg-success-transparent rounded-circle">
                        <i class="ri-stack-line fs-20 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mb-0 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 fs-11 fw-semibold">Low Stock</p>
                        <h4 class="mb-0 fs-20 fw-bold text-warning" id="lowStockCount">0</h4>
                    </div>
                    <div class="avatar avatar-md bg-warning-transparent rounded-circle">
                        <i class="ri-error-warning-line fs-20 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mb-0 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 fs-11 fw-semibold">Near Expiry</p>
                        <h4 class="mb-0 fs-20 fw-bold text-danger" id="nearExpiryCount">0</h4>
                    </div>
                    <div class="avatar avatar-md bg-danger-transparent rounded-circle">
                        <i class="ri-alarm-warning-line fs-20 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Products List View -->
<div class="card border-0 shadow-sm" id="productsListView">
    <div class="card-header bg-white p-3 border-bottom">
        <h6 class="mb-0 fs-15 fw-semibold">Products in Stock</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                <thead style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                    <tr>
                        <th class="py-2 px-3" style="width: 40px;">#</th>
                        <th class="py-2 px-3">Product Name</th>
                        <th class="py-2 px-3" style="width: 100px;">SKU</th>
                        <th class="py-2 px-3 text-center" style="width: 80px;">Total Qty</th>
                        <th class="py-2 px-3 text-center" style="width: 80px;">Batches</th>
                        <th class="py-2 px-3 text-end" style="width: 100px;">Avg Cost</th>
                        <th class="py-2 px-3 text-end" style="width: 100px;">MRP</th>
                        <th class="py-2 px-3 text-center" style="width: 100px;">Status</th>
                        <th class="py-2 px-3 text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2 mb-0 fs-12">Loading products...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Batches Detail View (Hidden by default) -->
<div class="card border-0 shadow-sm" id="productBatchesView" style="display: none;">
    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-light border" onclick="showProductsList()">
                <i class="ri-arrow-left-line me-1"></i> Back to Products
            </button>
            <div class="vr"></div>
            <div>
                <span class="fs-15 fw-semibold" id="productDetailName">Product Batches</span>
                <span class="badge bg-primary-transparent text-primary ms-2" id="productDetailSKU"></span>
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" style="font-size: 11px;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th class="py-2 px-2" style="width: 30px;">#</th>
                        <th class="py-2 px-2" style="width: 100px;">Batch No</th>
                        <th class="py-2 px-2" style="width: 100px;">PO Number</th>
                        <th class="py-2 px-2">Supplier</th>
                        <th class="py-2 px-2 text-center" style="width: 60px;">Qty</th>
                        <th class="py-2 px-2 text-end" style="width: 90px;">Cost Price</th>
                        <th class="py-2 px-2 text-end" style="width: 90px;">MRP</th>
                        <th class="py-2 px-2 text-center" style="width: 90px;">Mfg Date</th>
                        <th class="py-2 px-2 text-center" style="width: 90px;">Exp Date</th>
                        <th class="py-2 px-2 text-center" style="width: 90px;">Received</th>
                        <th class="py-2 px-2 text-center" style="width: 80px;">Status</th>
                    </tr>
                </thead>
                <tbody id="batchesTableBody">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    // Page guard - only run on stock page
    if (!document.getElementById('productsTableBody')) {
        console.log('Stock page script skipped - not on stock page');
        // Define no-op fallbacks
        if (typeof window.showProductBatches === 'undefined') window.showProductBatches = function() {};
        if (typeof window.showProductsList === 'undefined') window.showProductsList = function() {};
        if (typeof window.loadProducts === 'undefined') window.loadProducts = function() {};
        if (typeof window.exportStock === 'undefined') window.exportStock = function() {};
    } else {
        let currentProductId = null;
        let allProducts = [];

        // Initialize page
        (function initStockPage() {
            console.log('Stock page: Initializing...');
            loadProducts();
            loadSuppliers();
        })();

        async function loadSuppliers() {
            const select = document.getElementById('filterSupplier');
            if (!select) return;

            try {
                const res = await fetch('/api/v1/inventory/suppliers');
                const data = await res.json();
                if (data.success) {
                    select.innerHTML = '<option value="">All Suppliers</option>';
                    data.data.suppliers.forEach(s => {
                        const option = document.createElement('option');
                        option.value = s.supplier_id;
                        option.textContent = s.name;
                        select.appendChild(option);
                    });
                }
            } catch (e) {
                console.error('Failed to load suppliers:', e);
            }
        }

        async function loadProducts() {
            const tbody = document.getElementById('productsTableBody');
            if (!tbody) return;

            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></td></tr>';

            try {
                const search = document.getElementById('searchProduct').value;
                const supplier = document.getElementById('filterSupplier').value;
                const status = document.getElementById('filterStatus').value;

                const res = await fetch(`/api/v1/inventory/stock-products?search=${search}&supplier=${supplier}&status=${status}`);
                const data = await res.json();

                if (data.success && data.data.products) {
                    allProducts = data.data.products;
                    renderProducts(data.data.products);
                    updateSummary(data.data.summary);
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="ri-inbox-line fs-32 text-muted"></i>
                                <p class="text-muted mt-2 mb-0">No products found</p>
                            </td>
                        </tr>
                    `;
                }
            } catch (e) {
                console.error('Failed to load products:', e);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-3">
                            <span class="text-danger">Failed to load products</span>
                        </td>
                    </tr>
                `;
            }
        }

        function renderProducts(products) {
            const tbody = document.getElementById('productsTableBody');
            if (!tbody) return;

            if (products.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="ri-inbox-line fs-32 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">No products in stock</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = products.map((product, index) => {
                const statusBadge = product.total_qty > 10 
                    ? '<span class="badge bg-success-transparent text-success">In Stock</span>'
                    : product.total_qty > 0
                    ? '<span class="badge bg-warning-transparent text-warning">Low Stock</span>'
                    : '<span class="badge bg-danger-transparent text-danger">Out of Stock</span>';

                return `
                    <tr style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor=''" onclick="showProductBatches(${product.product_id})">
                        <td class="py-3 px-3">${index + 1}</td>
                        <td class="py-3 px-3 fw-semibold text-dark">${product.product_name}</td>
                        <td class="py-3 px-3"><code class="fs-11 bg-light px-2 py-1 rounded">${product.sku || 'N/A'}</code></td>
                        <td class="py-3 px-3 text-center"><span class="badge bg-primary fs-11">${product.total_qty}</span></td>
                        <td class="py-3 px-3 text-center"><span class="badge bg-secondary-transparent fs-11">${product.batch_count}</span></td>
                        <td class="py-3 px-3 text-end fw-semibold">₹${parseFloat(product.avg_cost || 0).toFixed(2)}</td>
                        <td class="py-3 px-3 text-end fw-semibold">₹${parseFloat(product.mrp || 0).toFixed(2)}</td>
                        <td class="py-3 px-3 text-center">${statusBadge}</td>
                        <td class="py-3 px-3 text-center">
                            <button class="btn btn-sm btn-primary-light py-1 px-2" onclick="event.stopPropagation(); showProductBatches(${product.product_id})">
                                <i class="ri-eye-line fs-12"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function showProductBatches(productId) {
            currentProductId = productId;
            
            // Find product details
            const product = allProducts.find(p => p.product_id == productId);
            if (!product) return;

            // Update header
            document.getElementById('productDetailName').textContent = product.product_name;
            document.getElementById('productDetailSKU').textContent = product.sku || 'N/A';

            // Hide summary cards and products list, show batches view
            document.getElementById('summaryCards').style.display = 'none';
            document.getElementById('productsListView').style.display = 'none';
            document.getElementById('productBatchesView').style.display = 'block';

            // Load batches
            const tbody = document.getElementById('batchesTableBody');
            tbody.innerHTML = '<tr><td colspan="11" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></td></tr>';

            try {
                const res = await fetch(`/api/v1/inventory/batches?product_id=${productId}`);
                const data = await res.json();

                if (data.success && data.data.batches) {
                    renderBatches(data.data.batches);
                } else {
                    tbody.innerHTML = '<tr><td colspan="11" class="text-center py-3 text-muted">No batches found</td></tr>';
                }
            } catch (e) {
                console.error('Failed to load batches:', e);
                tbody.innerHTML = '<tr><td colspan="11" class="text-center py-3 text-danger">Failed to load batches</td></tr>';
            }
        }

        function renderBatches(batches) {
            const tbody = document.getElementById('batchesTableBody');
            if (!tbody) return;

            tbody.innerHTML = batches.map((batch, index) => {
                const expDate = batch.exp_date ? new Date(batch.exp_date) : null;
                const isExpired = expDate && expDate < new Date();
                const isNearExpiry = expDate && !isExpired && expDate < new Date(Date.now() + 90 * 24 * 60 * 60 * 1000);

                const statusBadge = isExpired 
                    ? '<span class="badge bg-danger">Expired</span>'
                    : isNearExpiry
                    ? '<span class="badge bg-warning">Near Expiry</span>'
                    : '<span class="badge bg-success">Active</span>';

                return `
                    <tr>
                        <td class="py-1 px-2">${index + 1}</td>
                        <td class="py-1 px-2"><code class="fs-10">${batch.batch_number || 'N/A'}</code></td>
                        <td class="py-1 px-2"><code class="fs-10">${batch.po_number || 'N/A'}</code></td>
                        <td class="py-1 px-2">${batch.supplier_name || 'N/A'}</td>
                        <td class="py-1 px-2 text-center"><span class="badge bg-primary-transparent">${batch.quantity}</span></td>
                        <td class="py-1 px-2 text-end">₹${parseFloat(batch.purchase_price || 0).toFixed(2)}</td>
                        <td class="py-1 px-2 text-end">₹${parseFloat(batch.mrp || 0).toFixed(2)}</td>
                        <td class="py-1 px-2 text-center fs-10">${batch.mfg_date || 'N/A'}</td>
                        <td class="py-1 px-2 text-center fs-10">${batch.exp_date || 'N/A'}</td>
                        <td class="py-1 px-2 text-center fs-10">${batch.receipt_date ? new Date(batch.receipt_date).toLocaleDateString() : 'N/A'}</td>
                        <td class="py-1 px-2 text-center">${statusBadge}</td>
                    </tr>
                `;
            }).join('');
        }

        function showProductsList() {
            document.getElementById('summaryCards').style.display = 'flex';
            document.getElementById('productsListView').style.display = 'block';
            document.getElementById('productBatchesView').style.display = 'none';
            currentProductId = null;
        }

        function updateSummary(summary) {
            if (!summary) return;
            
            document.getElementById('totalProducts').textContent = summary.total_products || 0;
            document.getElementById('totalBatches').textContent = summary.total_batches || 0;
            document.getElementById('lowStockCount').textContent = summary.low_stock || 0;
            document.getElementById('nearExpiryCount').textContent = summary.near_expiry || 0;
        }

        function exportStock() {
            window.open('/api/v1/inventory/stock-export', '_blank');
        }

        // Make functions globally accessible
        window.showProductBatches = showProductBatches;
        window.showProductsList = showProductsList;
        window.loadProducts = loadProducts;
        window.exportStock = exportStock;
    }
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>
