<?php
$pageTitle = "Purchase Orders";
ob_start();
?>

<!-- Main Content Header -->
<div class="pos-content-header mb-4 px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="page-title fw-semibold fs-20 mb-1">Purchase Orders</h1>
            <p class="text-muted mb-0 fs-13">View and manage past purchase orders.</p>
        </div>
        <a href="/purchase-orders/create" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> New Purchase Order
        </a>
    </div>
</div>

<!-- Purchase Orders Table -->
<div class="card custom-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-nowrap w-100" id="purchaseOrdersTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="poTableBody">
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
        <!-- Pagination (Simple) -->
        <div class="d-flex justify-content-end mt-3" id="poPagination">
            <!-- Will be populated by JS -->
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', loadPurchaseOrders);

    // SPA Support
    if (document.readyState !== 'loading') {
        loadPurchaseOrders();
    }

    async function loadPurchaseOrders() {
        const tableBody = document.getElementById('poTableBody');
        if (!tableBody) return;

        try {
            // Fetch Purchase Orders from API
            // Note: Adjust API endpoint as needed. Assuming /api/v1/inventory/purchase-orders exists or similar
            const response = await fetch('/api/v1/inventory/purchase-orders');
            const data = await response.json();

            if (data.success && data.data && data.data.purchase_orders.length > 0) {
                renderTable(data.data.purchase_orders);
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="ri-file-list-3-line fs-48 mb-3 d-block opacity-25"></i>
                            <p>No purchase orders found.</p>
                        </td>
                    </tr>`;
            }
        } catch (error) {
            console.error('Error loading POs:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-danger">
                        <i class="ri-error-warning-line fs-48 mb-3 d-block"></i>
                        <p>Failed to load purchase orders.</p>
                    </td>
                </tr>`;
        }
    }

    function renderTable(orders) {
        const tableBody = document.getElementById('poTableBody');
        tableBody.innerHTML = '';

        orders.forEach((po, index) => {
            const row = document.createElement('tr');
            row.className = 'cursor-pointer hover-bg-light';
            row.onclick = (e) => {
                // Prevent navigation if clicking action buttons
                if (e.target.closest('.btn-action')) return;
                window.location.href = '/purchase-orders/create?po_id=' + po.po_id;
            };

            const statusBadge = getStatusBadge(po.status);
            // Handle date parsing safely
            const dateStr = po.order_date || po.created_at;
            const date = dateStr ? new Date(dateStr).toLocaleDateString() : '-';
            const total = parseFloat(po.total_amount || 0).toFixed(2);
            const poNumber = po.po_no || po.po_number || '-';

            row.innerHTML = `
                <td>${index + 1}</td>
                <td class="fw-semibold text-primary">${poNumber}</td>
                <td>${date}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-xs me-2 bg-light text-primary rounded-circle">
                            ${(po.supplier_name || 'U').charAt(0)}
                        </span>
                        ${po.supplier_name || 'Unknown'}
                    </div>
                </td>
                <td>${statusBadge}</td>
                <td class="text-end fw-bold">₹${total}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-icon btn-light btn-action" onclick="window.location.href='/purchase-orders/create?po_id=' + ${po.po_id}">
                        <i class="ri-eye-line"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function getStatusBadge(status) {
        let color = 'secondary';
        switch (status?.toLowerCase()) {
            case 'approved': color = 'success'; break;
            case 'pending': color = 'warning'; break;
            case 'rejected': color = 'danger'; break;
            case 'draft': color = 'info'; break;
        }
        return `<span class="badge bg-${color}-transparent">${status || 'Pending'}</span>`;
    }
</script>

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .hover-bg-light:hover {
        background-color: #f9fafb;
    }

    .btn-action {
        z-index: 2;
        position: relative;
    }
</style>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>