<?php
$pageTitle = "Staff Management";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Human Resources (HR)</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Staff & HR</a></li>
            <li class="breadcrumb-item active" aria-current="page">Staff Directory</li>
        </ol>
    </div>
</div>

<style>
    .branch-menu-container {
        border-right: 1px solid #e9ebec;
        height: 100%;
        min-height: calc(100vh - 200px);
    }

    .branch-menu .list-group-item {
        border: none;
        transition: all 0.2s ease;
        padding: 12px 20px;
        font-size: 13px;
        color: #495057;
        background: transparent;
        border-right: 3px solid transparent;
        margin-bottom: 0px;
        border-radius: 0 !important;
        margin: 0;
        cursor: pointer;
    }

    .branch-menu .list-group-item:hover {
        background-color: #f8f9fa;
        color: var(--primary-color);
    }

    .branch-menu .list-group-item.active {
        background-color: rgba(var(--primary-rgb, 84, 109, 254), 0.05) !important;
        color: var(--primary-color, #546dfe) !important;
        border-right: 3px solid var(--primary-color, #546dfe) !important;
        font-weight: 600;
        box-shadow: none;
    }

    .branch-header {
        padding: 15px 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #99a5b7;
    }
</style>

<div class="row">
    <!-- LEFT SIDEBAR: BRANCH LIST (Zoho Style) -->
    <div class="col-xxl-2 col-xl-3 p-0 bg-white branch-menu-container" id="branchSidebar">
        <div class="branch-header border-bottom">Organization Units</div>
        <div class="list-group list-group-flush branch-menu" id="branchList">
            <div class="p-3 text-center">
                <div class="spinner-border spinner-border-sm text-primary"></div>
            </div>
        </div>
        <div class="p-4 mt-auto">
            <a href="/branches" class="btn btn-sm btn-outline-light text-muted w-100 fs-11"><i
                    class="ri-settings-4-line me-1"></i> UNITS CONFIG</a>
        </div>
    </div>

    <!-- RIGHT PANEL: STAFF LIST (80% width) -->
    <div class="col-xxl-10 col-xl-9">
        <div class="card custom-card overflow-hidden">
            <div class="card-header justify-content-between flex-wrap gap-2">
                <div class="card-title">Employee Records</div>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" id="staffSearch"
                        placeholder="Search by name, ID or email..." onkeyup="fetchStaff()">
                    <a href="<?= baseUrl('/staff/create') ?>" id="createStaffBtn"
                        class="btn btn-primary btn-sm btn-wave waves-effect waves-light">
                        <i class="ri-user-add-line me-1 align-middle"></i>Add New Staff
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Employee Details</th>
                                <th>Role</th>
                                <th>Contact Information</th>
                                <th>Joining Date</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="staffList">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    let currentBranchId = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadBranches();
        fetchStaff();
    });

    async function loadBranches() {
        const list = document.getElementById('branchList');
        try {
            const res = await fetch('/api/v1/branches');
            const data = await res.json();

            if (data.success && data.data.branches.length > 0) {
                let html = `<button class="list-group-item list-group-item-action active" onclick="filterByBranch('all', this)">
                                <span>All Organization Units</span>
                            </button>`;

                const sortedBranches = data.data.branches.sort((a, b) => b.is_main - a.is_main);

                sortedBranches.forEach(b => {
                    const isActive = (currentBranchId == b.branch_id) ? 'active' : '';
                    const mainIndicator = b.is_main ? '<i class="ri-star-fill text-warning ms-1" style="font-size: 8px;"></i>' : '';

                    html += `<button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                                onclick="filterByBranch(${b.branch_id}, this, '${b.branch_name}')">
                                <span class="text-truncate">${b.branch_name} ${mainIndicator}</span>
                                <span class="badge bg-light text-muted border rounded-pill">${b.staff_count || 0}</span>
                            </button>`;
                });
                list.innerHTML = html;
            } else {
                list.innerHTML = '<div class="p-3 text-muted fs-12">No nodes found.</div>';
            }
        } catch (e) {
            console.error(e);
            list.innerHTML = '<div class="p-3 text-danger fs-12">Failed to load nodes.</div>';
        }
    }

    window.filterByBranch = function (branchId, btn) {
        currentBranchId = branchId;
        document.querySelectorAll('#branchList .list-group-item').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');

        // Update Create Link
        const createBtn = document.getElementById('createStaffBtn');
        createBtn.href = branchId ? `/staff/create?branch_id=${branchId}` : '/staff/create';

        fetchStaff();
    }

    async function fetchStaff() {
        const list = document.getElementById('staffList');
        const search = document.getElementById('staffSearch').value;
        list.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        let url = `/api/v1/staff?search=${search}`;
        if (currentBranchId) url += `&branch_id=${currentBranchId}`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            list.innerHTML = '';

            if (data.success && data.data.staff.length > 0) {
                data.data.staff.forEach(s => {
                    const date = s.joining_date ? new Date(s.joining_date).toLocaleDateString() : 'N/A';
                    const statusBadge = s.is_active ? '<span class="badge bg-success-transparent">Active</span>' : '<span class="badge bg-danger-transparent">Inactive</span>';

                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-primary-transparent text-primary rounded-circle me-3">${s.full_name[0]}</div>
                            <div>
                                <div class="fw-semibold">${s.full_name}</div>
                                <div class="text-muted fs-11">${s.code || 'EMP-' + s.staff_id}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-info-transparent">${s.role_name || 'N/A'}</span></td>
                    <td>
                        <div class="fs-12"><i class="ri-phone-line me-1 text-muted"></i>${s.phone || '-'}</div>
                        <div class="fs-11 text-muted"><i class="ri-mail-line me-1"></i>${s.email || '-'}</div>
                    </td>
                    <td>${date}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-icon btn-primary-light rounded-pill"><i class="ri-edit-line"></i></button>
                        <button class="btn btn-sm btn-icon btn-danger-light rounded-pill" onclick="deleteStaff(${s.staff_id})"><i class="ri-delete-bin-line"></i></button>
                    </td>
                `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No staff records found.</td></tr>';
            }
        } catch (e) {
            console.error(e);
            list.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load staff records.</td></tr>';
        }
    }

    async function deleteStaff(id) {
        if (!confirm('Are you sure you want to remove this staff record?')) return;
        try {
            const res = await fetch(`/api/v1/staff/${id}`, { method: 'DELETE' });
            const data = await res.json();
            if (data.success) fetchStaff();
        } catch (e) { }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>