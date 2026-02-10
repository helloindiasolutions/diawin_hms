<?php
$pageTitle = "Purchase Quotations";
ob_start();
?>

<!-- Main Content Header -->
<div class="pos-content-header mb-4 px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="page-title fw-semibold fs-20 mb-1">Purchase Quotations</h1>
            <p class="text-muted mb-0 fs-13">Manage supplier quotations and compare prices for purchase orders.</p>
        </div>
    </div>
</div>

<!-- 30/70 Split Layout -->
<div class="row">
    <!-- Left Sidebar - Supplier List (30%) - STICKY -->
    <div class="col-xl-3 col-lg-4 mb-3">
        <div class="card custom-card position-sticky" style="top: 70px;">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <div class="card-title mb-0 fs-14">Suppliers</div>
                <button class="btn btn-sm btn-primary btn-wave py-1 px-2"
                    onclick="window.location.href='/purchase-quotations/create'">
                    <i class="ri-add-line"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <!-- Search Input -->
                <div class="p-2 border-bottom">
                    <input type="text" class="form-control form-control-sm" id="supplierSearch"
                        placeholder="Search suppliers...">
                </div>

                <!-- Supplier List -->
                <div id="supplierList" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <!-- Populated via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Right Content - Quotation Details (70%) - SCROLLABLE -->
    <div class="col-xl-9 col-lg-8">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <div class="card-title mb-0 fs-14" id="quotationTitle">Select a supplier to view quotations</div>
                <div id="quotationActions" style="display: none;">
                    <button class="btn btn-sm btn-success py-1 px-2" id="btnCompareQuotations" style="display: none;">
                        <i class="ri-file-compare-line me-1"></i>Compare
                    </button>
                </div>
            </div>
            <div class="card-body p-2" id="quotationContent">
                <div class="text-center py-5 text-muted">
                    <i class="ri-file-list-3-line fs-48 mb-3 d-block opacity-50"></i>
                    <p>Select a supplier from the left panel to view their quotations</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quotation Form Modal -->
<div class="modal fade" id="quotationFormModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quotationFormTitle">New Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quotationForm">
                <div class="modal-body">
                    <input type="hidden" id="quotationId" name="quotation_id">

                    <!-- Quotation Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select" id="supplierId" name="supplier_id" required>
                                <option value="">Select Supplier</option>
                            </select>
                            <div class="invalid-feedback">Please select a supplier</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quotation Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quotationNo" name="quotation_no" required>
                            <div class="invalid-feedback">Please enter quotation number</div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Quotation Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="quotationDate" name="quotation_date" required>
                            <div class="invalid-feedback">Please enter quotation date</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="validUntil" name="valid_until" required>
                            <div class="invalid-feedback">Valid until must be after quotation date</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Supplier Reference</label>
                            <input type="text" class="form-control" id="supplierReference" name="supplier_reference">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="border-top pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Quotation Items <span class="text-danger">*</span></h6>
                            <button type="button" class="btn btn-sm btn-primary" id="btnAddItem">
                                <i class="ri-add-line me-1"></i>Add Item
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Product <span class="text-danger">*</span></th>
                                        <th style="width: 10%;">Qty <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">Unit Price <span class="text-danger">*</span></th>
                                        <th style="width: 10%;">Tax %</th>
                                        <th style="width: 15%;">MRP</th>
                                        <th style="width: 15%;">Total</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Items will be added here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="invalid-feedback d-block" id="itemsError" style="display: none !important;">
                            Please add at least one item
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveQuotation">
                        <i class="ri-save-line me-1"></i>Save Quotation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script src="/assets/js/purchase-quotations.js"></script>
<script>
    // Initialize immediately - this runs on BOTH initial load AND SPA navigation
    // The external script defines the module once, this inline script triggers init
    (function initPurchaseQuotationsOnLoad() {
        // Check if we're on the right page
        if (!document.getElementById('quotationContent')) {
            console.log('Purchase Quotations inline init skipped - not on page');
            return;
        }

        console.log('Purchase Quotations: Triggering initialization...');

        const runInit = () => {
            if (typeof window.initPurchaseQuotationsPage === 'function') {
                window.initPurchaseQuotationsPage();
                console.log('Purchase Quotations: Initialization triggered.');
            } else {
                console.error('Purchase Quotations: initPurchaseQuotationsPage function missing even after script load.');
            }
        };

        // If function exists, run it
        if (typeof window.initPurchaseQuotationsPage === 'function') {
            runInit();
        } else {
            // Function missing: Dynamically load the script
            console.log('Purchase Quotations: External script missing, loading dynamically...');
            const script = document.createElement('script');
            script.src = '/assets/js/purchase-quotations.js?v=' + new Date().getTime(); // Cache bust
            script.onload = () => {
                console.log('Purchase Quotations: External script loaded successfully.');
                // Small delay to ensure script execution completes
                setTimeout(runInit, 50);
            };
            script.onerror = () => {
                console.error('Purchase Quotations: Failed to load external script.');
            };
            document.body.appendChild(script);
        }
    })();
</script>

<style>
    .supplier-card {
        transition: background-color 0.2s;
    }

    .supplier-card:hover {
        background-color: #f8f9fa;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .hover-bg-light:hover {
        background-color: #f8f9fa;
    }
</style>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>