<?php
/**
 * Create New Estimate
 * Commercial Professional Interface
 */
$pageTitle = "Create Estimate";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Create New Estimate</h2>
        <span class="text-muted fs-12">Generate medical and surgical cost estimates for patients</span>
    </div>
</div>

<div class="row">
    <!-- Left: Estimate Creator -->
    <div class="col-xl-8 col-lg-7">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Estimate Details</div>
            </div>
            <div class="card-body p-4">
                <!-- Patient Selector -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Select Patient <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-user-search-line"></i></span>
                            <input type="text" class="form-control" placeholder="Search by name, MRN or Phone #">
                            <button class="btn btn-primary-light">Select</button>
                        </div>
                    </div>
                </div>

                <hr class="my-4 op-1">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estimate Date</label>
                        <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Validity (Days)</label>
                        <select class="form-select">
                            <option>7 Days</option>
                            <option selected>15 Days</option>
                            <option>30 Days</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Estimate For (Procedure / Surgery)</label>
                        <input type="text" class="form-control" placeholder="e.g. Total Hip Replacement (Left)">
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mt-5">
                    <table class="table table-bordered" id="estimateItemsTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Service / Item Description</th>
                                <th style="width: 120px;">Unit Price</th>
                                <th style="width: 80px;">Qty</th>
                                <th style="width: 150px;">Total (₹)</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="estimateItemsBody">
                            <!-- Dynamic rows will be added here -->
                        </tbody>
                    </table>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="addEstimateRow()">
                        <i class="ri-add-line me-1"></i>Add Row
                    </button>
                    <button class="btn btn-sm btn-outline-info mt-2 ms-2" onclick="showItemSelector()">
                        <i class="ri-search-line me-1"></i>Select from Items
                    </button>
                    <button class="btn btn-sm btn-outline-success mt-2 ms-2" onclick="showPackageSelector()">
                        <i class="ri-gift-line me-1"></i>Import from Package
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Summary & Action -->
    <div class="col-xl-4 col-lg-5">
        <div class="card custom-card bg-light border-0">
            <div class="card-header border-bottom">
                <div class="card-title text-dark">Estimate Summary</div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Sub-Total</span>
                    <span class="fw-bold fs-15" id="subTotal">₹ 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Consumables Margin</span>
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <input type="number" class="form-control form-control-sm" id="marginPercent" value="10" min="0" max="100" onchange="calculateTotal()">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-3 text-danger">
                    <span class="text-muted">Margin Amount</span>
                    <span class="fw-bold fs-15" id="marginAmount">₹ 0.00</span>
                </div>
                <hr class="op-1">
                <div class="d-flex justify-content-between mb-0">
                    <h5 class="fw-bold mb-0">Grand Total</h5>
                    <h5 class="fw-bold mb-0 text-primary" id="grandTotal">₹ 0.00</h5>
                </div>
                <div class="mt-4">
                    <label class="form-label fw-bold">Staff Remarks (Internal)</label>
                    <textarea class="form-control fs-12 mb-4" rows="3" id="staffRemarks"
                        placeholder="Notes for administrative review..."></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="generateEstimate()">
                        <i class="ri-printer-line me-2"></i>Generate PDF Estimate
                    </button>
                    <button class="btn btn-outline-success" onclick="sendViaWhatsApp()">
                        <i class="ri-whatsapp-line me-2"></i>Send via WhatsApp
                    </button>
                    <button class="btn btn-outline-primary" onclick="sendViaEmail()">
                        <i class="ri-mail-line me-2"></i>Send via Email
                    </button>
                </div>
            </div>
        </div>

        <div class="alert alert-warning-transparent d-flex align-items-center mb-0" role="alert">
            <i class="ri-alert-line me-2 fs-18"></i>
            <div>
                Estimates are only valid for <span id="validityDisplay">15</span> days and subject to clinical changes.
            </div>
        </div>
    </div>
</div>

<!-- Item Selector Modal -->
<div class="modal fade" id="itemSelectorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Items/Services</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="itemSearchInput" placeholder="Search items...">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemSelectorBody">
                            <tr><td colspan="4" class="text-center">Loading items...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Package Selector Modal -->
<div class="modal fade" id="packageSelectorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="packageSelectorBody">
                    <div class="col-12 text-center">Loading packages...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
// Estimate Data
let estimateItems = [];
let availableItems = [];
let availablePackages = [];
let rowCounter = 0;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadAvailableItems();
    loadAvailablePackages();
    addEstimateRow(); // Add one empty row to start
});


// Load available items from database
async function loadAvailableItems() {
    try {
        const response = await fetch('/api/v1/billing/items');
        const data = await response.json();
        if (data.success) {
            availableItems = data.data;
        }
    } catch (error) {
        console.error('Error loading items:', error);
    }
}

// Load available packages from database
async function loadAvailablePackages() {
    try {
        const response = await fetch('/api/v1/billing/packages');
        const data = await response.json();
        if (data.success) {
            availablePackages = data.data;
        }
    } catch (error) {
        console.error('Error loading packages:', error);
    }
}

// Add new estimate row
function addEstimateRow(itemData = null) {
    rowCounter++;
    const tbody = document.getElementById('estimateItemsBody');
    const row = document.createElement('tr');
    row.id = `row-${rowCounter}`;
    row.dataset.rowId = rowCounter;
    
    const description = itemData?.name || '';
    const unitPrice = itemData?.price || 0;
    const qty = 1;
    
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm item-description" 
                   value="${description}" placeholder="Enter item description"
                   onchange="calculateRowTotal(${rowCounter})">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-price" 
                   value="${unitPrice}" min="0" step="0.01"
                   onchange="calculateRowTotal(${rowCounter})">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-qty" 
                   value="${qty}" min="1"
                   onchange="calculateRowTotal(${rowCounter})">
        </td>
        <td class="text-end fw-bold item-total">₹ ${formatCurrency(unitPrice * qty)}</td>
        <td class="text-center">
            <button class="btn btn-sm text-danger" onclick="removeRow(${rowCounter})" title="Remove">
                <i class="ri-delete-bin-line"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    calculateTotal();
}

// Calculate row total
function calculateRowTotal(rowId) {
    const row = document.querySelector(`#row-${rowId}`);
    if (!row) return;
    
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const total = price * qty;
    
    row.querySelector('.item-total').textContent = '₹ ' + formatCurrency(total);
    calculateTotal();
}

// Remove row
function removeRow(rowId) {
    const row = document.querySelector(`#row-${rowId}`);
    if (row) {
        row.remove();
        calculateTotal();
    }
}

// Calculate totals
function calculateTotal() {
    let subTotal = 0;
    
    document.querySelectorAll('#estimateItemsBody tr').forEach(row => {
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
        subTotal += price * qty;
    });
    
    const marginPercent = parseFloat(document.getElementById('marginPercent').value) || 0;
    const marginAmount = (subTotal * marginPercent) / 100;
    const grandTotal = subTotal + marginAmount;
    
    document.getElementById('subTotal').textContent = '₹ ' + formatCurrency(subTotal);
    document.getElementById('marginAmount').textContent = '₹ ' + formatCurrency(marginAmount);
    document.getElementById('grandTotal').textContent = '₹ ' + formatCurrency(grandTotal);
}


// Show item selector modal
function showItemSelector() {
    const modal = new bootstrap.Modal(document.getElementById('itemSelectorModal'));
    modal.show();
    
    // Populate items
    const tbody = document.getElementById('itemSelectorBody');
    if (availableItems.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No items available</td></tr>';
        return;
    }
    
    tbody.innerHTML = availableItems.map(item => `
        <tr>
            <td>${item.name}</td>
            <td>${item.category || 'General'}</td>
            <td class="fw-semibold">₹ ${formatCurrency(item.price)}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="selectItem(${item.item_id})">
                    <i class="ri-add-line"></i> Add
                </button>
            </td>
        </tr>
    `).join('');
    
    // Search functionality
    document.getElementById('itemSearchInput').oninput = function(e) {
        const search = e.target.value.toLowerCase();
        const filtered = availableItems.filter(item => 
            item.name.toLowerCase().includes(search) || 
            (item.category && item.category.toLowerCase().includes(search))
        );
        
        tbody.innerHTML = filtered.map(item => `
            <tr>
                <td>${item.name}</td>
                <td>${item.category || 'General'}</td>
                <td class="fw-semibold">₹ ${formatCurrency(item.price)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="selectItem(${item.item_id})">
                        <i class="ri-add-line"></i> Add
                    </button>
                </td>
            </tr>
        `).join('');
    };
}

// Select item from modal
function selectItem(itemId) {
    const item = availableItems.find(i => i.item_id == itemId);
    if (item) {
        addEstimateRow(item);
        bootstrap.Modal.getInstance(document.getElementById('itemSelectorModal')).hide();
    }
}

// Show package selector modal
function showPackageSelector() {
    const modal = new bootstrap.Modal(document.getElementById('packageSelectorModal'));
    modal.show();
    
    const container = document.getElementById('packageSelectorBody');
    if (availablePackages.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted">No packages available</div>';
        return;
    }
    
    container.innerHTML = availablePackages.map(pkg => `
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">${pkg.name}</h6>
                    <p class="card-text text-muted fs-12">${pkg.description || ''}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary">₹ ${formatCurrency(pkg.price)}</span>
                        <button class="btn btn-sm btn-primary" onclick="selectPackage(${pkg.package_id})">
                            Import
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Select package and import items
async function selectPackage(packageId) {
    try {
        const response = await fetch(`/api/v1/billing/packages/${packageId}`);
        const data = await response.json();
        
        if (data.success && data.data.items) {
            // Clear existing rows
            document.getElementById('estimateItemsBody').innerHTML = '';
            
            // Add package items
            data.data.items.forEach(item => {
                addEstimateRow({
                    name: item.name,
                    price: item.price
                });
            });
            
            bootstrap.Modal.getInstance(document.getElementById('packageSelectorModal')).hide();
        }
    } catch (error) {
        console.error('Error loading package:', error);
        alert('Failed to load package items');
    }
}


// Generate estimate
async function generateEstimate() {
    // Collect estimate data
    const items = [];
    document.querySelectorAll('#estimateItemsBody tr').forEach(row => {
        const description = row.querySelector('.item-description').value;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        
        if (description && price > 0) {
            items.push({ description, price, qty, total: price * qty });
        }
    });
    
    if (items.length === 0) {
        alert('Please add at least one item to the estimate');
        return;
    }
    
    const estimateData = {
        patient_id: null, // TODO: Get from patient selector
        estimate_date: document.querySelector('input[type="date"]').value,
        validity_days: parseInt(document.querySelector('select').value),
        procedure: document.querySelector('input[placeholder*="Total Hip"]').value,
        items: items,
        margin_percent: parseFloat(document.getElementById('marginPercent').value) || 0,
        staff_remarks: document.getElementById('staffRemarks').value,
        sub_total: parseFloat(document.getElementById('subTotal').textContent.replace(/[₹,]/g, '')),
        margin_amount: parseFloat(document.getElementById('marginAmount').textContent.replace(/[₹,]/g, '')),
        grand_total: parseFloat(document.getElementById('grandTotal').textContent.replace(/[₹,]/g, ''))
    };
    
    try {
        const response = await fetch('/api/v1/estimates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(estimateData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Estimate generated successfully!');
            // Redirect or download PDF
            window.location.href = `/estimates/${data.data.estimate_id}/pdf`;
        } else {
            alert('Error: ' + (data.message || 'Failed to generate estimate'));
        }
    } catch (error) {
        console.error('Error generating estimate:', error);
        alert('Failed to generate estimate. Please try again.');
    }
}

// Send via WhatsApp
function sendViaWhatsApp() {
    alert('WhatsApp integration coming soon!');
}

// Send via Email
function sendViaEmail() {
    alert('Email integration coming soon!');
}

// Utility function
function formatCurrency(amount) {
    return parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Update validity display when changed
document.addEventListener('DOMContentLoaded', function() {
    const validitySelect = document.querySelector('select');
    if (validitySelect) {
        validitySelect.addEventListener('change', function() {
            const days = parseInt(this.value);
            document.getElementById('validityDisplay').textContent = days;
        });
    }
});
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>
