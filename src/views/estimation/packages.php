<?php
/**
 * Siddha Package Estimates Management
 * Commercial Grade Interface - Dynamic API Integrated
 */
$pageTitle = "Siddha Packages";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Siddha Packages</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Estimation</a></li>
            <li class="breadcrumb-item active" aria-current="page">Siddha Packages</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-outline-secondary btn-wave"><i class="ri-history-line align-middle me-1"></i> Revise
            Packages</button>
        <button class="btn btn-primary btn-wave"><i class="ri-add-line align-middle me-1"></i> New Siddha
            Package</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary">
                            <i class="ri-leaf-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0" id="total-packages-count">...</h5>
                        <p class="text-muted mb-0 fs-12">Active Siddha Plans</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-9">
        <div class="card custom-card">
            <div class="card-body p-3">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i
                            class="ri-search-line"></i></span>
                    <input type="text" class="form-control border-start-0" id="package-search"
                        placeholder="Search Siddha treatments, thailams or massage protocols..."
                        aria-label="Search Packages">
                    <button class="btn btn-primary" type="button" id="search-btn">Find</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="packages-container">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading Siddha Packages...</span>
        </div>
        <p class="mt-2 text-muted">Fetching live Siddha protocol data from API...</p>
    </div>
</div>

<style>
    .hover-animate:hover {
        transform: translateY(-5px);
        transition: all 0.3s ease;
        border-top-width: 5px !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadPackages();

        document.getElementById('search-btn').addEventListener('click', loadPackages);
        document.getElementById('package-search').addEventListener('keyup', function (e) {
            if (e.key === 'Enter') loadPackages();
        });

        async function loadPackages() {
            const container = document.getElementById('packages-container');
            const searchTerm = document.getElementById('package-search').value;

            try {
                const response = await fetch('<?= baseUrl('/api/v1/billing/packages') ?>?search=' + encodeURIComponent(searchTerm));
                const result = await response.json();

                if (result.success && result.data.packages) {
                    const packages = result.data.packages;
                    document.getElementById('total-packages-count').textContent = packages.length;
                    renderPackages(packages);
                } else {
                    container.innerHTML = '<div class="col-12 text-center"><p class="text-danger">Failed to load packages</p></div>';
                }
            } catch (error) {
                console.error('Error fetching packages:', error);
                container.innerHTML = '<div class="col-12 text-center"><p class="text-danger">API Connection Error</p></div>';
            }
        }

        function renderPackages(packages) {
            const container = document.getElementById('packages-container');
            if (packages.length === 0) {
                container.innerHTML = '<div class="col-12 text-center py-5"><h6>No packages found matching your criteria.</h6></div>';
                return;
            }

            container.innerHTML = '';
            packages.forEach(pkg => {
                const col = document.createElement('div');
                col.className = 'col-xl-4 col-md-6 mb-4';

                // Standardizing price display
                const price = parseFloat(pkg.total_price || pkg.price || 0).toLocaleString('en-IN', {
                    maximumFractionDigits: 0,
                    style: 'currency',
                    currency: 'INR'
                });

                col.innerHTML = `
                <div class="card custom-card border-top border-3 border-primary shadow-sm hover-animate h-100">
                    <div class="card-header justify-content-between">
                         <div class="card-title text-primary fs-16 fw-bold">${pkg.name}</div>
                         <span class="badge bg-success-transparent text-success">${pkg.status || 'Active'}</span>
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-3">Code: ${pkg.code || 'SID-PKG'}</small>
                        <p class="fs-13 text-muted mb-4">${pkg.description || 'Professional Siddha treatment protocol including internal and external therapies.'}</p>
                        
                        <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-auto">
                            <div>
                                <span class="text-muted fs-12 d-block">Treatment Cost</span>
                                <h4 class="fw-bold mb-0 text-dark">${price}</h4>
                            </div>
                            <div class="btn-list">
                                <button class="btn btn-sm btn-icon btn-primary-light" title="Apply to Patient"><i class="ri-user-add-line"></i></button>
                                <button class="btn btn-sm btn-icon btn-secondary-light" title="Print Protocol"><i class="ri-printer-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                container.appendChild(col);
            });
        }
    });
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>