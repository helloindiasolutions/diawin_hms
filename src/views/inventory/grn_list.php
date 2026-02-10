<?php
$pageTitle = "GRN List";
ob_start();
?>

<!-- Main Content Header -->
<div class="grn-content-header mb-4 px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="page-title fw-semibold fs-20 mb-1">Stock Receiving (GRN)</h1>
            <p class="text-muted mb-0 fs-13">View and manage Goods Receipt Notes.</p>
        </div>
        <a href="/grn/create" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> New Stock Inward (GRN)
        </a>
    </div>
</div>

<!-- GRN Table -->
<div class="card custom-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-nowrap w-100" id="grnTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">S.No</th>
                        <th>GRN Number</th>
                        <th>PO Ref</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Received By</th>
                        <th class="text-end">Total Amount</th>
                    </tr>
                </thead>
                <tbody id="grnTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .grn-link {
        color: var(--primary-color);
        font-weight: 600;
        text-decoration: none;
    }

    .grn-link:hover {
        text-decoration: underline;
    }

    #grnTableBody tr {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    #grnTableBody tr:hover {
        background-color: #f8fafc;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', loadGRNs);

    // SPA Support
    if (document.readyState !== 'loading') {
        loadGRNs();
    }

    async function loadGRNs() {
        const tableBody = document.getElementById('grnTableBody');
        if (!tableBody) return;

        try {
            const response = await fetch('/api/v1/inventory/grn');
            const data = await response.json();

            if (data.success && data.data && data.data.grns.length > 0) {
                renderTable(data.data.grns);
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="ri-file-list-3-line fs-48 mb-3 d-block opacity-25"></i>
                            <p>No Goods Receipt Notes found.</p>
                        </td>
                    </tr>`;
            }
        } catch (error) {
            console.error('Error loading GRNs:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-danger">
                        <i class="ri-error-warning-line fs-48 mb-3 d-block"></i>
                        <p>Failed to load data.</p>
                    </td>
                </tr>`;
        }
    }

    function renderTable(grns) {
        const tableBody = document.getElementById('grnTableBody');
        tableBody.innerHTML = '';

        grns.forEach((grn, index) => {
            const row = document.createElement('tr');
            const date = new Date(grn.received_at).toLocaleDateString();
            const amount = parseFloat(grn.total_amount).toFixed(2);

            // Make row clickable
            row.onclick = (e) => {
                // Prevent redirection if clicking strictly on links (optional, but good UX)
                // but here user said "click that row its that grn page"
                window.location.href = `/grn/view?id=${grn.grn_id}`;
            };

            row.innerHTML = `
                <td>${index + 1}</td>
                <td class="fw-bold" style="color: var(--primary-color);">${grn.grn_no}</td>
                <td>${grn.po_no || '-'}</td>
                <td>${date}</td>
                <td>${grn.supplier_name || '-'}</td>
                <td>${grn.received_by_name || '-'}</td>
                <td class="text-end">₹${amount}</td>
            `;
            tableBody.appendChild(row);
        });
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>