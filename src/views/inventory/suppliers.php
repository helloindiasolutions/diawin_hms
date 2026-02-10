<?php
$pageTitle = "Suppliers";
ob_start();
?>

<!-- Main Content Header -->
<div class="pos-content-header mb-4 px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="page-title fw-semibold fs-20 mb-1">Vendors & Suppliers</h1>
            <p class="text-muted mb-0 fs-13">Manage pharmaceutical vendors, distributors, and contact details.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12 mb-3">
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-wave"><i class="ri-add-line me-1"></i>New Supplier</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Approved Vendor List</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead>
                            <tr>
                                <th class="ps-4">Supplier Name</th>
                                <th>Contact Person</th>
                                <th>Contact No</th>
                                <th>GSTIN</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="supplierList">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    // Initialize immediately - works for both initial load AND SPA navigation
    (function initSuppliersPage() {
        const list = document.getElementById('supplierList');
        if (!list) {
            console.log('Suppliers script skipped - not on suppliers page');
            return;
        }
        console.log('Suppliers page: Initializing...');
        fetchSuppliers();
    })();

    async function fetchSuppliers() {
        const list = document.getElementById('supplierList');
        list.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const res = await fetch(`/api/v1/inventory/suppliers`);
            const data = await res.json();
            list.innerHTML = '';

            if (data.success && data.data.suppliers.length > 0) {
                data.data.suppliers.forEach(s => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td class="ps-4"><div class="fw-semibold">${s.name}</div><div class="text-muted fs-11">${s.email || '-'}</div></td>
                    <td>${s.contact_person || '-'}</td>
                    <td>${s.mobile || '-'}</td>
                    <td><span class="badge bg-light text-dark text-uppercase">${s.gstin || 'No GST'}</span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-icon btn-primary-light rounded-pill"><i class="ri-edit-line"></i></button>
                    </td>
                `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No suppliers registered.</td></tr>';
            }
        } catch (e) { }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>