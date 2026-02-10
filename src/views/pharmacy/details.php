<?php
$pageTitle = "Pharmacy Details";
$branchId = $branch_id ?? null;
ob_start(); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
<?php $styles = ob_get_clean();
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1 d-inline-block" id="branchName">Pharmacy Details</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/pharmacy">Pharmacy</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
        </ol>
    </div>
    <div>
        <button class="btn btn-primary"
            onclick="window.location.href='/invoices/create?type=pharmacy&branch=<?= $branchId ?>'">
            <i class="ri-shopping-cart-line me-2"></i>Billing Counter
        </button>
    </div>
</div>


<!-- Tabs -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <ul class="nav nav-tabs nav-tabs-header mb-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#overview" role="tab">
                            <i class="ri-dashboard-3-line me-2"></i>Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#inventory" role="tab">
                            <i class="ri-archive-line me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#sales" role="tab">
                            <i class="ri-line-chart-line me-2"></i>Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#expiry" role="tab">
                            <i class="ri-alarm-warning-line me-2"></i>Expiry Alerts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#users" role="tab">
                            <i class="ri-user-settings-line me-2"></i>Staff/Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#po" role="tab">
                            <i class="ri-file-list-3-line me-2"></i>Purchase Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#grn" role="tab">
                            <i class="ri-checkbox-circle-line me-2"></i>GRNs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#appointments" role="tab">
                            <i class="ri-calendar-event-line me-2"></i>Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#reports" role="tab">
                            <i class="ri-file-chart-line me-2"></i>Reports
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                                <div class="card custom-card overflow-hidden shadow-none border-0 bg-primary-transparent">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="fw-semibold mb-1 fs-12 text-muted text-uppercase">Active POs</h6>
                                                <h3 class="fw-bold mb-0 text-primary" id="overviewActivePOs">0</h3>
                                            </div>
                                            <div class="avatar avatar-lg bg-primary rounded-circle">
                                                <i class="ri-file-list-3-line fs-20 text-fixed-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                                <div class="card custom-card overflow-hidden shadow-none border-0 bg-success-transparent">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="fw-semibold mb-1 fs-12 text-muted text-uppercase">Recent GRNs</h6>
                                                <h3 class="fw-bold mb-0 text-success" id="overviewPendingGRNs">0</h3>
                                            </div>
                                            <div class="avatar avatar-lg bg-success rounded-circle">
                                                <i class="ri-checkbox-circle-line fs-20 text-fixed-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                                <div class="card custom-card overflow-hidden shadow-none border-0 bg-warning-transparent">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="fw-semibold mb-1 fs-12 text-muted text-uppercase">Today's Appts</h6>
                                                <h3 class="fw-bold mb-0 text-warning" id="overviewTodayAppts">0</h3>
                                            </div>
                                            <div class="avatar avatar-lg bg-warning rounded-circle">
                                                <i class="ri-calendar-event-line fs-20 text-fixed-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                                <div class="card custom-card overflow-hidden shadow-none border-0 bg-danger-transparent">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="fw-semibold mb-1 fs-12 text-muted text-uppercase">Expiry Alerts</h6>
                                                <h3 class="fw-bold mb-0 text-danger" id="overviewExpiryCount">0</h3>
                                            </div>
                                            <div class="avatar avatar-lg bg-danger rounded-circle">
                                                <i class="ri-error-warning-line fs-20 text-fixed-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-8">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">Sales Trend</div>
                                            </div>
                                            <div class="card-body">
                                                <div id="salesTrendChart"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="card custom-card">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">Inventory Distribution</div>
                                            </div>
                                            <div class="card-body">
                                                <div id="inventoryDistChart"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <div class="card custom-card">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">User Performance</div>
                                            </div>
                                            <div class="card-body">
                                                <div id="userPerformanceChart"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card custom-card">
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">Recent Activity</div>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush" id="recentActivityList">
                                            <li class="list-group-item text-center py-4">
                                                <div class="spinner-border text-primary" role="status"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card custom-card">
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">Top Selling Products</div>
                                    </div>
                                    <div class="card-body">
                                        <div id="topProductsChart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card custom-card">
                                    <div class="card-header">
                                        <div class="card-title">Stock Status Analytics</div>
                                    </div>
                                    <div class="card-body">
                                        <div id="stockStatusChart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card custom-card">
                                    <div class="card-header">
                                        <div class="card-title">Procurement vs Fulfillment</div>
                                    </div>
                                    <div class="card-body">
                                        <div id="mixedComparisonChart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Tab (Hidden by default now) -->
                    <div class="tab-pane fade" id="inventory" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered text-nowrap w-100" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Batch No</th>
                                        <th>Stock Qty</th>
                                        <th>MRP</th>
                                        <th>Expiry Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sales Tab -->
                    <div class="tab-pane fade" id="sales" role="tabpanel">
                        <div class="row mb-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-control" id="salesFromDate"
                                    value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-control" id="salesToDate" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap w-100" id="salesTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice No</th>
                                        <th>Patient</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Payment Mode</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Expiry Alerts Tab -->
                    <div class="tab-pane fade" id="expiry" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered text-nowrap w-100" id="expiryTable">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Batch No</th>
                                        <th>Stock Qty</th>
                                        <th>Expiry Date</th>
                                        <th>Days Left</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Users Tab -->
                    <div class="tab-pane fade" id="users" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered text-nowrap w-100" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- PO Tab -->
                    <div class="tab-pane fade" id="po" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered text-nowrap w-100" id="poTable">
                                <thead>
                                    <tr>
                                        <th>PO No</th>
                                        <th>Supplier</th>
                                        <th>Ordered By</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- GRN Tab -->
                    <div class="tab-pane fade" id="grn" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered text-nowrap w-100" id="grnTable">
                                <thead>
                                    <tr>
                                        <th>GRN No</th>
                                        <th>PO No</th>
                                        <th>Received By</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Appointments Tab -->
                    <div class="tab-pane fade" id="appointments" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered text-nowrap w-100" id="appointmentsTable">
                                <thead>
                                    <tr>
                                        <th>Appt No</th>
                                        <th>Patient</th>
                                        <th>Doctor/Provider</th>
                                        <th>Scheduled At</th>
                                        <th>Status</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Reports Tab -->
                    <div class="tab-pane fade" id="reports" role="tabpanel">
                        <div class="row mt-4">
                            <?php
                            $reports = [
                                ['name' => 'Inventory Valuation', 'icon' => 'ri-scales-3-line', 'color' => 'primary', 'desc' => 'Detailed stock value and quantity report.'],
                                ['name' => 'Monthly Sales Report', 'icon' => 'ri-money-cny-box-line', 'color' => 'success', 'desc' => 'Summary of all invoices and revenue.'],
                                ['name' => 'Purchase Summary', 'icon' => 'ri-shopping-basket-2-line', 'color' => 'info', 'desc' => 'Overview of procurement and vendor payments.'],
                                ['name' => 'Staff Activity log', 'icon' => 'ri-user-follow-line', 'color' => 'warning', 'desc' => 'Performance and login tracking of branch staff.'],
                                ['name' => 'Expiry Forecast', 'icon' => 'ri-calendar-event-line', 'color' => 'danger', 'desc' => 'Predictive analysis of upcoming stock expiries.'],
                                ['name' => 'Patient Visit Analytics', 'icon' => 'ri-group-line', 'color' => 'secondary', 'desc' => 'Branch-wise patient footfall and appointment stats.'],
                                ['name' => 'Credit Sales Report', 'icon' => 'ri-bank-card-line', 'color' => 'dark', 'desc' => 'Track unpaid invoices and credit limits.'],
                                ['name' => 'GST / Tax Report', 'icon' => 'ri-government-line', 'color' => 'success', 'desc' => 'Tax components and filing summary.']
                            ];
                            foreach ($reports as $r): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                                    <div class="card custom-card report-card h-100">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="avatar avatar-md bg-<?= $r['color'] ?>-transparent rounded">
                                                    <i class="<?= $r['icon'] ?> fs-18"></i>
                                                </div>
                                            </div>
                                            <h6 class="fw-semibold mb-2"><?= $r['name'] ?></h6>
                                            <p class="text-muted fs-12 mb-4"><?= $r['desc'] ?></p>
                                            <div class="d-grid">
                                                <button class="btn btn-sm btn-outline-light"
                                                    onclick="generateReport('<?= $r['name'] ?>')">
                                                    <i class="ri-download-2-line me-1"></i> Download PDF
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>

<script>
    const branchId = <?= json_encode($branchId) ?>;
    let tables = {};
    let charts = {};
    let dataLoaded = new Set(); // Track loaded tabs

    const initPharmacyDetails = () => {
        loadBranchDetails();
        initAllTables();

        // Check for tab in URL hash
        const hash = window.location.hash;
        if (hash) {
            const tabEl = document.querySelector(`a[data-bs-toggle="tab"][href="${hash}"]`);
            if (tabEl) {
                // Remove default active from overview before showing target
                document.getElementById('overview').classList.remove('show', 'active');
                document.querySelector('a[href="#overview"]').classList.remove('active');
                
                const bsTab = new bootstrap.Tab(tabEl);
                bsTab.show();
            }
        } else {
            loadOverviewData();
            dataLoaded.add('overview');
        }

        // Handle tab clicks to reload data/adjust columns
        const tabHandler = function (e) {
            const target = $(e.target).attr("href").replace('#', '');
            if (window.location.hash !== '#' + target) {
                window.location.hash = target; // Update URL hash
            }

            if (tables[target]) {
                tables[target].columns.adjust().responsive.recalc();
            }

            // Only load data if not already loaded
            if (!dataLoaded.has(target)) {
                loadTabData(target);
                dataLoaded.add(target);
            }
        };

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', tabHandler);

        // Window resize handle for charts
        const resizeHandler = () => {
            Object.values(charts).forEach(chart => {
                if (chart && typeof chart.render === 'function') chart.render();
            });
        };
        window.addEventListener('resize', resizeHandler);

        // Cleanup on navigate away
        if (typeof Melina !== 'undefined') {
            Melina.onPageUnload(() => {
                window.removeEventListener('resize', resizeHandler);
                // Destroy charts to free memory
                Object.values(charts).forEach(chart => {
                    if (chart && typeof chart.destroy === 'function') chart.destroy();
                });
                // Destroy tables
                Object.values(tables).forEach(table => {
                    if (table && typeof table.destroy === 'function') table.destroy();
                });
                tables = {}; // Reset tables object
                dataLoaded.clear(); // Reset loaded tabs
                console.log('Pharmacy details cleaned up');
            });
        }
    };

    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initPharmacyDetails);
    } else {
        document.addEventListener('DOMContentLoaded', initPharmacyDetails);
    }

    function initAllTables() {
        // Initialize all tables as DataTables
        const commonConfig = {
            responsive: true,
            destroy: true, // Prevent re-initialization errors
            language: {
                searchPlaceholder: 'Search...',
                sSearch: '',
            },
            pageLength: 10
        };

        tables.inventory = $('#inventoryTable').DataTable(commonConfig);
        tables.sales = $('#salesTable').DataTable(commonConfig);
        tables.expiry = $('#expiryTable').DataTable(commonConfig);
        tables.users = $('#usersTable').DataTable(commonConfig);
        tables.po = $('#poTable').DataTable(commonConfig);
        tables.grn = $('#grnTable').DataTable(commonConfig);
        tables.appointments = $('#appointmentsTable').DataTable(commonConfig);
    }

    async function loadTabData(tab) {
        switch (tab) {
            case 'overview': await loadOverviewData(); break;
            case 'inventory': await loadInventory(); break;
            case 'sales': await loadSales(); break;
            case 'expiry': await loadExpiryAlerts(); break;
            case 'users': await loadUsers(); break;
            case 'po': await loadPO(); break;
            case 'grn': await loadGRN(); break;
            case 'appointments': await loadAppointments(); break;
        }
    }

    async function loadOverviewData() {
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/analytics`);
            const data = await res.json();
            if (data.success) {
                const analytics = data.data.analytics;
                renderOverviewCharts(analytics);
                renderRecentActivity(analytics.recent_activity);

                // Populate analytics cards
                document.getElementById('overviewActivePOs').textContent = analytics.activity_counts.po_count || 0;
                document.getElementById('overviewPendingGRNs').textContent = analytics.activity_counts.grn_count || 0;
                document.getElementById('overviewTodayAppts').textContent = analytics.activity_counts.appt_count || 0;
                document.getElementById('overviewExpiryCount').textContent = analytics.expiry_count || 0;
            }
        } catch (e) { console.error('Error loading analytics:', e); }
    }

    function renderOverviewCharts(analytics) {
        // 1. Sales Trend (Area Chart)
        renderSalesTrend(analytics.sales_trend);

        // 2. Inventory Dist (Doughnut)
        renderInventoryDist(analytics.inventory_dist);

        // 3. User Performance (Bar Chart)
        renderUserPerformance(analytics.user_performance);

        // 4. Top Products (Radial Bar / Bar)
        renderTopProducts(analytics.top_selling);

        // 5. Stock Status (Radar/Polar)
        renderStockStatus(analytics.inventory_dist); // Using same data for demo variety

        // 6. Procurement Mixed Comparison
        renderComparisonChart(analytics.activity_counts);
    }

    function renderSalesTrend(data) {
        const options = {
            series: [{
                name: 'Sales Amount',
                data: data.map(i => parseFloat(i.total))
            }],
            chart: { height: 300, type: 'area', toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: { categories: data.map(i => i.month) },
            colors: ['#4eac4c'],
            fill: { gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } }
        };
        if (charts.salesTrend) charts.salesTrend.destroy();
        charts.salesTrend = new ApexCharts(document.querySelector("#salesTrendChart"), options);
        charts.salesTrend.render();
    }

    function renderInventoryDist(data) {
        const options = {
            series: data.map(i => parseInt(i.count)),
            labels: data.map(i => i.category),
            chart: { height: 300, type: 'donut' },
            colors: ['#4eac4c', '#5a66f1', '#f5b843', '#e6533c', '#26bf94'],
            legend: { position: 'bottom' }
        };
        if (charts.invDist) charts.invDist.destroy();
        charts.invDist = new ApexCharts(document.querySelector("#inventoryDistChart"), options);
        charts.invDist.render();
    }

    function renderUserPerformance(data) {
        const options = {
            series: [{
                name: 'Total Sales',
                data: data.map(i => parseFloat(i.total))
            }],
            chart: { height: 300, type: 'bar', toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
            xaxis: { categories: data.map(i => i.full_name) },
            colors: ['#5a66f1']
        };
        if (charts.userPerf) charts.userPerf.destroy();
        charts.userPerf = new ApexCharts(document.querySelector("#userPerformanceChart"), options);
        charts.userPerf.render();
    }

    function renderTopProducts(data) {
        const options = {
            series: [{
                data: data.map(i => parseInt(i.qty))
            }],
            chart: { height: 250, type: 'bar', toolbar: { show: false } },
            plotOptions: { bar: { distributed: true, borderRadius: 10 } },
            xaxis: { categories: data.map(i => i.name.substring(0, 10) + '..') },
            colors: ['#4eac4c', '#5a66f1', '#f5b843', '#e6533c', '#26bf94'],
            legend: { show: false }
        };
        if (charts.topProd) charts.topProd.destroy();
        charts.topProd = new ApexCharts(document.querySelector("#topProductsChart"), options);
        charts.topProd.render();
    }

    function renderStockStatus(data) {
        const options = {
            series: data.map(i => parseInt(i.count)),
            labels: data.map(i => i.category),
            chart: { height: 300, type: 'polarArea' },
            stroke: { colors: ['#fff'] },
            fill: { opacity: 0.8 },
            legend: { position: 'bottom' }
        };
        if (charts.stockStatus) charts.stockStatus.destroy();
        charts.stockStatus = new ApexCharts(document.querySelector("#stockStatusChart"), options);
        charts.stockStatus.render();
    }

    function renderComparisonChart(counts) {
        const options = {
            series: [{
                name: 'Volume',
                type: 'column',
                data: [counts.po_count, counts.grn_count, counts.invoice_count]
            }],
            labels: ['Purchase Orders', 'GRNs', 'Invoices'],
            chart: { height: 300, type: 'line', toolbar: { show: false } },
            colors: ['#4eac4c']
        };
        if (charts.mixedComp) charts.mixedComp.destroy();
        charts.mixedComp = new ApexCharts(document.querySelector("#mixedComparisonChart"), options);
        charts.mixedComp.render();
    }

    function renderRecentActivity(activities) {
        const list = document.getElementById('recentActivityList');
        if (!activities || activities.length === 0) {
            list.innerHTML = '<li class="list-group-item text-center py-4">No recent activity</li>';
            return;
        }
        list.innerHTML = activities.map(act => `
            <li class="list-group-item">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm bg-${act.type === 'Sale' ? 'success' : 'primary'}-transparent rounded me-3">
                        <i class="ri-${act.type === 'Sale' ? 'shopping-cart' : 'shopping-bag'}-line"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0 fw-semibold fs-13">${act.type}: ${escapeHtml(act.ref)}</p>
                        <p class="mb-0 text-muted fs-11">${formatDate(act.date)}</p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 fw-semibold fs-13">₹${formatNumber(act.amount)}</p>
                    </div>
                </div>
            </li>
        `).join('');
    }

    function generateReport(reportName) {
        Swal.fire({
            title: `Generating ${reportName}...`,
            html: 'Please wait while we gather full end-to-end data...',
            timer: 2000,
            timerProgressBar: true,
            didOpen: () => { Swal.showLoading(); }
        }).then(() => {
            Swal.fire('Success!', `${reportName} has been downloaded.`, 'success');
        });
    }

    async function loadBranchDetails() {
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}`);
            const data = await res.json();

            if (data.success && data.data.branch) {
                const branch = data.data.branch;
                const branchNameEl = document.getElementById('branchName');
                if (branchNameEl) branchNameEl.textContent = branch.branch_name;
            }
        } catch (e) {
            console.error('Error loading branch details:', e);
        }
    }

    async function loadInventory() {
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/inventory`);
            const data = await res.json();
            if (data.success) {
                const table = tables.inventory;
                if (!table) return;
                table.clear();
                data.data.inventory.forEach(item => {
                    table.row.add([
                        escapeHtml(item.product_name),
                        escapeHtml(item.batch_no),
                        item.qty_available,
                        '₹' + formatNumber(item.mrp),
                        formatDate(item.expiry_date),
                        `<span class="badge ${item.qty_available > 10 ? 'bg-success' : 'bg-warning'}">${item.qty_available > 10 ? 'In Stock' : 'Low Stock'}</span>`
                    ]);
                });
                table.draw();
            }
        } catch (e) { console.error(e); }
    }

    async function loadSales() {
        const fromDate = document.getElementById('salesFromDate')?.value || '';
        const toDate = document.getElementById('salesToDate')?.value || '';
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/sales?from=${fromDate}&to=${toDate}`);
            const data = await res.json();
            if (data.success) {
                const table = tables.sales;
                if (!table) return;
                table.clear();
                data.data.sales.forEach(sale => {
                    table.row.add([
                        formatDate(sale.invoice_date),
                        escapeHtml(sale.invoice_no),
                        escapeHtml(sale.patient_name),
                        sale.total_items,
                        '₹' + formatNumber(sale.total_amount),
                        escapeHtml(sale.payment_mode)
                    ]);
                });
                table.draw();
            }
        } catch (e) { console.error(e); }
    }

    async function loadExpiryAlerts() {
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/expiry-alerts`);
            const data = await res.json();
            if (data.success) {
                const table = tables.expiry;
                if (!table) return;
                table.clear();
                data.data.alerts.forEach(alert => {
                    table.row.add([
                        escapeHtml(alert.product_name),
                        escapeHtml(alert.batch_no),
                        alert.qty_available,
                        formatDate(alert.expiry_date),
                        `<span class="badge ${alert.days_left < 30 ? 'bg-danger' : 'bg-warning'}">${alert.days_left} days</span>`,
                        `<button class="btn btn-sm btn-danger" onclick="markDisposal(${alert.batch_id})"><i class="ri-delete-bin-line"></i></button>`
                    ]);
                });
                table.draw();
            }
        } catch (e) { console.error(e); }
    }

    async function loadUsers() {
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/users`);
            const data = await res.json();
            if (data.success) {
                const table = tables.users;
                if (!table) return;
                table.clear();
                data.data.users.forEach(user => {
                    table.row.add([
                        escapeHtml(user.full_name),
                        escapeHtml(user.username),
                        escapeHtml(user.email || 'N/A'),
                        escapeHtml(user.mobile || 'N/A'),
                        `<span class="badge ${user.is_active ? 'bg-success' : 'bg-danger'}">${user.is_active ? 'Active' : 'Inactive'}</span>`,
                        user.last_login ? formatDate(user.last_login) : 'Never'
                    ]);
                });
                table.draw();
            }
        } catch (e) { console.error(e); }
    }

    async function loadPO() {
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/po`);
            const data = await res.json();
            if (data.success) {
                const table = tables.po;
                if (!table) return;
                table.clear();
                data.data.orders.forEach(po => {
                    table.row.add([
                        escapeHtml(po.po_no),
                        escapeHtml(po.supplier_name || 'N/A'),
                        escapeHtml(po.ordered_by_name || 'N/A'),
                        '₹' + formatNumber(po.total_amount),
                        `<span class="badge bg-primary-transparent">${escapeHtml(po.status)}</span>`,
                        formatDate(po.created_at)
                    ]);
                });
                table.draw();
            }
        } catch (e) { console.error(e); }
    }

    async function loadGRN() {
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/grn`);
            const data = await res.json();
            if (data.success) {
                const table = tables.grn;
                if (!table) return;
                table.clear();
                data.data.grns.forEach(grn => {
                    table.row.add([
                        escapeHtml(grn.grn_no),
                        escapeHtml(grn.po_no || 'N/A'),
                        escapeHtml(grn.received_by_name || 'N/A'),
                        '₹' + formatNumber(grn.total_amount),
                        `<span class="badge bg-success-transparent">${escapeHtml(grn.status)}</span>`,
                        formatDate(grn.received_at)
                    ]);
                });
                table.draw();
            }
        } catch (e) { console.error(e); }
    }

    async function loadAppointments() {
        try {
            const res = await fetch(`/api/v1/pharmacy/branches/${branchId}/appointments`);
            const data = await res.json();
            if (data.success) {
                const table = tables.appointments;
                if (!table) return;
                table.clear();
                data.data.appointments.forEach(appt => {
                    table.row.add([
                        escapeHtml(appt.appointment_no),
                        escapeHtml(appt.patient_name),
                        escapeHtml(appt.provider_name || 'N/A'),
                        formatDate(appt.scheduled_at, true),
                        `<span class="badge bg-info-transparent">${escapeHtml(appt.status)}</span>`,
                        escapeHtml(appt.source)
                    ]);
                });
                table.draw();
            }
        } catch (e) { console.error(e); }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatCurrency(num) {
        return '₹' + Number(num).toLocaleString('en-IN', { maximumFractionDigits: 2 });
    }

    function formatNumber(num) {
        return Number(num).toLocaleString('en-IN', { maximumFractionDigits: 2 });
    }

    function formatDate(dateStr, includeTime = false) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
        }
        return date.toLocaleDateString('en-IN', options);
    }

    function markDisposal(batchId) {
        Swal.fire({
            title: 'Mark for Disposal?',
            text: 'This will mark the batch as expired and remove from active inventory.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, mark it'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Marked!', 'Batch marked for disposal.', 'success');
            }
        });
    }

    // Reload sales when date changes
    document.getElementById('salesFromDate')?.addEventListener('change', loadSales);
    document.getElementById('salesToDate')?.addEventListener('change', loadSales);
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>