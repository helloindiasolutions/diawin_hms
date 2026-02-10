<?php
/**
 * Siddha Store Sales Analysis
 * Dynamic API Integrated
 */
$pageTitle = "Siddha Store Analytics";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Siddha Store Sales Analysis</h2>
        <span class="text-muted fs-12">Monitor medication movement, category performance, and pharmacy revenue</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" id="export-excel"><i
                class="ri-file-excel-2-line align-middle me-1"></i> Export Data</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-9">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Monthly Movement Trend (Live)</div>
            </div>
            <div class="card-body">
                <div id="sales-chart-container" style="height: 300px;"
                    class="d-flex align-items-center justify-content-center bg-light rounded bg-opacity-50">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-2"></div>
                        <p class="text-muted mb-0">Synchronizing Siddha Store analytics...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title text-success">Product Categories</div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="category-distribution">
                    <!-- Populated dynamically -->
                    <li class="list-group-item text-center py-4 text-muted fs-12">Loading categories...</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Fast Moving Siddha Products</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100 table-sm" id="movement-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Item</th>
                                <th>Category</th>
                                <th>Quantity Sold</th>
                                <th>Avg Price</th>
                                <th>Net Revenue (₹)</th>
                                <th>Stock Status</th>
                            </tr>
                        </thead>
                        <tbody id="movement-body">
                            <tr>
                                <td colspan="6" class="text-center py-4">Fetching live stock movement data...</td>
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
        loadStoreAnalytics();

        async function loadStoreAnalytics() {
            try {
                const resp = await fetch('<?= baseUrl('/api/v1/inventory/products') ?>?report=sales_movement');
                const result = await resp.json();

                if (result.success && result.data.analytics) {
                    renderMovement(result.data.analytics.top_products);
                    renderCategories(result.data.analytics.categories);
                    // Chart logic would go here
                    document.getElementById('sales-chart-container').innerHTML = '<div class="text-success fw-bold">Analytics Engine Active</div>';
                } else {
                    document.getElementById('movement-body').innerHTML = '<tr><td colspan="6" class="text-center py-5">No sales data found for Siddha Store.</td></tr>';
                }
            } catch (e) {
                console.error('Analytics Fetch Error:', e);
            }
        }

        function renderMovement(products) {
            const body = document.getElementById('movement-body');
            body.innerHTML = '';
            products.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td><span class="fw-bold">${p.name}</span></td>
                <td>${p.category || 'Siddha Med'}</td>
                <td>${p.qty_sold} Units</td>
                <td>₹ ${parseFloat(p.avg_price).toLocaleString('en-IN')}</td>
                <td class="fw-bold">₹ ${parseFloat(p.revenue).toLocaleString('en-IN')}</td>
                <td><span class="badge bg-${p.stock > 10 ? 'success' : 'warning'}-transparent text-${p.stock > 10 ? 'success' : 'warning'}">${p.stock > 10 ? 'Stable' : 'Near Reorder'}</span></td>
            `;
                body.appendChild(tr);
            });
        }

        function renderCategories(cats) {
            const list = document.getElementById('category-distribution');
            list.innerHTML = '';
            cats.forEach(c => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between';
                li.innerHTML = `${c.name} <span class="fw-bold">${c.percentage}%</span>`;
                list.appendChild(li);
            });
        }
    });
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>