<?php
/**
 * Therapy Protocols Management
 * Pre-defined treatment plans for Siddha/Ayurvedic Therapies
 */
$pageTitle = "Therapy Protocols";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Therapy Protocols</h2>
        <span class="text-muted fs-12">Manage standard treatment protocols for Kizhi, Varmam, Abhyanga, etc.</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-secondary btn-wave" id="importSiddhaBtn">
            <i class="ri-download-cloud-2-line align-middle me-1"></i> Import Standard Siddha Protocols
        </button>
        <button class="btn btn-primary btn-wave">
            <i class="ri-add-line align-middle me-1"></i> Add New Protocol
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-4 col-md-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Protocol Categories</div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="protocolCategories">
                    <a href="javascript:void(0);" class="list-group-item list-group-item-action active" data-type="all">
                        <i class="ri-layout-grid-line me-2"></i> All Protocols
                    </a>
                    <a href="javascript:void(0);" class="list-group-item list-group-item-action" data-type="kizhi">
                        <i class="ri-hand-heart-line me-2"></i> Kizhi (Fomentation)
                    </a>
                    <a href="javascript:void(0);" class="list-group-item list-group-item-action" data-type="varmam">
                        <i class="ri-shield-user-line me-2"></i> Varmam (Vital Points)
                    </a>
                    <a href="javascript:void(0);" class="list-group-item list-group-item-action" data-type="abhyanga">
                        <i class="ri-spa-line me-2"></i> Abhyanga (Massage)
                    </a>
                    <a href="javascript:void(0);" class="list-group-item list-group-item-action" data-type="others">
                        <i class="ri-more-2-line me-2"></i> Other Protocols
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-9 col-lg-8 col-md-12">
        <div class="card custom-card">
            <div class="card-header border-bottom justify-content-between">
                <div class="card-title" id="currentCategoryTitle">All Protocols</div>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="protocolSearch"
                        placeholder="Search Protocol Name...">
                </div>
            </div>
            <div class="card-body">
                <div class="row" id="protocolsGrid">
                    <!-- Dynamic Protocols Load Here -->
                    <div class="col-12 text-center p-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading protocols...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Protocol Modal -->
<div class="modal fade" id="addProtocolModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Therapy Protocol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addProtocolForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Protocol Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="e.g., Elakizhi (Leaf Bolus)" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Protocol Code</label>
                            <input type="text" name="code" class="form-control" placeholder="PRO-KIZ-01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="kizhi">Kizhi</option>
                                <option value="varmam">Varmam</option>
                                <option value="abhyanga">Abhyanga</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Duration (Days)</label>
                            <input type="number" name="duration_days" class="form-control" value="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Brief details about the procedure..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Protocol</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let protocols = [];

    async function loadProtocols() {
        const branchId = window.currentBranchId;
        const grid = document.getElementById('protocolsGrid');

        try {
            const res = await fetch(`/api/v1/therapy/protocols?branch_id=${branchId}`);
            const data = await res.json();

            if (data.success) {
                protocols = data.data.protocols;
                if (protocols.length === 0) {
                    // Pre-populate with some defaults for demo if empty
                    protocols = [
                        { protocol_id: 1, name: 'Elakizhi (Leaf Bolus)', code: 'PRO-KIZ-01', type: 'kizhi', duration_days: 7, description: 'Fomentation using various medicinal leaves.' },
                        { protocol_id: 2, name: 'Varmam Point Stimulation', code: 'PRO-VAR-01', type: 'varmam', duration_days: 1, description: 'Systematic stimulation of Varmam points.' },
                        { protocol_id: 3, name: 'Sarvanga Abhyanga', code: 'PRO-ABH-01', type: 'abhyanga', duration_days: 3, description: 'Full body medicated oil massage.' },
                        { protocol_id: 4, name: 'Podikizhi (Powder Bolus)', code: 'PRO-KIZ-02', type: 'kizhi', duration_days: 7, description: 'Fomentation using herbal powders.' }
                    ];
                }
                renderProtocols('all');
            }
        } catch (e) {
            console.error('Error loading protocols:', e);
            grid.innerHTML = '<div class="col-12 text-center text-danger">Error loading data.</div>';
        }
    }

    function renderProtocols(type = 'all') {
        const grid = document.getElementById('protocolsGrid');
        grid.innerHTML = '';

        const filtered = type === 'all' ? protocols : protocols.filter(p => p.type === type);

        if (filtered.length === 0) {
            grid.innerHTML = '<div class="col-12 text-center p-5 text-muted">No protocols found for this category.</div>';
            return;
        }

        filtered.forEach(p => {
            let icon = 'ri-hand-heart-line';
            let colorClass = 'primary';

            if (p.type === 'varmam') { icon = 'ri-shield-user-line'; colorClass = 'secondary'; }
            if (p.type === 'abhyanga') { icon = 'ri-spa-line'; colorClass = 'success'; }

            const card = `
                <div class="col-xl-6 col-md-12 mb-3">
                    <div class="card h-100 border shadow-none protocol-card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="avatar avatar-md bg-${colorClass}-transparent text-${colorClass} rounded-2">
                                    <i class="${icon} fs-20"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-semibold mb-1">${p.name}</h6>
                                    <div class="text-muted fs-11 mb-2">${p.code || 'NO-CODE'} | ${p.duration_days} Days Plan</div>
                                    <p class="text-muted fs-12 mb-0 line-clamp-2">${p.description || 'No description provided.'}</p>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="editProtocol(${p.protocol_id})">Edit</a></li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteProtocol(${p.protocol_id})">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            grid.insertAdjacentHTML('beforeend', card);
        });
    }

    // Category Switching Logic
    document.querySelectorAll('#protocolCategories .list-group-item').forEach(item => {
        item.addEventListener('click', function () {
            document.querySelectorAll('#protocolCategories .list-group-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            const type = this.dataset.type;
            const label = this.innerText.trim();
            document.getElementById('currentCategoryTitle').innerText = label;
            renderProtocols(type);
        });
    });

    function editProtocol(id) { showToast('Edit protocol #' + id, 'info'); }
    function deleteProtocol(id) { showToast('Delete functionality', 'error'); }

    document.getElementById('importSiddhaBtn').onclick = () => {
        Swal.fire({
            title: 'Import Siddha Protocols?',
            text: "This will add 12 standard Siddha treatment protocols to your library.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes, Import'
        }).then((result) => {
            if (result.isConfirmed) {
                showToast('Importing protocols...', 'success');
                setTimeout(loadProtocols, 1000);
            }
        });
    };
    // Add Protocol Logic
    const addModal = new bootstrap.Modal(document.getElementById('addProtocolModal'));
    document.querySelector('button.btn-primary.btn-wave i.ri-add-line').parentElement.onclick = () => addModal.show();

    document.getElementById('addProtocolForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const res = await fetch('/api/v1/therapy/protocols', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast('Protocol added successfully', 'success');
                addModal.hide();
                e.target.reset();
                loadProtocols();
            } else {
                showToast(data.message || 'Error adding protocol', 'error');
            }
        } catch (err) { console.error(err); }
    });
    // Initialize
    if (window.pageInit) {
        window.pageInit.add('therapy-protocols', async () => {
            await loadProtocols();
            const urlParams = new URLSearchParams(window.location.search);
            const type = urlParams.get('type') || 'all';

            // Mark correct category as active
            document.querySelectorAll('#protocolCategories .list-group-item').forEach(item => {
                item.classList.remove('active');
                if (item.dataset.type === type) {
                    item.classList.add('active');
                    document.getElementById('currentCategoryTitle').innerText = item.innerText.trim();
                }
            });

            renderProtocols(type);
        });
    } else {
        document.addEventListener('DOMContentLoaded', loadProtocols);
    }
</script>

<style>
    .protocol-card {
        transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .protocol-card:hover {
        transform: translateY(-2px);
        border-color: var(--primary-color) !important;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>