<?php
$pageTitle = "Goods Receipt Note (GRN)";
ob_start();
?>

<div class="grn-container">
    <div class="grn-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="page-title fw-semibold fs-20 mb-1">Stock Receiving (GRN)</h1>
                <p class="text-muted mb-0 fs-13">Confirm received stock against purchase orders and warehouse updates.
                </p>
            </div>
        </div>

        <!-- Form Fields Grid -->
        <div class="form-fields-grid">
            <div class="form-field">
                <label>GRN Number</label>
                <input type="text" id="grn_no" placeholder="Auto-generated" readonly>
            </div>
            <div class="form-field">
                <label>Receipt Date</label>
                <input type="date" id="receipt_date" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-field">
                <label>Reference PO # *</label>
                <select id="po_ref" onchange="loadPOItems()">
                    <option value="">Select PO...</option>
                </select>
            </div>
            <div class="form-field">
                <label>Supplier</label>
                <input type="text" id="supplier_display" value="---" readonly>
            </div>
            <div class="form-field">
                <label>Store Location</label>
                <select id="store_location">
                    <option value="">Select Location...</option>
                </select>
            </div>
            <div class="form-field">
                <label>Lorry/Vehicle Number</label>
                <input type="text" id="vehicle_no" placeholder="e.g., TN01AB1234">
            </div>
            <div class="form-field">
                <label>Driver Name</label>
                <input type="text" id="driver_name" placeholder="Driver name">
            </div>
            <div class="form-field">
                <label>Received By</label>
                <input type="text" id="received_by" value="SYSTEM ADMIN" readonly>
            </div>
        </div>
    </div>

    <!-- Items Grid (Scrollable) -->
    <div class="items-grid-area">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">S.No</th>
                    <th>Product Name</th>
                    <th style="width: 100px;">SKU</th>
                    <th style="width: 120px;">Batch Number</th>
                    <th style="width: 100px;">Mfg Date</th>
                    <th style="width: 100px;">Exp Date</th>
                    <th style="width: 80px;">Ordered Qty</th>
                    <th style="width: 80px;">Received Qty</th>
                    <th style="width: 150px;">Rack Location</th>
                    <th style="width: 100px;">MRP</th>
                    <th style="width: 40px;"></th>
                </tr>
            </thead>
            <tbody id="grn_grid_body">
                <!-- Dynamic rows added here -->
            </tbody>
        </table>
        <div id="empty_grid_msg" class="empty-state">
            <i class="ri-inbox-line"></i>
            <p>Select a Purchase Order to load items for receiving</p>
        </div>
    </div>

    <!-- Bottom Summary (Fixed) -->
    <div class="bottom-summary-area">
        <div class="summary-grid-simple">
            <div class="totals-panel">
                <div class="total-row">
                    <label>Total Items:</label>
                    <span class="value" id="total_items">0</span>
                </div>
                <div class="total-row">
                    <label>Total Ordered:</label>
                    <span class="value" id="total_ordered">0</span>
                </div>
                <div class="total-row">
                    <label>Total Received:</label>
                    <span class="value text-primary" id="total_received">0</span>
                </div>
                <div class="total-row">
                    <label>Variance:</label>
                    <span class="value" id="variance">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons (Fixed Bottom) -->
    <div class="action-buttons-bar">
        <button class="btn-action btn-primary" onclick="postGRN()">
            <i class="ri-check-line"></i> POST GRN (F2)
        </button>
        <button class="btn-action btn-secondary" onclick="location.reload()">
            <i class="ri-refresh-line"></i> CLEAR (F5)
        </button>
        <button id="btn-back-po" class="btn-action btn-secondary" onclick="window.location.href='/purchase-orders'">
            <i class="ri-arrow-left-line"></i> BACK TO PO
        </button>
        <div class="shortcuts-hint">
            F2: Post GRN | F5: Clear | ESC: Back
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

    body {
        background: var(--bg-light);
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .grn-container {
        height: calc(100vh - 140px);
        display: flex;
        flex-direction: column;
        background: var(--bg-white);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    /* Header Section - Clean White */
    .grn-header {
        flex-shrink: 0;
        background: var(--bg-white);
        padding: 0.25rem 1rem;
    }

    .page-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .header-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.25rem;
    }

    .header-meta {
        display: flex;
        gap: 1rem;
    }

    .meta-item {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .form-fields-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
    }

    .form-field {
        display: flex;
        flex-direction: column;
    }

    .form-field label {
        font-size: 10px;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 0.2rem;
        text-transform: uppercase;
    }

    .form-field input,
    .form-field select {
        padding: 0.4rem 0.75rem;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        transition: all 0.2s;
    }

    .form-field input:focus,
    .form-field select:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-field input[readonly] {
        background: #fef3c7;
        color: var(--text-primary);
        cursor: default;
        border-color: #fbbf24;
        font-weight: 700;
    }

    .input-group {
        display: flex;
        gap: 0;
    }

    .input-group select {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        padding: 0.4rem 1.25rem;
        font-size: 11px;
        font-weight: 800;
        background: var(--primary-blue);
        color: white;
        border: 1px solid var(--primary-blue);
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .input-group .btn:hover {
        background: var(--primary-blue-dark);
        border-color: var(--primary-blue-dark);
    }

    /* Items Grid */
    .items-grid-area {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        background: var(--bg-white);
        position: relative;
        padding: 0.5rem;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid var(--border-color);
        background: white;
    }

    .items-table thead {
        position: sticky;
        top: 0;
        background: #f3f4f6;
        z-index: 10;
    }

    .items-table th {
        padding: 0.75rem 0.5rem;
        font-size: 10px;
        font-weight: 700;
        color: var(--text-secondary);
        text-align: left;
        text-transform: uppercase;
        border: 1px solid var(--border-color);
        background: #f3f4f6;
    }

    .items-table td {
        padding: 0.5rem;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        background: white;
    }

    .items-table tbody tr:hover {
        background: #f9fafb;
    }

    .items-table input {
        width: 100%;
        padding: 0.35rem 0.5rem;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid var(--border-color);
        border-radius: 3px;
        background: white;
    }

    .items-table input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    .items-table input[readonly] {
        background: #fef3c7;
        border-color: #fbbf24;
        font-weight: 700;
        color: #92400e;
    }

    .items-table .received-qty {
        font-weight: 700;
        color: #3b82f6;
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .empty-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: var(--text-secondary);
        padding: 2rem;
        background: #f9fafb;
        border: 2px dashed var(--border-color);
        border-radius: 8px;
        min-width: 400px;
    }

    .empty-state i {
        font-size: 3rem;
        display: block;
        margin-bottom: 1rem;
        opacity: 0.3;
        color: var(--primary-blue);
    }

    .empty-state p {
        font-size: 13px;
        font-weight: 600;
        margin: 0;
        color: var(--text-secondary);
    }

    /* Bottom Summary */
    .bottom-summary-area {
        flex-shrink: 0;
        background: var(--bg-white);
        border-top: 2px solid var(--border-color);
        padding: 0.75rem 1rem;
    }

    .summary-grid-simple {
        display: flex;
        justify-content: flex-end;
    }

    .totals-panel {
        border: 2px solid var(--border-color);
        border-radius: 6px;
        padding: 0.75rem 1.25rem;
        background: #f9fafb;
        min-width: 350px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        padding: 0.25rem 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .total-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .total-row label {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
    }

    .total-row .value {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
        padding: 0.25rem 0.75rem;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        min-width: 60px;
        text-align: center;
    }

    /* Action Buttons */
    .action-buttons-bar {
        flex-shrink: 0;
        background: var(--bg-white);
        border-top: 2px solid var(--border-color);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-action {
        padding: 0.625rem 1.5rem;
        font-size: 12px;
        font-weight: 800;
        border-radius: 6px;
        cursor: pointer;
        border: 2px solid transparent;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        letter-spacing: 0.5px;
        transition: all 0.2s;
    }

    .btn-action.btn-primary {
        background: var(--primary-blue);
        color: white;
        border-color: var(--primary-blue);
    }

    .btn-action.btn-primary:hover {
        background: var(--primary-blue-dark);
        border-color: var(--primary-blue-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-action.btn-secondary {
        background: var(--bg-white);
        color: var(--text-secondary);
        border: 2px solid var(--border-color);
    }

    .btn-action.btn-secondary:hover {
        background: var(--bg-light);
        border-color: var(--text-secondary);
    }

    .shortcuts-hint {
        margin-left: auto;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .text-center {
        text-align: center;
    }

    .text-end {
        text-align: right;
    }

    .fw-bold {
        font-weight: 700;
    }

    .text-danger {
        color: #ef4444;
        cursor: pointer;
    }

    .main-content.app-content {
        padding-inline: 0.5rem !important;
        margin-block-start: 8.5rem !important;
    }

    .main-content.app-content>.container-fluid {
        padding: 0 !important;
    }

    /* Compact responsive adjustments */
    @media (max-width: 1400px) {
        .form-fields-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .form-fields-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    // CRITICAL: Only execute if we're on the GRN page
    if (!document.getElementById('grn_grid_body')) {
        console.log('GRN script skipped - not on GRN page');
        // Define no-op fallbacks to prevent "undefined" errors from leftover HTML
        if (typeof window.postGRN === 'undefined') window.postGRN = function () { console.log('postGRN called but not on GRN page'); };
        if (typeof window.loadPOItems === 'undefined') window.loadPOItems = function () { console.log('loadPOItems called but not on GRN page'); };
    } else {
        let currentPO = null;
        let poItems = [];

        // Initialize page immediately - works for both initial load and SPA navigation
        (function initGRNPage() {
            console.log('GRN page: Initializing...');

            const urlParams = new URLSearchParams(window.location.search);
            const grnId = urlParams.get('id');

            if (grnId) {
                // View Mode
                loadGRNDetails(grnId);
            } else {
                // Create Mode
                generateGRNNumber();
                loadActivePOs();
                loadStoreLocations();
            }
        })();

        async function loadGRNDetails(id) {
            try {
                const res = await fetch(`/api/v1/inventory/grn/${id}`);
                const data = await res.json();

                if (data.success) {
                    const grn = data.data.grn;
                    const items = data.data.items;

                    // Populate Header
                    document.getElementById('grn_no').value = grn.grn_no;
                    document.getElementById('receipt_date').value = grn.received_at.split(' ')[0];
                    document.getElementById('supplier_display').value = grn.supplier_name || 'N/A';
                    document.getElementById('vehicle_no').value = grn.vehicle_no || ''; // Assuming field exists or just leave empty
                    // document.getElementById('driver_name').value = grn.driver_name || ''; 
                    document.getElementById('received_by').value = grn.received_by_name || '';

                    // Set PO reference locally for display
                    const poSelect = document.getElementById('po_ref');
                    poSelect.innerHTML = `<option value="${grn.po_id}" selected>${grn.po_no}</option>`;
                    poSelect.disabled = true;

                    // Disable other inputs
                    document.getElementById('receipt_date').readOnly = true;
                    document.getElementById('store_location').disabled = true; // Might need to fetch location name?

                    // Render Items in View Mode
                    renderGRNItems(items, true);
                    calculateTotals();

                    // Hide Post Button
                    const postBtn = document.querySelector('button[onclick="postGRN()"]');
                    if (postBtn) postBtn.style.display = 'none';

                    // Hide Clear Button (View Mode)
                    const clearBtn = document.querySelector('button[onclick="location.reload()"]');
                    if (clearBtn) clearBtn.style.display = 'none';

                    // Update Back Button to point to GRN List
                    const backBtn = document.getElementById('btn-back-po');
                    if (backBtn) {
                        backBtn.onclick = () => window.location.href = '/grn';
                        backBtn.innerHTML = '<i class="ri-arrow-left-line"></i> BACK TO LIST';
                    }

                    document.querySelector('.page-title').textContent = `View GRN: ${grn.grn_no}`;
                } else {
                    modalNotify.error(data.message || 'Failed to load GRN details');
                }
            } catch (e) {
                console.error('Failed to load GRN:', e);
                modalNotify.error('Error loading GRN details');
            }
        }

        // Keyboard shortcuts - use event delegation with page guard
        document.addEventListener('keydown', (e) => {
            // Only handle if still on GRN page
            if (!document.getElementById('grn_grid_body')) return;

            if (e.key === 'F2') {
                e.preventDefault();
                // Only post if not viewing
                const urlParams = new URLSearchParams(window.location.search);
                if (!urlParams.get('id')) postGRN();
            }
            if (e.key === 'F5') {
                e.preventDefault();
                location.reload();
            }
            if (e.key === 'Escape') {
                e.preventDefault();
                window.location.href = '/grn'; // Go back to list
            }
        });

        // Auto-calculate received qty changes and update MRP
        document.addEventListener('input', (e) => {
            if (!document.getElementById('grn_grid_body')) return;
            if (e.target.classList.contains('received-qty')) {
                updateRowMRP(e.target);
                calculateTotals();
            }
        });

        async function loadStoreLocations() {
            try {
                const res = await fetch('/api/v1/inventory/warehouses?status=active');
                const data = await res.json();

                if (data.success && data.data.warehouses) {
                    const select = document.getElementById('store_location');
                    select.innerHTML = '<option value="">Select Location...</option>';

                    data.data.warehouses.forEach(wh => {
                        const option = document.createElement('option');
                        option.value = wh.id;
                        option.textContent = `${wh.name} (${wh.type})`;
                        select.appendChild(option);
                    });
                }
            } catch (e) {
                console.error('Failed to load store locations:', e);
            }
        }

        function generateGRNNumber() {
            const grnNoInput = document.getElementById('grn_no');
            if (!grnNoInput) return; // Exit if element doesn't exist

            const date = new Date();
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const grnNo = `GRN-${year}${month}${day}-${random}`;
            grnNoInput.value = grnNo;
        }

        async function loadActivePOs() {
            try {
                const res = await fetch('/api/v1/inventory/purchase-orders');
                const data = await res.json();

                if (data.success && data.data.purchase_orders) {
                    // Filter POs that are 'sent' (submitted) and not yet received
                    const activePOs = data.data.purchase_orders.filter(po =>
                        (po.status === 'sent' || po.status === 'ordered') && !po.grn_posted
                    );
                    populatePODropdown(activePOs);
                }
            } catch (e) {
                console.error('Failed to load POs:', e);
            }
        }

        function populatePODropdown(pos) {
            const select = document.getElementById('po_ref');
            select.innerHTML = '<option value="">Select PO...</option>';
            pos.forEach(po => {
                const option = document.createElement('option');
                option.value = po.po_id;
                option.textContent = `${po.po_no} - ${po.supplier_name} (₹${parseFloat(po.total_amount || 0).toFixed(2)})`;
                option.dataset.poData = JSON.stringify(po);
                select.appendChild(option);
            });

            console.log(`Loaded ${pos.length} unreceived POs into dropdown`);
        }

        async function loadPOItems() {
            const poSelect = document.getElementById('po_ref');
            const poId = poSelect.value;

            if (!poId) {
                modalNotify.warning('Please select a Purchase Order');
                return;
            }

            const selectedOption = poSelect.options[poSelect.selectedIndex];
            currentPO = JSON.parse(selectedOption.dataset.poData);

            // Update supplier display
            document.getElementById('supplier_display').value = currentPO.supplier_name || 'N/A';

            // Fetch PO items from API
            try {
                const res = await fetch(`/api/v1/inventory/purchase-orders/${poId}/items`);
                const data = await res.json();

                if (data.success && data.data.items) {
                    poItems = data.data.items;

                    // Check if any items have zero unit_price
                    const hasZeroPrices = poItems.some(item => parseFloat(item.unit_price || 0) === 0);
                    if (hasZeroPrices) {
                        console.warn('Warning: Some items have zero unit price!', poItems);
                        modalNotify.warning('Warning: Some items have zero unit price. MRP will show as ₹0.00');
                    }

                    renderGRNItems(data.data.items);
                    calculateTotals();

                    modalNotify.success(`PO ${currentPO.po_no} loaded successfully`);
                } else {
                    modalNotify.error(data.message || 'Failed to load PO items');
                }

            } catch (e) {
                console.error('Failed to load PO items:', e);
                modalNotify.error('Failed to load PO items');
            }
        }

        // Make loadPOItems globally accessible for the onchange attribute
        window.loadPOItems = loadPOItems;

        function renderGRNItems(items, isViewMode = false) {
            const tbody = document.getElementById('grn_grid_body');
            if (!tbody) return; // Exit if element doesn't exist

            const emptyMsg = document.getElementById('empty_grid_msg');
            if (emptyMsg) {
                emptyMsg.style.display = 'none';
            }

            console.log('Rendering GRN items:', items); // Debug log

            tbody.innerHTML = items.map((item, index) => {
                // Use existing batch info if available (View Mode), else generate default
                const batchNo = item.batch_no || `PO-${new Date().getDate().toString().padStart(2, '0')}${(new Date().getMonth() + 1).toString().padStart(2, '0')}${new Date().getFullYear().toString().slice(-2)}`;

                // Use existing dates if available
                const mfgDate = item.manufacture_date ? item.manufacture_date.split(' ')[0] : new Date().toISOString().split('T')[0];
                const expDate = item.expiry_date ? item.expiry_date.split(' ')[0] : new Date(Date.now() + 730 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

                // Calculate initial MRP (unit_price * qty_ordered)
                const unitPrice = parseFloat(item.unit_price || item.unit_cost || 0); // Handle both field names
                const qtyOrdered = parseInt(item.qty_ordered || 0);     // In view mode this might not be in 'items' result if we didn't join PO items table properly or if we store it in grn_items. 
                // Actually `getGRN` joins `grn_items`. `grn_items` has `qty_received`. It doesn't have `qty_ordered`.
                // For view mode, we focus on what was received.

                const qtyReceived = parseInt(item.qty_received || item.qty_ordered || 0);
                const initialMRP = (unitPrice * qtyReceived).toFixed(2); // Use received qty for MRP in view mode
                const mrpDisplay = unitPrice > 0 ? `₹${item.mrp || initialMRP}` : '₹0.00';

                const readOnlyAttr = isViewMode ? 'readonly' : '';
                const disabledAttr = isViewMode ? 'disabled' : '';
                const bgStyle = isViewMode ? 'background: #f3f4f6;' : '';

                return `
            <tr data-product-id="${item.product_id}" data-unit-price="${unitPrice}">
                <td class="text-center">${index + 1}</td>
                <td class="fw-bold">${item.product_name}</td>
                <td class="text-center">${item.sku}</td>
                <td>
                    <input type="text" class="batch-no" placeholder="Batch #" value="${batchNo}" ${readOnlyAttr} style="${bgStyle}">
                </td>
                <td>
                    <input type="date" class="mfg-date" value="${mfgDate}" ${readOnlyAttr} style="${bgStyle}">
                </td>
                <td>
                    <input type="date" class="exp-date" value="${expDate}" ${readOnlyAttr} style="${bgStyle}">
                </td>
                <td class="text-center">
                    <input type="number" class="ordered-qty" value="${qtyOrdered}" readonly style="text-align: center; background: #fef3c7;">
                </td>
                <td class="text-center">
                    <input type="number" class="received-qty" value="${qtyReceived}" min="0" ${readOnlyAttr} style="text-align: center; font-weight: 700; color: #3b82f6; ${bgStyle}">
                </td>
                <td>
                    <input type="text" class="rack-location" placeholder="e.g., Rack A-3" value="Rack A" ${readOnlyAttr} style="${bgStyle}">
                </td>
                <td class="text-end mrp-cell">${mrpDisplay}</td>
                <td class="text-center">
                    ${!isViewMode ? `<i class="ri-close-circle-fill text-danger" onclick="this.closest('tr').remove(); calculateTotals();" style="cursor: pointer;"></i>` : ''}
                </td>
            </tr>
        `}).join('');
        }

        function updateRowMRP(receivedQtyInput) {
            const row = receivedQtyInput.closest('tr');
            if (!row) return;

            const unitPrice = parseFloat(row.dataset.unitPrice) || 0;
            const receivedQty = parseInt(receivedQtyInput.value) || 0;
            const totalMRP = (unitPrice * receivedQty).toFixed(2);

            const mrpCell = row.querySelector('.mrp-cell');
            if (mrpCell) {
                mrpCell.textContent = `₹${totalMRP}`;
            }
        }

        function calculateTotals() {
            const tbody = document.getElementById('grn_grid_body');
            if (!tbody) return; // Exit if element doesn't exist

            const rows = tbody.querySelectorAll('tr');
            let totalItems = rows.length;
            let totalOrdered = 0;
            let totalReceived = 0;

            rows.forEach(row => {
                const ordered = parseInt(row.querySelector('.ordered-qty').value) || 0;
                const received = parseInt(row.querySelector('.received-qty').value) || 0;

                totalOrdered += ordered;
                totalReceived += received;
            });

            const variance = totalReceived - totalOrdered;

            const totalItemsEl = document.getElementById('total_items');
            const totalOrderedEl = document.getElementById('total_ordered');
            const totalReceivedEl = document.getElementById('total_received');
            const varianceEl = document.getElementById('variance');

            if (totalItemsEl) totalItemsEl.textContent = totalItems;
            if (totalOrderedEl) totalOrderedEl.textContent = totalOrdered;
            if (totalReceivedEl) totalReceivedEl.textContent = totalReceived;
            if (varianceEl) {
                varianceEl.textContent = variance;
                varianceEl.style.color = variance === 0 ? '#10b981' : (variance > 0 ? '#3b82f6' : '#ef4444');
            }
        }

        async function postGRN() {
            const tbody = document.getElementById('grn_grid_body');
            if (!tbody) return; // Exit if element doesn't exist

            const rows = tbody.querySelectorAll('tr');

            if (rows.length === 0) {
                modalNotify.warning('Please load a Purchase Order first');
                return;
            }

            if (!currentPO) {
                modalNotify.warning('No PO selected');
                return;
            }

            // Validate all fields
            let isValid = true;
            const items = [];

            rows.forEach(row => {
                const batchNo = row.querySelector('.batch-no').value.trim();
                const mfgDate = row.querySelector('.mfg-date').value;
                const expDate = row.querySelector('.exp-date').value;
                const receivedQty = parseInt(row.querySelector('.received-qty').value) || 0;
                const rackLocation = row.querySelector('.rack-location').value.trim();
                const unitPrice = parseFloat(row.dataset.unitPrice) || 0;

                if (!batchNo || !mfgDate || !expDate || receivedQty <= 0 || !rackLocation) {
                    isValid = false;
                }

                items.push({
                    product_id: row.dataset.productId,
                    batch_no: batchNo,
                    mfg_date: mfgDate,
                    exp_date: expDate,
                    qty_received: receivedQty,
                    rack_location: rackLocation,
                    unit_cost: unitPrice
                });
            });

            if (!isValid) {
                modalNotify.error('Please fill all required fields (Batch, Dates, Qty, Rack)');
                return;
            }

            const payload = {
                grn_no: document.getElementById('grn_no').value,
                po_id: currentPO.po_id,
                receipt_date: document.getElementById('receipt_date').value,
                store_location: document.getElementById('store_location').value,
                vehicle_no: document.getElementById('vehicle_no').value,
                driver_name: document.getElementById('driver_name').value,
                items: items
            };

            try {
                const res = await fetch('/api/v1/inventory/grn', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    modalNotify.success('GRN posted successfully! Stock updated.', 'Success');
                    setTimeout(() => window.location.href = '/inventory/stock', 1500);
                } else {
                    modalNotify.error(data.message || 'Failed to post GRN');
                }
            } catch (e) {
                modalNotify.error('Network error: ' + e.message);
            }
        }

        // Make postGRN globally accessible for the onclick attribute
        window.postGRN = postGRN;

    } // End of GRN page check
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>