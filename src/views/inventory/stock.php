<?php
$pageTitle = "Inventory & Stock Management";
ob_start();
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title fw-semibold fs-20 mb-1">Inventory & Stock Management</h1>
        <p class="text-muted mb-0 fs-13">Batch-wise inventory tracking with complete traceability from PO to GRN</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" onclick="window.location.href='/purchase-orders'">
            <i class="ri-add-line me-1"></i>New Purchase Order
        </button>
        <button class="btn btn-success" onclick="window.location.href='/grn'">
            <i class="ri-inbox-line me-1"></i>Create GRN
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fs-12 fw-semibold">Search Product</label>
                <input type="text" class="form-control" id="searchProduct" placeholder="Product name or SKU...">
            </div>
            <div class="col-md-2">
                <label class="form-label fs-12 fw-semibold">Supplier</label>
                <select class="form-select" id="filterSupplier">
                    <option value="">All Suppliers</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-12 fw-semibold">Status</label>
                <select class="form-select" id="filterStatus">
                    <option value="">All Batches</option>
                    <option value="active">Active</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="near_expiry">Near Expiry</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-12 fw-semibold">View Mode</label>
                <select class="form-select" id="viewMode">
                    <option value="batch">Batch-wise</option>
                    <option value="product">Product-wise</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fs-12 fw-semibold">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary w-100" onclick="applyFilters()">
                        <i class="ri-search-line me-1"></i>Search
                    </button>
                    <button class="btn btn-secondary" onclick="resetFilters()">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 fs-12">Total Batches</p>
                        <h4 class="mb-0" id="totalBatches">0</h4>
                    </div>
                    <div class="avatar avatar-lg bg-primary-transparent">
                        <i class="ri-stack-line fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 fs-12">Total Products</p>
                        <h4 class="mb-0" id="totalProducts">0</h4>
                    </div>
                    <div class="avatar avatar-lg bg-success-transparent">
                        <i class="ri-medicine-bottle-line fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 fs-12">Low Stock Items</p>
                        <h4 class="mb-0 text-warning" id="lowStockCount">0</h4>
                    </div>
                    <div class="avatar avatar-lg bg-warning-transparent">
                        <i class="ri-error-warning-line fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 fs-12">Near Expiry</p>
                        <h4 class="mb-0 text-danger" id="nearExpiryCount">0</h4>
                    </div>
                    <div class="avatar avatar-lg bg-danger-transparent">
                        <i class="ri-alarm-warning-line fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Batch List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inventory Batches</h6>
        <button class="btn btn-sm btn-primary" onclick="exportInventory()">
            <i class="ri-download-line me-1"></i>Export
        </button>
    </div>
    <div class="card-body">
        <div id="batchesContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Loading inventory data...</p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    let preloadedBatches = [];

    // Page guard - only run on stock page
    if (!document.getElementById('batchesContainer')) {
        console.log('Stock page script skipped - not on stock page');
    } else {
        // Initialize page - works for both initial load and SPA navigation
        (function initStockPage() {
            console.log('Stock page: Initializing...');
            loadInventoryData();
            loadSuppliers();
            initInstantSearch();
        })();
    }

    function initInstantSearch() {
        const searchInput = document.getElementById('searchProduct');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                // ⚡ Instant filter on input for snappy feel
                applyFilters(true);
            });
        }
    }

    async function loadSuppliers() {
        const select = document.getElementById('filterSupplier');
        if (!select) return;

        try {
            const res = await fetch('/api/v1/inventory/suppliers');
            const data = await res.json();
            if (data.success) {
                // Clear existing options except first (All Suppliers)
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

    async function loadInventoryData() {
        const container = document.getElementById('batchesContainer');
        if (!container) return;

        try {
            const res = await fetch('/api/v1/inventory/batches');
            const data = await res.json();

            if (data.success && data.data.batches) {
                preloadedBatches = data.data.batches;
                renderBatches(preloadedBatches);
                updateSummary(preloadedBatches);
            } else {
                container.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-inbox-line fs-48 text-muted"></i>
                    <p class="text-muted mt-3">No inventory batches found</p>
                    <button class="btn btn-primary mt-2" onclick="window.location.href='/purchase-orders'">
                        <i class="ri-add-line me-1"></i>Create Purchase Order
                    </button>
                </div>
            `;
            }
        } catch (e) {
            console.error('Failed to load inventory:', e);
            const container = document.getElementById('batchesContainer');
            if (container) {
                container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    Failed to load inventory data. Please try again.
                </div>
            `;
            }
        }
    }

    function renderBatches(batches) {
        const container = document.getElementById('batchesContainer');
        if (!container) return;

        if (batches.length === 0) {
            container.innerHTML = `
            <div class="text-center py-5">
                <i class="ri-inbox-line fs-48 text-muted"></i>
                <p class="text-muted mt-3">No batches found</p>
            </div>
        `;
            return;
        }

        let html = '';
        batches.forEach(batch => {
            const isExpired = new Date(batch.exp_date) < new Date();
            const isNearExpiry = !isExpired && new Date(batch.exp_date) < new Date(Date.now() + 90 * 24 * 60 * 60 * 1000);

            html += `
            <div class="card mb-3 border">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1">
                                <i class="ri-stack-line me-1"></i>
                                Batch: ${batch.batch_number}
                                ${isExpired ? '<span class="badge bg-danger ms-2">Expired</span>' : ''}
                                ${isNearExpiry ? '<span class="badge bg-warning ms-2">Near Expiry</span>' : ''}
                            </h6>
                            <small class="text-muted">
                                PO: ${batch.po_number || 'N/A'} | 
                                GRN: ${batch.grn_number || 'N/A'} | 
                                Supplier: ${batch.supplier_name || 'N/A'}
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">Received: ${batch.receipt_date || 'N/A'}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">MRP</th>
                                    <th class="text-end">Purchase Price</th>
                                    <th>Mfg Date</th>
                                    <th>Exp Date</th>
                                    <th>Rack</th>
                                    <th>QC Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">${batch.product_name}</td>
                                    <td><code>${batch.sku || 'N/A'}</code></td>
                                    <td class="text-center">
                                        <span class="badge ${batch.quantity > 0 ? 'bg-success' : 'bg-danger'}">
                                            ${batch.quantity}
                                        </span>
                                    </td>
                                    <td class="text-end">₹${parseFloat(batch.mrp || 0).toFixed(2)}</td>
                                    <td class="text-end">₹${parseFloat(batch.purchase_price || 0).toFixed(2)}</td>
                                    <td>${batch.mfg_date || 'N/A'}</td>
                                    <td>${batch.exp_date || 'N/A'}</td>
                                    <td>${batch.rack_location || 'Not Set'}</td>
                                    <td>
                                        <span class="badge bg-${batch.qc_status === 'approved' ? 'success' : 'warning'}">
                                            ${batch.qc_status || 'pending'}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        });

        container.innerHTML = html;
    }

    function updateSummary(batches) {
        const totalBatches = batches.length;
        const uniqueProducts = new Set(batches.map(b => b.product_id)).size;
        const lowStock = batches.filter(b => b.quantity < 10).length;
        const nearExpiry = batches.filter(b => {
            const expDate = new Date(b.exp_date);
            const now = new Date();
            const threeMonths = new Date(now.getTime() + 90 * 24 * 60 * 60 * 1000);
            return expDate > now && expDate < threeMonths;
        }).length;

        const totalBatchesEl = document.getElementById('totalBatches');
        const totalProductsEl = document.getElementById('totalProducts');
        const lowStockEl = document.getElementById('lowStockCount');
        const nearExpiryEl = document.getElementById('nearExpiryCount');

        if (totalBatchesEl) totalBatchesEl.textContent = totalBatches;
        if (totalProductsEl) totalProductsEl.textContent = uniqueProducts;
        if (lowStockEl) lowStockEl.textContent = lowStock;
        if (nearExpiryEl) nearExpiryEl.textContent = nearExpiry;
    }

    function applyFilters(isInstant = false) {
        const query = document.getElementById('searchProduct').value.toLowerCase().trim();
        const supplier = document.getElementById('filterSupplier').value;
        const status = document.getElementById('filterStatus').value;

        // ⚡ Filter locally for instant response
        let filtered = preloadedBatches.filter(batch => {
            const matchesQuery = !query || 
                batch.product_name.toLowerCase().includes(query) || 
                (batch.sku && batch.sku.toLowerCase().includes(query)) ||
                (batch.batch_number && batch.batch_number.toLowerCase().includes(query));
            
            const matchesSupplier = !supplier || batch.supplier_id == supplier;
            
            let matchesStatus = true;
            if (status) {
                const isExpired = new Date(batch.exp_date) < new Date();
                const isNearExpiry = !isExpired && new Date(batch.exp_date) < new Date(Date.now() + 90 * 24 * 60 * 60 * 1000);
                const isLowStock = batch.quantity < 10;

                if (status === 'active') matchesStatus = !isExpired;
                else if (status === 'low_stock') matchesStatus = isLowStock;
                else if (status === 'near_expiry') matchesStatus = isNearExpiry;
                else if (status === 'expired') matchesStatus = isExpired;
            }

            return matchesQuery && matchesSupplier && matchesStatus;
        });

        renderBatches(filtered);
        updateSummary(filtered);

        // If no results and NOT instant, maybe fetch from server as fallback
        if (filtered.length === 0 && !isInstant) {
            loadInventoryData();
        }
    }

    function resetFilters() {
        const searchEl = document.getElementById('searchProduct');
        const supplierEl = document.getElementById('filterSupplier');
        const statusEl = document.getElementById('filterStatus');
        const viewModeEl = document.getElementById('viewMode');

        if (searchEl) searchEl.value = '';
        if (supplierEl) supplierEl.value = '';
        if (statusEl) statusEl.value = '';
        if (viewModeEl) viewModeEl.value = 'batch';

        loadInventoryData();
    }

    function exportInventory() {
        modalNotify.info('Export functionality coming soon');
    }
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>