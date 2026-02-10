<?php
/**
 * Role Master - Commercial Industry Standard UI
 * Advanced permission management interface
 */
$pageTitle = 'Role Master';
ob_start();
?>

<!-- Start::page-header -->
<div class="d-flex align-items-center justify-content-between mb-4 page-header-breadcrumb flex-wrap gap-2">
    <div>
        <h1 class="page-title fw-medium fs-20 mb-0">Role Master</h1>
        <p class="text-muted mb-0 fs-12">Configure access levels and system permissions.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button class="btn btn-primary btn-wave shadow-sm" onclick="showAddModal()">
            <i class="ri-shield-keyhole-line me-1 align-middle"></i>Add New Role
        </button>
    </div>
</div>
<!-- End::page-header -->

<!-- Start:: row-1 (Stats) -->
<div class="row">
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-primary-transparent text-primary">
                        <i class="ri-admin-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="totalRoles">Loading...</h4>
                        <span class="fs-12 fw-medium text-muted">Total Defined Roles</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-secondary-transparent text-secondary">
                        <i class="ri-lock-2-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="systemRoles">Loading...</h4>
                        <span class="fs-12 fw-medium text-muted">System Protected Roles</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-success-transparent text-success">
                        <i class="ri-user-settings-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="customRoles">Loading...</h4>
                        <span class="fs-12 fw-medium text-muted">Custom Created Roles</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End:: row-1 -->

<!-- Start:: row-2 (Table) -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        <input type="text" class="form-control" id="roleSearch" placeholder="Search roles..."
                            onkeyup="debounce(fetchRoles)">
                        <span class="input-group-text bg-light text-muted border-start-0"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" class="ps-4 fs-11 text-muted text-uppercase">Role Name</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Access Scope</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Active Users</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Last Modified</th>
                                <th scope="col" class="text-end pe-4 fs-11 text-muted text-uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="roleList">
                            <!-- JS Filled -->
                        </tbody>
                    </table>
                </div>
                <div id="loadingState" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>
                <div id="emptyState" class="text-center py-5 d-none">
                    <i class="ri-shield-keyhole-line fs-40 text-muted op-3"></i>
                    <p class="text-muted mt-2">No roles found.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End:: row-2 -->

<!-- Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title fw-medium fs-15 text-white" id="modalTitle">Configure Role Access</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <form id="roleForm" class="needs-validation" novalidate>
                    <input type="hidden" id="roleId">
                    <div class="p-4 bg-light-transparent border-bottom">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fs-13">Role Name</label>
                                <input type="text" class="form-control" id="roleName" placeholder="e.g. Senior Nurse"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-13">Description</label>
                                <input type="text" class="form-control" id="roleDesc"
                                    placeholder="Brief description of responsibilities">
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <h6 class="fs-13 fw-semibold text-uppercase text-muted mb-3">Permission Matrix</h6>
                        <div class="row g-3" id="permissionGrid">
                            <!-- JS Generated Permissions -->
                        </div>
                    </div>

                    <div class="p-4 border-top bg-light-transparent text-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave shadow-sm px-4">Save Role
                            Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    // Fake Data for UI Demo (will be replaced by API)
    const MOCK_ROLES = [
        { id: 1, name: 'Super Admin', desc: 'Full System Access', scope: 'Global', users: 2, modified: '2025-01-10', system: true },
        { id: 2, name: 'Doctor', desc: 'Clinical Access Only', scope: 'OPD, IPD', users: 14, modified: '2025-01-12', system: true },
        { id: 3, name: 'Receptionist', desc: 'Front Desk Operations', scope: 'Appointments, Billing', users: 5, modified: '2025-01-15', system: false },
        { id: 4, name: 'Pharmacist', desc: 'Inventory & Dispensing', scope: 'Pharmacy Module', users: 3, modified: '2025-01-18', system: false },
        { id: 5, name: 'Main Accountant', desc: 'Finance Dashboard', scope: 'Billing, Reports', users: 1, modified: '2025-01-20', system: false }
    ];

    const MOCK_MODULES = [
        { id: 'opd', name: 'OPD & Visits', icon: 'ri-stethoscope-line' },
        { id: 'ipd', name: 'In-Patient (IPD)', icon: 'ri-hospital-line' },
        { id: 'pharmacy', name: 'Pharmacy', icon: 'ri-capsule-line' },
        { id: 'lab', name: 'Laboratory', icon: 'ri-flask-line' },
        { id: 'billing', name: 'Billing & Finance', icon: 'ri-wallet-3-line' },
        { id: 'reports', name: 'Analytics', icon: 'ri-pie-chart-line' },
        { id: 'settings', name: 'System Settings', icon: 'ri-settings-3-line' },
        { id: 'hr', name: 'Staff & HR', icon: 'ri-group-line' }
    ];

    document.addEventListener('DOMContentLoaded', () => {
        renderRoles(MOCK_ROLES);
        renderStats();
        generatePermissionGrid();

        document.getElementById('roleForm').addEventListener('submit', handleSave);
    });

    function renderStats() {
        document.getElementById('totalRoles').textContent = MOCK_ROLES.length;
        document.getElementById('systemRoles').textContent = MOCK_ROLES.filter(r => r.system).length;
        document.getElementById('customRoles').textContent = MOCK_ROLES.filter(r => !r.system).length;
    }

    function renderRoles(roles) {
        document.getElementById('loadingState').classList.add('d-none');
        const list = document.getElementById('roleList');

        if (roles.length === 0) {
            document.getElementById('emptyState').classList.remove('d-none');
            list.innerHTML = '';
            return;
        }

        document.getElementById('emptyState').classList.add('d-none');
        list.innerHTML = roles.map(r => `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm avatar-rounded bg-${r.system ? 'primary' : 'success'}-transparent text-${r.system ? 'primary' : 'success'} fw-medium">
                            <i class="${r.system ? 'ri-shield-star-line' : 'ri-shield-user-line'}"></i>
                        </div>
                        <div>
                            <span class="d-block fw-medium">${r.name}</span>
                            <span class="text-muted fs-11">${r.desc}</span>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-light text-default border">${r.scope}</span></td>
                <td><span class="text-muted fs-12"><i class="ri-group-line me-1"></i>${r.users} Users</span></td>
                <td><span class="text-muted fs-12">${r.modified}</span></td>
                <td class="text-end pe-4">
                    <div class="btn-list">
                        <button class="btn btn-sm btn-icon btn-primary-light" onclick="editRole(${r.id})"><i class="ri-pencil-line"></i></button>
                        ${!r.system ? `<button class="btn btn-sm btn-icon btn-danger-light" onclick="deleteRole(${r.id})"><i class="ri-delete-bin-line"></i></button>` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function generatePermissionGrid() {
        const grid = document.getElementById('permissionGrid');
        grid.innerHTML = MOCK_MODULES.map(m => `
            <div class="col-md-6">
                <div class="card shadow-none border role-perm-card mb-0">
                    <div class="p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-xs bg-light text-muted">
                                <i class="${m.icon}"></i>
                            </div>
                            <span class="fw-medium fs-13">${m.name}</span>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input perm-toggle" type="checkbox" id="perm_${m.id}">
                        </div>
                    </div>
                    <div class="px-3 pb-3 pt-0 border-top-0 d-none">
                        <!-- Granular permissions could go here -->
                        <div class="d-flex gap-2 mt-2">
                            <span class="badge bg-light text-muted fw-normal">View</span>
                            <span class="badge bg-light text-muted fw-normal">Create</span>
                            <span class="badge bg-light text-muted fw-normal">Edit</span>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Configure New Role';
        document.getElementById('roleForm').reset();
        document.getElementById('roleId').value = '';
        const modal = new bootstrap.Modal(document.getElementById('roleModal'));
        modal.show();
    }

    function editRole(id) {
        const r = MOCK_ROLES.find(x => x.id === id);
        if (!r) return;

        document.getElementById('modalTitle').textContent = `Edit Role: ${r.name}`;
        document.getElementById('roleId').value = r.id;
        document.getElementById('roleName').value = r.name;
        document.getElementById('roleDesc').value = r.desc;

        // Randomly check some permissions for demo
        document.querySelectorAll('.perm-toggle').forEach(el => el.checked = Math.random() > 0.5);

        const modal = new bootstrap.Modal(document.getElementById('roleModal'));
        modal.show();
    }

    function handleSave(e) {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        btn.disabled = true;

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            showToast('Role configuration saved successfully!', 'success');
            // In real app, re-fetch roles here
        }, 800);
    }

    async function deleteRole(id) {
        const result = await Swal.fire({
            title: 'Delete Role?',
            text: "This will remove access for assigned users.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Delete'
        });

        if (result.isConfirmed) {
            showToast('Role deleted successfully.', 'success');
        }
    }

    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    function fetchRoles() {
        const term = document.getElementById('roleSearch').value.toLowerCase();
        const filtered = MOCK_ROLES.filter(r => r.name.toLowerCase().includes(term));
        renderRoles(filtered);
    }
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>