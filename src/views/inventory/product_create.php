<?php
$pageTitle = "Add New Product";
ob_start();
?>

<!-- Main Content Header -->
<div class="pos-content-header mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="page-title fw-semibold fs-20 mb-1">Add New Product</h1>
            <p class="text-muted mb-0 fs-13">Register new pharmaceutical items and medication stock records.</p>
        </div>
        <div>
            <button class="btn btn-sm btn-pink" onclick="location.href='/products'"
                style="background: #ff49cd; color: #fff; border: none; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 6px 16px; border-radius: 4px;">
                <i class="ri-arrow-left-line me-1"></i> Back to Products
            </button>
        </div>
    </div>
</div>

<div class="product-form-wrapper">
    <div class="quick-entry-container">
        <form id="productForm" class="needs-validation" novalidate method="POST" action="/api/v1/inventory/products">
            <?= csrfField() ?>

            <!-- Quick Entry Grid -->
            <div class="quick-entry-grid">
                <!-- Row 1 -->
                <div class="entry-field">
                    <label>Product Name <span class="req">*</span></label>
                    <input type="text" name="product_name" id="product_name" placeholder="Enter product name" required
                        autofocus>
                </div>

                <div class="entry-field">
                    <label>SKU/Barcode <span class="req">*</span></label>
                    <input type="text" name="sku" id="sku" placeholder="Enter SKU" required>
                </div>

                <div class="entry-field">
                    <label>Category</label>
                    <select name="category" id="category">
                        <option value="">Select Category</option>
                    </select>
                </div>

                <div class="entry-field">
                    <label>HSN Code</label>
                    <input type="text" name="hsn_code" id="hsn_code" placeholder="HSN">
                </div>

                <!-- Row 2 -->
                <div class="entry-field">
                    <label>Unit</label>
                    <select name="unit" id="unit">
                        <option value="">Select Unit</option>
                    </select>
                </div>

                <div class="entry-field">
                    <label>Tax Rate (%)</label>
                    <select name="tax_rate" id="tax_rate">
                        <option value="0">0%</option>
                        <option value="5">5%</option>
                        <option value="12">12%</option>
                        <option value="18" selected>18%</option>
                        <option value="28">28%</option>
                    </select>
                </div>

                <div class="entry-field">
                    <label>Reorder Level</label>
                    <input type="number" name="reorder_level" id="reorder_level" value="10">
                </div>

                <div class="entry-field span-1">
                    <label>Description</label>
                    <input type="text" name="description" id="description" placeholder="Product description">
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="left-actions">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="is_active" id="is_active" checked>
                        <span>Active Product</span>
                    </label>
                </div>
                <div class="right-actions">
                    <button type="button" class="btn-action btn-cancel" onclick="location.href='/products'">
                        <i class="ri-close-line"></i> CANCEL (ESC)
                    </button>
                    <button type="submit" class="btn-action btn-save">
                        <i class="ri-save-line"></i> SAVE & NEW (F2)
                    </button>
                    <button type="button" class="btn-action btn-save-close" onclick="saveAndClose()">
                        <i class="ri-check-line"></i> SAVE & CLOSE (F8)
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<style>
    .page-header {
        background: #fff;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    }

    .page-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }

    .product-form-wrapper {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 1.5rem;
    }

    .quick-entry-container {
        max-width: 100%;
        margin: 0;
    }

    .quick-entry-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem 1rem;
        margin-bottom: 1.5rem;
    }

    .entry-field {
        display: flex;
        flex-direction: column;
    }

    .entry-field.span-2 {
        grid-column: span 2;
    }

    .entry-field label {
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .entry-field .req {
        color: #ef4444;
    }

    .entry-field input,
    .entry-field select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        background: #fff;
        border: 1px solid var(--erp-border);
        border-radius: 3px;
        transition: all 0.15s;
    }

    .entry-field input:focus,
    .entry-field select:focus {
        outline: none;
        border-color: var(--erp-primary);
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.1);
    }

    .entry-field input::placeholder {
        color: #9ca3af;
        font-weight: 500;
    }

    .entry-field select {
        cursor: pointer;
    }

    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1.5rem;
        border-top: 2px solid #e5e7eb;
    }

    .left-actions {
        display: flex;
        align-items: center;
    }

    .checkbox-inline {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        margin: 0;
    }

    .checkbox-inline input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .checkbox-inline span {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .right-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-action {
        padding: 0.5rem 1.25rem;
        font-size: 11px;
        font-weight: 800;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .btn-cancel {
        background: #fff;
        color: #6b7280;
        border: 1px solid var(--erp-border);
    }

    .btn-cancel:hover {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-save {
        background: #3b82f6;
        color: #fff;
    }

    .btn-save:hover {
        background: #2563eb;
    }

    .btn-save-close {
        background: var(--erp-primary);
        color: #fff;
    }

    .btn-save-close:hover {
        background: var(--erp-primary-dark);
    }

    @media (max-width: 1400px) {
        .quick-entry-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .quick-entry-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .quick-entry-grid {
            grid-template-columns: 1fr;
        }

        .action-bar {
            flex-direction: column;
            gap: 1rem;
        }

        .right-actions {
            width: 100%;
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }

    /* Parent Layout Overrides */
    .main-content.app-content {
        padding-inline: 1.5rem !important;
        margin-block-start: 8.5rem !important;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    let saveMode = 'close';

    document.getElementById('productForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.is_active = formData.has('is_active');

        try {
            const response = await fetch('/api/v1/inventory/products', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message || 'Product saved successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    if (saveMode === 'new') {
                        // Clear form for new entry
                        document.getElementById('productForm').reset();
                        document.getElementById('product_name').focus();
                    } else {
                        // Go back to products list
                        window.location.href = '/products';
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to save product'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while saving the product'
            });
        }
    });

    function saveAndClose() {
        saveMode = 'close';
        document.getElementById('productForm').requestSubmit();
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F2') {
            e.preventDefault();
            saveMode = 'new';
            document.getElementById('productForm').requestSubmit();
        }
        if (e.key === 'F8') {
            e.preventDefault();
            saveAndClose();
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            window.location.href = '/products';
        }
    });

    // Auto-focus first field
    document.getElementById('product_name').focus();

    // Load dynamic categories
    async function loadCategories() {
        try {
            const res = await fetch('/api/v1/inventory/categories?status=active');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('category');
                // Keep default option
                select.innerHTML = '<option value="">Select Category</option>';
                data.data.categories.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    select.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Failed to load categories');
        }
    }

    // Load dynamic units
    async function loadUnits() {
        try {
            const res = await fetch('/api/v1/inventory/units?status=active');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('unit');
                // Keep default option
                select.innerHTML = '<option value="">Select Unit</option>';
                data.data.units.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.code;
                    opt.textContent = `${u.name} (${u.code})`;
                    select.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Failed to load units');
        }
    }

    // Initialize immediately - works for both initial load AND SPA navigation
    (function initProductCreatePage() {
        const form = document.getElementById('productForm');
        if (!form) {
            console.log('Product Create script skipped - not on product create page');
            return;
        }
        console.log('Product Create page: Initializing...');
        loadCategories();
        loadUnits();
    })();
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>