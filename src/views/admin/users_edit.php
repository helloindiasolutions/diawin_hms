<?php
/**
 * Unified User Edit Wizard
 * Entry point for editing system users
 */
$pageTitle = 'Edit User';
$userId = $user_id ?? null;
ob_start();
?>

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
            <small class="text-muted fs-10 fw-bold text-uppercase"><i class="ri-lock-2-line me-1"></i> Global
                Assignment</small>
        </div>
    </div>

    <!-- RIGHT PANEL: EDIT FORM (80% width) -->
    <div class="col-xxl-10 col-xl-9 pt-4 px-4">
        <div class="d-md-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Edit User Profile</h4>
                <p class="text-muted mb-0">Update account details, roles, and branch assignments.</p>
            </div>
            <div class="ms-auto">
                <a href="/users" class="btn btn-light btn-sm btn-wave waves-effect waves-light">
                    <i class="ri-arrow-left-line align-middle me-1"></i> Back to List
                </a>
            </div>
        </div>
        <form id="editUserForm" class="needs-validation" novalidate>
            <!-- 1. Role Selection -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">1. Account Type & Role</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">System Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="roleSelect" required onchange="handleRoleChange()">
                                <option value="" disabled selected>Loading roles...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select class="form-select" id="departmentSelect" name="department">
                                <option value="">General</option>
                                <option value="cardiology">Cardiology</option>
                                <option value="orthopedics">Orthopedics</option>
                                <option value="pediatrics">Pediatrics</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="billing">Accounts & Billing</option>
                                <option value="front_office">Front Desk</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Branch Assignment</label>
                            <select class="form-select" id="branchSelect" name="branch_id" required>
                                <option value="" disabled selected>Loading branches...</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Basic Information -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">2. Basic Information</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="mobile" required maxlength="10"
                                pattern="[0-9]{10}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username (Login ID)</label>
                            <input type="text" class="form-control bg-light" name="username" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reset Password (Leave blank to keep current)</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="passwordField" minlength="6">
                                <button class="btn btn-light" type="button" onclick="togglePassword()"><i
                                        class="ri-eye-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Dynamic Fields Container -->
            <div id="dynamicFieldsContainer">
                <div class="card custom-card role-section d-none" id="section-doctor">
                    <div class="card-header bg-primary-transparent">
                        <div class="card-title text-primary">Medical Practitioner Details</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Medical Specialization</label>
                                <select class="form-select" name="specialization">
                                    <option value="">Select Specialty...</option>
                                    <option value="General Medicine">General Medicine</option>
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Orthopedics">Orthopedics</option>
                                    <option value="Dermatology">Dermatology</option>
                                    <option value="Siddha">Siddha Medicine</option>
                                    <option value="Ayurveda">Ayurveda</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Medical License No.</label>
                                <input type="text" class="form-control" name="license_no" placeholder="e.g. MMC-12345">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Consultation Fee (₹)</label>
                                <input type="number" class="form-control" name="consultation_fee" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PHARMACIST FIELDS -->
                <div class="card custom-card role-section d-none" id="section-pharmacist">
                    <div class="card-header bg-success-transparent">
                        <div class="card-title text-success"><i class="ri-capsule-line me-2"></i>Pharmacy Credentials
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pharmacy License No.</label>
                                <input type="text" class="form-control" name="pharmacy_license"
                                    placeholder="Required for drug dispensing">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Access Level</label>
                                <select class="form-select" name="pharmacy_access">
                                    <option value="dispense">Dispensing Only</option>
                                    <option value="manager">Inventory Manager</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FRONT OFFICE FIELDS -->
                <div class="card custom-card role-section d-none" id="section-receptionist">
                    <div class="card-header bg-info-transparent">
                        <div class="card-title text-info"><i class="ri-service-line me-2"></i>Front Desk Config</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Shift Timing</label>
                                <select class="form-select" name="shift">
                                    <option value="morning">Morning (8 AM - 4 PM)</option>
                                    <option value="evening">Evening (4 PM - 12 AM)</option>
                                    <option value="night">Night (12 AM - 8 AM)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Submit Action -->
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Account Active</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" onclick="window.history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-wave">
                                <i class="ri-save-line me-1"></i> Update User Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const userId = "<?= $userId ?>";

    document.addEventListener('DOMContentLoaded', async () => {
        await fetchRoles();
        await loadBranches();
        if (userId) {
            await loadUserData();
        }
    });

    async function fetchRoles() {
        try {
            const res = await fetch('/api/v1/roles');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('roleSelect');
                let html = '<option value="" disabled>Select Role...</option>';
                data.data.roles.forEach(r => {
                    html += `<option value="${r.code.toLowerCase()}">${r.name}</option>`;
                });
                select.innerHTML = html;
            }
        } catch (e) { console.error('Failed to load roles', e); }
    }

    async function loadBranches() {
        try {
            const res = await fetch('/api/v1/branches');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('branchSelect');
                const sidebar = document.getElementById('branchList');
                let options = '';
                let sidebarHtml = '';

                data.data.branches.forEach(b => {
                    options += `<option value="${b.branch_id}">${b.branch_name}</option>`;
                    sidebarHtml += `<button type="button" class="list-group-item list-group-item-action" onclick="selectBranch(${b.branch_id}, this)" id="sidebar-branch-${b.branch_id}">${b.branch_name}</button>`;
                });

                select.innerHTML = options;
                sidebar.innerHTML = sidebarHtml;
            }
        } catch (e) { console.error('Failed to load branches', e); }
    }

    async function loadUserData() {
        try {
            const res = await fetch(`/api/v1/users/${userId}`);
            const data = await res.json();

            if (data.success) {
                const u = data.data.user;
                const form = document.getElementById('editUserForm');

                form.elements['full_name'].value = u.full_name || '';
                form.elements['mobile'].value = u.mobile || '';
                form.elements['email'].value = u.email || '';
                form.elements['username'].value = u.username || '';

                // Set role (ensure lowercase matching)
                const roleCode = (u.role_code || '').toLowerCase();
                document.getElementById('roleSelect').value = roleCode;

                document.getElementById('branchSelect').value = u.branch_id || '';
                document.getElementById('isActive').checked = parseInt(u.is_active) === 1;

                if (u.department) document.getElementById('departmentSelect').value = u.department;

                // Highlight sidebar
                selectBranch(u.branch_id, document.getElementById(`sidebar-branch-${u.branch_id}`));
                handleRoleChange();

                // Populate dynamic fields
                if (u.role_code === 'DOCTOR' || u.role_code === 'doctor') {
                    form.elements['specialization'].value = u.specialization || '';
                    form.elements['license_no'].value = u.license_no || '';
                }
            }
        } catch (e) {
            showToast('Failed to load user data', 'error');
        }
    }

    window.selectBranch = function (branchId, btn) {
        if (!btn) return;
        document.querySelectorAll('#branchList .list-group-item').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('branchSelect').value = branchId;
    }

    function handleRoleChange() {
        const role = document.getElementById('roleSelect').value;
        const sections = document.querySelectorAll('.role-section');
        sections.forEach(s => s.classList.add('d-none'));

        const target = document.getElementById(`section-${role}`);
        if (target) target.classList.remove('d-none');
    }

    function togglePassword() {
        const p = document.getElementById('passwordField');
        p.type = p.type === 'password' ? 'text' : 'password';
    }

    document.getElementById('editUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        const form = new FormData(this);
        const payload = {
            full_name: form.get('full_name'),
            mobile: form.get('mobile'),
            email: form.get('email'),
            branch_id: document.getElementById('branchSelect').value,
            role: document.getElementById('roleSelect').value,
            department: document.getElementById('departmentSelect').value,
            is_active: document.getElementById('isActive').checked ? 1 : 0
        };

        const pwd = document.getElementById('passwordField').value;
        if (pwd) payload.password = pwd;

        // Medical fields
        if (payload.role === 'doctor') {
            payload.specialization = form.get('specialization');
            payload.license_no = form.get('license_no');
        }

        try {
            const res = await fetch(`/api/v1/users/${userId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                showToast('User updated successfully', 'success');
                setTimeout(() => window.location.href = '/users', 1000);
            } else {
                showToast(data.message || 'Update failed', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (e) {
            showToast('Connection error', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>