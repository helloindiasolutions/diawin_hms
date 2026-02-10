<?php
$pageTitle = 'User Management';
ob_start();
?>

<style>
    .avatar-md {
        width: 38px;
        height: 38px;
        line-height: 38px;
    }

    .bg-primary-transparent {
        background-color: rgba(var(--primary-rgb, 84, 109, 254), 0.1) !important;
    }

    .bg-outline-info {
        background-color: transparent;
        border: 1px solid #17a2b8;
        color: #17a2b8;
    }

    .btn-primary-light {
        background-color: rgba(var(--primary-rgb, 84, 109, 254), 0.1);
        color: #546dfe;
        border: none;
    }

    .btn-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: none;
    }

    .btn-icon {
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .pagination .page-link {
        border-radius: 4px;
        margin: 0 2px;
    }

    .table-nowrap td,
    .table-nowrap th {
        white-space: nowrap;
    }

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

    .branch-menu .list-group-item.active .badge {
        background-color: var(--primary-color, #546dfe) !important;
        color: #fff !important;
    }

    .branch-menu .badge {
        font-size: 10px;
        font-weight: 500;
        padding: 4px 8px;
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

<!-- Page Title -->
<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">User Management</h2>
        <span class="text-muted fs-12">Control system access and monitor user activity across all branches</span>
    </div>
</div>

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

    <!-- RIGHT PANEL: USER LIST -->
    <div class="col-xxl-10 col-xl-9" id="userPanel">
        <div class="card custom-card overflow-hidden">
            <div class="card-header border-bottom d-md-flex align-items-center justify-content-between">
                <div class="card-title mb-2 mb-md-0">
                    <span id="currentBranchTitle">All Active Accounts</span>
                    <span class="ms-2 badge bg-primary-transparent text-primary" id="userCountBadge">0 found</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm">
                        <select class="form-select bg-light border-0" id="filterRole"
                            style="width: 130px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="">All Roles</option>
                        </select>
                        <span class="input-group-text bg-light border-0"><i class="ri-search-line"></i></span>
                        <input type="text" class="form-control bg-light border-0" id="searchUser"
                            placeholder="Quick search..."
                            style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    </div>
                    <a href="/users/create" id="createUserBtn"
                        class="btn btn-primary btn-sm btn-wave waves-effect waves-light">
                        <i class="ri-user-add-line align-middle me-1"></i> Add Professional
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Identity</th>
                                <th>Access Role</th>
                                <th>Contact Interface</th>
                                <th>Status</th>
                                <th>Registry Date</th>
                                <th class="text-center">Utility</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="spinner-grow text-primary" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">Synchronizing with node...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-top bg-light-transparent px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-muted fs-13" id="paginationInfo">Showing 0 entries</div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="javascript:void(0);">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                            <li class="page-item disabled"><a class="page-link" href="javascript:void(0);">Next</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentBranchId = null;
    let currentUser = null;

    document.addEventListener('DOMContentLoaded', async () => {
        await fetchCurrentUser();
        loadBranches();
        loadRoles(); // Added role loading
        loadUsers();

        // Search debounce
        let timeout;
        document.getElementById('searchUser').addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => loadUsers(e.target.value), 500);
        });

        // Role filter change
        document.getElementById('filterRole').addEventListener('change', () => {
            loadUsers(document.getElementById('searchUser').value);
        });
    });

    // 1. Fetch current user data
    async function fetchCurrentUser() {
        try {
            const res = await fetch('/api/v1/auth/me');
            const data = await res.json();
            if (data.success) {
                currentUser = data.data.user;
            }
        } catch (e) {
            console.error('Error fetching current user:', e);
            // Fallback: Assume admin if auth fails locally but page loaded
            currentUser = { roles: ['admin'] };
        }
    }

    // 3. Load Roles for Filter
    async function loadRoles() {
        try {
            const res = await fetch('/api/v1/roles');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('filterRole');
                data.data.roles.forEach(role => {
                    const option = new Option(role.name, role.code);
                    select.add(option);
                });
            }
        } catch (e) { console.error('Failed to load roles', e); }
    }

    // 4. Load Branches for Sidebar
    async function loadBranches() {
        try {
            const res = await fetch('/api/v1/branches');
            const data = await res.json();

            if (data.success && data.data.branches.length > 0) {
                // ... (existing visibility logic)
                const userRoles = currentUser?.roles || [];
                const isSuperAdmin = userRoles.length === 0 || userRoles.some(r => {
                    const role = typeof r === 'string' ? r : (r.name || '');
                    return ['super_admin', 'system_admin', 'admin'].includes(role.toLowerCase());
                });

                if (isSuperAdmin) {
                    document.getElementById('branchSidebar').classList.remove('d-none');
                    document.getElementById('userPanel').classList.remove('col-12');
                } else {
                    document.getElementById('branchSidebar').classList.add('d-none');
                    document.getElementById('userPanel').classList.add('col-12');
                    document.getElementById('userPanel').classList.remove('col-xxl-10', 'col-xl-9');
                }

                const list = document.getElementById('branchList');
                // All Branches option
                let html = `<button class="list-group-item list-group-item-action active" onclick="filterByBranch(null, this)">
                                <span>All Organization Units</span>
                            </button>`;

                // Sort: Main Branch first (is_main = 1), then others
                const sortedBranches = data.data.branches.sort((a, b) => b.is_main - a.is_main);

                sortedBranches.forEach(b => {
                    const mainIndicator = b.is_main ? '<i class="ri-star-fill text-warning ms-1" style="font-size: 8px;"></i>' : '';

                    html += `<button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                                onclick="filterByBranch(${b.branch_id}, this, '${b.branch_name}')">
                                <span class="text-truncate">${b.branch_name} ${mainIndicator}</span>
                                <span class="badge bg-light text-muted border rounded-pill">${b.user_count}</span>
                            </button>`;
                });
                list.innerHTML = html;
            } else {
                list.innerHTML = '<div class="p-3 text-muted fs-12">No nodes found.</div>';
            }
        } catch (e) {
            console.error('Failed to load branches', e);
            document.getElementById('branchList').innerHTML = '<div class="p-3 text-danger fs-12">Failed to load nodes.</div>';
        }
    }

    // 3. Filter Logic
    window.filterByBranch = function (branchId, btn, branchName = 'All Users') {
        currentBranchId = branchId;

        // Update UI active state
        document.querySelectorAll('#branchList .list-group-item').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');

        document.getElementById('currentBranchTitle').innerText = branchId ? branchName : 'All Users';

        // Update Create Link to include branch_id
        const createBtn = document.getElementById('createUserBtn');
        createBtn.href = branchId ? `/users/create?branch_id=${branchId}` : '/users/create';

        loadUsers();
    }

    // 5. Load Users
    async function loadUsers(search = '') {
        const tbody = document.getElementById('userTableBody');
        const roleFilter = document.getElementById('filterRole').value;

        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-grow text-primary"></div></td></tr>';

        let url = '/api/v1/users?';
        if (currentBranchId) url += `branch_id=${currentBranchId}&`;
        if (search) url += `search=${search}&`;
        if (roleFilter) url += `role=${roleFilter}`;

        try {
            const res = await fetch(url);
            const data = await res.json();

            if (data.success && data.data.users.length > 0) {
                let rows = '';
                data.data.users.forEach(u => {
                    const statusBadge = (parseInt(u.is_active) === 1) ?
                        '<span class="badge bg-success-subtle text-success">Active</span>' :
                        '<span class="badge bg-danger-subtle text-danger">Inactive</span>';

                    const joinedDate = u.created_at ? new Date(u.created_at).toLocaleDateString() : '-';

                    rows += `<tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md me-3 bg-primary-transparent text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                    ${u.full_name ? u.full_name.charAt(0).toUpperCase() : u.username.charAt(0).toUpperCase()}
                                </span>
                                <div>
                                    <h6 class="mb-0 fw-semibold">${u.full_name || u.username}</h6>
                                    <span class="text-muted fs-11">ID: @${u.username}</span>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-outline-info rounded-pill px-3">${u.role_names || 'User'}</span></td>
                        <td>
                            <div class="mb-1"><i class="ri-phone-fill me-1 text-muted fs-11"></i> <span class="fs-13">${u.mobile || '-'}</span></div>
                            <div class="text-muted fs-11"><i class="ri-mail-fill me-1"></i> ${u.email || '-'}</div>
                        </td>
                        <td>${statusBadge}</td>
                        <td class="text-muted fs-12">${joinedDate}</td>
                        <td class="text-center">
                            <div class="btn-list">
                                <a href="/users/${u.user_id}/edit" class="btn btn-sm btn-icon btn-primary-light btn-wave"><i class="ri-edit-line"></i></a>
                                <button onclick="confirmDelete(${u.user_id})" class="btn btn-sm btn-icon btn-danger-light btn-wave"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = rows;
                document.getElementById('userCountBadge').innerText = `${data.data.users.length} professional${data.data.users.length > 1 ? 's' : ''} found`;
                document.getElementById('paginationInfo').innerText = `Showing ${data.data.users.length} of ${data.data.users.length} entries`;
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No users found in this branch.</td></tr>';
                document.getElementById('userCountBadge').innerText = '0 users';
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Failed to load users list.</td></tr>';
        }
    }

    // 5. Delete Confirmation (SweetAlert2)
    window.confirmDelete = function (userId) {
        Swal.fire({
            title: 'Delete User?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6e7d88',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`/api/v1/users/${userId}`, { method: 'DELETE' });
                    const resData = await res.json();
                    if (resData.success) {
                        showToast('User has been deleted successfully.', 'success');
                        loadUsers();
                    } else {
                        showToast(resData.message || 'Failed to delete user.', 'error');
                    }
                } catch (e) {
                    showToast('Network error occurred.', 'error');
                }
            }
        })
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>