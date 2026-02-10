/**
 * Purchase Quotations Management
 * Complete implementation with form modal, validation, and submission
 */

// Prevent re-declaration if already loaded (SPA navigation)
// Prevent re-declaration if already loaded (SPA navigation), unless init function is missing
if (typeof window.PurchaseQuotationsModule === 'undefined' || typeof window.initPurchaseQuotationsPage === 'undefined') {

    window.PurchaseQuotationsModule = {
        initialized: false
    };

    // Local variables for this module
    let selectedSupplierId = null;
    let suppliers = [];
    let products = [];

    /**
     * Validate that validUntil date is after quotationDate
     */
    function validateDates() {
        const quotationDateEl = document.getElementById('quotationDate');
        const validUntilEl = document.getElementById('validUntil');

        if (!quotationDateEl || !validUntilEl) return;

        const quotationDate = new Date(quotationDateEl.value);
        const validUntil = new Date(validUntilEl.value);

        if (validUntilEl.value && quotationDateEl.value && validUntil < quotationDate) {
            validUntilEl.setCustomValidity('Valid Until date must be after Quotation Date');
            if (typeof modalNotify !== 'undefined') {
                modalNotify.warning('Valid Until date must be after Quotation Date');
            }
        } else {
            validUntilEl.setCustomValidity('');
        }
    }

    // Make validateDates globally accessible
    window.validateDates = validateDates;

    function initializePage() {
        // Debug: Check what elements exist
        const supplierListEl = document.getElementById('supplierList');
        const quotationContentEl = document.getElementById('quotationContent');
        console.log('Purchase Quotations: Checking page elements', {
            supplierList: !!supplierListEl,
            quotationContent: !!quotationContentEl,
            pathname: window.location.pathname
        });

        // Guard: Only run on purchase quotations page
        // Check for quotationContent which is UNIQUE to this page
        if (!quotationContentEl) {
            console.log('Purchase Quotations script skipped - not on the right page');
            return;
        }

        console.log('Purchase Quotations: Initializing page...');

        fetchSuppliers();
        fetchProducts();

        // Search functionality - use event delegation to avoid duplicate listeners
        const searchInput = document.getElementById('supplierSearch');
        if (searchInput && !searchInput.dataset.listenerAttached) {
            searchInput.addEventListener('input', (e) => {
                filterSuppliers(e.target.value);
            });
            searchInput.dataset.listenerAttached = 'true';
        }

        // Date validation
        const quotationDateEl = document.getElementById('quotationDate');
        const validUntilEl = document.getElementById('validUntil');

        if (quotationDateEl && !quotationDateEl.dataset.listenerAttached) {
            quotationDateEl.addEventListener('change', validateDates);
            quotationDateEl.dataset.listenerAttached = 'true';
        }

        if (validUntilEl && !validUntilEl.dataset.listenerAttached) {
            validUntilEl.addEventListener('change', validateDates);
            validUntilEl.dataset.listenerAttached = 'true';
        }
    }

    // Make initializePage globally accessible for SPA navigation
    window.initPurchaseQuotationsPage = initializePage;

    // NOTE: Initialization is now handled by the inline script in purchase_quotations.php
    // which calls window.initPurchaseQuotationsPage() on every page load/navigation.
    // This prevents double API calls that would occur if we also listened for DOMContentLoaded.

    async function fetchSuppliers() {
        const list = document.getElementById('supplierList');
        if (!list) return;

        list.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const res = await fetch(`/api/v1/inventory/suppliers`);
            const data = await res.json();

            if (data.success && data.data.suppliers.length > 0) {
                suppliers = data.data.suppliers;
                renderSuppliers(suppliers);

                // Populate supplier dropdown in form
                populateSupplierDropdown();

                // Auto-select Melina supplier
                autoSelectMelina();
            } else {
                list.innerHTML = '<div class="text-center py-4 text-muted">No suppliers found.</div>';
            }
        } catch (e) {
            list.innerHTML = '<div class="text-center py-4 text-danger">Error loading suppliers.</div>';
        }
    }

    function autoSelectMelina() {
        // Find Melina supplier
        const melinaSupplier = suppliers.find(s =>
            s.gstin === 'MELINA-PRIM' ||
            s.gstin === 'REMOTE_SOURCE' ||
            s.name.toLowerCase().includes('melina')
        );

        if (melinaSupplier) {
            // Trigger selection
            setTimeout(() => {
                const melinaCard = document.querySelector(`[data-supplier-id="${melinaSupplier.supplier_id}"]`);
                if (melinaCard) {
                    melinaCard.click();
                }
            }, 100);
        }
    }

    async function fetchProducts() {
        try {
            const res = await fetch(`/api/v1/inventory/products`);
            const data = await res.json();
            if (data.success && data.data.products) {
                products = data.data.products;
            }
        } catch (e) {
            console.error('Error fetching products:', e);
        }
    }

    function populateSupplierDropdown() {
        const select = document.getElementById('supplierId');
        if (!select) return;

        select.innerHTML = '<option value="">Select Supplier</option>';
        suppliers.forEach(s => {
            const option = document.createElement('option');
            option.value = s.supplier_id;
            option.textContent = s.name;
            select.appendChild(option);
        });
    }

    function renderSuppliers(supplierList) {
        const list = document.getElementById('supplierList');
        if (!list) return;

        list.innerHTML = '';

        // Sort suppliers: Melina first, then others
        const sortedSuppliers = [...supplierList].sort((a, b) => {
            const aIsMelina = a.gstin === 'MELINA-PRIM' ||
                a.gstin === 'REMOTE_SOURCE' ||
                a.name.toLowerCase().includes('melina');
            const bIsMelina = b.gstin === 'MELINA-PRIM' ||
                b.gstin === 'REMOTE_SOURCE' ||
                b.name.toLowerCase().includes('melina');

            if (aIsMelina && !bIsMelina) return -1;
            if (!aIsMelina && bIsMelina) return 1;
            return 0;
        });

        sortedSuppliers.forEach(supplier => {
            const card = document.createElement('div');
            card.className = 'supplier-card p-2 border-bottom cursor-pointer hover-bg-light';
            card.dataset.supplierId = supplier.supplier_id;

            // Check if this is Melina supplier by GSTIN or name
            const isMelina = supplier.gstin === 'MELINA-PRIM' ||
                supplier.gstin === 'REMOTE_SOURCE' ||
                supplier.name.toLowerCase().includes('melina');

            card.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="fw-semibold fs-13 mb-1">
                        ${supplier.name}
                        ${isMelina ? '<span class="badge bg-primary-transparent ms-1 fs-10">Melina</span>' : ''}
                    </div>
                    <div class="text-muted fs-11">${supplier.gstin || 'No GST'}</div>
                </div>
            </div>
        `;

            card.addEventListener('click', () => selectSupplier(supplier.supplier_id, supplier.name, isMelina));
            list.appendChild(card);
        });
    }

    function filterSuppliers(searchTerm) {
        const filtered = suppliers.filter(s =>
            s.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            (s.gstin && s.gstin.toLowerCase().includes(searchTerm.toLowerCase()))
        );
        renderSuppliers(filtered);
    }

    async function selectSupplier(supplierId, supplierName, isMelina) {
        selectedSupplierId = supplierId;

        document.querySelectorAll('.supplier-card').forEach(card => {
            card.classList.remove('bg-primary-transparent');
        });
        event.currentTarget.classList.add('bg-primary-transparent');

        // Update title based on supplier type
        if (isMelina) {
            document.getElementById('quotationTitle').textContent = `${supplierName} - Products`;
        } else {
            document.getElementById('quotationTitle').textContent = `${supplierName} - Quotations`;
        }

        // Load appropriate content
        if (isMelina) {
            loadMelinaProducts();
        } else {
            loadSupplierQuotations(supplierId);
        }
    }

    async function loadSupplierQuotations(supplierId) {
        const content = document.getElementById('quotationContent');
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const res = await fetch(`/api/v1/quotations/supplier/${supplierId}`);
            const data = await res.json();

            if (data.success && data.data.quotations && data.data.quotations.length > 0) {
                renderQuotations(data.data.quotations);
            } else {
                content.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <p>No quotations found for this supplier.</p>
                    <button class="btn btn-primary mt-2" onclick="window.location.href='/purchase-quotations/create'">
                        <i class="ri-add-line me-1"></i>Create First Quotation
                    </button>
                </div>
            `;
            }
        } catch (e) {
            content.innerHTML = '<div class="text-center py-4 text-danger">Error loading quotations.</div>';
        }
    }

    function renderQuotations(quotations) {
        const content = document.getElementById('quotationContent');

        let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">${quotations.length} Quotation(s) Found</h6>
            <button class="btn btn-sm btn-primary" onclick="window.location.href='/purchase-quotations/create'">
                <i class="ri-add-line me-1"></i>New Quotation
            </button>
        </div>
        <div class="quotation-list">
    `;

        quotations.forEach(q => {
            const statusClass = q.status === 'active' ? 'success' : 'secondary';
            const isExpired = new Date(q.valid_until) < new Date();

            html += `
            <div class="card mb-3 border quotation-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">${q.quotation_no}</h6>
                            <small class="text-muted">
                                Date: ${q.quotation_date} | Valid Until: ${q.valid_until}
                                ${q.items_count ? ` | ${q.items_count} item(s)` : ''}
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-${statusClass}-transparent">${q.status}</span>
                            ${isExpired ? '<span class="badge bg-danger-transparent">Expired</span>' : ''}
                        </div>
                    </div>
                    ${q.remarks ? `<p class="text-muted fs-12 mb-2">${q.remarks}</p>` : ''}
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-primary-light" onclick="viewQuotationDetails(${q.quotation_id})">
                            <i class="ri-eye-line me-1"></i>View Details
                        </button>
                        <button class="btn btn-sm btn-danger-light" onclick="deleteQuotation(${q.quotation_id}, '${q.quotation_no}')">
                            <i class="ri-delete-bin-line me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
        });

        html += '</div>';
        content.innerHTML = html;
    }

    async function loadMelinaProducts() {
        const content = document.getElementById('quotationContent');
        content.innerHTML = `
        <div class="alert alert-info mb-3">
            <i class="ri-information-line me-2"></i>
            <strong>Melina Direct Access:</strong> Products are fetched directly from Melina's database with real-time pricing.
        </div>
        <div class="mb-3">
            <input type="text" class="form-control" id="melinaProductSearch" placeholder="Search products...">
        </div>
        <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
    `;

        try {
            const res = await fetch(`/api/v1/inventory/melina-products`);
            const data = await res.json();

            if (data.success && data.data.products && data.data.products.length > 0) {
                renderMelinaProducts(data.data.products);
            } else {
                content.innerHTML = `
                <div class="alert alert-info mb-3">
                    <i class="ri-information-line me-2"></i>
                    <strong>Melina Direct Access:</strong> Products are fetched directly from Melina's database with real-time pricing.
                </div>
                <div class="text-center py-4 text-muted">No products found in Melina database.</div>
            `;
            }
        } catch (e) {
            content.innerHTML = `
            <div class="alert alert-info mb-3">
                <i class="ri-information-line me-2"></i>
                <strong>Melina Direct Access:</strong> Products are fetched directly from Melina's database with real-time pricing.
            </div>
            <div class="alert alert-warning">
                <i class="ri-error-warning-line me-2"></i>
                Unable to connect to Melina database. Please try again later.
            </div>
        `;
        }
    }

    function renderMelinaProducts(products) {
        const content = document.getElementById('quotationContent');

        // Separate in-stock and out-of-stock products
        const inStockProducts = [];
        const outOfStockProducts = [];

        products.forEach(p => {
            const hasBatches = p.batches && p.batches.length > 0;
            const totalStock = hasBatches ? p.batches.reduce((sum, b) => sum + parseFloat(b.quantity || 0), 0) : 0;

            if (totalStock > 0) {
                inStockProducts.push(p);
            } else {
                outOfStockProducts.push(p);
            }
        });

        // Debug: Log first product to see batch data
        if (inStockProducts.length > 0) {
            console.log('Sample product with batches:', inStockProducts[0]);
            console.log('Total products:', { inStock: inStockProducts.length, outOfStock: outOfStockProducts.length });
        }

        let html = `
        <div class="alert alert-info mb-2 py-1 px-2">
            <i class="ri-information-line me-1 fs-12"></i>
            <small><strong>Melina Direct:</strong> Real-time pricing and batch information.</small>
        </div>
        <div class="mb-2">
            <input type="text" class="form-control form-control-sm" id="melinaProductSearch" placeholder="Search products..." onkeyup="filterMelinaProducts()">
        </div>
    `;

        if (inStockProducts.length === 0 && outOfStockProducts.length === 0) {
            html += `
            <div class="alert alert-warning">
                <i class="ri-information-line me-2"></i>
                No products found.
            </div>
        `;
        } else {
            html += `<div class="row g-2" id="melinaProductsContainer">`;

            // Render in-stock products first
            inStockProducts.forEach(p => {
                html += renderProductCard(p, false);
            });

            // Render out-of-stock products (disabled)
            outOfStockProducts.forEach(p => {
                html += renderProductCard(p, true);
            });

            html += `</div>`;
        }

        content.innerHTML = html;

        // Store all products with their batches
        window.melinaProducts = products.map(p => ({
            ...p,
            batches: p.batches || []
        }));
    }

    function renderProductCard(p, isOutOfStock) {
        const hasBatches = p.batches && p.batches.length > 0;

        // Filter batches with stock > 0
        const inStockBatches = hasBatches ? p.batches.filter(b => parseFloat(b.quantity || 0) > 0) : [];
        const hasInStockBatches = inStockBatches.length > 0;

        // Get the first in-stock batch or first batch if all out of stock
        const defaultBatch = hasInStockBatches ? inStockBatches[0] : (hasBatches ? p.batches[0] : null);

        // Use batch-specific prices if available, otherwise fall back to product prices
        const displayMrp = defaultBatch?.mrp || defaultBatch?.batch_mrp || p.mrp || 0;
        const displaySellingPrice = defaultBatch?.selling_price || defaultBatch?.batch_selling_price || p.selling_price || 0;
        const displayCost = displaySellingPrice || p.cost_price || 0;
        const totalStock = hasBatches ? p.batches.reduce((sum, b) => sum + parseFloat(b.quantity || 0), 0) : 0;

        return `
        <div class="col-12 melina-product-card">
            <div class="card border mb-0 ${isOutOfStock ? 'bg-light border-secondary' : ''}">
                <div class="card-body p-2 ${isOutOfStock ? 'opacity-75' : ''}">
                    <div class="row align-items-start g-2">
                        <div class="col-md-4">
                            <div class="fw-semibold fs-13 mb-1">
                                ${p.name}
                                ${isOutOfStock ? '<span class="badge bg-danger ms-1 fs-10">Out of Stock</span>' : ''}
                            </div>
                            <div class="mb-1">
                                <span class="badge bg-light text-dark fs-10">${p.sku || '-'}</span>
                            </div>
                            
                            ${!isOutOfStock && hasInStockBatches && inStockBatches.length > 1 ? `
                                <div>
                                    <label class="form-label fs-10 mb-1 text-muted">Batch:</label>
                                    <select class="form-select form-select-sm batch-selector fs-11" 
                                            data-product-id="${p.id}" 
                                            onchange="updateBatchDetails(${p.id}, this.value)">
                                        ${inStockBatches.map((batch, idx) => `
                                            <option value="${idx}">
                                                ${batch.batch_number} - Qty: ${parseFloat(batch.quantity || 0).toFixed(0)} (Exp: ${batch.exp_date || 'N/A'})
                                            </option>
                                        `).join('')}
                                    </select>
                                </div>
                            ` : !isOutOfStock && hasInStockBatches ? `
                                <div class="fs-11 text-muted">
                                    Batch: ${inStockBatches[0].batch_number}
                                </div>
                            ` : isOutOfStock && hasBatches ? `
                                <div class="fs-11 text-danger">
                                    All batches out of stock
                                </div>
                            ` : ''}
                        </div>
                        
                        <div class="col-md-8">
                            <div id="batch-details-${p.id}">
                                <div class="row g-1">
                                    <div class="col-4">
                                        <div class="text-muted fs-9">MRP</div>
                                        <div class="fw-semibold ${isOutOfStock ? 'text-muted' : 'text-success'} fs-13">₹${parseFloat(displayMrp).toFixed(2)}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted fs-9">Selling Price</div>
                                        <div class="fw-semibold ${isOutOfStock ? 'text-muted' : ''} fs-13">₹${parseFloat(displayCost).toFixed(2)}</div>
                                    </div>
                                    <div class="col-2">
                                        <div class="text-muted fs-9">GST</div>
                                        <div class="fs-12">${p.gst_rate || 0}%</div>
                                    </div>
                                    <div class="col-2">
                                        <div class="text-muted fs-9">Stock</div>
                                        <div>
                                            <span class="badge ${totalStock > 0 ? 'bg-success-transparent' : 'bg-danger'} fs-10">
                                                ${totalStock.toFixed(0)}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                ${defaultBatch ? `
                                    <div class="mt-1 pt-1 border-top">
                                        <div class="row g-1">
                                            <div class="col-3">
                                                <div class="fs-9 text-muted">Batch Qty</div>
                                                <div class="fs-11 fw-semibold ${isOutOfStock ? 'text-danger' : ''}">${parseFloat(defaultBatch.quantity || 0).toFixed(0)}</div>
                                            </div>
                                            <div class="col-3">
                                                <div class="fs-9 text-muted">Mfg</div>
                                                <div class="fs-11">${defaultBatch.mfg_date || '-'}</div>
                                            </div>
                                            <div class="col-3">
                                                <div class="fs-9 text-muted">Exp</div>
                                                <div class="fs-11">${defaultBatch.exp_date || '-'}</div>
                                            </div>
                                            <div class="col-3">
                                                <div class="fs-9 text-muted">QC</div>
                                                <div>
                                                    <span class="badge bg-${defaultBatch.qc_status === 'approved' ? 'success' : 'warning'}-transparent fs-9">
                                                        ${defaultBatch.qc_status || '-'}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    }

    // Update batch details when user selects a different batch
    function updateBatchDetails(productId, batchIndex) {
        const product = window.melinaProducts.find(p => p.id === productId);
        if (!product || !product.batches || !product.batches[batchIndex]) {
            console.error('Product or batch not found:', { productId, batchIndex });
            return;
        }

        const batch = product.batches[batchIndex];
        const totalStock = product.batches.reduce((sum, b) => sum + parseFloat(b.quantity || 0), 0);

        // Debug: Log batch data to see what prices are available
        console.log('=== Batch Change Debug ===');
        console.log('Product ID:', productId);
        console.log('Batch Index:', batchIndex);
        console.log('Selected batch:', batch);
        console.log('Product base prices:', {
            mrp: product.mrp,
            selling_price: product.selling_price,
            cost_price: product.cost_price
        });

        // Use batch-specific prices if available, otherwise fall back to product prices
        // Check both batch.mrp and batch.batch_mrp (in case backend uses different naming)
        const batchMrp = batch.mrp || batch.batch_mrp || product.mrp || 0;
        const batchSellingPrice = batch.selling_price || batch.batch_selling_price || product.selling_price || 0;
        const batchCost = batchSellingPrice || product.cost_price || 0;

        console.log('Displaying prices:', {
            mrp: batchMrp,
            selling_price: batchSellingPrice,
            cost: batchCost
        });
        console.log('=========================');

        const detailsHtml = `
        <div class="row g-1">
            <div class="col-4">
                <div class="text-muted fs-9">MRP</div>
                <div class="fw-semibold text-success fs-13">₹${parseFloat(batchMrp).toFixed(2)}</div>
            </div>
            <div class="col-4">
                <div class="text-muted fs-9">Selling Price</div>
                <div class="fw-semibold fs-13">₹${parseFloat(batchCost).toFixed(2)}</div>
            </div>
            <div class="col-2">
                <div class="text-muted fs-9">GST</div>
                <div class="fs-12">${product.gst_rate || 0}%</div>
            </div>
            <div class="col-2">
                <div class="text-muted fs-9">Stock</div>
                <div>
                    <span class="badge bg-success-transparent fs-10">
                        ${totalStock.toFixed(0)}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="mt-1 pt-1 border-top">
            <div class="row g-1">
                <div class="col-3">
                    <div class="fs-9 text-muted">Batch Qty</div>
                    <div class="fs-11 fw-semibold">${parseFloat(batch.quantity || 0).toFixed(0)}</div>
                </div>
                <div class="col-3">
                    <div class="fs-9 text-muted">Mfg</div>
                    <div class="fs-11">${batch.mfg_date || '-'}</div>
                </div>
                <div class="col-3">
                    <div class="fs-9 text-muted">Exp</div>
                    <div class="fs-11">${batch.exp_date || '-'}</div>
                </div>
                <div class="col-3">
                    <div class="fs-9 text-muted">QC</div>
                    <div>
                        <span class="badge bg-${batch.qc_status === 'approved' ? 'success' : 'warning'}-transparent fs-9">
                            ${batch.qc_status || '-'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    `;

        document.getElementById(`batch-details-${productId}`).innerHTML = detailsHtml;
    }

    function filterMelinaProducts() {
        const searchTerm = document.getElementById('melinaProductSearch')?.value.toLowerCase() || '';
        const container = document.getElementById('melinaProductsContainer');
        if (!container) return;

        const cards = container.getElementsByClassName('melina-product-card');
        for (let card of cards) {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(searchTerm) ? '' : 'none';
        }
    }

    // View and Delete Quotation Functions
    async function viewQuotationDetails(quotationId) {
        try {
            const res = await fetch(`/api/v1/quotations/${quotationId}`);
            const data = await res.json();

            if (data.success) {
                showQuotationDetailsModal(data.data);
            } else {
                modalNotify.error('Failed to load quotation details');
            }
        } catch (e) {
            console.error(e);
            modalNotify.error('Error loading quotation details');
        }
    }

    function showQuotationDetailsModal(data) {
        const { quotation, items } = data;

        let itemsHtml = '';
        items.forEach(item => {
            const subtotal = item.quantity * item.unit_price;
            const taxAmount = subtotal * (item.tax_percent / 100);
            const total = subtotal + taxAmount;

            itemsHtml += `
            <tr>
                <td>${item.product_name}<br><small class="text-muted">${item.sku}</small></td>
                <td class="text-center">${item.quantity}</td>
                <td>${item.unit || '-'}</td>
                <td class="text-end">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                <td class="text-center">${item.tax_percent}%</td>
                <td class="text-end">₹${parseFloat(item.mrp || 0).toFixed(2)}</td>
                <td class="text-end fw-semibold">₹${total.toFixed(2)}</td>
            </tr>
        `;
        });

        const modalHtml = `
        <div class="modal fade" id="quotationDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Quotation Details - ${quotation.quotation_no}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Supplier Information</h6>
                                <p class="mb-1"><strong>${quotation.supplier_name}</strong></p>
                                <p class="mb-1 text-muted">${quotation.supplier_gstin || 'No GST'}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1"><strong>Quotation Date:</strong> ${quotation.quotation_date}</p>
                                <p class="mb-1"><strong>Valid Until:</strong> ${quotation.valid_until}</p>
                                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-${quotation.status === 'active' ? 'success' : 'secondary'}-transparent">${quotation.status}</span></p>
                            </div>
                        </div>
                        
                        ${quotation.remarks ? `<div class="alert alert-info"><strong>Remarks:</strong> ${quotation.remarks}</div>` : ''}
                        
                        <h6 class="mb-3">Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th>Unit</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-center">Tax %</th>
                                        <th class="text-end">MRP</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

        const existingModal = document.getElementById('quotationDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = new bootstrap.Modal(document.getElementById('quotationDetailsModal'));
        modal.show();
    }

    async function deleteQuotation(quotationId, quotationNo) {
        modalNotify.confirm(`Are you sure you want to delete quotation ${quotationNo}?`, {
            title: 'Confirm Delete',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            onConfirm: async () => {
                try {
                    const res = await fetch(`/api/v1/quotations/${quotationId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await res.json();

                    if (data.success) {
                        modalNotify.success('Quotation deleted successfully');
                        if (selectedSupplierId) {
                            loadSupplierQuotations(selectedSupplierId);
                        }
                    } else {
                        modalNotify.error('Failed to delete quotation: ' + (data.message || 'Unknown error'));
                    }
                } catch (e) {
                    console.error(e);
                    modalNotify.error('Error deleting quotation');
                }
            }
        });
    }

} // End of SPA guard if block

// NOTE: Initialization is now handled by the inline script in purchase_quotations.php
// which calls window.initPurchaseQuotationsPage() directly on every page load/navigation.
// This approach is more reliable than event listeners for SPA navigation.
