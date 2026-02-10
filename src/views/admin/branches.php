<?php
$pageTitle = "Branch Management";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Branch Management</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Administration</a></li>
            <li class="breadcrumb-item active" aria-current="page">Branches</li>
        </ol>
    </div>
    <div id="addBranchBtn">
        <!-- Button will be shown only for super admin -->
    </div>
</div>

<div class="row" id="branchesGrid">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- Add/Edit Branch Modal -->
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="branchModalTitle">Add Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="branchForm">
                    <input type="hidden" id="branchId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="branchCode" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="branchName" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="branchAddress" rows="2"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" id="branchCity">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" id="branchState">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="branchPincode">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="branchPhone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="branchEmail">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timezone</label>
                            <select class="form-select" id="branchTimezone">
                                <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="branchStatus">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveBranch()">Save Branch</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Branch Admin Modal -->
<div class="modal fade" id="branchAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Branch Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="branchAdminForm">
                    <input type="hidden" id="adminBranchId">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="adminFullName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="adminUsername" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="adminEmail">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="adminMobile">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="adminPassword" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createBranchAdmin()">Create Admin</button>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    let currentUserRole = null;
    let branchModal, branchAdminModal;

    document.addEventListener('DOMContentLoaded', () => {
        branchModal = new bootstrap.Modal(document.getElementById('branchModal'));
        branchAdminModal = new bootstrap.Modal(document.getElementById('branchAdminModal'));
        loadBranches();
    });

    async function loadBranches() {
        try {
            const res = await fetch('/api/v1/branches');
            const data = await res.json();
            
            if (data.success && data.data.branches) {
                currentUserRole = data.data.user_role || null;
                renderBranches(data.data.branches);
                
                // Show add button only for super admin
                if (isSuperAdmin()) {
                    document.getElementById('addBranchBtn').innerHTML = `
                        <button class="btn btn-primary" onclick="showAddBranchModal()">
                            <i class="ri-add-line me-2"></i>Add Branch
                        </button>
                    `;
                }
            }
        } catch (e) {
            showError('Failed to load branches: ' + e.message);
        }
    }

    function renderBranches(branches) {
        const grid = document.getElementById('branchesGrid');
        
        if (branches.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info">No branches found.</div>
                </div>
            `;
            return;
        }

        grid.innerHTML = branches.map(branch => `
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1">${escapeHtml(branch.branch_name)}</h5>
                                <span class="badge bg-primary-transparent">${escapeHtml(branch.code)}</span>
                                ${branch.is_main ? '<span class="badge bg-success ms-2">Main Branch</span>' : ''}
                            </div>
                            <span class="badge ${branch.is_active ? 'bg-success' : 'bg-secondary'}">
                                ${branch.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <p class="mb-1 text-muted fs-12">
                                <i class="ri-map-pin-line me-1"></i>
                                ${escapeHtml(branch.city || 'N/A')}, ${escapeHtml(branch.state || '')}
                            </p>
                            <p class="mb-1 text-muted fs-12">
                                <i class="ri-phone-line me-1"></i>
                                ${escapeHtml(branch.phone || 'N/A')}
                            </p>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <p class="mb-1 fs-11 text-muted">Users</p>
                                    <h6 class="mb-0 fw-bold">${branch.total_users || 0}</h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <p class="mb-1 fs-11 text-muted">Patients</p>
                                    <h6 class="mb-0 fw-bold">${branch.total_patients || 0}</h6>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary flex-fill" onclick="viewBranchUsers(${branch.branch_id})">
                                <i class="ri-team-line me-1"></i>Users
                            </button>
                            ${isSuperAdmin() ? `
                                <button class="btn btn-sm btn-success" onclick="showCreateAdminModal(${branch.branch_id})">
                                    <i class="ri-user-add-line"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="editBranch(${branch.branch_id})">
                                    <i class="ri-edit-line"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function isSuperAdmin() {
        // Check from session or API response
        return true; // TODO: Get from API
    }

    function showAddBranchModal() {
        document.getElementById('branchModalTitle').textContent = 'Add Branch';
        document.getElementById('branchForm').reset();
        document.getElementById('branchId').value = '';
        branchModal.show();
    }

    async function editBranch(branchId) {
        try {
            const res = await fetch(`/api/v1/branches/${branchId}`);
            const data = await res.json();
            
            if (data.success && data.data.branch) {
                const branch = data.data.branch;
                document.getElementById('branchModalTitle').textContent = 'Edit Branch';
                document.getElementById('branchId').value = branch.branch_id;
                document.getElementById('branchCode').value = branch.code;
                document.getElementById('branchName').value = branch.name;
                document.getElementById('branchAddress').value = branch.address || '';
                document.getElementById('branchCity').value = branch.city || '';
                document.getElementById('branchState').value = branch.state || '';
                document.getElementById('branchPincode').value = branch.pincode || '';
                document.getElementById('branchPhone').value = branch.phone || '';
                document.getElementById('branchEmail').value = branch.email || '';
                document.getElementById('branchTimezone').value = branch.timezone || 'Asia/Kolkata';
                document.getElementById('branchStatus').value = branch.is_active ? '1' : '0';
                branchModal.show();
            }
        } catch (e) {
            showError('Failed to load branch details');
        }
    }

    async function saveBranch() {
        const branchId = document.getElementById('branchId').value;
        const data = {
            code: document.getElementById('branchCode').value,
            name: document.getElementById('branchName').value,
            address: document.getElementById('branchAddress').value,
            city: document.getElementById('branchCity').value,
            state: document.getElementById('branchState').value,
            pincode: document.getElementById('branchPincode').value,
            phone: document.getElementById('branchPhone').value,
            email: document.getElementById('branchEmail').value,
            timezone: document.getElementById('branchTimezone').value,
            is_active: parseInt(document.getElementById('branchStatus').value)
        };

        try {
            const url = branchId ? `/api/v1/branches/${branchId}` : '/api/v1/branches';
            const method = branchId ? 'PUT' : 'POST';
            
            const res = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            
            if (result.success) {
                branchModal.hide();
                Swal.fire('Success', result.message || 'Branch saved successfully', 'success');
                loadBranches();
            } else {
                Swal.fire('Error', result.message || 'Failed to save branch', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Failed to save branch: ' + e.message, 'error');
        }
    }

    function showCreateAdminModal(branchId) {
        document.getElementById('branchAdminForm').reset();
        document.getElementById('adminBranchId').value = branchId;
        branchAdminModal.show();
    }

    async function createBranchAdmin() {
        const data = {
            branch_id: parseInt(document.getElementById('adminBranchId').value),
            full_name: document.getElementById('adminFullName').value,
            username: document.getElementById('adminUsername').value,
            email: document.getElementById('adminEmail').value,
            mobile: document.getElementById('adminMobile').value,
            password: document.getElementById('adminPassword').value
        };

        try {
            const res = await fetch('/api/v1/branches/create-admin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            
            if (result.success) {
                branchAdminModal.hide();
                Swal.fire('Success', 'Branch admin created successfully', 'success');
                loadBranches();
            } else {
                Swal.fire('Error', result.message || 'Failed to create admin', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Failed to create admin: ' + e.message, 'error');
        }
    }

    function viewBranchUsers(branchId) {
        window.location.href = `/branches/${branchId}/users`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showError(message) {
        document.getElementById('branchesGrid').innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">${message}</div>
            </div>
        `;
    }
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>
