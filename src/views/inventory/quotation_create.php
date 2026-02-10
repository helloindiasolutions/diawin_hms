<?php
$pageTitle = "New Quotation";
ob_start();
?>

<div class="quotation-container">
    <div class="quotation-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="page-title fw-semibold fs-20 mb-1">New Quotation</h1>
                <p class="text-muted mb-0 fs-13">Create supplier quotation with pricing and terms</p>
            </div>
        </div>
        <!-- Form Fields Grid -->
        <div class="form-fields-grid">
            <div class="form-field">
                <label>Supplier *</label>
                <select id="supplier_id" required>
                    <option value="">Select Supplier...</option>
                </select>
            </div>
            <div class="form-field">
                <label>Quotation Number *</label>
                <input type="text" id="quotation_no" placeholder="Auto-generated" readonly>
            </div>
            <div class="form-field">
                <label>Quotation Date *</label>
                <input type="date" id="quotation_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-field">
                <label>Valid Until *</label>
                <input type="date" id="valid_until" required>
            </div>
            <div class="form-field">
                <label>Supplier Reference</label>
                <input type="text" id="supplier_reference" placeholder="Optional">
            </div>
            <div class="form-field">
                <label>Remarks</label>
                <input type="text" id="remarks" placeholder="Optional notes">
            </div>
        </div>
    </div>

    <!-- Item Entry Strip (Fixed) -->
    <div class="item-entry-strip">
        <div class="item-entry-row">
            <div style="position:relative; flex:1;">
                <input type="text" id="q_item_name" placeholder="Product Name (F6)" autocomplete="off" tabindex="1">
            </div>
            <input type="hidden" id="q_item_id">
            <input type="number" id="q_qty" value="1" placeholder="Qty" min="1" tabindex="2">
            <input type="number" id="q_unit_price" placeholder="Unit Price" step="0.01" min="0" tabindex="3">
            <input type="number" id="q_tax_percent" placeholder="Tax %" step="0.01" min="0" max="100" tabindex="4">
            <input type="number" id="q_mrp" placeholder="MRP" step="0.01" min="0" tabindex="5">
            <button class="btn-add-item" id="btn_add_quotation" onclick="addQuotationItem()" tabindex="6">ADD</button>
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
                    <th style="width: 100px;">Unit Price</th>
                    <th style="width: 80px;">Tax %</th>
                    <th style="width: 100px;">MRP</th>
                    <th style="width: 120px;">Total</th>
                    <th style="width: 40px;"></th>
                </tr>
            </thead>
            <tbody id="quotation_grid_body">
                <!-- Dynamic rows added here -->
            </tbody>
        </table>
        <div id="empty_grid_msg" class="empty-state">
            <i class="ri-file-list-3-line"></i>
            <p>No items added. Press F6 to search products.</p>
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
                    <label>Total Quantity:</label>
                    <span class="value" id="total_qty">0</span>
                </div>
                <div class="total-row">
                    <label>Sub Total:</label>
                    <span class="value" id="sub_total">₹0.00</span>
                </div>
                <div class="total-row">
                    <label>Total Tax:</label>
                    <span class="value" id="total_tax">₹0.00</span>
                </div>
                <div class="total-row grand">
                    <label>Grand Total:</label>
                    <span class="value" id="grand_total">₹0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons (Fixed Bottom) -->
    <div class="action-buttons-bar">
        <button class="btn-action btn-primary" onclick="saveQuotation()">
            <i class="ri-save-line"></i> SAVE QUOTATION (F2)
        </button>
        <button class="btn-action btn-secondary" onclick="location.reload()">
            <i class="ri-refresh-line"></i> CLEAR (F5)
        </button>
        <button class="btn-action btn-secondary" onclick="window.location.href='/purchase-quotations'">
            <i class="ri-arrow-left-line"></i> BACK
        </button>
        <div class="shortcuts-hint">
            F2: Save | F5: Clear | F6: Search Item | ESC: Back
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

    .quotation-container {
        height: calc(100vh - 110px);
        display: flex;
        flex-direction: column;
        background: var(--bg-white);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    .quotation-header {
        flex-shrink: 0;
        background: var(--bg-white);
        border-bottom: 2px solid var(--border-color);
        padding: 0.25rem 1rem;
    }

    .page-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .form-fields-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
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

    .item-entry-strip {
        flex-shrink: 0;
        background: #eff6ff;
        border-bottom: 1px solid var(--border-color);
        padding: 0.35rem 1rem;
    }

    .item-entry-row {
        display: grid;
        grid-template-columns: 4fr 0.8fr 1.2fr 0.8fr 1fr auto;
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

    /* Make product name input wider and more visible */
    #q_item_name {
        min-width: 400px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .item-entry-row input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
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

    .item-entry-row .btn-add-item:hover {
        background: var(--primary-blue-dark);
    }

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

    .bottom-summary-area {
        flex-shrink: 0;
        background: var(--bg-white);
        border-top: 2px solid var(--border-color);
        padding: 0.5rem 1rem;
    }

    .summary-grid-simple {
        display: flex;
        justify-content: flex-end;
    }

    .totals-panel {
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 0.5rem 1rem;
        background: var(--bg-light);
        min-width: 350px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.3rem;
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
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 2px solid var(--border-color);
    }

    .total-row.grand label {
        font-size: 13px;
        color: var(--text-primary);
    }

    .total-row.grand .value {
        font-size: 1.25rem;
        color: var(--primary-blue);
    }

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

    .main-content.app-content {
        padding-inline: 0.5rem !important;
        margin-block-start: 8.5rem !important;
    }

    .main-content.app-content>.container-fluid {
        padding: 0 !important;
    }

    /* Fix autocomplete z-index */
    #autoComplete_list_1 {
        z-index: 9999 !important;
        position: absolute !important;
        min-width: 500px !important;
        max-width: 700px !important;
        background: var(--bg-white) !important;
        border: 2px solid var(--primary-blue) !important;
        border-radius: 6px !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
        margin-top: 4px !important;
    }

    #autoComplete_list_1 li {
        padding: 0.75rem 1rem !important;
        border-bottom: 1px solid #f3f4f6 !important;
        cursor: pointer !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--text-primary) !important;
    }

    #autoComplete_list_1 li:hover,
    #autoComplete_list_1 li[aria-selected="true"] {
        background: #eff6ff !important;
        color: var(--primary-blue) !important;
    }

    #autoComplete_list_1 mark {
        background: #dbeafe !important;
        font-weight: 700 !important;
        color: var(--primary-blue) !important;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    let selectedProduct = null;

    // Initialize immediately - works for both initial load AND SPA navigation
    (function initQuotationCreatePage() {
        const gridBody = document.getElementById('quotation_grid_body');
        if (!gridBody) {
            console.log('Quotation Create script skipped - not on quotation create page');
            return;
        }
        console.log('Quotation Create page: Initializing...');

        generateQuotationNumber();
        loadSuppliers();
        initProductSearch();
        setDefaultValidUntil();

        // Keyboard shortcuts - only add once
        if (!window._quotationCreateKeydownAdded) {
            document.addEventListener('keydown', (e) => {
                // Only handle if still on this page
                if (!document.getElementById('quotation_grid_body')) return;

                if (e.key === 'F6') {
                    e.preventDefault();
                    document.getElementById('q_item_name')?.focus();
                }
                if (e.key === 'F2') {
                    e.preventDefault();
                    saveQuotation();
                }
                if (e.key === 'F5') {
                    e.preventDefault();
                    location.reload();
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    window.location.href = '/purchase-quotations';
                }
            });
            window._quotationCreateKeydownAdded = true;
        }

        // Tab navigation improvements
        const itemNameInput = document.getElementById('q_item_name');
        const mrpInput = document.getElementById('q_mrp');
        const addBtn = document.getElementById('btn_add_quotation');

        if (itemNameInput) {
            itemNameInput.addEventListener('keydown', handleItemInputKeydown);
        }
        if (mrpInput) {
            mrpInput.addEventListener('keydown', handleMrpKeydown);
        }
        if (addBtn) {
            addBtn.addEventListener('keydown', handleAddButtonKeydown);
        }

        function handleItemInputKeydown(e) {
            // If Tab pressed and no item selected, prevent default and stay on item input
            if (e.key === 'Tab' && !document.getElementById('q_item_id').value) {
                e.preventDefault();
                modalNotify.warning('Please select an item first');
                return;
            }
        }

        function handleMrpKeydown(e) {
            // If Enter or Tab on MRP, move to Add button
            if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                document.getElementById('btn_add_quotation')?.focus();
            }
        }

        function handleAddButtonKeydown(e) {
            // If Enter or Tab on Add button, add item and return to item input
            if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                addQuotationItem();
            }
        }
    })();

    function generateQuotationNumber() {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        const quotationNo = `QT-${year}${month}${day}-${random}`;
        document.getElementById('quotation_no').value = quotationNo;
    }

    function setDefaultValidUntil() {
        const today = new Date();
        const nextMonth = new Date(today);
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        const validUntil = nextMonth.toISOString().split('T')[0];
        document.getElementById('valid_until').value = validUntil;
    }

    async function loadSuppliers() {
        try {
            const res = await fetch('/api/v1/inventory/suppliers');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('supplier_id');
                // Filter out Melina suppliers (they are for direct product viewing, not quotations)
                const regularSuppliers = data.data.suppliers.filter(s => {
                    const isMelina = s.gstin === 'MELINA-PRIM' ||
                        s.gstin === 'REMOTE_SOURCE' ||
                        s.name.toLowerCase().includes('melina');
                    return !isMelina;
                });

                regularSuppliers.forEach(s => {
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

    function initProductSearch() {
        const input = document.getElementById('q_item_name');
        const autoCompleteJS = new autoComplete({
            selector: "#q_item_name",
            placeHolder: "Search Products (F6)...",
            threshold: 2,
            debounce: 300,
            data: {
                src: async (query) => {
                    try {
                        const source = await fetch(`/api/v1/inventory/products?search=${query}`);
                        const data = await source.json();
                        return data.data.products || [];
                    } catch (error) {
                        console.error('Product search error:', error);
                        return [];
                    }
                },
                keys: ["name", "sku"],
                cache: false
            },
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
                    item.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 700; font-size: 12px;">${data.value.name}</div>
                                <div style="font-size: 10px; color: #6b7280;">SKU: ${data.value.sku || 'N/A'}</div>
                            </div>
                            <div style="font-size: 10px; color: #059669; background: #d1fae5; padding: 0.15rem 0.4rem; border-radius: 3px;">
                                Tax: ${data.value.tax_percent || 0}%
                            </div>
                        </div>
                    `;
                }
            },
            events: {
                input: {
                    selection: (event) => {
                        const selection = event.detail.selection.value;
                        input.value = selection.name;
                        document.getElementById('q_item_id').value = selection.product_id;
                        selectedProduct = selection;

                        // Auto-populate tax from product master
                        document.getElementById('q_tax_percent').value = selection.tax_percent || 0;

                        // Focus on quantity field and select it for easy editing
                        setTimeout(() => {
                            document.getElementById('q_qty').focus();
                            document.getElementById('q_qty').select();
                        }, 100);
                    }
                }
            }
        });
    }

    function addQuotationItem() {
        const name = document.getElementById('q_item_name').value;
        const itemId = document.getElementById('q_item_id').value;
        if (!name || !itemId) {
            modalNotify.warning('Please select a product from search');
            document.getElementById('q_item_name').focus();
            return;
        }

        const qty = parseInt(document.getElementById('q_qty').value || 1);
        if (qty <= 0) {
            modalNotify.warning('Quantity must be greater than 0');
            document.getElementById('q_qty').focus();
            return;
        }

        const unitPrice = parseFloat(document.getElementById('q_unit_price').value || 0);
        if (unitPrice <= 0) {
            modalNotify.warning('Please enter a valid unit price');
            document.getElementById('q_unit_price').focus();
            return;
        }

        const taxPercent = parseFloat(document.getElementById('q_tax_percent').value || 0);
        const mrp = parseFloat(document.getElementById('q_mrp').value || 0);

        document.getElementById('empty_grid_msg').style.display = 'none';
        const body = document.getElementById('quotation_grid_body');

        // Check if product already exists in grid
        const existingRow = Array.from(body.querySelectorAll('tr')).find(row => row.dataset.itemId === itemId);

        if (existingRow) {
            // Product exists - update quantity
            const qtyCell = existingRow.querySelector('[data-qty]');
            const currentQty = parseInt(qtyCell.dataset.qty);
            const newQty = currentQty + qty;
            qtyCell.dataset.qty = newQty;
            qtyCell.innerText = newQty;

            // Recalculate amount for this row
            const priceCell = existingRow.querySelector('[data-price]');
            const price = parseFloat(priceCell.dataset.price);
            const taxCell = existingRow.querySelector('[data-tax]');
            const tax = parseFloat(taxCell.dataset.tax);

            const subtotal = newQty * price;
            const taxAmount = subtotal * (tax / 100);
            const total = subtotal + taxAmount;

            const totalCell = existingRow.querySelector('[data-total]');
            totalCell.dataset.total = total;
            totalCell.innerText = `₹${total.toFixed(2)}`;

            // Show custom success modal
            showQuantityUpdateModal(name, currentQty, qty, newQty);
        } else {
            // New product - add new row
            const sno = body.children.length + 1;
            const row = document.createElement('tr');
            row.dataset.itemId = itemId;

            const subtotal = qty * unitPrice;
            const taxAmount = subtotal * (taxPercent / 100);
            const total = subtotal + taxAmount;

            row.innerHTML = `
                <td class="text-center">${sno}</td>
                <td class="fw-bold">${name}</td>
                <td class="text-center">${selectedProduct?.sku || '-'}</td>
                <td class="text-center fw-bold" data-qty="${qty}">${qty}</td>
                <td class="text-end" data-price="${unitPrice}">₹${unitPrice.toFixed(2)}</td>
                <td class="text-center" data-tax="${taxPercent}">${taxPercent}%</td>
                <td class="text-end">₹${mrp.toFixed(2)}</td>
                <td class="text-end fw-bold" data-total="${total}">₹${total.toFixed(2)}</td>
                <td class="text-center">
                    <i class="ri-close-circle-fill text-danger" onclick="this.closest('tr').remove(); calculateTotals();"></i>
                </td>
            `;
            body.appendChild(row);
        }

        // Reset inputs
        document.getElementById('q_item_name').value = '';
        document.getElementById('q_item_id').value = '';
        document.getElementById('q_qty').value = '1';
        document.getElementById('q_unit_price').value = '';
        document.getElementById('q_tax_percent').value = '';
        document.getElementById('q_mrp').value = '';
        selectedProduct = null;

        // Return focus to item input for next entry
        setTimeout(() => {
            document.getElementById('q_item_name').focus();
        }, 100);

        calculateTotals();
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

    function calculateTotals() {
        const rows = document.getElementById('quotation_grid_body').querySelectorAll('tr');
        let totalItems = rows.length;
        let totalQty = 0;
        let subTotal = 0;
        let totalTax = 0;

        rows.forEach(row => {
            const qty = parseInt(row.querySelector('[data-qty]').dataset.qty);
            const price = parseFloat(row.querySelector('[data-price]').dataset.price);
            const tax = parseFloat(row.querySelector('[data-tax]').dataset.tax);

            totalQty += qty;
            const itemSubtotal = qty * price;
            subTotal += itemSubtotal;
            totalTax += itemSubtotal * (tax / 100);
        });

        const grandTotal = subTotal + totalTax;

        document.getElementById('total_items').innerText = totalItems;
        document.getElementById('total_qty').innerText = totalQty;
        document.getElementById('sub_total').innerText = `₹${subTotal.toFixed(2)}`;
        document.getElementById('total_tax').innerText = `₹${totalTax.toFixed(2)}`;
        document.getElementById('grand_total').innerText = `₹${grandTotal.toFixed(2)}`;
    }

    async function saveQuotation() {
        const rows = document.getElementById('quotation_grid_body').querySelectorAll('tr');
        if (rows.length === 0) {
            modalNotify.warning('Please add items to save');
            return;
        }

        const supplierId = document.getElementById('supplier_id').value;
        if (!supplierId) {
            modalNotify.warning('Please select a supplier');
            return;
        }

        const quotationDate = document.getElementById('quotation_date').value;
        const validUntil = document.getElementById('valid_until').value;

        if (new Date(validUntil) <= new Date(quotationDate)) {
            modalNotify.warning('Valid until date must be after quotation date');
            return;
        }

        const items = Array.from(rows).map(row => ({
            product_id: parseInt(row.dataset.itemId),
            quantity: parseInt(row.querySelector('[data-qty]').dataset.qty),
            unit_price: parseFloat(row.querySelector('[data-price]').dataset.price),
            tax_percent: parseFloat(row.querySelector('[data-tax]').dataset.tax),
            mrp: parseFloat(row.querySelector('td:nth-child(7)').innerText.replace('₹', ''))
        }));

        const payload = {
            supplier_id: parseInt(supplierId),
            quotation_no: document.getElementById('quotation_no').value,
            quotation_date: quotationDate,
            valid_until: validUntil,
            supplier_reference: document.getElementById('supplier_reference').value || null,
            remarks: document.getElementById('remarks').value || null,
            items: items
        };

        try {
            const res = await fetch('/api/v1/quotations', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                modalNotify.success('Quotation saved successfully!', 'Success');
                setTimeout(() => window.location.href = '/purchase-quotations', 1500);
            } else {
                modalNotify.error(data.message || 'Failed to save quotation', 'Error');
            }
        } catch (e) {
            modalNotify.error('Network error: ' + e.message, 'Connection Error');
        }
    }
</script>
<script src="<?= asset('libs/@tarekraafat/autocomplete.js/autoComplete.min.js') ?>"></script>
<link rel="stylesheet" href="<?= asset('libs/@tarekraafat/autocomplete.js/css/autoComplete.02.css') ?>">
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>