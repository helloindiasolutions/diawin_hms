<?php
$type = $_GET['type'] ?? 'pharmacy';
$isEstimate = ($type === 'estimate');
$pageTitle = $isEstimate ? "ESTIMATION / DRAFT" : "SALES / POINT OF SALES [POS]";
$fullScreenMode = true; // Enable full-screen view for the billing system
ob_start();
?>
<script>
    const IS_ESTIMATE = <?= json_encode($isEstimate) ?>;
    const INVOICE_TYPE = <?= json_encode($type) ?>;
</script>

<div class="pos-container">
    <!-- Header -->
    <header class="pos-header">
        <div class="d-flex align-items-center justify-content-between px-3 h-100">
            <div class="d-flex align-items-center">
                <h1 class="pos-title mb-0">
                    <?= $isEstimate ? 'DIAWIN ESTIMATION / COST ESTIMATE' : 'DIAWIN SALES / POINT OF SALES [POS]' ?>
                </h1>
                <?php if ($isEstimate): ?>
                    <span class="badge bg-warning text-dark ms-2">DRAFT MODE</span>
                <?php endif; ?>
            </div>
            <div class="header-shortcuts text-white fs-12 d-flex align-items-center gap-3">
                <span>FY [<?= date('y') ?>-<?= date('y', strtotime('+1 year')) ?>]</span>
                <a href="/visits" class="btn btn-xs text-white btn-outline-light border-0 py-0 px-2 fs-10 fw-bold"
                    style="background: rgba(255,255,255,0.1)">
                    <i class="ri-arrow-left-line align-middle"></i> EXIT TO LIST
                </a>
            </div>
        </div>
    </header>

    <div class="pos-body d-flex">
        <!-- Main Form Area -->
        <div class="pos-main flex-fill p-2">
            <!-- Top Form -->
            <div class="pos-card mb-2 p-2">
                <div class="row g-2">
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-erp">Bill No #</span>
                            <input type="text" class="form-control" id="bill_no" value="BN-<?= rand(1000, 9999) ?>"
                                readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-erp">Bill Date</span>
                            <input type="date" class="form-control" id="bill_date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-erp">Customer Name</span>
                            <div class="flex-grow-1 position-relative search-container">
                                <input type="text" class="form-control cursor-pointer" id="customer_search"
                                    placeholder="Click or Press Ctrl+D to Search Patient..." autocomplete="off" readonly
                                    onclick="openPatientLookupModal()" style="background: #fff;">
                                <input type="hidden" id="selected_customer_id">
                            </div>
                            <span class="input-group-text bg-white text-danger fs-11 fw-bold border-start-0"
                                id="customer_balance_info">
                                Cur Bal: 0.00 Cr, Cr Limit: 0
                            </span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-erp">Way Bill/Ref</span>
                            <input type="text" class="form-control" id="ref_no" value="REF-<?= rand(100, 999) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-erp">Invoice Type</span>
                            <select class="form-select" id="gst_type">
                                <option value="GST" selected>GST</option>
                                <option value="IGST">IGST</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-erp">Payment Type</span>
                            <select class="form-select" id="pay_mode">
                                <option value="Cash" selected>Cash</option>
                                <option value="Credit">Credit</option>
                                <option value="UPI">UPI</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-erp">Sales Man</span>
                            <input type="text" class="form-control" value="ADMIN" readonly>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <button class="btn btn-erp-sm btn-primary me-2">Add More Info</button>
                        <select class="form-select form-select-sm" style="width: 120px;" id="price_list_type">
                            <option value="retail" selected>Retails Price</option>
                            <option value="wholesale">Whole Sale</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Item Input Strip -->
            <div class="pos-card mb-2 p-1 bg-light border-primary">
                <div class="row g-1 align-items-center">
                    <div class="col-auto"><span class="fs-11 fw-bold text-dark px-1">Item Entry</span></div>
                    <div class="col search-container">
                        <input type="text" class="form-control form-control-sm border-primary" id="pos_item_search"
                            placeholder="Enter barcode or item name... [Ctrl+I]">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control form-control-sm text-center" placeholder="Batch"
                            id="pos_batch" readonly tabindex="-1">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control form-control-sm text-center" placeholder="Pack"
                            id="pos_pack" readonly tabindex="-1">
                    </div>
                    <div class="col-md-1">
                        <input type="number" class="form-control form-control-sm text-center" value="1" id="pos_qty"
                            min="1" readonly tabindex="-1">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control form-control-sm text-end" placeholder="Price"
                            id="pos_price" readonly tabindex="-1">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control form-control-sm text-center" placeholder="Disc%"
                            id="pos_disc" value="0" readonly tabindex="-1">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control form-control-sm text-end bg-navy text-white fw-bold"
                            value="0.00" readonly id="pos_line_total" tabindex="-1">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-erp-sm btn-navy px-2" onclick="processEntry()"
                            id="btn_add_entry">ADD</button>
                    </div>
                </div>
            </div>

            <!-- Main Items Grid -->
            <div class="pos-grid-container mb-2">
                <table class="pos-table w-100">
                    <thead>
                        <tr>
                            <th width="30">Sno</th>
                            <th width="100">Barcode</th>
                            <th>Product Name</th>
                            <th width="80">HSN</th>
                            <th width="60">Size</th>
                            <th width="80">Batch</th>
                            <th width="60">Qty</th>
                            <th width="60">Exp</th>
                            <th width="80">Price</th>
                            <th width="70">Disc%</th>
                            <th width="60">Tax%</th>
                            <th width="100">Total</th>
                            <th width="40"></th>
                        </tr>
                    </thead>
                    <tbody id="pos_grid_body">
                        <!-- Rows rendered here -->
                    </tbody>
                </table>
                <div id="empty_grid_msg" class="text-center py-5 text-muted">
                    <i class="ri-shopping-cart-line fs-40 d-block mb-2"></i>
                    <p class="fs-12 fw-bold">No items added to the bill yet. Use CTRL+I to search products.</p>
                </div>
            </div>

            <!-- Bottom Tabs & Secondary Info -->
            <div class="pos-tabs-bottom">
                <div class="row g-2 h-100">
                    <div class="col-md-7 h-100">
                        <div class="pos-tabs h-100 d-flex flex-column">
                            <ul class="nav nav-tabs fs-11" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_gst">GST
                                        Details</a></li>
                            </ul>
                            <div class="tab-content border-erp border-top-0 p-2 bg-white flex-fill overflow-auto">
                                <div class="tab-pane fade show active" id="tab_gst">
                                    <div id="gst_summary_dynamic" class="p-2">
                                        <!-- Dynamic GST split Table -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 h-100">
                        <div
                            class="totals-panel p-2 bg-light border-erp h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fs-14 fw-bold text-muted">Total Billed Qty :</span>
                                    <span class="fs-16 fw-bold text-dark" id="total_qty_display">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 border-bottom pb-1">
                                    <span class="fs-14 fw-bold text-muted">Sub Total :</span>
                                    <span class="fs-16 fw-bold text-dark" id="sub_total_display">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 mt-1">
                                    <span class="fs-14 fw-bold text-muted">Trade Disc (%) :</span>
                                    <div class="d-flex" style="width: 150px;">
                                        <input type="number" class="form-control form-control-sm text-center me-1"
                                            value="0" id="trade_disc_pct" oninput="calculateGlobals()">
                                        <input type="number" class="form-control form-control-sm text-end" value="0.00"
                                            id="trade_disc_amt" readonly>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-1 text-primary">
                                    <span class="fs-14 fw-bold">GST :</span>
                                    <span class="fs-16 fw-bold" id="total_tax_display">0.00</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-end mt-2 border-top pt-2">
                                <div>
                                    <h4 class="mb-0 text-navy fw-extrabold uppercase fs-14">Net Payable</h4>
                                    <div class="fs-10 text-muted opacity-75">Incl. GST & Roundoff</div>
                                </div>
                                <h1 class="mb-0 text-magenta fw-extrabold" id="grand_total_display"
                                    style="font-size: 2.8rem;">0.00</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Shortcuts -->
        <aside class="pos-sidebar bg-white p-2 border-start">
            <div class="sidebar-section mb-3">
                <div class="sidebar-title bg-dark text-white p-1 mb-2 text-center">SHORTCUTS</div>
                <div class="d-grid gap-1">
                    <button class="btn btn-erp btn-outline-primary text-start"
                        onclick="window.location.href='/accounts/cashbook'"><span class="fw-bold me-2">F4</span> CASH
                        BOOK</button>
                    <button class="btn btn-erp btn-outline-primary text-start"
                        onclick="window.location.href='/accounts'"><span class="fw-bold me-2">ALT+A</span>
                        ACCOUNTS</button>
                    <button class="btn btn-erp btn-outline-primary text-start"
                        onclick="window.location.href='/reports/daily-sales'"><span class="fw-bold me-2">ALT+D</span>
                        DAILY SALES</button>
                    <button class="btn btn-erp btn-outline-primary text-start" onclick="openLastBillsModal()"><span
                            class="fw-bold me-2">F4</span> LAST
                        BILLS</button>
                </div>
            </div>

            <div class="sidebar-section mb-3">
                <div class="sidebar-title bg-navy text-white p-1 mb-2 text-center">ACTIONS</div>
                <div class="d-grid gap-1">
                    <button class="btn btn-erp btn-outline-primary text-start" onclick="savePOS('thermal')"><span
                            class="fw-bold me-2">F7</span> THERMAL BILL</button>
                    <button class="btn btn-erp btn-outline-primary text-start" onclick="savePOS('print')"><span
                            class="fw-bold me-2">F8</span> PHARMACY BILL</button>
                </div>
            </div>

            <div class="mt-4 p-2 bg-light border rounded text-center">
                <div class="fs-10 text-muted mb-1">CLOCK</div>
                <h5 class="mb-0 fw-bold text-primary" id="erp_clock">--:--:--</h5>
                <div class="fs-10 text-muted mt-1 uppercase"><?= date('D, d M Y') ?></div>
            </div>
        </aside>
    </div>

    <!-- Bottom Shortcuts -->
    <footer class="pos-footer p-1 border-top d-flex align-items-center justify-content-between bg-light">
        <div class="d-flex">
            <button class="btn-footer bg-light" title="Go back to Dashboard"
                onclick="window.location.href='/dashboard'">
                <i class="ri-home-4-line me-1"></i> Dashboard (Ctrl+Q)
            </button>
            <button class="btn-footer bg-primary text-white mx-2" onclick="window.location.reload()">
                <i class="ri-refresh-line me-1"></i> New <?= $isEstimate ? 'Estimate' : 'Bill' ?> (F12)
            </button>
            <button class="btn-footer bg-red text-white" onclick="clearPOS()">
                <i class="ri-delete-bin-line me-1"></i> DELETE ALL
            </button>
        </div>
        <?php if ($isEstimate): ?>
            <button class="btn-footer bg-orange text-white px-4" onclick="savePOS('estimate')"><i
                    class="ri-file-list-3-line me-1"></i> Save Estimate</button>
        <?php else: ?>
            <button class="btn-footer bg-green text-white px-4 me-2" onclick="savePOS('print')"><i
                    class="ri-printer-line me-1"></i> Save & Print (F2)</button>
            <button class="btn-footer bg-navy text-white px-4" onclick="savePOS('only')"><i class="ri-save-line me-1"></i>
                Save (F8)</button>
        <?php endif; ?>
</div>
</footer>
</div>

<style>
    /* Full Screen ERP Layout (Commercial Density) */
    :root {
        --erp-primary: #10b981;
        /* Professional Emerald */
        --erp-primary-dark: #059669;
        /* Dark Emerald */
        --erp-header-bg: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
        /* Deep Forest Emerald */
        --erp-navy: #1a233a;
        --erp-bg: #f3f4f6;
        --erp-border: #d1d5db;
        --erp-accent: #0d9488;
    }

    /* Professional Search Loading (No Layout Shift) */
    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    .search-container {
        position: relative;
        flex-grow: 1;
    }

    .search-loading::after {
        content: "";
        position: absolute;
        right: 10px;
        top: 50%;
        margin-top: -7px;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(0, 0, 0, 0.1);
        border-top-color: var(--erp-primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        z-index: 5;
        pointer-events: none;
    }

    .bg-orange {
        background-color: #f97316 !important;
        border: 1px solid #c2410c;
    }

    body {
        overflow: hidden;
        background: #e5e7eb;
    }

    .pos-container {
        height: 100vh;
        background: #fff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        flex-direction: column;
    }

    .pos-header {
        height: 32px;
        background: var(--erp-header-bg);
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .pos-title {
        font-size: 13px;
        font-weight: 800;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pos-body {
        flex: 1;
        overflow: hidden;
        height: calc(100vh - 72px);
    }

    .pos-sidebar {
        width: 180px;
        flex-shrink: 0;
    }

    .pos-main {
        overflow-y: auto;
        background: var(--erp-bg);
        border-right: 1px solid var(--erp-border);
    }

    /* Condensed ERP Controls */
    .pos-card {
        background: #fff;
        border: 1px solid var(--erp-border);
        border-radius: 4px;
    }

    .input-group-text.label-erp {
        background: #f3f4f6;
        color: #374151;
        min-width: 95px;
        padding: 0.1rem 0.6rem;
        font-size: 10.5px;
        font-weight: 700;
        border: 1px solid var(--erp-border);
        text-transform: uppercase;
    }

    .form-control-sm,
    .form-select-sm {
        font-size: 11px;
        font-weight: 600;
        height: 26px;
        border-radius: 0;
        border: 1px solid var(--erp-border);
    }

    .form-control-sm:focus {
        border-color: var(--erp-primary);
        background-color: #fff;
        box-shadow: none;
    }

    .bg-light.border-primary {
        background: #ecfdf5 !important;
        border: 1px solid var(--erp-primary) !important;
    }

    .bg-navy {
        background: var(--erp-navy) !important;
    }

    .text-navy {
        color: var(--erp-navy) !important;
    }

    .text-magenta {
        color: #be185d;
    }

    .btn-primary {
        background: var(--erp-primary);
        color: #fff;
        border: 1px solid var(--erp-primary-dark);
        font-weight: 700;
    }

    .btn-primary:hover {
        background: var(--erp-primary-dark);
        color: #fff;
    }

    .btn-blue {
        background: #3b82f6;
        color: #fff;
        border: 1px solid #2563eb;
    }

    .text-primary {
        color: var(--erp-primary) !important;
    }

    .btn-green {
        background: #10b981;
        color: #fff;
        border: 1px solid #059669;
    }

    .btn-navy {
        background: var(--erp-navy);
        color: #fff;
        font-weight: 700;
        border: none;
    }

    .btn-erp-sm {
        font-size: 10px;
        padding: 4px 10px;
        font-weight: 800;
        border-radius: 3px;
    }

    /* High Density Grid - AUTO EXPANDING & SCROLLING ONLY HERE */
    .pos-grid-container {
        flex: 1;
        min-height: 0;
        background: #fff;
        border: 1px solid var(--erp-border);
        overflow-y: auto;
        position: relative;
    }

    .pos-table {
        border-collapse: collapse;
        width: 100%;
        table-layout: fixed;
    }

    .pos-table th {
        position: sticky;
        top: 0;
        background: #eaedf2;
        color: #1f2937;
        font-size: 10.5px;
        font-weight: 800;
        border: 1px solid var(--erp-border);
        padding: 5px;
        text-transform: uppercase;
        z-index: 10;
    }

    .pos-table td {
        border: 1px solid #e5e7eb;
        padding: 4px 6px;
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        transition: 0.1s;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pos-table tr:nth-child(even) {
        background: #f9fafb;
    }

    .pos-table tr:hover {
        background: #ecfdf5;
    }

    /* POS Footer */
    .pos-footer {
        height: 40px;
        background: #eaedf2;
        color: #1f2937;
        flex-shrink: 0;
    }

    .btn-footer {
        border: 1px solid #d1d5db;
        padding: 4px 15px;
        font-size: 11px;
        font-weight: 800;
        border-radius: 4px;
        cursor: pointer;
        transition: 0.2s;
        text-transform: uppercase;
    }

    .btn-footer:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Dynamic Info */
    .totals-panel {
        border-left: 4px solid var(--erp-primary);
        border-radius: 4px;
        background: #fff !important;
    }

    .sidebar-title {
        font-size: 10px;
        font-weight: 800;
        border-radius: 2px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-erp {
        font-size: 10.5px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 3px;
        border: 1px solid var(--erp-border);
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .btn-outline-primary {
        border-color: var(--erp-primary);
        color: var(--erp-primary);
    }

    .btn-outline-primary:hover {
        background: var(--erp-primary);
        color: #fff;
    }

    .nav-tabs .nav-link {
        padding: 4px 15px;
        font-weight: 800;
        color: #6b7280;
        font-size: 10px;
        text-transform: uppercase;
        border-radius: 0;
    }

    .nav-tabs .nav-link.active {
        color: var(--erp-primary);
        border-top: 2px solid var(--erp-primary);
        border-bottom: 1px solid #fff;
    }

    /* Layout Adjustments to prevent full page scroll */
    .pos-main {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden !important;
        padding-bottom: 5px;
        /* Extra breathing room */
    }

    .pos-tabs-bottom {
        height: 210px;
        /* Increased from 180px to show price clearly */
        flex-shrink: 0;
        margin-top: 8px;
    }

    /* Autocomplete Styling Overrides */
    .autoComplete_wrapper {
        width: 100%;
    }

    .autoComplete_wrapper>input {
        width: 100% !important;
        height: 35px !important;
    }

    #autoComplete_list_1,
    #autoComplete_list_2,
    .customer-autocomplete-list {
        z-index: 9999 !important;
        background: #fff;
        border: 2px solid var(--erp-primary);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        border-radius: 4px;
        padding: 0;
        list-style: none;
        max-height: 350px;
        overflow-y: auto;
        margin-top: 2px;
    }

    .autoComplete_result,
    .customer-autocomplete-item {
        padding: 10px 14px;
        font-size: 12px;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #fff;
    }

    /* Selection Highlight using Attribute */
    #autoComplete_list_1 li[aria-selected="true"],
    #autoComplete_list_2 li[aria-selected="true"],
    #autoComplete_list_1 li:hover,
    #autoComplete_list_2 li:hover,
    .autoComplete_result[aria-selected="true"],
    .customer-autocomplete-item[aria-selected="true"] {
        background: var(--erp-primary) !important;
        color: #fff !important;
        font-weight: 700 !important;
        border-left: 4px solid #047857 !important;
        padding-left: 10px !important;
    }

    /* Ensure all nested text is white when selected */
    li[aria-selected="true"] *,
    li:hover * {
        color: #fff !important;
    }

    li[aria-selected="true"] .text-muted,
    li:hover .text-muted {
        color: #d1fae5 !important;
    }

    .autoComplete_result[aria-selected="true"] .text-muted,
    .customer-autocomplete-item[aria-selected="true"] .text-muted,
    li[aria-selected="true"] .text-muted {
        color: #d1fae5 !important;
    }

    .autoComplete_result:hover .text-muted,
    .customer-autocomplete-item:hover .text-muted {
        color: #d1fae5 !important;
    }

    .autoComplete_result mark,
    .customer-autocomplete-item mark {
        background: transparent;
        color: inherit;
        font-weight: 800;
        text-decoration: underline;
    }

    /* First item default highlight */
    .autoComplete_result:first-child,
    .customer-autocomplete-item:first-child {
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }

    .autoComplete_result:last-child,
    .customer-autocomplete-item:last-child {
        border-bottom: none;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    /* ===== PATIENT LOOKUP MODAL STYLES ===== */
    #patientLookupModal .modal-content {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    #patientLookupModal .modal-header {
        border-bottom: none;
        padding: 0.875rem 1rem;
    }

    #patientLookupModal .modal-title {
        letter-spacing: 0.3px;
        color: white;
        font-size: 13px;
    }

    /* Search Input */
    #patientLookupModal #lookup_patient_search {
        font-size: 15px;
        font-weight: 500;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border-radius: 8px;
        border: 1px solid #d1d5db;
    }

    #patientLookupModal #lookup_patient_search:focus {
        border-color: var(--erp-primary);
        outline: none;
    }

    #patientLookupModal #lookup_patient_search::placeholder {
        color: #9ca3af;
        font-weight: 400;
    }

    /* Keyboard hints */
    #patientLookupModal kbd {
        background: #374151;
        color: #fff;
        font-size: 10px;
        padding: 2px 5px;
        border-radius: 3px;
        font-family: inherit;
        font-weight: 500;
    }

    /* Result Items */
    #patientLookupModal .lookup-result-item {
        transition: all 0.12s ease;
        border-left: 3px solid transparent;
        padding: 10px 14px !important;
        border-bottom: 1px solid #f3f4f6;
    }

    #patientLookupModal .lookup-result-item:hover {
        background: #f9fafb !important;
        border-left-color: #34d399;
    }

    #patientLookupModal .lookup-result-item.active,
    #patientLookupModal .lookup-result-item.bg-primary-transparent {
        background: #ecfdf5 !important;
        border-left-color: var(--erp-primary) !important;
    }

    #patientLookupModal .lookup-result-item .fs-13 {
        font-size: 13px !important;
        font-weight: 600;
        color: #1f2937;
    }

    #patientLookupModal .lookup-result-item .badge {
        font-size: 9px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
    }

    /* Avatars */
    #patientLookupModal .avatar-sm {
        width: 36px;
        height: 36px;
        min-width: 36px;
        background: #f3f4f6;
    }

    #patientLookupModal .avatar-lg {
        width: 48px;
        height: 48px;
        min-width: 48px;
        background: #d1fae5;
    }

    #patientLookupModal .avatar-lg i {
        color: #059669 !important;
        font-size: 20px;
    }

    /* Patient Details Header */
    #patientLookupModal #lookup_patient_name {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    #patientLookupModal #lookup_patient_mrn {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        background: #dbeafe;
        color: #1e40af;
    }

    /* Tabs - Clean & Visible */
    #patientLookupModal .nav-tabs {
        border-bottom: 1px solid #e5e7eb;
        background: #fafafa;
    }

    #patientLookupModal .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #6b7280;
        padding: 0.75rem 1rem;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: -1px;
        background: transparent;
        border-radius: 0;
    }

    #patientLookupModal .nav-tabs .nav-link.active {
        color: #059669;
        border-bottom-color: #059669;
        background: #fff;
    }

    #patientLookupModal .nav-tabs .nav-link:hover:not(.active) {
        color: #374151;
        background: #f3f4f6;
    }

    #patientLookupModal .nav-tabs .nav-link i {
        font-size: 13px;
    }

    /* Buttons - Clean, no shadows */
    #patientLookupModal .btn-primary {
        background: #059669;
        border: none;
        font-weight: 600;
        font-size: 12px;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
    }

    #patientLookupModal .btn-primary:hover {
        background: #047857;
    }

    #patientLookupModal .btn-outline-primary,
    #patientLookupModal .btn-outline-success {
        border-width: 1px;
        font-weight: 600;
        font-size: 11px;
        border-radius: 6px;
    }

    /* Color Utilities */
    #patientLookupModal .bg-primary-transparent {
        background: #ecfdf5 !important;
    }

    #patientLookupModal .bg-success-transparent {
        background: #d1fae5 !important;
    }

    #patientLookupModal .bg-warning-transparent {
        background: #fef3c7 !important;
    }

    #patientLookupModal .bg-info-transparent {
        background: #cffafe !important;
    }

    #patientLookupModal .text-primary {
        color: #059669 !important;
    }

    /* Font sizes */
    #patientLookupModal .fs-60 {
        font-size: 48px;
    }

    #patientLookupModal .fs-40 {
        font-size: 32px;
    }

    #patientLookupModal .fs-30 {
        font-size: 24px;
    }

    .cursor-pointer {
        cursor: pointer !important;
    }

    /* Walk-in Quick Create - Clean */
    #walkin_quick_create {
        border-top: 1px solid #e5e7eb !important;
        background: #f9fafb;
    }

    #walkin_quick_create .input-group {
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        border: none;
    }

    #walkin_quick_create .input-group-text {
        padding: 0.5rem 0.625rem;
        background: #fff;
        border: none !important;
    }

    #walkin_quick_create .input-group-text i {
        font-size: 14px;
    }

    #walkin_quick_create .form-control {
        padding: 0.5rem 0.4rem;
        font-size: 13px;
        border: none !important;
        box-shadow: none !important;
    }

    #walkin_quick_create .form-control:focus {
        background: #f0fdf4;
    }

    #walkin_quick_create .form-control::placeholder {
        color: #9ca3af;
        font-size: 12px;
    }

    #walkin_quick_create .btn-success {
        border-radius: 0 6px 6px 0 !important;
        padding: 0.5rem 0.875rem;
        background: #059669;
        border: none;
    }

    #walkin_quick_create .btn-success:hover {
        background: #047857;
    }

    /* Empty states */
    #patientLookupModal .opacity-25 {
        opacity: 0.15;
    }

    #patientLookupModal .opacity-50 {
        opacity: 0.3;
    }

    /* ===== LAST BILLS MODAL STYLES ===== */
    #lastBillsModal .modal-content {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    #lastBillsModal .bill-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background 0.15s;
    }

    #lastBillsModal .bill-item:hover {
        background: #f9fafb;
    }

    #lastBillsModal .bill-item.active {
        background: #ecfdf5;
        border-color: #10b981;
    }

    /* Editable Grid Inputs */
    .grid-edit-input {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        text-align: right;
        font-weight: 700;
        font-size: 11px;
        padding: 2px 4px;
        color: var(--erp-navy);
        transition: all 0.2s;
    }

    .grid-edit-input:hover {
        border-color: #d1d5db;
        background: #fff;
    }

    .grid-edit-input:focus {
        border-color: var(--erp-primary);
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1);
    }

    .grid-edit-input::-webkit-outer-spin-button,
    .grid-edit-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    border-left: 3px solid #059669;
    }

    /* ===== PAYMENT METHOD MODAL STYLES ===== */
    #paymentMethodModal .modal-content {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    #paymentMethodModal .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }

    #paymentMethodModal .modal-header .modal-title {
        color: #374151;
    }

    .payment-method-card {
        flex: 1;
        padding: 20px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #fff;
        min-width: 100px;
    }

    .payment-method-card:hover {
        border-color: #d1d5db;
        background: #fafafa;
    }

    .payment-method-card.selected {
        border-color: #059669;
        background: #f0fdf4;
    }

    .payment-method-card img {
        width: 40px;
        height: 40px;
        object-fit: contain;
        margin-bottom: 10px;
        opacity: 0.85;
    }

    .payment-method-card.selected img {
        opacity: 1;
    }

    .payment-method-card .method-name {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .payment-method-card.selected .method-name {
        color: #059669;
    }

    .payment-method-card kbd {
        font-size: 9px;
        background: #e5e7eb;
        color: #6b7280;
        padding: 2px 5px;
        border-radius: 3px;
        margin-top: 6px;
        display: inline-block;
        font-weight: 500;
    }

    .print-type-hint {
        cursor: pointer;
        margin-top: 15px;
        padding: 15px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        transition: all 0.2s;
        text-align: center;
    }

    .print-type-hint:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .print-type-hint.active {
        background: #ecfdf5;
        border-color: #10b981;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }

    .print-type-hint.active-pharmacy {
        background: #eff6ff;
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .print-type-hint .hint-title {
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .print-type-hint.active .hint-title {
        color: #059669;
    }

    .print-type-hint.active-pharmacy .hint-title {
        color: #1d4ed8;
    }
</style>

<!-- Last Bills Modal (F4) -->
<div class="modal fade" id="lastBillsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 px-3" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);">
                <h6 class="modal-title text-white fs-12 fw-bold" id="lastBillsModalTitle">
                    <i class="ri-file-list-3-line me-1"></i> RECENT BILLS
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="lastBillsList">
                    <div class="text-center py-4 text-muted">
                        <i class="ri-file-list-line fs-40 opacity-25 mb-2 d-block"></i>
                        <p class="fs-12 mb-0">Loading bills...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light">
                <div class="d-flex gap-2 fs-10 text-muted">
                    <span><kbd>↑↓</kbd> Navigate</span>
                    <span><kbd>Enter</kbd> Load Bill</span>
                    <span><kbd>Esc</kbd> Close</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Modal -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content">
            <div class="modal-header py-2 px-3">
                <h6 class="modal-title fs-11 fw-bold">
                    <i class="ri-wallet-3-line me-1"></i> SELECT PAYMENT METHOD
                </h6>
            </div>
            <div class="modal-body py-3 px-3">
                <div class="d-flex justify-content-center gap-2" id="paymentMethodCards">
                    <div class="payment-method-card selected" data-method="cash" onclick="selectPaymentMethod('cash')">
                        <img src="/assets/images/cash.png" alt="Cash">
                        <span class="method-name d-block">Cash</span>
                        <kbd>1</kbd>
                    </div>
                    <div class="payment-method-card" data-method="card" onclick="selectPaymentMethod('card')">
                        <img src="/assets/images/card.png" alt="Card">
                        <span class="method-name d-block">Card</span>
                        <kbd>2</kbd>
                    </div>
                    <div class="payment-method-card" data-method="upi" onclick="selectPaymentMethod('upi')">
                        <img src="/assets/images/gpay.png" alt="UPI">
                        <span class="method-name d-block">UPI</span>
                        <kbd>3</kbd>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <div class="print-type-hint" id="thermal_hint" onclick="setPendingSaveMode('thermal')">
                            <div class="hint-title"><i class="ri-printer-line me-1"></i> THERMAL</div>
                            <span class="badge bg-success">F7</span>
                            <p class="fs-10 text-muted mb-0 mt-1">80mm Roll Print</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="print-type-hint" id="pharmacy_hint" onclick="setPendingSaveMode('print')">
                            <div class="hint-title"><i class="ri-file-list-3-line me-1"></i> PHARMACY</div>
                            <span class="badge bg-primary">F8</span>
                            <p class="fs-10 text-muted mb-0 mt-1">A4 Landscape Bill</p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3 fs-10 text-muted">
                    <div class="d-flex justify-content-center gap-3">
                        <span><kbd>←</kbd><kbd>→</kbd> Method</span>
                        <span><kbd>F7</kbd><kbd>F8</kbd> Print Type</span>
                        <span><kbd>Enter</kbd> Confirm</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fs-12 fw-bold text-uppercase"><i class="ri-user-add-line me-1"></i> New Customer
                </h6>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2">
                    <label class="form-label fs-11 fw-bold text-muted mb-1">Mobile Number</label>
                    <input type="text" class="form-control form-control-sm fw-bold" id="new_cust_mobile" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fs-11 fw-bold text-muted mb-1">Customer Name <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm fw-bold uppercase" id="new_cust_name"
                        placeholder="ENTER NAME">
                </div>
                <button type="button" class="btn btn-success btn-sm w-100 fw-bold" onclick="saveNewCustomer()">SAVE &
                    SELECT (ENTER)</button>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Patient Lookup Modal -->
<div class="modal fade" id="patientLookupModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="max-height: 90vh;">
            <div class="modal-header bg-gradient-primary text-white py-2 px-3"
                style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-search-2-line fs-18"></i>
                    <div>
                        <h6 class="modal-title mb-0 fs-13 fw-bold text-uppercase">Patient Lookup</h6>
                        <span class="fs-10 opacity-75">Search by Name, Mobile, or MRD ID</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0" style="height: 70vh;">
                    <!-- Left: Search Panel -->
                    <div class="col-md-5 border-end d-flex flex-column" style="background: #f8fafc;">
                        <!-- Search Input -->
                        <div class="p-3 border-bottom bg-white">
                            <div class="position-relative mb-3">
                                <i class="ri-search-line position-absolute text-muted"
                                    style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 18px;"></i>
                                <input type="text" class="form-control form-control-lg ps-5" id="lookup_patient_search"
                                    placeholder="Search patients..." autocomplete="off" autofocus
                                    style="font-size: 15px;">
                                <div class="position-absolute"
                                    style="right: 14px; top: 50%; transform: translateY(-50%); display: none;"
                                    id="lookup_loading_indicator">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <span class="fs-10 text-muted"><kbd>↑↓</kbd> Navigate</span>
                                <span class="fs-10 text-muted"><kbd>Enter</kbd> Select</span>
                                <span class="fs-10 text-muted"><kbd>Esc</kbd> Close</span>
                            </div>
                        </div>

                        <!-- Results List -->
                        <div class="flex-fill overflow-auto" id="lookup_results_container">
                            <div id="lookup_results_list" class="list-group list-group-flush">
                                <!-- Initial State -->
                                <div class="text-center py-5 text-muted" id="lookup_initial_state">
                                    <i class="ri-user-search-line fs-40 d-block mb-2 opacity-50"></i>
                                    <p class="fs-12 mb-1">Start typing to search patients</p>
                                    <p class="fs-10 text-muted">Search by name, mobile number, or MRD ID</p>
                                </div>
                                <!-- No Results State -->
                                <div class="text-center py-4 text-muted d-none" id="lookup_no_results">
                                    <i class="ri-user-unfollow-line fs-30 d-block mb-2 opacity-50"></i>
                                    <p class="fs-11 mb-0">No patients found</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Walk-in Patient Creation -->
                        <div class="p-3 border-top " id="walkin_quick_create">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 24px; height: 24px;">
                                        <i class="ri-add-line text-white fs-12"></i>
                                    </div>
                                    <span class="fw-bold fs-11 text-success">NEW WALK-IN</span>
                                </div>
                                <span class="fs-9 text-muted"><kbd class="bg-secondary px-1">Enter</kbd> to
                                    create</span>
                            </div>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-0 text-success">
                                    <i class="ri-phone-line"></i>
                                </span>
                                <input type="text" class="form-control border-0 border-end" id="walkin_mobile"
                                    placeholder="Mobile" maxlength="10"
                                    style="max-width: 110px; border-right: 1px solid #e5e7eb !important;"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <span class="input-group-text bg-white border-0 text-muted">
                                    <i class="ri-user-line"></i>
                                </span>
                                <input type="text" class="form-control border-0 fw-semibold" id="walkin_name"
                                    placeholder="Patient Name"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault();quickCreateWalkin();}">
                                <button class="btn btn-success px-3 border-0" onclick="quickCreateWalkin()"
                                    type="button">
                                    <i class="ri-check-line fs-14"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Patient Details Panel -->
                    <div class="col-md-7 d-flex flex-column bg-white">
                        <!-- Patient Info Header -->
                        <div id="lookup_patient_details" class="flex-fill overflow-auto">
                            <!-- Empty State -->
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted"
                                id="lookup_details_empty">
                                <i class="ri-file-user-line fs-60 opacity-25 mb-3"></i>
                                <p class="fs-13 mb-1">Select a patient to view details</p>
                                <p class="fs-11 text-muted">Prescription and billing history will appear here</p>
                            </div>

                            <!-- Patient Details Content (Hidden by default) -->
                            <div id="lookup_details_content" class="d-none h-100 d-flex flex-column">
                                <!-- Patient Header Card -->
                                <div class="p-3 border-bottom bg-light">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="avatar-lg bg-primary-transparent rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="ri-user-3-line text-primary fs-24"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <h5 class="mb-0 fw-bold" id="lookup_patient_name">---</h5>
                                            <div class="d-flex gap-3 mt-1 fs-11">
                                                <span class="text-muted"><i class="ri-phone-line me-1"></i><span
                                                        id="lookup_patient_mobile">---</span></span>
                                                <span class="badge bg-primary-transparent text-primary"
                                                    id="lookup_patient_mrn">---</span>
                                                <span class="text-muted" id="lookup_patient_age_gender">---</span>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary px-4" onclick="selectPatientFromLookup()"
                                            id="btn_select_patient">
                                            <i class="ri-check-line me-1"></i> SELECT
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabs for History -->
                                <ul class="nav nav-tabs nav-justified fs-11 fw-bold" id="lookup_history_tabs"
                                    role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab-prescription" data-bs-toggle="tab"
                                            data-bs-target="#prescription_panel" type="button">
                                            <i class="ri-capsule-line me-1"></i> Last Prescription
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-billing" data-bs-toggle="tab"
                                            data-bs-target="#billing_panel" type="button">
                                            <i class="ri-bill-line me-1"></i> Recent Bills
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-visits" data-bs-toggle="tab"
                                            data-bs-target="#visits_panel" type="button">
                                            <i class="ri-calendar-check-line me-1"></i> Visit History
                                        </button>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content flex-fill overflow-auto" id="lookup_history_content">
                                    <!-- Prescription Tab -->
                                    <div class="tab-pane fade show active p-3" id="prescription_panel" role="tabpanel">
                                        <div id="prescription_content">
                                            <div class="text-center py-4 text-muted">
                                                <i class="ri-file-list-3-line fs-30 opacity-50"></i>
                                                <p class="fs-11 mb-0 mt-2">Loading prescriptions...</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Billing Tab -->
                                    <div class="tab-pane fade p-3" id="billing_panel" role="tabpanel">
                                        <div id="billing_content">
                                            <div class="text-center py-4 text-muted">
                                                <i class="ri-file-list-3-line fs-30 opacity-50"></i>
                                                <p class="fs-11 mb-0 mt-2">Loading billing history...</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Visits Tab -->
                                    <div class="tab-pane fade p-3" id="visits_panel" role="tabpanel">
                                        <div id="visits_content">
                                            <div class="text-center py-4 text-muted">
                                                <i class="ri-file-list-3-line fs-30 opacity-50"></i>
                                                <p class="fs-11 mb-0 mt-2">Loading visit history...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="p-2 border-top bg-light d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm flex-fill"
                                        onclick="loadLastBillToCart()" id="btn_load_last_bill">
                                        <i class="ri-refresh-line me-1"></i> Load Last Bill Items
                                    </button>
                                    <button class="btn btn-outline-success btn-sm flex-fill"
                                        onclick="loadPrescriptionToCart()" id="btn_load_prescription">
                                        <i class="ri-capsule-line me-1"></i> Load Prescription Items
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    let posItems = [];
    let currentSelectedProduct = null;
    let currentSelectedCustomer = null;

    document.addEventListener('DOMContentLoaded', () => {
        // Start Clock
        setInterval(updateClock, 1000);
        updateClock();

        // Init Dynamic Controls
        initAutocompleteCustomer();
        initAutocompleteProduct();
        fetchPOSInitData();

        // AUTO-OPEN Patient Lookup Modal on Page Load
        setTimeout(() => {
            openPatientLookupModal();
        }, 300);

        // Listen for Global Shortcuts
        document.addEventListener('keydown', (e) => {
            // Function Keys
            if (e.key === 'F12') { e.preventDefault(); location.reload(); }
            if (e.key === 'F10') { e.preventDefault(); location.href = '/inventory/purchase-orders'; }
            if (e.key === 'F2') { e.preventDefault(); savePOS('print'); }
            if (e.key === 'F8') { e.preventDefault(); savePOS('only'); }
            if (e.key === 'F5') { e.preventDefault(); location.reload(); }

            // Ctrl Shortcuts
            if (e.ctrlKey && e.key === 'i') { e.preventDefault(); document.getElementById('pos_item_search').focus(); }
            if (e.ctrlKey && e.key === 'd') { e.preventDefault(); document.getElementById('customer_search').focus(); }
            if (e.ctrlKey && e.key === 'q') { e.preventDefault(); location.href = '/dashboard'; }



            // F4 - Last Bills
            if (e.key === 'F4') { e.preventDefault(); openLastBillsModal(); }



            // Enter to process item (but NOT when in item search dropdown)
            if (e.key === 'Enter') {
                // Check if autocomplete dropdown is open
                const isAutocompleteOpen = document.querySelector('.autoComplete_wrapper[aria-expanded="true"]');
                const isItemDropdownOpen = document.getElementById('autoComplete_list_2')?.children.length > 0;

                // Don't process entry if in item search (let the dropdown handle selection)
                if (document.activeElement.id === 'pos_item_search' && isItemDropdownOpen) {
                    return; // Let the item dropdown handle Enter key
                }

                if (!isAutocompleteOpen && document.activeElement.id !== 'customer_search' && document.activeElement.id !== 'bill_notes') {
                    if (currentSelectedProduct) processEntry();
                }
            }
        });

        // Price/Qty change logic for line item entry
        ['pos_qty', 'pos_price', 'pos_disc'].forEach(id => {
            document.getElementById(id).addEventListener('input', updateLineTotal);
        });

        // Shortcut: TAB on Qty field adds the product immediately
        document.getElementById('pos_qty').addEventListener('keydown', (e) => {
            if (e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                processEntry();
            }
        });
    });

    function updateClock() {
        const now = new Date();
        document.getElementById('erp_clock').textContent = now.toLocaleTimeString('en-GB');
    }

    async function fetchPOSInitData() {
        try {
            const res = await fetch('/api/v1/billing/init');
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('bill_no').value = data.data.bill_no;
                document.getElementById('ref_no').value = data.data.ref_no;
                document.getElementById('bill_date').value = data.data.date;
            }

            // PRELOAD: Fetch patients and products once for instant "Live Search"
            preloadSearchData();
        } catch (e) {
            console.error('Failed to fetch init data', e);
        }
    }

    let preloadedPatients = [];
    let preloadedProducts = [];

    async function preloadSearchData() {
        try {
            // Fetch first page of patients (first 100) and all stock items
            const [pRes, iRes] = await Promise.all([
                fetch('/api/v1/patients/search?q=a'), // Initial fetch
                fetch('/api/v1/billing/items?search=')
            ]);
            const pData = await pRes.json();
            const iData = await iRes.json();

            preloadedPatients = pData.data.patients || [];
            preloadedProducts = iData.data.items || [];
            console.log('POS Data Preloaded:', { patients: preloadedPatients.length, items: preloadedProducts.length });
        } catch (e) {
            console.warn('Preload failed, falling back to live search', e);
        }
    }

    async function initAutocompleteCustomer() {
        let lastCustomerQuery = "";
        const searchCache = new Map();
        const CACHE_DURATION = 300000; // 5 minutes cache

        // Faster debounce for "instant" feel
        const debounceTime = 200; // 200ms debounce for ultra-fast response

        const autoCompleteJS = new autoComplete({
            selector: "#customer_search",
            placeHolder: "Search Patient...",
            threshold: 2, // Require at least 2 characters before searching
            debounce: debounceTime, // Longer debounce for server to prevent request spam
            data: {
                src: async (query) => {
                    // 1. Check local preloaded data FIRST for instant "0ms" results
                    const localQuery = query.toLowerCase().trim();
                    const localResults = preloadedPatients.filter(p =>
                        p.full_name.toLowerCase().includes(localQuery) ||
                        p.mrn.toLowerCase().includes(localQuery) ||
                        (p.mobile && p.mobile.includes(localQuery))
                    );

                    if (localResults.length > 0) {
                        console.log('Instant Local Result:', localResults.length);
                        return localResults;
                    }

                    // 2. Fallback to API if not found in first 100/preload
                    const cacheKey = localQuery;
                    const cached = searchCache.get(cacheKey);
                    if (cached && (Date.now() - cached.timestamp < CACHE_DURATION)) {
                        return cached.data;
                    }

                    lastCustomerQuery = query;
                    const startTime = Date.now();

                    try {
                        const res = await fetch(`/api/v1/patients/search?q=${encodeURIComponent(query)}`);
                        const data = await res.json();
                        const results = data.data.patients || [];

                        // Update preload if we found new data
                        if (results.length > 0) {
                            preloadedPatients = [...new Set([...preloadedPatients, ...results])];
                        }

                        return results;
                    } catch (error) {
                        return [];
                    }
                },
                keys: ["full_name", "mrn", "mobile"],
                cache: false // We handle caching manually
            },
            resultsList: {
                navigate: false,
                element: (list, data) => {
                    list.id = "autoComplete_list_1";
                    if (!data.results.length) {
                        const createItem = document.createElement("li");
                        createItem.setAttribute("class", "autoComplete_result create-new-customer-item");
                        createItem.setAttribute("role", "option");
                        createItem.setAttribute("data-action", "create-new");
                        createItem.innerHTML = `<span class="text-success fw-bold"><i class="ri-add-circle-fill"></i> Create New Customer: "${data.query}"</span>`;
                        createItem.addEventListener('click', () => openNewCustomerModal(data.query));
                        list.append(createItem);
                    }
                },
                noResults: true,
                maxResults: 15,
                tabSelect: true,
                class: "customer-autocomplete-list"
            },
            resultItem: {
                highlight: true,
                className: "autoComplete_result customer-autocomplete-item",
                element: (item, data) => {
                    const query = document.getElementById('customer_search').value.trim();
                    const isNumeric = /^\d+$/.test(query);
                    const mobile = data.value.mobile || 'No mobile';
                    const name = data.value.full_name;

                    // USER REQUEST: Swap order/prominence based on search type
                    if (isNumeric) {
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div>
                                    <strong class="customer-mobile text-primary">${mobile}</strong>
                                    <span class="text-muted ms-2 fs-11">${name} (${data.value.mrn})</span>
                                </div>
                                <div class="badge bg-light text-dark fs-10">Matched Phone</div>
                            </div>
                        `;
                    } else {
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div>
                                    <strong class="customer-name">${name}</strong>
                                    <span class="text-muted ms-2 customer-mrn">(${data.value.mrn})</span>
                                </div>
                                <div class="text-muted fs-11 customer-mobile">${mobile}</div>
                            </div>
                        `;
                    }
                }
            },
            onSelection: (feedback) => {
                const item = feedback.selection.value;
                selectCustomer(item);
            }
        });

        function selectCustomer(item) {
            currentSelectedCustomer = item;
            document.getElementById('customer_search').value = `${item.full_name}`;
            document.getElementById('selected_customer_id').value = item.patient_id;
            document.getElementById('customer_balance_info').textContent = `Bal: 0.00 Cr | MRN: ${item.mrn}`;

            // Clear list
            const list = document.getElementById('autoComplete_list_1');
            if (list) list.innerHTML = '';

            // Focus next field
            setTimeout(() => {
                document.getElementById('pos_item_search').focus();
            }, 100);
        }

        // Manual keyboard navigation handling
        const customerInput = document.getElementById('customer_search');
        let currentIndex = -1;
        let listItems = [];
        let lastCustomerResults = [];

        // Manual keyboard navigation handling for Patients
        customerInput.addEventListener('keydown', (e) => {
            const list = document.getElementById('autoComplete_list_1');

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'PageDown' || e.key === 'PageUp') {
                if (!list) return;
                listItems = Array.from(list.querySelectorAll('li:not(.no_result)'));
                if (listItems.length === 0) return;
                if (currentIndex === -1) currentIndex = 0;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentIndex = (currentIndex + 1) % listItems.length;
                    highlightItem(currentIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentIndex = currentIndex <= 0 ? listItems.length - 1 : currentIndex - 1;
                    highlightItem(currentIndex);
                } else if (e.key === 'PageDown') {
                    e.preventDefault();
                    currentIndex = Math.min(currentIndex + 5, listItems.length - 1);
                    highlightItem(currentIndex);
                } else if (e.key === 'PageUp') {
                    e.preventDefault();
                    currentIndex = Math.max(currentIndex - 5, 0);
                    highlightItem(currentIndex);
                }
            } else if (e.key === 'Enter') {
                if (currentIndex >= 0) {
                    // Check if Create New Item
                    if (listItems[currentIndex] && listItems[currentIndex].getAttribute('data-action') === 'create-new') {
                        e.preventDefault();
                        openNewCustomerModal(customerInput.value);
                        return;
                    }

                    if (lastCustomerResults[currentIndex]) {
                        e.preventDefault();
                        const selection = lastCustomerResults[currentIndex];
                        selectCustomer(selection.value);
                        currentIndex = -1;
                    }
                }
            }
        });
        // FORCE highlight first item when search results appear
        customerInput.addEventListener('results', (event) => {
            lastCustomerResults = event.detail.results;
            setTimeout(() => {
                const list = document.getElementById('autoComplete_list_1');
                if (list) {
                    listItems = Array.from(list.querySelectorAll('li:not(.no_result)'));
                    if (listItems.length > 0) {
                        currentIndex = 0;
                        highlightItem(0);
                    }
                }
            }, 50); // Small buffer for rendering
        });

        customerInput.addEventListener('input', () => {
            if (!customerInput.value) {
                currentIndex = -1;
            }
        });

        function highlightItem(index) {
            listItems.forEach((item, i) => {
                if (i === index) {
                    item.setAttribute('aria-selected', 'true');
                    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    item.removeAttribute('aria-selected');
                }
            });
        }
    }

    async function initAutocompleteProduct() {
        const itemInput = document.getElementById('pos_item_search');
        let prodIndex = -1;
        let prodListItems = [];
        let lastProductQuery = "";
        const productCache = new Map();
        const CACHE_DURATION = 300000; // 5 minutes cache

        // Faster debounce for items too
        const debounceTime = 200;

        new autoComplete({
            selector: "#pos_item_search",
            placeHolder: "Search Items...",
            threshold: 2, // Require at least 2 characters
            debounce: debounceTime, // Longer debounce for server
            data: {
                src: async (query) => {
                    // 1. Instant Local search
                    const localQuery = query.toLowerCase().trim();
                    const localResults = preloadedProducts.filter(p =>
                        p.name.toLowerCase().includes(localQuery) ||
                        (p.sku && p.sku.toLowerCase().includes(localQuery))
                    );

                    if (localResults.length > 0) {
                        return localResults;
                    }

                    // 2. Fallback to API if really needed
                    const cacheKey = localQuery;
                    const cached = productCache.get(cacheKey);
                    if (cached && (Date.now() - cached.timestamp < CACHE_DURATION)) {
                        return cached.data;
                    }

                    lastProductQuery = query;
                    try {
                        const res = await fetch(`/api/v1/billing/items?search=${query}`);
                        const data = await res.json();
                        const results = data.data.items || [];
                        return results;
                    } catch (error) {
                        return [];
                    }
                },
                keys: ["name", "sku"]
            },
            resultsList: {
                noResults: true,
                maxResults: 25,
                tabSelect: true,
                navigate: false,
                element: (list, data) => {
                    list.id = "autoComplete_list_2";
                }
            },
            submit: true,
            resultItem: {
                highlight: true,
                className: "autoComplete_result"
            },
            onSelection: (feedback) => {
                const item = feedback.selection.value;
                selectProduct(item);
            }
        });

        function selectProduct(item) {
            currentSelectedProduct = item;

            document.getElementById('pos_item_search').value = item.name;
            document.getElementById('pos_batch').value = item.batch_no || 'BN998';
            document.getElementById('pos_pack').value = item.unit || '10\'S';
            document.getElementById('pos_price').value = item.price || 0;
            document.getElementById('pos_qty').value = 1;
            document.getElementById('pos_disc').value = 0;

            // Unlock primary fields for keyboard flow
            ['pos_batch', 'pos_qty'].forEach(id => {
                const el = document.getElementById(id);
                el.removeAttribute('readonly');
                el.removeAttribute('tabindex');
            });

            // Price and Disc stay searchable/clickable but skipped by TAB for speed
            ['pos_price', 'pos_disc'].forEach(id => {
                document.getElementById(id).removeAttribute('readonly');
            });

            updateLineTotal();

            // Clear list
            const list = document.getElementById('autoComplete_list_2');
            if (list) list.innerHTML = '';

            setTimeout(() => {
                const qtyEl = document.getElementById('pos_qty');
                qtyEl.focus();
                qtyEl.select();
            }, 50);
        }

        // Manual keyboard navigation for Products
        itemInput.addEventListener('keydown', (e) => {
            const list = document.getElementById('autoComplete_list_2');

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'PageDown' || e.key === 'PageUp') {
                if (!list) return;
                prodListItems = Array.from(list.querySelectorAll('li'));
                if (prodListItems.length === 0) return;
                if (prodIndex === -1) prodIndex = 0;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    prodIndex = (prodIndex + 1) % prodListItems.length;
                    highlightProduct(prodIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    prodIndex = prodIndex <= 0 ? prodListItems.length - 1 : prodIndex - 1;
                    highlightProduct(prodIndex);
                } else if (e.key === 'PageDown') {
                    e.preventDefault();
                    prodIndex = Math.min(prodIndex + 5, prodListItems.length - 1);
                    highlightProduct(prodIndex);
                } else if (e.key === 'PageUp') {
                    e.preventDefault();
                    prodIndex = Math.max(prodIndex - 5, 0);
                    highlightProduct(prodIndex);
                }
            } else if (e.key === 'Enter') {
                if (prodIndex >= 0 && lastProductResults[prodIndex]) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent global Enter handler
                    const selection = lastProductResults[prodIndex];
                    selectProduct(selection.value); // Direct Select - moves to QTY input
                    prodIndex = -1;
                }
            }
        });

        let lastProductResults = [];
        // FORCE highlight first item when search results appear
        itemInput.addEventListener('results', (event) => {
            lastProductResults = event.detail.results;
            setTimeout(() => {
                const list = document.getElementById('autoComplete_list_2');
                if (list) {
                    prodListItems = Array.from(list.querySelectorAll('li:not(.no_result):not(.no-results)'));
                    if (prodListItems.length > 0) {
                        prodIndex = 0;
                        highlightProduct(0);
                    }
                }
            }, 50); // Small buffer for rendering
        });

        itemInput.addEventListener('input', () => {
            if (!itemInput.value) {
                prodIndex = -1;
            }
        });

        function highlightProduct(index) {
            prodListItems.forEach((item, i) => {
                if (i === index) {
                    item.setAttribute('aria-selected', 'true');
                    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    item.removeAttribute('aria-selected');
                }
            });
        }
    }

    function updateLineTotal() {
        const qty = parseFloat(document.getElementById('pos_qty').value) || 0;
        const price = parseFloat(document.getElementById('pos_price').value) || 0;
        const disc = parseFloat(document.getElementById('pos_disc').value) || 0;

        const gross = qty * price;
        const net = gross - (gross * (disc / 100));
        document.getElementById('pos_line_total').value = net.toFixed(2);
    }

    function processEntry() {
        if (!currentSelectedProduct) {
            Swal.fire({ icon: 'warning', title: 'ITEM REQUIRED', text: 'Please select a product first' });
            return;
        }

        const qty = parseFloat(document.getElementById('pos_qty').value) || 0;
        if (qty <= 0) {
            Swal.fire({ icon: 'warning', title: 'INVALID QTY', text: 'Quantity must be at least 1' });
            return;
        }

        const itemData = {
            id: currentSelectedProduct.id || Math.random(),
            barcode: currentSelectedProduct.sku || 'N/A',
            name: currentSelectedProduct.name,
            hsn: currentSelectedProduct.hsn_code || '---',
            size: currentSelectedProduct.unit || 'PC',
            batch: document.getElementById('pos_batch').value || '---',
            qty: qty,
            exp: currentSelectedProduct.expiry || '---',
            price: parseFloat(document.getElementById('pos_price').value),
            disc: parseFloat(document.getElementById('pos_disc').value),
            tax_pct: parseFloat(currentSelectedProduct.tax_rate) || 0,
            total: parseFloat(document.getElementById('pos_line_total').value)
        };

        // Check if item exists (Same ID and Same Batch)
        const existingIndex = posItems.findIndex(i => i.id === itemData.id && i.batch === itemData.batch);

        if (existingIndex > -1) {
            // Update existing item
            posItems[existingIndex].qty += itemData.qty;

            // Recalculate totals for this line
            const gross = posItems[existingIndex].qty * posItems[existingIndex].price;
            const net = gross - (gross * (posItems[existingIndex].disc / 100));
            posItems[existingIndex].total = parseFloat(net.toFixed(2));

            // Move updated item to top for visibility if desired (optional, keeping current order is usually better for invoices)
            // For now, we keep it in place or we can unshift it to top. 
            // The user asked to "increase that particular product", implying update in place.
        } else {
            // Add new item
            posItems.unshift(itemData);
        }
        renderGrid();

        // Clear and Lock Entry
        currentSelectedProduct = null;
        document.getElementById('pos_item_search').value = '';
        document.getElementById('pos_batch').value = '';
        document.getElementById('pos_pack').value = '';
        document.getElementById('pos_price').value = '';
        document.getElementById('pos_qty').value = 1;
        document.getElementById('pos_line_total').value = '0.00';

        ['pos_batch', 'pos_qty', 'pos_price', 'pos_disc'].forEach(id => {
            const el = document.getElementById(id);
            el.setAttribute('readonly', true);
            el.setAttribute('tabindex', '-1');
        });

        document.getElementById('pos_item_search').focus();
    }

    function renderGrid() {
        const body = document.getElementById('pos_grid_body');
        const msg = document.getElementById('empty_grid_msg');
        body.innerHTML = '';

        if (posItems.length === 0) {
            msg.style.display = 'block';
            calculateGlobals();
            return;
        }
        msg.style.display = 'none';

        posItems.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${item.barcode}</td>
                <td class="fw-bold">${item.name}</td>
                <td class="text-center">${item.hsn}</td>
                <td class="text-center">${item.size}</td>
                <td class="text-center">${item.batch}</td>
                <td class="text-center p-0">
                    <input type="number" step="0.01" class="grid-edit-input text-center" 
                           value="${item.qty.toFixed(2)}" 
                           onfocus="this.select()"
                           onkeydown="if(event.key==='Enter'){this.blur(); event.preventDefault();}"
                           onchange="updateItemQty(${index}, this.value)">
                </td>
                <td class="text-center">${item.exp}</td>
                <td class="text-end p-0">
                    <input type="number" step="0.01" class="grid-edit-input text-end" 
                           value="${item.price.toFixed(2)}" 
                           onfocus="this.select()"
                           onkeydown="if(event.key==='Enter'){this.blur(); event.preventDefault();}"
                           onchange="updateItemPrice(${index}, this.value)">
                </td>
                <td class="text-center">${item.disc}%</td>
                <td class="text-center">${item.tax_pct}</td>
                <td class="text-end fw-extrabold text-navy">${item.total.toFixed(2)}</td>
                <td class="text-center p-0">
                    <button class="btn btn-sm btn-icon border-0 text-danger" onclick="removeItem(${index})"><i class="ri-delete-bin-5-line"></i></button>
                </td>
            `;
            body.appendChild(tr);
        });

        calculateGlobals();
    }

    function removeItem(index) {
        posItems.splice(index, 1);
        renderGrid();
    }

    function updateItemQty(index, val) {
        const qty = parseFloat(val) || 0;
        posItems[index].qty = qty;
        recalculateItemTotal(index);
    }

    function updateItemPrice(index, val) {
        const price = parseFloat(val) || 0;
        posItems[index].price = price;
        recalculateItemTotal(index);
    }

    function recalculateItemTotal(index) {
        const item = posItems[index];
        const gross = item.qty * item.price;
        const net = gross - (gross * (item.disc / 100));
        item.total = parseFloat(net.toFixed(2));
        renderGrid();
    }

    function calculateGlobals() {
        let totalQty = 0;
        let subTotal = 0;
        let taxTotal = 0;

        posItems.forEach(item => {
            totalQty += item.qty;
            const itemTaxable = item.total / (1 + (item.tax_pct / 100));
            subTotal += itemTaxable;
            taxTotal += (item.total - itemTaxable);
        });

        const tradeDiscPct = parseFloat(document.getElementById('trade_disc_pct').value) || 0;
        const tradeDiscAmt = subTotal * (tradeDiscPct / 100);
        const finalNet = (subTotal - tradeDiscAmt) + taxTotal;

        document.getElementById('total_qty_display').textContent = totalQty.toFixed(2);
        document.getElementById('sub_total_display').textContent = subTotal.toFixed(2);
        document.getElementById('trade_disc_amt').value = tradeDiscAmt.toFixed(2);
        document.getElementById('total_tax_display').textContent = taxTotal.toFixed(2);
        document.getElementById('grand_total_display').textContent = Math.round(finalNet).toFixed(2);

        renderGSTSummary();
    }

    function renderGSTSummary() {
        if (posItems.length === 0) {
            document.getElementById('gst_summary_dynamic').innerHTML = '<p class="text-muted fs-11 text-center">No items to calculate tax</p>';
            return;
        }

        const taxGroups = {};

        posItems.forEach(item => {
            const taxRate = item.tax_pct || 0;
            const itemTaxable = item.total / (1 + (taxRate / 100));
            const itemTaxAmt = item.total - itemTaxable;

            if (!taxGroups[taxRate]) {
                taxGroups[taxRate] = { taxable: 0, tax: 0 };
            }
            taxGroups[taxRate].taxable += itemTaxable;
            taxGroups[taxRate].tax += itemTaxAmt;
        });

        let html = `
            <table class="table table-bordered table-sm fs-10 mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Tax %</th>
                        <th class="text-end">Taxable</th>
                        <th class="text-end">CGST</th>
                        <th class="text-end">SGST</th>
                        <th class="text-end">Total Tax</th>
                    </tr>
                </thead>
                <tbody>
        `;

        for (const [rate, data] of Object.entries(taxGroups)) {
            const r = parseFloat(rate);
            const halfTax = data.tax / 2;
            html += `
                <tr>
                    <td>GST ${r}%</td>
                    <td class="text-end">${data.taxable.toFixed(2)}</td>
                    <td class="text-end">${halfTax.toFixed(2)}</td>
                    <td class="text-end">${halfTax.toFixed(2)}</td>
                    <td class="text-end">${data.tax.toFixed(2)}</td>
                </tr>
            `;
        }

        html += `</tbody></table>`;
        document.getElementById('gst_summary_dynamic').innerHTML = html;
    }

    async function savePOS(mode) {
        if (posItems.length === 0) {
            Swal.fire({ icon: 'error', title: 'EMPTY BILL', text: 'Add items before saving' });
            return;
        }

        const customerId = document.getElementById('selected_customer_id').value;
        if (!customerId) {
            Swal.fire({ icon: 'error', title: 'CUSTOMER REQUIRED', text: 'Please select a customer first' });
            return;
        }

        const payload = {
            patient_id: customerId,
            items: posItems.map(item => ({
                product_id: item.id, // Assuming id is product_id
                description: item.name,
                qty: item.qty,
                unit_price: item.price,
                discount_pct: item.disc,
                tax_percent: item.tax_pct
            })),
            net_total: parseFloat(document.getElementById('grand_total_display').textContent),
            tax_total: parseFloat(document.getElementById('total_tax_display').textContent),
            paid_amount: mode === 'estimate' ? 0 : parseFloat(document.getElementById('grand_total_display').textContent), // Full payment for now or 0 for estimate
            received: mode === 'estimate' ? 0 : parseFloat(document.getElementById('grand_total_display').textContent),
            notes: document.getElementById('bill_notes') ? document.getElementById('bill_notes').value : '',
            payment_mode: document.getElementById('pay_mode').value,
            invoice_type: mode === 'estimate' ? 'estimate' : 'pharmacy' // Default to pharmacy for now
        };

        Swal.fire({
            title: mode === 'estimate' ? 'SAVING ESTIMATE...' : 'PROCESSING BILL...',
            html: 'Communicating with server...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch('/api/v1/billing/invoices', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                const invoiceId = data.data.invoice_id || data.data.id;
                const printType = mode === 'thermal' ? 'thermal' : 'pharmacy';
                const printUrl = `/billing/print?id=${invoiceId}&type=${printType}`;

                // Update loader message
                Swal.update({
                    title: 'OPENING PRINT DIALOG...',
                    html: 'Please wait while we prepare your invoice...'
                });

                // Open print dialog automatically
                window.open(printUrl, '_blank');

                // Wait a moment then show success with option to print again or start new
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'SUCCESS',
                        html: `
                            <div class="mt-2 mb-4">Invoice saved successfully!</div>
                            <div class="d-grid gap-2">
                                <a href="${printUrl}" target="_blank" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center">
                                    <i class="ri-printer-line me-2"></i> PRINT AGAIN
                                </a>
                                <button onclick="location.reload()" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                                    <i class="ri-refresh-line me-2"></i> START NEW BILL
                                </button>
                            </div>
                        `,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                }, 1000);
            } else {
                Swal.fire({ icon: 'error', title: 'FAILED', text: data.message || 'Unknown error occurred' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'NETWORK ERROR', text: e.message });
        }
    }

    function clearPOS() {
        Swal.fire({
            title: 'ARE YOU SURE?',
            text: "This will clear all items from the current bill window.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'YES, CLEAR ALL'
        }).then((result) => {
            if (result.isConfirmed) {
                posItems = [];
                renderGrid();
            }
        });
    }

    // New Customer Functions
    function openNewCustomerModal(query) {
        const mobile = query.replace(/[^0-9]/g, '');
        document.getElementById('new_cust_mobile').value = mobile;
        document.getElementById('new_cust_name').value = '';

        const modal = new bootstrap.Modal(document.getElementById('newCustomerModal'));
        modal.show();

        setTimeout(() => {
            const nameInput = document.getElementById('new_cust_name');
            nameInput.focus();

            // Enter key to save in modal
            nameInput.onkeydown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveNewCustomer();
                }
            };
        }, 500);
    }

    async function saveNewCustomer() {
        const mobile = document.getElementById('new_cust_mobile').value;
        const name = document.getElementById('new_cust_name').value;

        if (!name || name.length < 2) {
            Swal.fire({ icon: 'warning', text: 'Name required (min 2 chars)' });
            return;
        }

        try {
            const res = await fetch('/api/v1/patients', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    first_name: name,
                    mobile: mobile,
                    gender: 'unknown'
                })
            });

            const data = await res.json();
            if (res.ok) {
                const modalEl = document.getElementById('newCustomerModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                // Select the new customer
                const newPatient = {
                    patient_id: data.data.patient_id,
                    full_name: name,
                    mrn: data.data.mrn,
                    mobile: mobile
                };

                // Manually trigger selection
                // We need to access the inner function selectCustomer... 
                // But selectCustomer is defined inside initAutocompleteCustomer scope...
                // Solution: Make selectCustomer global or dispatch logic similar to before.
                // Or: Redefine logic here since we are in global scope.

                currentSelectedCustomer = newPatient;
                document.getElementById('customer_search').value = `${newPatient.full_name}`;
                document.getElementById('selected_customer_id').value = newPatient.patient_id;
                document.getElementById('customer_balance_info').textContent = `Bal: 0.00 Cr | MRN: ${newPatient.mrn}`;

                // Clear list
                const list = document.getElementById('autoComplete_list_1');
                if (list) list.innerHTML = '';

                setTimeout(() => {
                    document.getElementById('pos_item_search').focus();
                }, 100);

            } else {
                Swal.fire({ icon: 'error', text: data.message || 'Failed to create' });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: 'error', text: 'Network Error' });
        }
    }

    function simulateBarcodeScan() {
        Swal.fire({
            title: 'MANUAL BARCODE',
            input: 'text',
            inputPlaceholder: 'Type barcode and press Enter',
            showCancelButton: true
        }).then((result) => {
            if (result.value) {
                // Mock search by barcode
                document.getElementById('pos_item_search').value = 'MOCK ITEM FROM BARCODE';
                document.getElementById('pos_batch').value = 'SCAN01';
                document.getElementById('pos_price').value = '245.00';
                updateLineTotal();
                document.getElementById('pos_qty').focus();
            }
        });
    }
    // ========================================
    // ADVANCED PATIENT LOOKUP MODAL FUNCTIONS
    // ========================================

    let lookupModal = null;
    let lookupSearchTimeout = null;
    let lookupResults = [];
    let lookupSelectedIndex = -1;
    let selectedLookupPatient = null;
    let patientPrescriptions = [];
    let patientBillingHistory = [];
    let patientVisitHistory = [];

    function openPatientLookupModal() {
        // Close last bills modal if open
        if (lastBillsModal) lastBillsModal.hide();
        if (paymentMethodModal) paymentMethodModal.hide();

        // Initialize modal if not already
        if (!lookupModal) {
            lookupModal = new bootstrap.Modal(document.getElementById('patientLookupModal'), {
                keyboard: true
            });
        }

        // Reset state
        lookupResults = [];
        lookupSelectedIndex = -1;
        selectedLookupPatient = null;

        // Reset UI
        document.getElementById('lookup_patient_search').value = '';
        document.getElementById('lookup_initial_state').classList.remove('d-none');
        document.getElementById('lookup_no_results').classList.add('d-none');
        document.getElementById('lookup_details_empty').classList.remove('d-none');
        document.getElementById('lookup_details_content').classList.add('d-none');

        // Clear results list (keep only state divs)
        const resultsList = document.getElementById('lookup_results_list');
        const items = resultsList.querySelectorAll('.lookup-result-item');
        items.forEach(item => item.remove());

        lookupModal.show();

        // Focus search input after modal is shown
        document.getElementById('patientLookupModal').addEventListener('shown.bs.modal', function onShown() {
            document.getElementById('lookup_patient_search').focus();
            document.getElementById('patientLookupModal').removeEventListener('shown.bs.modal', onShown);
        });

        // Set up keyboard navigation
        setupLookupKeyboardNavigation();
    }

    function setupLookupKeyboardNavigation() {
        const searchInput = document.getElementById('lookup_patient_search');

        // Remove old listeners
        searchInput.onkeydown = null;
        searchInput.oninput = null;

        // Input handler for instant search
        searchInput.oninput = function (e) {
            const query = e.target.value.trim();

            // Clear previous timeout
            if (lookupSearchTimeout) {
                clearTimeout(lookupSearchTimeout);
            }

            // Auto-sync numbers to walk-in mobile field
            const digits = query.replace(/[^0-9]/g, '');
            if (digits.length >= 6) {
                document.getElementById('walkin_mobile').value = digits.slice(0, 10);
            }

            if (query.length < 2) {
                // Show initial state
                document.getElementById('lookup_initial_state').classList.remove('d-none');
                document.getElementById('lookup_no_results').classList.add('d-none');
                clearLookupResults();
                return;
            }

            // Debounce search for 150ms (instant feel)
            lookupSearchTimeout = setTimeout(() => {
                performLookupSearch(query);
            }, 150);
        };

        // Keyboard navigation
        searchInput.onkeydown = function (e) {
            const resultItems = document.querySelectorAll('.lookup-result-item');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (resultItems.length > 0) {
                    // If not selected yet, start at first item
                    if (lookupSelectedIndex < 0) {
                        lookupSelectedIndex = 0;
                    } else {
                        lookupSelectedIndex = Math.min(lookupSelectedIndex + 1, resultItems.length - 1);
                    }
                    highlightLookupResult(lookupSelectedIndex);
                    loadPatientPreview(lookupResults[lookupSelectedIndex]);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (resultItems.length > 0 && lookupSelectedIndex > 0) {
                    // Go up in the list
                    lookupSelectedIndex = lookupSelectedIndex - 1;
                    highlightLookupResult(lookupSelectedIndex);
                    loadPatientPreview(lookupResults[lookupSelectedIndex]);
                } else if (lookupSelectedIndex === 0) {
                    // At first item, go back to input (deselect all)
                    lookupSelectedIndex = -1;
                    // Remove highlight from all items
                    resultItems.forEach(item => item.classList.remove('active', 'bg-primary-transparent'));
                    // Focus on input and select text for easy editing
                    searchInput.focus();
                    searchInput.select();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedLookupPatient) {
                    selectPatientFromLookup();
                } else if (lookupSelectedIndex >= 0 && lookupResults[lookupSelectedIndex]) {
                    selectPatientFromLookup();
                }
            } else if (e.key === 'Escape') {
                lookupModal.hide();
            }
        };
    }

    async function performLookupSearch(query) {
        // Show loading indicator
        document.getElementById('lookup_loading_indicator').style.display = 'block';
        document.getElementById('lookup_initial_state').classList.add('d-none');

        try {
            // First check preloaded data
            const localQuery = query.toLowerCase();
            let results = preloadedPatients.filter(p =>
                p.full_name.toLowerCase().includes(localQuery) ||
                p.mrn.toLowerCase().includes(localQuery) ||
                (p.mobile && p.mobile.includes(localQuery))
            );

            // If no local results, fetch from API
            if (results.length === 0) {
                const res = await fetch(`/api/v1/patients/search?q=${encodeURIComponent(query)}`);
                const data = await res.json();
                results = data.data?.patients || [];

                // Update preloaded cache
                if (results.length > 0) {
                    preloadedPatients = [...new Map([...preloadedPatients, ...results].map(p => [p.patient_id, p])).values()];
                }
            }

            lookupResults = results;
            renderLookupResults(results);

        } catch (error) {
            console.error('Search error:', error);
            lookupResults = [];
            renderLookupResults([]);
        } finally {
            document.getElementById('lookup_loading_indicator').style.display = 'none';
        }
    }

    function renderLookupResults(results) {
        const container = document.getElementById('lookup_results_list');

        // Clear existing result items
        const existingItems = container.querySelectorAll('.lookup-result-item');
        existingItems.forEach(item => item.remove());

        if (results.length === 0) {
            document.getElementById('lookup_initial_state').classList.add('d-none');
            document.getElementById('lookup_no_results').classList.remove('d-none');
            return;
        }

        document.getElementById('lookup_initial_state').classList.add('d-none');
        document.getElementById('lookup_no_results').classList.add('d-none');

        results.forEach((patient, index) => {
            const item = document.createElement('a');
            item.href = 'javascript:void(0)';
            item.className = 'list-group-item list-group-item-action lookup-result-item py-2 px-3';
            item.setAttribute('data-index', index);

            const isNumericSearch = /^\d+$/.test(document.getElementById('lookup_patient_search').value.trim());

            item.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="ri-user-line text-muted"></i>
                    </div>
                    <div class="flex-fill min-w-0">
                        <div class="d-flex align-items-center gap-2">
                            <strong class="fs-13 text-truncate">${patient.full_name}</strong>
                            <span class="badge bg-primary-transparent text-primary fs-9">${patient.mrn}</span>
                        </div>
                        <div class="fs-11 text-muted text-truncate">
                            <i class="ri-phone-line me-1"></i>${patient.mobile || 'No mobile'}
                            ${patient.age ? `<span class="ms-2"><i class="ri-user-line me-1"></i>${patient.age}Y ${patient.gender || ''}</span>` : ''}
                        </div>
                    </div>
                    <i class="ri-arrow-right-s-line text-muted"></i>
                </div>
            `;

            // Click handler
            item.onclick = function () {
                lookupSelectedIndex = index;
                highlightLookupResult(index);
                loadPatientPreview(patient);
            };

            // Double-click to select immediately
            item.ondblclick = function () {
                loadPatientPreview(patient);
                setTimeout(() => selectPatientFromLookup(), 100);
            };

            container.appendChild(item);
        });

        // Auto-select first result
        if (results.length > 0) {
            lookupSelectedIndex = 0;
            highlightLookupResult(0);
            loadPatientPreview(results[0]);
        }
    }

    function highlightLookupResult(index) {
        const items = document.querySelectorAll('.lookup-result-item');
        items.forEach((item, i) => {
            if (i === index) {
                item.classList.add('active', 'bg-primary-transparent');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('active', 'bg-primary-transparent');
            }
        });
    }

    function clearLookupResults() {
        const container = document.getElementById('lookup_results_list');
        const existingItems = container.querySelectorAll('.lookup-result-item');
        existingItems.forEach(item => item.remove());
        lookupResults = [];
        lookupSelectedIndex = -1;
    }

    async function loadPatientPreview(patient) {
        selectedLookupPatient = patient;

        // Show details panel
        document.getElementById('lookup_details_empty').classList.add('d-none');
        document.getElementById('lookup_details_content').classList.remove('d-none');

        // Update patient info
        document.getElementById('lookup_patient_name').textContent = patient.full_name;
        document.getElementById('lookup_patient_mobile').textContent = patient.mobile || 'No mobile';
        document.getElementById('lookup_patient_mrn').textContent = patient.mrn;
        document.getElementById('lookup_patient_age_gender').textContent =
            `${patient.age ? patient.age + 'Y' : ''} ${patient.gender || ''}`.trim() || '---';

        // Load history data in parallel
        await Promise.all([
            loadPatientPrescriptions(patient.patient_id),
            loadPatientBillingHistory(patient.patient_id),
            loadPatientVisitHistory(patient.patient_id)
        ]);
    }

    async function loadPatientPrescriptions(patientId) {
        const container = document.getElementById('prescription_content');
        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const res = await fetch(`/api/v1/patients/${patientId}/prescriptions`);
            const data = await res.json();
            patientPrescriptions = data.data?.prescriptions || [];

            if (patientPrescriptions.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="ri-capsule-line fs-30 opacity-50"></i>
                        <p class="fs-11 mb-0 mt-2">No prescriptions found</p>
                    </div>
                `;
                return;
            }

            // Show last prescription with items
            const lastPrescription = patientPrescriptions[0];
            let itemsHtml = '';

            if (lastPrescription.items && lastPrescription.items.length > 0) {
                itemsHtml = `
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered fs-11 mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Medicine</th>
                                    <th class="text-center">Qty</th>
                                    <th>Dosage</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${lastPrescription.items.map(item => `
                                    <tr>
                                        <td class="fw-semibold">${item.product_name || item.medicine_name || 'N/A'}</td>
                                        <td class="text-center">${item.quantity || 1}</td>
                                        <td>${item.frequency || item.dosage || '-'}</td>
                                        <td>${item.duration_days || item.duration || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                itemsHtml = '<p class="text-muted fs-11">No items in this prescription</p>';
            }

            container.innerHTML = `
                <div class="card border mb-2 cursor-pointer shadow-sm-hover" onclick="loadPrescriptionToCart(0)" style="cursor: pointer; transition: all 0.2s;">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold fs-12 text-primary"><i class="ri-add-circle-line me-1"></i>Last Prescription (Click to Load)</span>
                            <span class="text-muted fs-10 ms-2">${formatDate(lastPrescription.prescribed_at || lastPrescription.created_at)}</span>
                        </div>
                        <span class="badge bg-success-transparent text-success fs-9">
                            ${lastPrescription.items?.length || 0} items
                        </span>
                    </div>
                    <div class="card-body p-2">
                        ${itemsHtml}
                    </div>
                </div>
                ${patientPrescriptions.length > 1 ? `<p class="text-muted fs-10 text-center">+ ${patientPrescriptions.length - 1} more prescriptions</p>` : ''}
            `;

        } catch (error) {
            console.error('Error loading prescriptions:', error);
            container.innerHTML = '<div class="text-center py-4 text-danger fs-11">Error loading prescriptions</div>';
        }
    }

    async function loadPatientBillingHistory(patientId) {
        const container = document.getElementById('billing_content');
        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const res = await fetch(`/api/v1/patients/${patientId}/billing`);
            const data = await res.json();
            const invoices = data.data?.invoices || [];
            patientBillingHistory = invoices;

            if (invoices.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="ri-bill-line fs-30 opacity-50"></i>
                        <p class="fs-11 mb-0 mt-2">No billing history found</p>
                    </div>
                `;
                return;
            }

            // Show recent bills
            container.innerHTML = `
                <div class="list-group list-group-flush">
                    ${invoices.slice(0, 5).map((inv, idx) => `
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center ${idx === 0 ? 'bg-warning-transparent' : ''}">
                            <div>
                                <div class="fw-semibold fs-12">${inv.invoice_no || 'INV-' + inv.invoice_id}</div>
                                <div class="fs-10 text-muted">${formatDate(inv.created_at)}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-13 text-primary">₹${parseFloat(inv.total_amount || 0).toFixed(2)}</div>
                                <span class="badge ${inv.status === 'paid' ? 'bg-success' : 'bg-warning'} fs-9">${inv.status || 'pending'}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
                ${invoices.length > 5 ? `<p class="text-muted fs-10 text-center mt-2">+ ${invoices.length - 5} more invoices</p>` : ''}
            `;

        } catch (error) {
            console.error('Error loading billing:', error);
            container.innerHTML = '<div class="text-center py-4 text-danger fs-11">Error loading billing history</div>';
        }
    }

    async function loadPatientVisitHistory(patientId) {
        const container = document.getElementById('visits_content');
        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const res = await fetch(`/api/v1/patients/${patientId}/visits`);
            const data = await res.json();
            const visits = data.data?.visits || [];
            patientVisitHistory = visits;

            if (visits.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="ri-calendar-check-line fs-30 opacity-50"></i>
                        <p class="fs-11 mb-0 mt-2">No visit history found</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="list-group list-group-flush">
                    ${visits.slice(0, 5).map(visit => `
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold fs-12">${visit.visit_type || 'Consultation'}</div>
                                    <div class="fs-10 text-muted">${visit.doctor_name || 'Doctor'}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fs-11">${formatDate(visit.visit_date || visit.created_at)}</div>
                                    <span class="badge bg-info-transparent text-info fs-9">${visit.status || 'completed'}</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;

        } catch (error) {
            console.error('Error loading visits:', error);
            container.innerHTML = '<div class="text-center py-4 text-danger fs-11">Error loading visit history</div>';
        }
    }

    function selectPatientFromLookup() {
        if (!selectedLookupPatient) {
            Swal.fire({ icon: 'warning', text: 'Please select a patient first' });
            return;
        }

        // Set the selected customer
        currentSelectedCustomer = selectedLookupPatient;

        // Display as "Name (Phone)" format
        const phone = selectedLookupPatient.mobile || '';
        const displayName = phone ? `${selectedLookupPatient.full_name} (${phone})` : selectedLookupPatient.full_name;
        document.getElementById('customer_search').value = displayName;
        document.getElementById('selected_customer_id').value = selectedLookupPatient.patient_id;
        document.getElementById('customer_balance_info').textContent =
            `Bal: 0.00 Cr | MRN: ${selectedLookupPatient.mrn}`;

        // Close modal
        lookupModal.hide();

        // Focus on item search
        setTimeout(() => {
            document.getElementById('pos_item_search').focus();
        }, 100);
    }

    async function loadLastBillToCart() {
        if (!selectedLookupPatient || patientBillingHistory.length === 0) {
            Swal.fire({ icon: 'info', text: 'No previous bills to load' });
            return;
        }

        const lastBill = patientBillingHistory[0];

        // First select the patient
        selectPatientFromLookup();

        // Fetch invoice details with items
        try {
            const res = await fetch(`/api/v1/billing/invoices/${lastBill.invoice_id}`);
            const data = await res.json();

            if (data.data?.items) {
                data.data.items.forEach(item => {
                    const itemData = {
                        id: item.product_id || Math.random(),
                        barcode: item.sku || 'N/A',
                        name: item.description || item.product_name,
                        hsn: item.hsn_code || '---',
                        size: item.unit || 'PC',
                        batch: item.batch_no || '---',
                        qty: parseFloat(item.qty) || 1,
                        exp: item.expiry || '---',
                        price: parseFloat(item.unit_price) || 0,
                        disc: parseFloat(item.discount_pct) || 0,
                        tax_pct: parseFloat(item.tax_percent) || 0,
                        total: parseFloat(item.total) || 0
                    };
                    posItems.unshift(itemData);
                });
                renderGrid();
                Swal.fire({
                    icon: 'success',
                    title: 'Items Loaded',
                    text: `${data.data.items.length} items added from last bill`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            console.error('Error loading bill items:', error);
            Swal.fire({ icon: 'error', text: 'Failed to load bill items' });
        }
    }

    async function loadPrescriptionToCart(index = 0) {
        if (!selectedLookupPatient || patientPrescriptions.length === 0) {
            Swal.fire({ icon: 'info', text: 'No prescriptions to load' });
            return;
        }

        const prescription = patientPrescriptions[index];
        if (!prescription) return;

        // First select the patient (if not already fully active in main UI)
        selectPatientFromLookup();

        if (!prescription.items || prescription.items.length === 0) {
            Swal.fire({ icon: 'info', text: 'Prescription has no items' });
            return;
        }

        let itemsAdded = 0;

        Swal.fire({
            title: 'Loading Prescription...',
            html: 'Matching items with inventory...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Add prescription items as bill items
        for (const item of prescription.items) {
            // Try to find product in inventory for price using ID if available, else name
            let price = 0;
            let stockProduct = null;

            try {
                // If we have product_id, try to use it (assuming we have an endpoint or can search by ID specifically)
                // For now, we search by name which is universal
                const query = item.product_name || item.medicine_name;
                if (query) {
                    const searchRes = await fetch(`/api/v1/billing/items?search=${encodeURIComponent(query)}`);
                    const searchData = await searchRes.json();
                    if (searchData.data?.items?.length > 0) {
                        // Try to find exact match
                        stockProduct = searchData.data.items.find(p => p.name.toLowerCase() === query.toLowerCase()) || searchData.data.items[0];
                        price = parseFloat(stockProduct.price) || 0;
                    }
                }
            } catch (e) {
                console.warn('Could not fetch product price:', e);
            }

            const itemData = {
                id: stockProduct ? stockProduct.id : (item.product_id || Math.random()),
                barcode: stockProduct ? stockProduct.sku : (item.sku || 'N/A'),
                name: item.product_name || item.medicine_name || 'Unknown',
                hsn: stockProduct ? stockProduct.hsn_code : (item.hsn_code || '---'),
                size: stockProduct ? stockProduct.unit : (item.unit || 'PC'),
                batch: stockProduct ? (stockProduct.batch_no || '---') : '---',
                qty: parseFloat(item.quantity) || 1,
                exp: stockProduct ? (stockProduct.expiry || '---') : '---',
                price: price,
                disc: 0,
                tax_pct: stockProduct ? (parseFloat(stockProduct.tax_percent) || 0) : 0,
                total: price * (parseFloat(item.quantity) || 1)
            };
            posItems.unshift(itemData);
            itemsAdded++;
        }

        renderGrid();
        Swal.fire({
            icon: 'success',
            title: 'Prescription Loaded',
            text: `${itemsAdded} items added to bill`,
            timer: 1500,
            showConfirmButton: false
        });
    }

    function openNewCustomerFromLookup() {
        const searchQuery = document.getElementById('lookup_patient_search').value;
        lookupModal.hide();
        setTimeout(() => {
            openNewCustomerModal(searchQuery);
        }, 300);
    }

    // Quick Walk-in Patient Creation (Inline in Modal)
    async function quickCreateWalkin() {
        const mobile = document.getElementById('walkin_mobile').value.trim();
        const name = document.getElementById('walkin_name').value.trim();

        if (!name || name.length < 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Name Required',
                text: 'Please enter patient name (min 2 characters)',
                timer: 2000,
                showConfirmButton: false
            });
            document.getElementById('walkin_name').focus();
            return;
        }

        // Show quick loading
        const btn = document.querySelector('#walkin_quick_create button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;

        try {
            const res = await fetch('/api/v1/patients', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    first_name: name,
                    mobile: mobile || null,
                    gender: 'unknown'
                })
            });

            const data = await res.json();

            if (res.ok) {
                // Create patient object
                const newPatient = {
                    patient_id: data.data.patient_id,
                    full_name: name,
                    mrn: data.data.mrn,
                    mobile: mobile || ''
                };

                // Select this patient immediately
                currentSelectedCustomer = newPatient;
                document.getElementById('customer_search').value = newPatient.full_name;
                document.getElementById('selected_customer_id').value = newPatient.patient_id;
                document.getElementById('customer_balance_info').textContent =
                    `Bal: 0.00 Cr | MRN: ${newPatient.mrn}`;

                // Close modal
                lookupModal.hide();

                // Clear walk-in fields
                document.getElementById('walkin_mobile').value = '';
                document.getElementById('walkin_name').value = '';

                // Success feedback
                Swal.fire({
                    icon: 'success',
                    title: 'Patient Created!',
                    html: `<strong>${name}</strong> (MRN: ${data.data.mrn})`,
                    timer: 1500,
                    showConfirmButton: false
                });

                // Focus on item search
                setTimeout(() => {
                    document.getElementById('pos_item_search').focus();
                }, 100);

            } else {
                Swal.fire({ icon: 'error', text: data.message || 'Failed to create patient' });
            }
        } catch (error) {
            console.error('Quick create error:', error);
            Swal.fire({ icon: 'error', text: 'Network error while creating patient' });
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    // Auto-fill walk-in mobile when search input has numbers
    function syncSearchToWalkin() {
        const searchInput = document.getElementById('lookup_patient_search');
        const walkinMobile = document.getElementById('walkin_mobile');

        if (searchInput && walkinMobile) {
            const searchVal = searchInput.value.trim();
            // If search looks like a phone number, auto-fill walk-in mobile
            const digits = searchVal.replace(/[^0-9]/g, '');
            if (digits.length >= 6) {
                walkinMobile.value = digits.slice(0, 10);
            }
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '---';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    // Override Ctrl+D to open lookup modal
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            openPatientLookupModal();
        }
    });

    // ===== LAST BILLS MODAL (F4) =====
    let lastBillsModal = null;
    let lastBillsIndex = 0;
    let customerLastBills = [];

    function openLastBillsModal() {
        // Close other modals if open
        if (lookupModal) lookupModal.hide();
        if (paymentMethodModal) paymentMethodModal.hide();

        if (!lastBillsModal) {
            lastBillsModal = new bootstrap.Modal(document.getElementById('lastBillsModal'));
        }

        // Load last 5 bills (for customer if selected, otherwise all recent)
        loadLastBills();
        lastBillsModal.show();
    }

    async function loadLastBills() {
        const listContainer = document.getElementById('lastBillsList');
        const titleEl = document.getElementById('lastBillsModalTitle');
        listContainer.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            // If customer selected, show their bills. Otherwise show all recent bills.
            let url = '/api/v1/billing/invoices?limit=5';
            if (currentSelectedCustomer) {
                url += `&patient_id=${currentSelectedCustomer.patient_id}`;
                titleEl.innerHTML = `<i class="ri-file-list-3-line me-1"></i> BILLS - ${currentSelectedCustomer.full_name}`;
            } else {
                titleEl.innerHTML = '<i class="ri-file-list-3-line me-1"></i> RECENT BILLS';
            }

            const res = await fetch(url);
            const data = await res.json();
            // Handle different API response formats
            if (data.data?.invoices) {
                customerLastBills = data.data.invoices;
            } else if (data.data?.items) {
                customerLastBills = data.data.items;
            } else if (Array.isArray(data.data)) {
                customerLastBills = data.data;
            } else {
                customerLastBills = [];
            }

            if (customerLastBills.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="ri-file-list-line fs-40 opacity-25 mb-2 d-block"></i>
                        <p class="fs-12 mb-0">No previous bills found</p>
                    </div>`;
                return;
            }

            listContainer.innerHTML = customerLastBills.map((bill, i) => {
                const name = bill.patient_name || (bill.first_name ? `${bill.first_name} ${bill.last_name || ''}` : 'Walk-in');
                const date = bill.bill_date || bill.created_at || '';
                const amount = parseFloat(bill.total_amount || 0).toFixed(2);
                return `
                    <div class="bill-item ${i === 0 ? 'active' : ''}" data-index="${i}" onclick="selectBillFromList(${i})">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold fs-12">${bill.bill_no || bill.invoice_no}</div>
                                <div class="text-muted fs-11">${name}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success fs-13">₹${amount}</div>
                                <div class="text-muted fs-10">${formatDate(date)}</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            lastBillsIndex = 0;
        } catch (error) {
            listContainer.innerHTML = '<div class="text-center py-3 text-danger fs-11">Error loading bills</div>';
        }
    }

    function selectBillFromList(index) {
        const items = document.querySelectorAll('#lastBillsList .bill-item');
        items.forEach((item, i) => item.classList.toggle('active', i === index));
        lastBillsIndex = index;
    }

    // Keyboard navigation for last bills modal
    document.getElementById('lastBillsModal').addEventListener('keydown', (e) => {
        const items = document.querySelectorAll('#lastBillsList .bill-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            lastBillsIndex = Math.min(lastBillsIndex + 1, items.length - 1);
            selectBillFromList(lastBillsIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            lastBillsIndex = Math.max(lastBillsIndex - 1, 0);
            selectBillFromList(lastBillsIndex);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            loadBillItemsToCart(customerLastBills[lastBillsIndex]);
        }
    });

    async function loadBillItemsToCart(bill) {
        if (!bill) return;

        try {
            const res = await fetch(`/api/v1/billing/invoices/${bill.invoice_id || bill.id}`);
            const data = await res.json();

            if (data.success && data.data) {
                const inv = data.data;

                // Select Patient
                currentSelectedCustomer = {
                    patient_id: inv.patient_id,
                    full_name: inv.full_name || `${inv.first_name} ${inv.last_name || ''}`,
                    mobile: inv.mobile || ''
                };
                document.getElementById('customer_search').value = `${currentSelectedCustomer.full_name} (${currentSelectedCustomer.mobile})`;

                const items = inv.items || inv.details || [];
                if (items.length > 0) {
                    posItems = [];

                    items.forEach(item => {
                        const newItem = {
                            id: item.product_id,
                            barcode: item.barcode || item.sku || '---',
                            name: item.product_name || item.name,
                            hsn: item.hsn_code || '---',
                            size: item.unit || item.size || 'PC',
                            batch: item.batch_no || item.batch || '---',
                            qty: parseFloat(item.qty) || 1,
                            exp: item.expiry || '---',
                            price: parseFloat(item.rate || item.price || item.unit_price) || 0,
                            disc: parseFloat(item.discount_percent || item.discount_pct || item.discount || 0),
                            tax_pct: parseFloat(item.gst_percent || item.tax_rate || item.tax_percent || 0),
                            total: parseFloat(item.total || item.amount || 0)
                        };
                        posItems.push(newItem);
                    });
                    renderGrid();
                }

                lastBillsModal.hide();
                Swal.fire({
                    icon: 'success',
                    text: `Loaded ${items.length} items for ${currentSelectedCustomer.full_name}`,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            console.error('Load items error:', error);
            Swal.fire({ icon: 'error', text: 'Failed to load bill items' });
        }
    }

    // ===== PAYMENT METHOD MODAL =====
    let paymentMethodModal = null;
    let selectedPaymentMethod = 'cash';
    const paymentMethods = ['cash', 'card', 'upi'];
    let pendingSaveMode = null;

    function openPaymentMethodModal(saveMode) {
        if (!paymentMethodModal) {
            paymentMethodModal = new bootstrap.Modal(document.getElementById('paymentMethodModal'));
        }

        // 1. Get last used print type from localStorage
        const lastPrintType = localStorage.getItem('last_print_type') || 'thermal';

        // 2. Set mode (if passed from F2/F8 button, use that, otherwise use last preference)
        pendingSaveMode = saveMode || lastPrintType;
        selectedPaymentMethod = 'cash';

        // Reset selection UI
        selectPaymentMethod('cash');
        updatePrintTypeHints();
        paymentMethodModal.show();
    }

    function setPendingSaveMode(mode) {
        pendingSaveMode = mode;
        updatePrintTypeHints();
    }

    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.classList.toggle('selected', card.dataset.method === method);
        });
    }

    // Keyboard navigation for payment method modal
    document.getElementById('paymentMethodModal').addEventListener('keydown', (e) => {
        const currentIndex = paymentMethods.indexOf(selectedPaymentMethod);

        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            selectPaymentMethod(paymentMethods[Math.max(0, currentIndex - 1)]);
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            selectPaymentMethod(paymentMethods[Math.min(2, currentIndex + 1)]);
        } else if (e.key === '1') {
            e.preventDefault();
            selectPaymentMethod('cash');
        } else if (e.key === '2') {
            e.preventDefault();
            selectPaymentMethod('card');
        } else if (e.key === '3') {
            e.preventDefault();
            selectPaymentMethod('upi');
        } else if (e.key === 'F7') {
            e.preventDefault();
            setPendingSaveMode('thermal');
        } else if (e.key === 'F8') {
            e.preventDefault();
            setPendingSaveMode('print');
        }
        else if (e.key === 'Enter') {
            e.preventDefault();
            confirmPaymentAndPrint();
        } else if (e.key === 'Escape') {
            paymentMethodModal.hide();
        }
    });

    function updatePrintTypeHints() {
        const tHint = document.getElementById('thermal_hint');
        const pHint = document.getElementById('pharmacy_hint');

        // Clear classes
        tHint.classList.remove('active');
        pHint.classList.remove('active', 'active-pharmacy');

        if (pendingSaveMode === 'thermal') {
            tHint.classList.add('active');
        } else {
            pHint.classList.add('active', 'active-pharmacy');
        }
    }

    function confirmPaymentAndPrint() {
        paymentMethodModal.hide();

        // Store payment method for the bill (update the pay_mode dropdown)
        const payModeSelect = document.getElementById('pay_mode');
        if (payModeSelect) {
            // Fix UPI casing
            const displayMethod = selectedPaymentMethod === 'upi' ? 'UPI' : selectedPaymentMethod.charAt(0).toUpperCase() + selectedPaymentMethod.slice(1);
            payModeSelect.value = displayMethod;
        }

        // Now actually save the POS
        actualSavePOS(pendingSaveMode);
    }

    // Wrap the original savePOS function
    const originalSavePOS = typeof savePOS === 'function' ? savePOS : null;

    function savePOS(mode) {
        if (mode === 'print' || mode === 'thermal') {
            // Show payment method modal first
            openPaymentMethodModal(mode);
        } else {
            // For 'only' or 'estimate' modes, skip payment modal
            actualSavePOS(mode);
        }
    }

    // The actual save function (renamed from original savePOS logic)
    async function actualSavePOS(mode) {
        // Validate
        if (!currentSelectedCustomer) {
            Swal.fire({ icon: 'warning', text: 'Please select a customer first' });
            return;
        }

        if (posItems.length === 0) {
            Swal.fire({ icon: 'warning', text: 'Please add at least one item' });
            return;
        }

        const billData = {
            patient_id: currentSelectedCustomer.patient_id,
            patient_name: currentSelectedCustomer.full_name,
            invoice_no: document.getElementById('bill_no')?.value || '',
            invoice_date: document.getElementById('bill_date')?.value || new Date().toISOString().split('T')[0],
            invoice_type: INVOICE_TYPE,
            gst_type: document.getElementById('gst_type')?.value || 'GST',
            ref_no: document.getElementById('ref_no')?.value || '',
            payment_mode: (document.getElementById('pay_mode')?.value || 'Cash').toLowerCase(),
            salesman: 'ADMIN',
            discount_total: parseFloat(document.getElementById('trade_disc_amt')?.value) || 0,
            tax_total: parseFloat(document.getElementById('total_tax_display')?.textContent?.replace(/[^0-9.]/g, '') || 0),
            sub_total: parseFloat(document.getElementById('sub_total_display')?.textContent?.replace(/[^0-9.]/g, '') || 0),
            net_total: parseFloat(document.getElementById('grand_total_display')?.textContent?.replace(/[^0-9.]/g, '') || 0),
            paid_amount: parseFloat(document.getElementById('grand_total_display')?.textContent?.replace(/[^0-9.]/g, '') || 0),
            notes: document.getElementById('bill_notes')?.value || '',
            items: posItems.map(item => ({
                product_id: item.id,
                description: item.name,
                qty: item.qty,
                unit_price: item.price,
                discount_pct: item.disc,
                tax_percent: item.tax_pct,
                total: item.total,
                batch_no: item.batch
            }))
        };

        // Show loader
        Swal.fire({
            title: mode === 'estimate' ? 'SAVING ESTIMATE...' : 'PROCESSING BILL...',
            html: 'Generating PDF and sending WhatsApp...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch('/api/v1/billing/invoices', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(billData)
            });

            const data = await res.json();

            if (res.ok) {
                if (mode === 'print' || mode === 'thermal') {
                    // Open print window with correct route
                    const invoiceId = data.data.invoice_id || data.data.id;
                    const printType = (mode === 'thermal') ? 'thermal' : 'pharmacy';

                    // Save preference for next time
                    localStorage.setItem('last_print_type', mode);

                    // Update loader message
                    Swal.update({
                        title: 'OPENING PRINT DIALOG...',
                        html: 'Please wait...'
                    });

                    // Open print dialog
                    window.open(`/billing/print?id=${invoiceId}&type=${printType}`, '_blank');

                    // Wait a moment then show success
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'SUCCESS',
                            html: `
                                <div class="mt-2 mb-4">Invoice saved successfully!</div>
                                <div class="d-grid gap-2">
                                    <a href="/billing/print?id=${invoiceId}&type=${printType}" target="_blank" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center">
                                        <i class="ri-printer-line me-2"></i> PRINT AGAIN
                                    </a>
                                    <button onclick="location.reload()" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                                        <i class="ri-refresh-line me-2"></i> START NEW BILL
                                    </button>
                                </div>
                            `,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                    }, 1000);
                }

                Swal.fire({
                    icon: 'success',
                    text: `Bill ${data.data.bill_no} saved successfully!`,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // New bill
                });
            } else {
                Swal.fire({ icon: 'error', text: data.message || 'Failed to save bill' });
            }
        } catch (error) {
            console.error('Save error:', error);
            Swal.fire({ icon: 'error', text: 'Network error while saving bill' });
        }
    }

</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>