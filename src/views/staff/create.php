<?php
$pageTitle = "Registration Staff";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Staff Intake</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Staff & HR</a></li>
            <li class="breadcrumb-item"><a href="<?= baseUrl('/staff') ?>">Staff Directory</a></li>
            <li class="breadcrumb-item active" aria-current="page">New Staff</li>
        </ol>
    </div>
    <div class="ms-auto">
        <a href="<?= baseUrl('/staff') ?>" class="btn btn-light btn-sm btn-wave waves-effect waves-light">
            <i class="ri-arrow-left-line align-middle me-1"></i> Back to Directory
        </a>
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
            <small class="text-muted fs-10 fw-bold text-uppercase"><i class="ri-lock-2-line me-1"></i> Assignment
                Locked</small>
        </div>
    </div>

    <!-- RIGHT PANEL: CREATE FORM (80% width) -->
    <div class="col-xxl-10 col-xl-9">
        <div class="card custom-card">
            <div class="card-header bg-primary text-white">
                <div class="card-title text-white">New Employee Profile</div>
            </div>
            <div class="card-body">
                <form id="staffForm" class="needs-validation" novalidate onsubmit="saveStaff(event)">
                    <div class="row g-4">
                        <input type="hidden" id="branch_id" name="branch_id">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" required
                                placeholder="Enter full name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee Code</label>
                            <input type="text" class="form-control" id="code" placeholder="e.g. EMP-2024-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign Role</label>
                            <select class="form-select" id="role_id">
                                <option value="">Select Role</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Joining Date</label>
                            <input type="date" class="form-control" id="joining_date">
                        </div>

                        <div class="col-md-12">
                            <hr class="my-2">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                placeholder="Enter 10-digit mobile number" maxlength="10" pattern="[0-9]{10}" required>
                            <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="staff@hospital.com">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Residential Address</label>
                            <textarea class="form-control" id="address" rows="3"
                                placeholder="Full address details..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Employment Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active Employment</label>
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <a href="<?= baseUrl('/staff') ?>" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Profile</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadBranches();
        loadRoles();
    });

    async function loadBranches() {
        const list = document.getElementById('branchList');
        const urlParams = new URLSearchParams(window.location.search);
        const preSelectedBranchId = urlParams.get('branch_id');

        try {
            const res = await fetch('/api/v1/branches');
            const data = await res.json();

            if (data.success && data.data.branches.length > 0) {
                let sidebarHtml = '';
                const sortedBranches = data.data.branches.sort((a, b) => b.is_main - a.is_main);

                sortedBranches.forEach(b => {
                    const isActive = (preSelectedBranchId == b.branch_id) ? 'active' : '';
                    const mainIndicator = b.is_main ? '<i class="ri-star-fill text-warning ms-1" style="font-size: 8px;"></i>' : '';

                    sidebarHtml += `
                            <button type="button" class="list-group-item list-group-item-action ${isActive}" 
                                    onclick="selectBranch(${b.branch_id}, this)">
                                <span class="text-truncate">${b.branch_name} ${mainIndicator}</span>
                            </button>`;
                });

                list.innerHTML = sidebarHtml;

                if (!preSelectedBranchId && sortedBranches.length > 0) {
                    const firstBtn = list.querySelector('button');
                    if (firstBtn) firstBtn.click();
                } else if (preSelectedBranchId) {
                    document.getElementById('branch_id').value = preSelectedBranchId;
                }
            } else {
                list.innerHTML = '<div class="p-3 text-muted fs-12">No nodes found.</div>';
            }
        } catch (e) {
            console.error(e);
            list.innerHTML = '<div class="p-3 text-danger fs-12">Failed to load nodes.</div>';
        }
    }

    window.selectBranch = function (branchId, btn) {
        document.querySelectorAll('#branchList .list-group-item').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('branch_id').value = branchId;
    }

    async function loadRoles() {
        try {
            const res = await fetch('/api/v1/staff/roles');
            const data = await res.json();
            const select = document.getElementById('role_id');
            if (data.success) {
                data.data.roles.forEach(r => {
                    const opt = new Option(r.name, r.role_id);
                    select.add(opt);
                });
            }
        } catch (e) { }
    }

    async function saveStaff(e) {
        e.preventDefault();
        const payload = {
            full_name: document.getElementById('full_name').value,
            code: document.getElementById('code').value,
            role_id: document.getElementById('role_id').value,
            joining_date: document.getElementById('joining_date').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value,
            address: document.getElementById('address').value,
            branch_id: document.getElementById('branch_id').value,
            is_active: document.getElementById('is_active').checked ? 1 : 0
        };

        try {
            const res = await fetch('/api/v1/staff', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.success) {
                window.location.href = '<?= baseUrl('/staff') ?>';
            } else {
                alert(result.message || 'Failed to save profile');
            }
        } catch (e) { }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>