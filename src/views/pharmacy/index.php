<?php
$pageTitle = "Pharmacy Branches";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Pharmacy Branches</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Pharmacy</a></li>
            <li class="breadcrumb-item active" aria-current="page">All Branches</li>
        </ol>
    </div>
</div>

<div class="row" id="pharmacyGrid">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', loadPharmacies);

    async function loadPharmacies() {
        try {
            const res = await fetch('/api/v1/pharmacy/branches');
            const data = await res.json();

            if (data.success && data.data.branches) {
                renderPharmacyCards(data.data.branches);
            } else {
                showError('Failed to load pharmacy branches');
            }
        } catch (e) {
            showError('Error loading pharmacies: ' + e.message);
        }
    }

    function renderPharmacyCards(branches) {
        const grid = document.getElementById('pharmacyGrid');

        if (branches.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        No pharmacy branches found.
                    </div>
                </div>
            `;
            return;
        }

        grid.innerHTML = branches.map(branch => `
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card custom-card pharmacy-card" onclick="viewPharmacy(${branch.branch_id})">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar avatar-lg bg-primary-transparent rounded-circle me-3">
                                <i class="ri-capsule-line fs-24 text-primary"></i>
                            </div>
                            <div class="flex-fill">
                                <h5 class="fw-semibold mb-1">${escapeHtml(branch.branch_name)}</h5>
                                <p class="text-muted mb-0 fs-12">${escapeHtml(branch.city || 'N/A')}</p>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <p class="mb-1 fs-11 text-muted">Total Products</p>
                                    <h6 class="mb-0 fw-bold">${branch.total_products || 0}</h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <p class="mb-1 fs-11 text-muted">Stock Value</p>
                                    <h6 class="mb-0 fw-bold">₹${formatNumber(branch.stock_value || 0)}</h6>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge ${branch.is_active ? 'bg-success' : 'bg-secondary'}">
                                ${branch.is_active ? 'Active' : 'Inactive'}
                            </span>
                            <span class="text-primary fs-12">
                                View Details <i class="ri-arrow-right-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function viewPharmacy(branchId) {
        window.open(`/pharmacy/${branchId}`, '_blank');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatNumber(num) {
        return Number(num).toLocaleString('en-IN', { maximumFractionDigits: 2 });
    }

    function showError(message) {
        document.getElementById('pharmacyGrid').innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    ${message}
                </div>
            </div>
        `;
    }
</script>

<style>
    .pharmacy-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid var(--default-border);
    }

    .pharmacy-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-color: var(--primary);
    }
</style>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>