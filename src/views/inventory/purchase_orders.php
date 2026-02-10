<?php
$pageTitle = "Purchase Entry";
ob_start();
?>

<div class="purchase-container">
    <div class="purchase-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 class="page-title fw-semibold fs-20 mb-1">Purchase Entry</h1>
                <p class="text-muted mb-0 fs-13">Inward stock entries from distributors and vendor purchase recording.
                </p>
            </div>
        </div>
        <!-- Form Fields Grid -->
        <div class="form-fields-grid">
            <div class="form-field">
                <label>User</label>
                <input type="text" value="SYSTEM ADMIN" readonly>
            </div>
            <div class="form-field">
                <label>Entry Date</label>
                <input type="date" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-field">
                <label>Distributor / Vendor</label>
                <select id="distributor_id" required>
                    <option value="">Select Vendor...</option>
                </select>
            </div>
            <div class="form-field">
                <label>Distributor Name</label>
                <input type="text" id="dist_name_display" value="---" readonly>
            </div>
            <div class="form-field">
                <label>Invoice Number</label>
                <input type="text" id="invoice_no" placeholder="Auto-generated" readonly>
            </div>
            <div class="form-field">
                <label>Invoice Date</label>
                <input type="date" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-field">
                <label>Payment Type</label>
                <select>
                    <option>Credit</option>
                    <option>Cash</option>
                </select>
            </div>
            <div class="form-field">
                <label>Credit Days</label>
                <input type="number" value="21">
            </div>
        </div>
    </div>

    <!-- Item Entry Strip (Fixed) -->
    <div class="item-entry-strip">
        <div class="item-entry-row">
            <div style="position:relative; flex:1;">
                <input type="text" id="p_item_name" placeholder="Select Vendor First..." autocomplete="off" disabled
                    tabindex="1">
            </div>
            <input type="hidden" id="p_item_id">
            <input type="number" id="p_qty" value="1" placeholder="Qty" min="1" disabled tabindex="2">
            <input type="text" id="p_rate" placeholder="Rate" readonly tabindex="-1">
            <input type="text" id="p_tax" placeholder="Tax %" readonly tabindex="-1">
            <input type="text" id="p_mrp" placeholder="MRP" readonly tabindex="-1">
            <input type="text" id="p_amount" placeholder="Amount" readonly
                style="font-weight: 700; color: #3b82f6; background: #eff6ff; border-color: #3b82f6;" tabindex="-1">
            <button class="btn-add-item" id="btn_add_item" onclick="addItemToGrid()" disabled tabindex="3">ADD</button>
        </div>
    </div>

    <!-- Items Grid (Scrollable) -->
    <div class="items-grid-area">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">S.No</th>
                    <th>Product Name</th>
                    <th style="width: 80px;">SKU</th>
                    <th style="width: 80px;">Qty</th>
                    <th style="width: 100px;">Rate</th>
                    <th style="width: 80px;">Tax%</th>
                    <th style="width: 100px;">MRP</th>
                    <th style="width: 120px;">Amount</th>
                    <th style="width: 40px;"></th>
                </tr>
            </thead>
            <tbody id="purchase_grid_body">
                <!-- Dynamic rows added here -->
            </tbody>
        </table>
        <div id="empty_grid_msg" class="empty-state">
            <i class="ri-truck-line"></i>
            <p>No items added. Press F6 to search products.</p>
        </div>
    </div>

    <!-- Bottom Summary (Fixed) -->
    <div class="bottom-summary-area">
        <div class="summary-grid">
            <div class="gst-breakdown">
                <h6>GST Analysis</h6>
                <table class="gst-table">
                    <thead>
                        <tr>
                            <th>GST %</th>
                            <th>Taxable Val</th>
                            <th>SGST Amt</th>
                            <th>CGST Amt</th>
                            <th>Total Tax</th>
                        </tr>
                    </thead>
                    <tbody id="gst_breakdown_body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
            <div class="totals-panel">
                <div class="total-row">
                    <label>Total Qty:</label>
                    <span class="value" id="total_qty_footer">0</span>
                </div>
                <div class="total-row">
                    <label>Sub Total:</label>
                    <span class="value" id="sum_taxable">0.00</span>
                </div>
                <div class="total-row">
                    <label>Cash Disc:</label>
                    <input type="text"
                        style="width: 100px; text-align: right; padding: 0.25rem 0.5rem; border: 1px solid var(--border-color); border-radius: 3px;"
                        value="0.00">
                </div>
                <div class="total-row">
                    <label>Total GST:</label>
                    <span class="value" id="f_gst_tax">0.00</span>
                </div>
                <div class="total-row grand">
                    <label>Net Payable:</label>
                    <span class="value" id="grand_total_footer">0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons (Fixed Bottom) -->
    <div class="action-buttons-bar">
        <button class="btn-action btn-primary" onclick="savePurchase('draft')">
            <i class="ri-save-line"></i> SAVE ENTRY (F2)
        </button>
        <button class="btn-action" style="background: #fbbf24; color: #1f2937;" onclick="savePurchase('sent')">
            <i class="ri-send-plane-fill"></i> SAVE & SEND (F4)
        </button>
        <button class="btn-action btn-secondary" onclick="location.reload()">
            <i class="ri-refresh-line"></i> CLEAR (F5)
        </button>
        <button class="btn-action btn-secondary" onclick="window.location.href='/products'">
            <i class="ri-arrow-left-line"></i> BACK
        </button>
        <div class="shortcuts-hint">
            F2: Save | F4: Save & Send | F5: Clear | F6: Search Item | ESC: Exit
        </div>
    </div>
</div>

<style>
    /* Zoho-Style Clean UI - Neutral Colors */
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

    .purchase-container {
        height: calc(100vh - 140px);
        /* Adjusted for proper button visibility */
        display: flex;
        flex-direction: column;
        background: var(--bg-white);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    /* Header Section - Clean White */
    .purchase-header {
        flex-shrink: 0;
        background: var(--bg-white);
        border-bottom: 2px solid var(--border-color);
        padding: 0.25rem 1rem;
    }

    .header-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.25rem;
    }

    .page-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    input#p_item_name {
        min-width: 350px;
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
        padding: 0.35rem 0.6rem;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .form-field input:focus,
    .form-field select:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    /* Item Entry Strip - Fixed */
    .item-entry-strip {
        flex-shrink: 0;
        background: #eff6ff;
        border-bottom: 1px solid var(--border-color);
        padding: 0.35rem 1rem;
    }

    .item-entry-row {
        display: grid;
        grid-template-columns: 4fr 0.8fr 1fr 0.8fr 1fr 1.2fr auto;
        gap: 0.4rem;
        align-items: center;
    }

    .item-entry-row input {
        padding: 0.4rem 0.6rem;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .item-entry-row input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    .item-entry-row input:disabled {
        background: #f5f5f5;
        color: #999;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .item-entry-row input[readonly] {
        background: #fef3c7;
        color: var(--text-primary);
        cursor: default;
        border-color: #fbbf24;
    }

    .item-entry-row .btn-add-item {
        padding: 0.5rem 1.5rem;
        background: var(--primary-blue);
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-transform: uppercase;
    }

    .item-entry-row .btn-add-item:disabled {
        background: #ccc;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .item-entry-row .btn-add-item:hover {
        background: var(--primary-blue-dark);
    }

    /* Items Grid - Scrollable */
    .items-grid-area {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        background: var(--bg-white);
        position: relative;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table thead {
        position: sticky;
        top: 0;
        background: var(--bg-light);
        z-index: 10;
    }

    .items-table th {
        padding: 0.5rem 0.4rem;
        font-size: 10px;
        font-weight: 700;
        color: var(--text-secondary);
        text-align: left;
        text-transform: uppercase;
        border-bottom: 2px solid var(--border-color);
    }

    .items-table td {
        padding: 0.4rem;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
    }

    .items-table tbody tr:hover {
        background: var(--bg-light);
    }

    .empty-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 13px;
        font-weight: 600;
        margin: 0;
    }

    /* Bottom Summary - Fixed */
    .bottom-summary-area {
        flex-shrink: 0;
        background: var(--bg-white);
        border-top: 2px solid var(--border-color);
        padding: 0.35rem 1rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 1rem;
    }

    .gst-breakdown {
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 0.5rem;
    }

    .gst-breakdown h6 {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-secondary);
        margin: 0 0 0.5rem 0;
        text-transform: uppercase;
    }

    .gst-table {
        width: 100%;
        font-size: 11px;
        border-collapse: collapse;
    }

    .gst-table th {
        font-weight: 700;
        color: var(--text-secondary);
        padding: 0.25rem 0.4rem;
        text-align: right;
        border-bottom: 1px solid var(--border-color);
    }

    .gst-table th:first-child {
        text-align: left;
    }

    .gst-table td {
        padding: 0.25rem 0.4rem;
        text-align: right;
        font-weight: 600;
    }

    .gst-table td:first-child {
        text-align: left;
    }

    .totals-panel {
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 0.25rem 0.75rem;
        background: var(--bg-light);
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.25rem 1rem;
    }

    .totals-panel .total-row:last-child {
        grid-column: span 2;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.2rem;
    }

    .total-row label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-secondary);
    }

    .total-row .value {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .total-row.grand {
        margin-top: 0.25rem;
        padding-top: 0.25rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-row.grand label {
        font-size: 13px;
        color: var(--text-primary);
    }

    .total-row.grand .value {
        font-size: 1.25rem;
        color: var(--primary-blue);
    }

    /* Action Buttons - Fixed Bottom */
    .action-buttons-bar {
        flex-shrink: 0;
        background: var(--bg-white);
        border-top: 1px solid var(--border-color);
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.5rem 1.25rem;
        font-size: 12px;
        font-weight: 700;
        border-radius: 4px;
        cursor: pointer;
        border: none;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .btn-action.btn-primary {
        background: var(--primary-blue);
        color: white;
    }

    .btn-action.btn-primary:hover {
        background: var(--primary-blue-dark);
    }

    .btn-action.btn-secondary {
        background: var(--bg-white);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .btn-action.btn-secondary:hover {
        background: var(--bg-light);
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

    /* Parent Layout Overrides to Maximize Height */
    .main-content.app-content {
        padding-inline: 0.5rem !important;
        margin-block-start: 8.5rem !important;
        /* improved space from header */
    }

    .main-content.app-content>.container-fluid {
        padding: 0 !important;
    }

    /* AutoComplete Dropdown Styling - Zoho Style */
    #autoComplete_list_1 {
        background: var(--bg-white) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 6px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        max-height: 350px !important;
        overflow-y: auto !important;
        margin-top: 4px !important;
        z-index: 1000 !important;
        padding: 0 !important;
        width: auto !important;
        min-width: 100% !important;
    }

    #autoComplete_list_1 li {
        padding: 0.5rem 0.75rem !important;
        border-bottom: 1px solid #f3f4f6 !important;
        cursor: pointer !important;
        transition: background 0.15s !important;
        list-style: none !important;
        display: block !important;
    }

    #autoComplete_list_1 li:last-child {
        border-bottom: none !important;
    }

    #autoComplete_list_1 li:hover,
    #autoComplete_list_1 li[aria-selected="true"] {
        background: #eff6ff !important;
    }

    #autoComplete_list_1 mark {
        background: #dbeafe !important;
        font-weight: 700 !important;
        color: var(--primary-blue) !important;
        padding: 0 !important;
    }

    .autocomplete-product-item {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 1rem !important;
        width: 100% !important;
    }

    .autocomplete-product-left {
        flex: 1 !important;
        min-width: 0 !important;
    }

    .autocomplete-product-name {
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--text-primary) !important;
        margin-bottom: 0.15rem !important;
        line-height: 1.2 !important;
        display: block !important;
    }

    .autocomplete-product-sku {
        font-size: 10px !important;
        font-weight: 600 !important;
        color: var(--text-secondary) !important;
        font-family: 'Courier New', monospace !important;
        display: block !important;
    }

    .autocomplete-product-right {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 0.35rem !important;
        flex-shrink: 0 !important;
    }

    .autocomplete-product-category {
        font-size: 9px !important;
        font-weight: 700 !important;
        color: var(--text-secondary) !important;
        background: #f3f4f6 !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 3px !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
    }

    .autocomplete-product-tax {
        font-size: 9px !important;
        font-weight: 700 !important;
        color: #059669 !important;
        background: #d1fae5 !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 3px !important;
        white-space: nowrap !important;
    }

    .autocomplete-no-quotation {
        font-size: 9px !important;
        font-weight: 700 !important;
        color: #dc2626 !important;
        background: #fee2e2 !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 3px !important;
        white-space: nowrap !important;
    }

    .disabled-item {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
    }

    .no_result {
        padding: 1rem !important;
        text-align: center !important;
        color: var(--text-secondary) !important;
        font-size: 12px !important;
        font-weight: 600 !important;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    // CRITICAL: Only execute if we're on the Purchase Orders page
    // Fixed: Removed extra closing brace - v2.0
    if (!document.getElementById('purchase_grid_body')) {
        console.log('Purchase Orders script skipped - not on the right page');
    } else {

        // Global function for row deletion
        window.deleteRow = function (btn) {
            btn.closest('tr').remove();
            if (typeof calculateFooter === 'function') {
                calculateFooter();
            } else {
                console.warn('calculateFooter not defined');
            }
        };

        // Ensure calculateFooter is available globally if needed by other scripts
        // (It is defined later in this script, so window.calculateFooter might be redundant if scope is shared, but helpful)


        // Initialize page on DOM ready (first load) or immediately (SPA navigation)
        async function initPurchaseOrdersPage() {
            generatePONumber();
            loadSuppliers();
            initProductSearch();
            preloadLocalProducts(); // ⚡ Start local preload for instant search
            // loadStoreLocations();

            // Check for PO ID in URL
            const urlParams = new URLSearchParams(window.location.search);
            const poId = urlParams.get('po_id');
            // Ensure poId is valid and not the string "undefined"
            if (poId && poId !== 'undefined' && poId !== 'null') {
                setTimeout(() => loadPurchaseOrderDetails(poId), 500); // Small delay to ensure suppliers loaded
            }
        }

        async function loadPurchaseOrderDetails(poId) {
            try {
                // Show loading state
                document.querySelector('.purchase-container').style.opacity = '0.6';

                const res = await fetch(`/api/v1/inventory/purchase-orders/${poId}`);
                const data = await res.json();

                if (data.success && data.data) {
                    const po = data.data.purchase_order; // Adjust based on actual API response structure
                    const items = data.data.items;

                    // Update Header
                    const displayPoNumber = po.po_no || po.po_number || po.invoice_no;
                    const pageTitle = document.querySelector('.page-title');
                    if (pageTitle) pageTitle.innerText = `Purchase Order #${displayPoNumber}`;

                    const invoiceInput = document.getElementById('invoice_no');
                    if (invoiceInput) invoiceInput.value = po.invoice_no || displayPoNumber;

                    // Select Supplier (Wait for options to populate if needed, but we have a delay)
                    const supplierSelect = document.getElementById('distributor_id');
                    if (supplierSelect) {
                        supplierSelect.value = po.supplier_id;
                        // Update display name manually
                        const selectedOption = Array.from(supplierSelect.options).find(opt => opt.value == po.supplier_id);
                        if (selectedOption) {
                            document.getElementById('dist_name_display').value = selectedOption.text.split('(')[0].trim();
                            selectedSupplierId = po.supplier_id;
                        }
                    }

                    // Clear Grid
                    const body = document.getElementById('purchase_grid_body');
                    if (body) body.innerHTML = '';

                    // Populate Grid
                    if (items && items.length > 0) {
                        items.forEach((item, index) => {
                            const row = document.createElement('tr');
                            row.dataset.itemId = item.product_id;

                            const qty = parseFloat(item.quantity || item.qty_ordered || 0);
                            // Adjust for potential field naming differences
                            const rate = parseFloat(item.unit_price || item.cost_price || 0).toFixed(2);
                            const tax = parseFloat(item.tax_percent || item.tax_rate || 0).toFixed(2);
                            const subtotal = qty * rate;
                            const taxAmount = subtotal * (tax / 100);
                            const total = (subtotal + taxAmount).toFixed(2);
                            const mrp = parseFloat(item.mrp || 0).toFixed(2);

                            row.innerHTML = `
                                <td class="text-center">${index + 1}</td>
                                <td class="fw-bold">${item.product_name}</td>
                                <td class="text-center">${item.sku || '-'}</td>
                                <td class="text-center fw-bold">${qty}</td>
                                <td class="text-end">₹${rate}</td>
                                <td class="text-center">${tax}%</td>
                                <td class="text-end">₹${mrp}</td>
                                <td class="text-end fw-bold">₹${total}</td>
                                <td class="text-center">
                                    ${po.status !== 'approved' ? '<i class="ri-close-circle-fill text-danger" style="cursor:pointer" onclick="deleteRow(this)"></i>' : ''}
                                </td>
                            `;
                            body.appendChild(row);
                        });

                        document.getElementById('empty_grid_msg').style.display = 'none';
                        calculateFooter();
                    }

                    // If Approved, Disable Editing
                    if (po.status === 'approved') {
                        disableEditing();
                    }
                }
            } catch (e) {
                console.error('Error loading PO:', e);
                if (typeof modalNotify !== 'undefined') modalNotify.error('Failed to load purchase order details');
            } finally {
                document.querySelector('.purchase-container').style.opacity = '1';
            }
        }

        function disableEditing() {
            // Disable inputs
            const inputs = document.querySelectorAll('.purchase-container input, .purchase-container select, .purchase-container button');
            inputs.forEach(el => {
                if (!el.classList.contains('btn-secondary') && !el.id.includes('search')) {
                    el.disabled = true;
                }
            });
            // Hide specific elements
            const saveBtn = document.querySelector('.btn-primary');
            if (saveBtn) saveBtn.style.display = 'none';

            const entryStrip = document.querySelector('.item-entry-strip');
            if (entryStrip) entryStrip.style.display = 'none';
        }

        async function preloadLocalProducts() {
            try {
                const res = await fetch('/api/v1/inventory/products?status=active');
                const data = await res.json();
                if (data.success) {
                    preloadedLocalProducts = data.data.products || [];
                    console.log('⚡ Local products preloaded:', preloadedLocalProducts.length);
                }
            } catch (e) {
                console.warn('Local product preload failed');
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Check if we're still on this page
            if (!document.getElementById('purchase_grid_body')) return;

            if (e.key === 'F6') {
                e.preventDefault();
                document.getElementById('p_item_name')?.focus();
            }
            if (e.key === 'F2') {
                e.preventDefault();
                savePurchase('draft');
            }
            if (e.key === 'F4') {
                e.preventDefault();
                savePurchase('sent');
            }
            if (e.key === 'F5') {
                e.preventDefault();
                location.reload();
            }
            if (e.key === 'Escape') {
                e.preventDefault();
                window.location.href = '/products';
            }
        });

        // Vendor selection with change detection
        document.getElementById('distributor_id')?.addEventListener('change', async (e) => {
            const select = e.target;
            const newSupplierId = select.value;

            // Check if there are items in the grid
            const existingItems = document.getElementById('purchase_grid_body')?.querySelectorAll('tr');

            if (existingItems?.length > 0 && selectedSupplierId && selectedSupplierId !== newSupplierId) {
                // Show comparison modal before changing supplier
                await showSupplierChangeModal(selectedSupplierId, newSupplierId, existingItems);
            } else if (newSupplierId) {
                // No items or first selection - proceed normally
                await changeSupplier(newSupplierId);
            } else {
                // Cleared selection
                resetSupplierSelection();
            }
        });

        // Quantity change listener - calculate amount in real-time
        document.getElementById('p_qty')?.addEventListener('input', calculateItemAmount);

        // Tab navigation improvements
        document.getElementById('p_item_name')?.addEventListener('keydown', handleItemInputKeydown);
        document.getElementById('p_qty')?.addEventListener('keydown', handleQtyKeydown);
        document.getElementById('btn_add_item')?.addEventListener('keydown', handleAddButtonKeydown);

        function calculateItemAmount() {
            const qty = parseFloat(document.getElementById('p_qty')?.value) || 0;
            const rate = parseFloat(document.getElementById('p_rate')?.value) || 0;
            const tax = parseFloat(document.getElementById('p_tax')?.value) || 0;

            if (qty > 0 && rate > 0) {
                const subtotal = qty * rate;
                const taxAmount = subtotal * (tax / 100);
                const amount = subtotal + taxAmount;
                const amountEl = document.getElementById('p_amount');
                if (amountEl) amountEl.value = amount.toFixed(2);
            } else {
                const amountEl = document.getElementById('p_amount');
                if (amountEl) amountEl.value = '';
            }
        }

        function handleItemInputKeydown(e) {
            // If Tab pressed and no item selected, prevent default and stay on item input
            if (e.key === 'Tab' && !document.getElementById('p_item_id')?.value) {
                e.preventDefault();
                if (typeof modalNotify !== 'undefined') {
                    modalNotify.warning('Please select an item first');
                }
                return;
            }
        }

        function handleQtyKeydown(e) {
            // If Enter or Tab on qty, move to Add button
            if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                document.getElementById('btn_add_item')?.focus();
            }
        }

        function handleAddButtonKeydown(e) {
            // If Enter or Tab on Add button, add item and return to item input
            if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                addItemToGrid();
            }
        }

        // Call initialization - DOM is ready since script is at bottom of page
        initPurchaseOrdersPage();

        function generatePONumber() {
            const invoiceInput = document.getElementById('invoice_no');
            if (!invoiceInput) return; // Guard against null element

            const date = new Date();
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const poNumber = `PO-${year}${month}${day}-${random}`;
            invoiceInput.value = poNumber;
        }

        async function loadSuppliers() {
            try {
                const res = await fetch('/api/v1/inventory/suppliers');
                const data = await res.json();
                if (data.success) {
                    const select = document.getElementById('distributor_id');
                    data.data.suppliers.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.supplier_id;
                        opt.textContent = `${s.name} (${s.gstin || 'No GST'})`;
                        select.appendChild(opt);
                    });
                }
            } catch (e) {
                console.error('Failed to load suppliers');
            }
        }

        let selectedProduct = null;
        let selectedSupplierId = null;
        let supplierQuotations = [];
        let preloadedLocalProducts = [];
        let melinaProductsCache = [];
        let melinaCacheLoading = false;
        let melinaCacheTimestamp = null;
        const CACHE_EXPIRY_MS = 30 * 60 * 1000; // 30 minutes

        async function changeSupplier(supplierId) {
            selectedSupplierId = supplierId;
            const select = document.getElementById('distributor_id');
            const text = select.options[select.selectedIndex].text;
            document.getElementById('dist_name_display').value = text.split('(')[0].trim();

            // Check if this is Melina supplier
            const isMelinaSupplier = text.toLowerCase().includes('melina');

            if (isMelinaSupplier) {
                // Start background cache loading for Melina products
                loadMelinaProductsCache();
                // Don't wait - let user start typing immediately
            } else {
                // Load quotations for regular suppliers
                await loadSupplierQuotations(supplierId);
                // Clear Melina cache when switching to regular supplier
                melinaProductsCache = null;
                melinaCacheTimestamp = null;
            }

            // Enable item entry fields
            document.getElementById('p_item_name').disabled = false;
            document.getElementById('p_item_name').placeholder = isMelinaSupplier
                ? 'Product Name (F6) - Loading products...'
                : 'Product Name (F6)';
            document.getElementById('p_qty').disabled = false;
            document.querySelector('.btn-add-item').disabled = false;

            // Auto-focus on item input after supplier selection
            setTimeout(() => {
                document.getElementById('p_item_name').focus();
            }, 100);
        }

        /**
         * Load all Melina products in background and cache them
         */
        async function loadMelinaProductsCache() {
            // Check if cache is still valid
            if (melinaProductsCache && melinaCacheTimestamp) {
                const cacheAge = Date.now() - melinaCacheTimestamp;
                if (cacheAge < CACHE_EXPIRY_MS) {
                    console.log('✅ Using cached Melina products (age: ' + Math.round(cacheAge / 1000) + 's)');
                    updatePlaceholder('Product Name (F6) - Ready!');
                    return; // Cache is still valid
                }
            }

            // Prevent multiple simultaneous loads
            if (melinaCacheLoading) {
                console.log('⏳ Melina cache already loading...');
                return;
            }

            melinaCacheLoading = true;
            console.log('🔄 Loading Melina products cache in background...');

            try {
                // Fetch all products without search filter to get everything
                // Use high limit to ensure we get all products
                const response = await fetch('/api/v1/inventory/melina-products?search=&limit=1000');
                const data = await response.json();

                console.log('📦 API Response:', data); // Debug log

                if (data.success && data.data.products) {
                    // Process and cache all products
                    const rawProducts = data.data.products;

                    melinaProductsCache = rawProducts
                        .filter(p => p.batches && p.batches.length > 0)
                        .map(p => {
                            const latestBatch = p.batches.reduce((latest, batch) => {
                                return (!latest || batch.batch_id > latest.batch_id) ? batch : latest;
                            }, null);

                            return {
                                product_id: p.id,
                                name: p.name,
                                sku: p.sku,
                                description: p.description,
                                tax_percent: p.gst_rate,
                                unit_price: latestBatch?.selling_price || p.selling_price,
                                mrp: latestBatch?.mrp || p.mrp,
                                cost_price: p.cost_price,
                                hasStock: true,
                                isRemote: true,
                                batch_info: latestBatch,
                                total_batches: p.batches.length
                            };
                        });

                    melinaCacheTimestamp = Date.now();
                    console.log('✅ Melina products cached:', melinaProductsCache.length, 'items');

                    // 🚀 SYNC PRODUCTS TO LOCAL DATABASE (BATCH OPERATION)
                    console.log('🔄 Syncing products to local database...');
                    await syncMelinaProductsToLocal(melinaProductsCache);

                    // Update placeholder to show ready status (silent background process)
                    updatePlaceholder('Product Name (F6) - Ready! ' + melinaProductsCache.length + ' items loaded');
                } else {
                    console.error('❌ Failed to load Melina products:', data);
                    melinaProductsCache = [];
                }
            } catch (error) {
                console.error('❌ Error loading Melina products cache:', error);
                melinaProductsCache = null; // Will fall back to remote search
                updatePlaceholder('Product Name (F6) - Cache failed, using live search');
            } finally {
                melinaCacheLoading = false;
            }
        }

        /**
         * Sync Melina products to local database (batch operation)
         */
        async function syncMelinaProductsToLocal(products) {
            try {
                // Prepare products for sync (only essential data)
                const productsToSync = products.map(p => ({
                    id: p.product_id,
                    name: p.name,
                    sku: p.sku,
                    description: p.description || '',
                    tax_percent: p.tax_percent || 0
                }));

                console.log('📤 Sending', productsToSync.length, 'products to sync...');

                const response = await fetch('/api/v1/inventory/sync-melina-products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        products: productsToSync
                    })
                });

                const result = await response.json();

                if (result.success) {
                    console.log('✅ Products synced to local database:', {
                        synced: result.data.synced,
                        skipped: result.data.skipped,
                        errors: result.data.errors
                    });

                    // Now update cache with local product IDs
                    await updateCacheWithLocalIds();
                } else {
                    console.error('❌ Failed to sync products:', result.message);
                }
            } catch (error) {
                console.error('❌ Error syncing products to local database:', error);
            }
        }

        /**
         * Update cache with local product IDs after sync
         */
        async function updateCacheWithLocalIds() {
            try {
                console.log('🔄 Updating cache with local product IDs...');

                // Fetch local products to get their IDs
                const response = await fetch('/api/v1/inventory/products?limit=1000');
                const data = await response.json();

                if (data.success && data.data.products) {
                    const localProducts = data.data.products;

                    // Map remote products to local product IDs by SKU
                    melinaProductsCache = melinaProductsCache.map(remoteProduct => {
                        const localProduct = localProducts.find(lp => lp.sku === remoteProduct.sku);

                        if (localProduct) {
                            // Use local product_id for purchase orders
                            return {
                                ...remoteProduct,
                                product_id: localProduct.product_id, // Override with local ID
                                local_product_id: localProduct.product_id,
                                remote_product_id: remoteProduct.product_id
                            };
                        }

                        return remoteProduct;
                    });

                    console.log('✅ Cache updated with local product IDs');
                } else {
                    console.warn('⚠️ Could not fetch local products for ID mapping');
                }
            } catch (error) {
                console.error('❌ Error updating cache with local IDs:', error);
            }
        }

        /**
         * Helper to update item input placeholder
         */
        function updatePlaceholder(text) {
            const input = document.getElementById('p_item_name');
            if (input && !input.value) {
                input.placeholder = text;
            }
        }

        function resetSupplierSelection() {
            document.getElementById('dist_name_display').value = '---';
            selectedSupplierId = null;
            supplierQuotations = [];

            // Clear Melina cache when no supplier selected
            melinaProductsCache = null;
            melinaCacheTimestamp = null;

            // Disable item entry fields
            document.getElementById('p_item_name').disabled = true;
            document.getElementById('p_item_name').placeholder = 'Select Vendor First...';
            document.getElementById('p_qty').disabled = true;
            document.getElementById('p_rate').value = '';
            document.getElementById('p_tax').value = '';
            document.getElementById('p_mrp').value = '';
            document.getElementById('p_amount').value = '';
            document.querySelector('.btn-add-item').disabled = true;
        }

        async function showSupplierChangeModal(oldSupplierId, newSupplierId, existingItems) {
            // Get supplier names
            const supplierSelect = document.getElementById('distributor_id');
            const oldSupplierName = Array.from(supplierSelect.options).find(o => o.value == oldSupplierId)?.text.split('(')[0].trim();
            const newSupplierName = Array.from(supplierSelect.options).find(o => o.value == newSupplierId)?.text.split('(')[0].trim();

            // Load new supplier quotations
            const newSupplierQuotations = await fetchSupplierQuotations(newSupplierId);

            // Build comparison data
            let comparisonHTML = '';
            let matchedProducts = [];
            let unmatchedProducts = [];

            existingItems.forEach(row => {
                const productId = row.dataset.itemId;
                const productName = row.children[1].innerText;
                const currentQty = parseInt(row.children[3].innerText);
                const currentRate = parseFloat(row.children[4].innerText.replace('₹', ''));
                const currentTax = parseFloat(row.children[5].innerText.replace('%', ''));

                // Find in new supplier quotations
                const newQuotation = newSupplierQuotations.find(q => q.product_id == productId);

                if (newQuotation) {
                    const priceDiff = newQuotation.unit_price - currentRate;
                    const percentDiff = ((priceDiff / currentRate) * 100).toFixed(2);

                    matchedProducts.push({
                        productId,
                        productName,
                        currentQty,
                        currentRate,
                        currentTax,
                        newRate: newQuotation.unit_price,
                        newTax: newQuotation.tax_percent,
                        newMrp: newQuotation.mrp,
                        priceDiff,
                        percentDiff
                    });

                    const diffClass = priceDiff > 0 ? 'text-danger' : priceDiff < 0 ? 'text-success' : 'text-muted';
                    const diffIcon = priceDiff > 0 ? 'ri-arrow-up-line' : priceDiff < 0 ? 'ri-arrow-down-line' : 'ri-subtract-line';

                    comparisonHTML += `
                    <tr>
                        <td class="fw-semibold">${productName}</td>
                        <td class="text-center">${currentQty}</td>
                        <td class="text-end">₹${currentRate.toFixed(2)}</td>
                        <td class="text-end">₹${newQuotation.unit_price.toFixed(2)}</td>
                        <td class="text-end ${diffClass}">
                            <i class="${diffIcon}"></i>
                            ₹${Math.abs(priceDiff).toFixed(2)} (${percentDiff}%)
                        </td>
                    </tr>
                `;
                } else {
                    unmatchedProducts.push({
                        productName,
                        currentQty,
                        currentRate
                    });

                    comparisonHTML += `
                    <tr class="table-warning">
                        <td class="fw-semibold">${productName}</td>
                        <td class="text-center">${currentQty}</td>
                        <td class="text-end">₹${currentRate.toFixed(2)}</td>
                        <td class="text-end text-muted">No Quotation</td>
                        <td class="text-end text-muted">-</td>
                    </tr>
                `;
                }
            });

            // Create modal
            const modalHTML = `
            <div id="supplierChangeModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center; overflow-y: auto; padding: 20px;">
                <div style="background: white; border-radius: 12px; max-width: 900px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                    <div style="padding: 1.5rem; border-bottom: 2px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h5 style="margin: 0; color: #1f2937; font-weight: 700;">
                                    <i class="ri-exchange-line me-2"></i>Supplier Change Confirmation
                                </h5>
                                <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 13px;">
                                    Comparing prices between suppliers
                                </p>
                            </div>
                            <button onclick="closeSupplierChangeModal()" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div style="padding: 1.5rem;">
                        <div style="background: #f3f4f6; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 1rem; align-items: center;">
                                <div>
                                    <div style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Current Supplier</div>
                                    <div style="font-size: 15px; font-weight: 700; color: #1f2937;">${oldSupplierName}</div>
                                </div>
                                <div style="text-align: center;">
                                    <i class="ri-arrow-right-line" style="font-size: 24px; color: #3b82f6;"></i>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">New Supplier</div>
                                    <div style="font-size: 15px; font-weight: 700; color: #3b82f6;">${newSupplierName}</div>
                                </div>
                            </div>
                        </div>
                        
                        ${unmatchedProducts.length > 0 ? `
                            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                                <div style="display: flex; align-items-start; gap: 0.75rem;">
                                    <i class="ri-error-warning-line" style="font-size: 20px; color: #f59e0b; flex-shrink: 0;"></i>
                                    <div>
                                        <div style="font-weight: 700; color: #92400e; margin-bottom: 0.25rem;">Warning: ${unmatchedProducts.length} product(s) without quotation</div>
                                        <div style="font-size: 13px; color: #78350f;">
                                            These products will be removed from the purchase order as the new supplier doesn't have quotations for them.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                        
                        <div style="margin-bottom: 1rem;">
                            <h6 style="font-size: 13px; font-weight: 700; color: #1f2937; margin-bottom: 0.75rem;">Price Comparison</h6>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                    <thead>
                                        <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                            <th style="padding: 0.75rem; text-align: left; font-weight: 700; color: #6b7280; font-size: 11px; text-transform: uppercase;">Product</th>
                                            <th style="padding: 0.75rem; text-align: center; font-weight: 700; color: #6b7280; font-size: 11px; text-transform: uppercase;">Qty</th>
                                            <th style="padding: 0.75rem; text-align: right; font-weight: 700; color: #6b7280; font-size: 11px; text-transform: uppercase;">Current Price</th>
                                            <th style="padding: 0.75rem; text-align: right; font-weight: 700; color: #6b7280; font-size: 11px; text-transform: uppercase;">New Price</th>
                                            <th style="padding: 0.75rem; text-align: right; font-weight: 700; color: #6b7280; font-size: 11px; text-transform: uppercase;">Difference</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${comparisonHTML}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div style="background: #eff6ff; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                            <div style="font-size: 12px; color: #1e40af;">
                                <strong>Summary:</strong> 
                                ${matchedProducts.length} product(s) with quotations will be updated. 
                                ${unmatchedProducts.length > 0 ? `${unmatchedProducts.length} product(s) will be removed.` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div style="padding: 1rem 1.5rem; border-top: 2px solid #e5e7eb; display: flex; gap: 0.75rem; justify-content: flex-end; position: sticky; bottom: 0; background: white;">
                        <button onclick="closeSupplierChangeModal()" style="padding: 0.625rem 1.5rem; background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px;">
                            Cancel
                        </button>
                        <button onclick="confirmSupplierChange('${newSupplierId}')" style="padding: 0.625rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px;">
                            <i class="ri-check-line me-1"></i>Confirm Change
                        </button>
                    </div>
                </div>
            </div>
        `;

            document.body.insertAdjacentHTML('beforeend', modalHTML);

            // Store data for confirmation
            window.pendingSupplierChange = {
                newSupplierId,
                matchedProducts,
                newSupplierQuotations
            };
        }



        async function fetchSupplierQuotations(supplierId) {
            try {
                const res = await fetch(`/api/v1/quotations/supplier/${supplierId}`);
                const data = await res.json();

                const quotations = [];
                if (data.success && data.data.quotations) {
                    const activeQuotations = data.data.quotations.filter(q => q.status === 'active');

                    for (const quotation of activeQuotations) {
                        const itemsRes = await fetch(`/api/v1/quotations/${quotation.quotation_id}`);
                        const itemsData = await itemsRes.json();

                        if (itemsData.success && itemsData.data.items) {
                            itemsData.data.items.forEach(item => {
                                quotations.push({
                                    product_id: item.product_id,
                                    product_name: item.product_name,
                                    sku: item.sku,
                                    unit_price: item.unit_price,
                                    tax_percent: item.tax_percent,
                                    mrp: item.mrp,
                                    quotation_no: quotation.quotation_no
                                });
                            });
                        }
                    }
                }

                return quotations;
            } catch (e) {
                console.error('Failed to fetch quotations:', e);
                return [];
            }
        }

        function closeSupplierChangeModal() {
            const modal = document.getElementById('supplierChangeModal');
            if (modal) {
                modal.remove();
            }

            // Revert supplier selection
            if (selectedSupplierId) {
                document.getElementById('distributor_id').value = selectedSupplierId;
            }

            window.pendingSupplierChange = null;
        }

        async function confirmSupplierChange(newSupplierId) {
            // Retrieve data from memory
            const matchedProducts = window.pendingSupplierChange?.matchedProducts || [];

            // Close modal
            const modal = document.getElementById('supplierChangeModal');
            if (modal) {
                modal.remove();
            }

            // Clear existing grid
            const body = document.getElementById('purchase_grid_body');
            body.innerHTML = '';

            // Change supplier
            await changeSupplier(newSupplierId);

            // Re-add matched products with new prices
            matchedProducts.forEach((product, index) => {
                setTimeout(() => {
                    const row = document.createElement('tr');
                    row.dataset.itemId = product.productId;

                    const qty = product.currentQty;
                    const rate = product.newRate.toFixed(2);
                    const tax = product.newTax.toFixed(2);
                    const mrp = product.newMrp.toFixed(2);
                    const subtotal = qty * parseFloat(rate);
                    const taxAmount = subtotal * (parseFloat(tax) / 100);
                    const total = (subtotal + taxAmount).toFixed(2);

                    row.innerHTML = `
                    <td class="text-center">${index + 1}</td>
                    <td class="fw-bold">${product.productName}</td>
                    <td class="text-center">-</td>
                    <td class="text-center fw-bold">${qty}</td>
                    <td class="text-end">₹${rate}</td>
                    <td class="text-center">${tax}%</td>
                    <td class="text-end">₹${mrp}</td>
                    <td class="text-end fw-bold">₹${total}</td>
                    <td class="text-center">
                        <i class="ri-close-circle-fill text-danger" onclick="this.closest('tr').remove(); calculateFooter();"></i>
                    </td>
                `;
                    body.appendChild(row);
                }, index * 50);
            });

            // Show empty state if no products
            if (matchedProducts.length === 0) {
                document.getElementById('empty_grid_msg').style.display = 'flex';
            } else {
                document.getElementById('empty_grid_msg').style.display = 'none';
            }

            // Recalculate totals
            setTimeout(() => {
                calculateFooter();
            }, matchedProducts.length * 50 + 100);

            modalNotify.success(`Supplier changed successfully. ${matchedProducts.length} product(s) updated with new prices.`);

            window.pendingSupplierChange = null;
        }

        async function loadSupplierQuotations(supplierId) {
            selectedSupplierId = supplierId;
            supplierQuotations = [];

            try {
                const res = await fetch(`/api/v1/quotations/supplier/${supplierId}`);
                const data = await res.json();

                if (data.success && data.data.quotations) {
                    // Get the latest active quotation items
                    const activeQuotations = data.data.quotations.filter(q => q.status === 'active');

                    if (activeQuotations.length > 0) {
                        // Fetch items for each quotation
                        for (const quotation of activeQuotations) {
                            const itemsRes = await fetch(`/api/v1/quotations/${quotation.quotation_id}`);
                            const itemsData = await itemsRes.json();

                            if (itemsData.success && itemsData.data.items) {
                                itemsData.data.items.forEach(item => {
                                    supplierQuotations.push({
                                        product_id: item.product_id,
                                        product_name: item.product_name,
                                        sku: item.sku,
                                        unit_price: item.unit_price,
                                        tax_percent: item.tax_percent,
                                        mrp: item.mrp,
                                        quotation_no: quotation.quotation_no
                                    });
                                });
                            }
                        }
                    }
                }

                console.log('Loaded quotations for supplier:', supplierQuotations);
            } catch (e) {
                console.error('Failed to load supplier quotations:', e);
            }
        }

        function initProductSearch() {
            const input = document.getElementById('p_item_name');
            const autoCompleteJS = new autoComplete({
                selector: "#p_item_name",
                placeHolder: "Search Products (F6)...",
                threshold: 2,
                debounce: 300,
                data: {
                    src: async (query) => {
                        try {
                            const supplierSelect = document.getElementById('distributor_id');
                            const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
                            const supplierName = selectedOption?.text || '';
                            const isMelinaSupplier = supplierName.toLowerCase().includes('melina');

                            let products = [];

                            if (isMelinaSupplier) {
                                // ⚡ INSTANT MELINA SEARCH from cache
                                if (melinaProductsCache && melinaProductsCache.length > 0) {
                                    const lowerQuery = query.toLowerCase().trim();
                                    products = melinaProductsCache.filter(p =>
                                        p.name.toLowerCase().includes(lowerQuery) ||
                                        (p.sku && p.sku.toLowerCase().includes(lowerQuery))
                                    );
                                    if (products.length > 0) return products;
                                }
                                // Fallback to remote API
                                const source = await fetch(`/api/v1/inventory/melina-products?search=${query}`);
                                const data = await source.json();
                                products = data.data.products || [];
                                return products;
                            } else {
                                // ⚡ INSTANT LOCAL SEARCH from preload
                                const lowerQuery = query.toLowerCase().trim();
                                products = preloadedLocalProducts.filter(p =>
                                    p.name.toLowerCase().includes(lowerQuery) ||
                                    (p.sku && p.sku.toLowerCase().includes(lowerQuery))
                                );

                                if (products.length > 0) {
                                    // Mark products with/without quotations
                                    return products.map(p => ({
                                        ...p,
                                        hasQuotation: supplierQuotations.some(q => q.product_id == p.product_id),
                                        isRemote: false
                                    }));
                                }

                                // Fallback to local API
                                const source = await fetch(`/api/v1/inventory/products?search=${query}`);
                                const data = await source.json();
                                products = data.data.products || [];

                                return products.map(p => ({
                                    ...p,
                                    hasQuotation: supplierQuotations.some(q => q.product_id == p.product_id),
                                    isRemote: false
                                }));
                            }
                        } catch (error) {
                            console.error('Product search error:', error);
                            return [];
                        }
                    },
                },
                keys: ["name", "sku"],
                cache: false,
                resultsList: {
                    element: (list, data) => {
                        if (!data.results.length) {
                            const message = document.createElement("div");
                            message.setAttribute("class", "no_result");
                            message.innerHTML = `<span>No products found for "${data.query}"</span>`;
                            list.prepend(message);
                        }
                    },
                    noResults: true,
                    maxResults: 10
                },
                resultItem: {
                    highlight: true,
                    element: (item, data) => {
                        const isRemote = data.value.isRemote;
                        const hasQuotation = data.value.hasQuotation;
                        const hasStock = data.value.hasStock;

                        // For remote (Melina) products, all in-stock items are enabled
                        // For local products, only those with quotations are enabled
                        const isEnabled = isRemote ? hasStock : hasQuotation;
                        const disabledClass = !isEnabled ? 'disabled-item' : '';

                        let statusBadge = '';
                        if (isRemote) {
                            if (hasStock) {
                                const batchInfo = data.value.batch_info;
                                const totalBatches = data.value.total_batches || 0;
                                statusBadge = `
                                    <div class="autocomplete-product-tax">
                                        In Stock (${totalBatches} batch${totalBatches > 1 ? 'es' : ''})
                                    </div>
                                    <div class="autocomplete-product-category">
                                        ₹${parseFloat(data.value.unit_price || 0).toFixed(2)}
                                    </div>
                                `;
                            } else {
                                statusBadge = `<div class="autocomplete-no-quotation">Out of Stock</div>`;
                            }
                        } else {
                            statusBadge = hasQuotation
                                ? `<div class="autocomplete-product-tax">Tax: ${data.value.tax_percent || 0}%</div>`
                                : `<div class="autocomplete-no-quotation">No Quotation</div>`;
                        }

                        item.innerHTML = `
                        <div class="autocomplete-product-item ${disabledClass}">
                            <div class="autocomplete-product-left">
                                <div class="autocomplete-product-name">${data.value.name}</div>
                                <div class="autocomplete-product-sku">SKU: ${data.value.sku || 'N/A'}</div>
                            </div>
                            <div class="autocomplete-product-right">
                                ${statusBadge}
                            </div>
                        </div>
                    `;

                        // Disable selection for products without stock/quotation
                        if (!isEnabled) {
                            item.style.pointerEvents = 'none';
                            item.style.opacity = '0.5';
                        }
                    }
                },
                events: {
                    input: {
                        selection: (event) => {
                            const selection = event.detail.selection.value;
                            const isRemote = selection.isRemote;

                            // Validation based on source
                            if (isRemote) {
                                // For remote (Melina) products, check stock
                                if (!selection.hasStock) {
                                    modalNotify.warning('This product is out of stock');
                                    return;
                                }
                            } else {
                                // For local products, check quotation
                                if (!selection.hasQuotation) {
                                    modalNotify.warning('This product does not have a quotation from the selected supplier');
                                    return;
                                }
                            }

                            input.value = selection.name;
                            document.getElementById('p_item_id').value = selection.product_id;
                            selectedProduct = selection;

                            if (isRemote) {
                                // For Melina remote products, use latest batch price
                                const rate = parseFloat(selection.unit_price || 0);
                                const mrp = parseFloat(selection.mrp || 0);
                                const tax = parseFloat(selection.tax_percent || 0);

                                document.getElementById('p_rate').value = rate.toFixed(2);
                                document.getElementById('p_tax').value = tax.toFixed(2);
                                document.getElementById('p_mrp').value = mrp.toFixed(2);
                                selectedProduct.tax_percent = tax;

                                // Calculate initial amount with default qty (1)
                                const qty = parseFloat(document.getElementById('p_qty').value) || 1;
                                const subtotal = qty * rate;
                                const taxAmount = subtotal * (tax / 100);
                                const amount = subtotal + taxAmount;
                                document.getElementById('p_amount').value = amount.toFixed(2);

                                // Show batch info in console for debugging
                                if (selection.batch_info) {
                                    console.log('Selected batch info:', {
                                        batch_number: selection.batch_info.batch_number,
                                        quantity: selection.batch_info.quantity,
                                        mfg_date: selection.batch_info.mfg_date,
                                        exp_date: selection.batch_info.exp_date,
                                        selling_price: selection.batch_info.selling_price
                                    });
                                }
                            } else {
                                // For local products, use quotation data
                                const quotation = supplierQuotations.find(q => q.product_id == selection.product_id);

                                if (quotation) {
                                    // Auto-fill from quotation (readonly)
                                    document.getElementById('p_rate').value = quotation.unit_price;
                                    document.getElementById('p_tax').value = quotation.tax_percent;
                                    document.getElementById('p_mrp').value = quotation.mrp;
                                    selectedProduct.tax_percent = quotation.tax_percent;

                                    // Calculate initial amount with default qty (1)
                                    const qty = parseFloat(document.getElementById('p_qty').value) || 1;
                                    const rate = parseFloat(quotation.unit_price);
                                    const tax = parseFloat(quotation.tax_percent);
                                    const subtotal = qty * rate;
                                    const taxAmount = subtotal * (tax / 100);
                                    const amount = subtotal + taxAmount;
                                    document.getElementById('p_amount').value = amount.toFixed(2);
                                }
                            }

                            // Focus on quantity after item selection
                            setTimeout(() => {
                                document.getElementById('p_qty').focus();
                                document.getElementById('p_qty').select();
                            }, 100);
                        }
                    }
                }
            });
        }

        function addItemToGrid() {
            const name = document.getElementById('p_item_name').value;
            const itemId = document.getElementById('p_item_id').value;
            if (!name || !itemId) {
                modalNotify.warning('Please select a product from search');
                document.getElementById('p_item_name').focus();
                return;
            }

            const qty = parseInt(document.getElementById('p_qty').value || 1);
            if (qty <= 0) {
                modalNotify.warning('Quantity must be greater than 0');
                document.getElementById('p_qty').focus();
                return;
            }

            document.getElementById('empty_grid_msg').style.display = 'none';
            const body = document.getElementById('purchase_grid_body');

            // Check if product already exists in grid
            const existingRow = Array.from(body.querySelectorAll('tr')).find(row => row.dataset.itemId === itemId);

            if (existingRow) {
                // Product exists - update quantity
                const qtyCell = existingRow.children[3];
                const currentQty = parseInt(qtyCell.innerText);
                const newQty = currentQty + qty;
                qtyCell.innerText = newQty;

                // Recalculate amount for this row
                const rate = parseFloat(existingRow.children[4].innerText.replace('₹', ''));
                const tax = parseFloat(existingRow.children[5].innerText.replace('%', ''));
                const subtotal = newQty * rate;
                const taxAmount = subtotal * (tax / 100);
                const total = (subtotal + taxAmount).toFixed(2);
                existingRow.children[7].innerText = `₹${total}`;

                // Show custom success modal
                showQuantityUpdateModal(name, currentQty, qty, newQty);
            } else {
                // New product - add new row
                const sno = body.children.length + 1;
                const row = document.createElement('tr');
                row.dataset.itemId = itemId;

                // Get values
                const rate = parseFloat(document.getElementById('p_rate').value || 0).toFixed(2);
                const tax = parseFloat(document.getElementById('p_tax').value || 0).toFixed(2);
                const mrp = parseFloat(document.getElementById('p_mrp').value || 0).toFixed(2);
                const subtotal = qty * parseFloat(rate);
                const taxAmount = subtotal * (parseFloat(tax) / 100);
                const total = (subtotal + taxAmount).toFixed(2);

                row.innerHTML = `
                <td class="text-center">${sno}</td>
                <td class="fw-bold">${name}</td>
                <td class="text-center">${selectedProduct?.sku || '-'}</td>
                <td class="text-center fw-bold">${qty}</td>
                <td class="text-end">₹${rate}</td>
                <td class="text-center">${tax}%</td>
                <td class="text-end">₹${mrp}</td>
                <td class="text-end fw-bold">₹${total}</td>
                <td class="text-center">
                    <i class="ri-close-circle-fill text-danger" onclick="this.closest('tr').remove(); calculateFooter();"></i>
                </td>
            `;
                body.appendChild(row);
            }

            // Reset inputs
            document.getElementById('p_item_name').value = '';
            document.getElementById('p_item_id').value = '';
            document.getElementById('p_qty').value = '1';
            document.getElementById('p_rate').value = '';
            document.getElementById('p_tax').value = '';
            document.getElementById('p_mrp').value = '';
            document.getElementById('p_amount').value = '';
            selectedProduct = null;

            // Return focus to item input for next entry
            setTimeout(() => {
                document.getElementById('p_item_name').focus();
            }, 100);

            calculateFooter();
        }

        function showQuantityUpdateModal(productName, oldQty, addedQty, newQty) {
            // Remove existing modal if any
            const existingModal = document.getElementById('qtyUpdateModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Create modal HTML
            const modalHTML = `
            <div id="qtyUpdateModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; border-radius: 8px; padding: 2rem; max-width: 400px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                    <div style="width: 60px; height: 60px; background: #10b981; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                        <i class="ri-check-line" style="font-size: 2rem; color: white;"></i>
                    </div>
                    <h5 style="color: #1f2937; margin-bottom: 1rem; font-weight: 700;">Success</h5>
                    <p style="color: #6b7280; margin-bottom: 1.5rem; font-size: 14px;">
                        Quantity updated: <strong>${productName}</strong> (${oldQty} + ${addedQty} = ${newQty})
                    </p>
                    <button id="qtyModalOkBtn" style="background: #10b981; color: white; border: none; padding: 0.75rem 2rem; border-radius: 6px; font-weight: 700; cursor: pointer; width: 100%; font-size: 14px;">
                        OK
                    </button>
                </div>
            </div>
        `;

            document.body.insertAdjacentHTML('beforeend', modalHTML);

            const modal = document.getElementById('qtyUpdateModal');
            const okBtn = document.getElementById('qtyModalOkBtn');

            // Close modal function
            const closeModal = () => {
                if (modal) {
                    modal.remove();
                }
                // Remove event listeners
                document.removeEventListener('keydown', handleEnterKey);
            };

            // Handle Enter key
            const handleEnterKey = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    closeModal();
                }
            };

            // Add event listeners
            okBtn.addEventListener('click', closeModal);
            document.addEventListener('keydown', handleEnterKey);

            // Auto-hide after 1.5 seconds
            setTimeout(() => {
                closeModal();
            }, 1500);
        }

        function calculateFooter() {
            const rows = document.getElementById('purchase_grid_body').querySelectorAll('tr');
            let totalQty = 0;
            let subTotal = 0;
            let totalTax = 0;

            // GST breakdown by tax percentage
            const gstBreakdown = {};

            rows.forEach(row => {
                const qty = parseInt(row.children[3].innerText);
                const rate = parseFloat(row.children[4].innerText.replace('₹', ''));
                const tax = parseFloat(row.children[5].innerText.replace('%', ''));

                const itemSubtotal = qty * rate;
                const itemTax = itemSubtotal * (tax / 100);

                totalQty += qty;
                subTotal += itemSubtotal;
                totalTax += itemTax;

                // Group by GST percentage
                if (!gstBreakdown[tax]) {
                    gstBreakdown[tax] = {
                        taxableValue: 0,
                        sgst: 0,
                        cgst: 0,
                        totalTax: 0
                    };
                }

                gstBreakdown[tax].taxableValue += itemSubtotal;
                gstBreakdown[tax].sgst += itemTax / 2;
                gstBreakdown[tax].cgst += itemTax / 2;
                gstBreakdown[tax].totalTax += itemTax;
            });

            // Update totals
            document.getElementById('total_qty_footer').innerText = totalQty;
            document.getElementById('sum_taxable').innerText = subTotal.toFixed(2);
            document.getElementById('f_gst_tax').innerText = totalTax.toFixed(2);
            document.getElementById('grand_total_footer').innerText = (subTotal + totalTax).toFixed(2);

            // Update GST breakdown table
            const gstBody = document.getElementById('gst_breakdown_body');
            gstBody.innerHTML = '';

            if (Object.keys(gstBreakdown).length > 0) {
                Object.keys(gstBreakdown).sort((a, b) => parseFloat(a) - parseFloat(b)).forEach(taxRate => {
                    const data = gstBreakdown[taxRate];
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td class="fw-bold">${taxRate}%</td>
                    <td class="text-end">₹${data.taxableValue.toFixed(2)}</td>
                    <td class="text-end">₹${data.sgst.toFixed(2)}</td>
                    <td class="text-end">₹${data.cgst.toFixed(2)}</td>
                    <td class="text-end fw-bold">₹${data.totalTax.toFixed(2)}</td>
                `;
                    gstBody.appendChild(row);
                });
            } else {
                gstBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>';
            }
        }

        async function savePurchase(status = 'draft') {
            const rows = document.getElementById('purchase_grid_body').querySelectorAll('tr');
            if (rows.length === 0) {
                modalNotify.warning('Please add items to save');
                return;
            }

            const supplierId = document.getElementById('distributor_id').value;
            if (!supplierId) {
                modalNotify.warning('Please select a supplier');
                return;
            }

            const items = Array.from(rows).map(row => ({
                product_id: row.dataset.itemId,
                qty: parseInt(row.children[3].innerText),
                cost: parseFloat(row.children[4].innerText.replace('₹', '')) // FIX: Use Rate column (index 4), not Amount (index 7)
            }));

            const totalAmount = parseFloat(document.getElementById('grand_total_footer').innerText);

            const payload = {
                supplier_id: supplierId,
                invoice_no: document.getElementById('invoice_no').value,
                total_amount: totalAmount,
                items: items,
                status: status
            };

            const statusLabel = status === 'sent' ? 'Save & Send' : 'Save Draft';
            console.log(`Saving PO (${status}) with payload:`, payload);

            try {
                const res = await fetch('/api/v1/inventory/purchase-orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    modalNotify.success(`Purchase order ${status === 'sent' ? 'sent' : 'saved'} successfully!`, 'Success');
                    setTimeout(() => window.location.href = '/purchase-orders', 1500);
                } else {
                    modalNotify.error(data.message || 'Failed to save purchase order');
                }
            } catch (e) {
                modalNotify.error('Network error: ' + e.message);
            }
        }
    } // End of page guard else block
</script>
<!-- Cache bust: v2 -->
<script src="<?= asset('libs/@tarekraafat/autocomplete.js/autoComplete.min.js') ?>"></script>
<link rel="stylesheet" href="<?= asset('libs/@tarekraafat/autocomplete.js/css/autoComplete.02.css') ?>">
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>