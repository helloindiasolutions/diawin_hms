<?php
/**
 * Unified User Creation Wizard
 * Single entry point for creating ANY system user (Doctor, Staff, Admin, etc.)
 */
$pageTitle = 'Create New User';
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

    <!-- RIGHT PANEL: CREATE FORM (80% width) -->
    <div class="col-xxl-10 col-xl-9 pt-4 px-4">
        <div class="d-md-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Create New Professional</h4>
                <p class="text-muted mb-0">Onboard a new medical or administrative staff member to the system.</p>
            </div>
            <div class="ms-auto">
                <a href="/users" class="btn btn-light btn-sm btn-wave waves-effect waves-light">
                    <i class="ri-arrow-left-line align-middle me-1"></i> Back to List
                </a>
            </div>
        </div>
        <form id="createUserForm" class="needs-validation" novalidate>
            <!-- 1. Role Selection (The Driver) -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">1. Account Type & Role</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">System Role <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="roleSelect" required
                                onchange="handleRoleChange()">
                                <option value="" selected disabled>Loading roles...</option>
                            </select>
                            <div class="form-text text-muted" id="roleDescription">Select a role to see specific fields.
                            </div>
                        </div>
                        <div class="col-md-4" id="departmentField">
                            <label class="form-label">Department</label>
                            <select class="form-select" id="departmentSelect">
                                <option value="">General</option>
                                <option value="cardiology">Cardiology</option>
                                <option value="orthopedics">Orthopedics</option>
                                <option value="pediatrics">Pediatrics</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="billing">Accounts & Billing</option>
                                <option value="front_office">Front Desk</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="branchField">
                            <label class="form-label">Branch Assignment</label>
                            <select class="form-select" id="branchSelect" name="branch_id" required
                                onchange="generateUsername()">
                                <option value="" disabled selected>Loading branches...</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>



            <!-- 2. Basic Information (Common to All) -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">2. Basic Information</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" placeholder="e.g. Dr. Sarah Smith"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="mobile"
                                placeholder="Enter 10-digit mobile number" required maxlength="10" pattern="[0-9]{10}">
                            <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="emailInput"
                                placeholder="official@email.com" required onchange="generateUsername()">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" name="username" id="usernameInput"
                                placeholder="Auto-generated from branch & email" required minlength="4" readonly>
                            <div class="form-text text-muted">Auto-generated: branchcode_emailname</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Initial Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" value="Melina@123" id="passwordField"
                                    required minlength="6">
                                <button class="btn btn-light" type="button" onclick="togglePassword()"><i
                                        class="ri-eye-line"></i></button>
                            </div>
                            <div class="form-text">Default: Melina@123 (User must change on login)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Dynamic Fields Container -->
            <div id="dynamicFieldsContainer">
                <!-- DOCTOR FIELDS -->
                <div class="card custom-card role-section d-none" id="section-doctor">
                    <div class="card-header bg-primary-transparent">
                        <div class="card-title text-primary"><i class="ri-stethoscope-line me-2"></i>Medical
                            Practitioner Details</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Specialization <span class="text-danger">*</span></label>
                                <select class="form-select" name="specialization">
                                    <option value="">Select Specialty...</option>
                                    <option value="General Medicine">General Medicine</option>
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Orthopedics">Orthopedics</option>
                                    <option value="Dermatology">Dermatology</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Medical License No. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="license_no" placeholder="e.g. MMC-12345">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Consultation Fee (₹)</label>
                                <input type="number" class="form-control" name="consultation_fee" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Education / Qualification</label>
                                <input type="text" class="form-control" placeholder="e.g. MBBS, MD (Cardiology)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Schedule Type</label>
                                <select class="form-select">
                                    <option value="full_time">Full Time (In-House)</option>
                                    <option value="visiting">Visiting Consultant</option>
                                    <option value="on_call">On-Call Only</option>
                                </select>
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
                                <label class="form-label">Pharmacy License No. <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Required for drug dispensing">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Access Level</label>
                                <select class="form-select">
                                    <option value="dispense">Dispensing Only</option>
                                    <option value="manager">Inventory Manager (Can Purchase)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- FRONT OFFICE FIELDS -->
                <div class="card custom-card role-section d-none" id="section-front_office">
                    <div class="card-header bg-info-transparent">
                        <div class="card-title text-info"><i class="ri-service-line me-2"></i>Front Desk Config</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Assigned Counters</label>
                                <select class="form-select" multiple>
                                    <option value="reception_1" selected>Main Reception</option>
                                    <option value="emergency">Emergency Desk</option>
                                    <option value="ipd_admission">IPD Admission</option>
                                </select>
                                <div class="form-text">Hold Ctrl to select multiple</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Shift Timing</label>
                                <select class="form-select">
                                    <option>Morning (8 AM - 4 PM)</option>
                                    <option>Evening (4 PM - 12 AM)</option>
                                    <option>Night (12 AM - 8 AM)</option>
                                    <option>Rotational</option>
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
                            <input class="form-check-input" type="checkbox" id="sendWelcomeEmail" checked>
                            <label class="form-check-label" for="sendWelcomeEmail">Send Welcome Email with
                                Credentials</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light custom-btn-w"
                                onclick="window.history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary custom-btn-w btn-wave">
                                <i class="ri-save-line me-1"></i> Create User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        await fetchRoles();
        await loadBranches();
    });

    async function fetchRoles() {
        try {
            const res = await fetch('/api/v1/roles');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('roleSelect');
                let html = '<option value="" selected disabled>Select Role to Configure...</option>';
                data.data.roles.forEach(r => {
                    // Hide super_admin role from selection - only 1 super_admin should exist
                    const roleCode = r.code.toLowerCase();
                    if (roleCode === 'super_admin' || roleCode === 'super-admin' || r.name.toLowerCase() === 'super_admin' || r.name.toLowerCase() === 'super admin') {
                        return; // Skip super_admin role
                    }
                    html += `<option value="${roleCode}">${r.name}</option>`;
                });
                select.innerHTML = html;
            }
        } catch (e) { console.error('Failed to load roles', e); }
    }

    async function loadBranches() {
        const list = document.getElementById('branchList');
        const urlParams = new URLSearchParams(window.location.search);
        const preSelectedBranchId = urlParams.get('branch_id');

        try {
            const res = await fetch('/api/v1/branches');
            const data = await res.json();

            if (data.success && data.data.branches.length > 0) {
                const select = document.getElementById('branchSelect');
                let selectHtml = '';
                let sidebarHtml = '';

                // Sort: Main Branch first
                const sortedBranches = data.data.branches.sort((a, b) => b.is_main - a.is_main);

                sortedBranches.forEach(b => {
                    const isActive = (preSelectedBranchId == b.branch_id) ? 'active' : '';
                    const mainIndicator = b.is_main ? '<i class="ri-star-fill text-warning ms-1" style="font-size: 8px;"></i>' : '';

                    // Sidebar Item
                    sidebarHtml += `
                        <button type="button" class="list-group-item list-group-item-action ${isActive}" 
                                onclick="selectBranch(${b.branch_id}, this)" data-id="${b.branch_id}">
                            <span class="text-truncate">${b.branch_name} ${mainIndicator}</span>
                        </button>`;

                    // Dropdown Item
                    selectHtml += `<option value="${b.branch_id}" ${(preSelectedBranchId == b.branch_id) ? 'selected' : ''}>${b.branch_name}</option>`;
                });

                list.innerHTML = sidebarHtml;
                select.innerHTML = selectHtml;

                // If no pre-selected, activate first branch in sidebar
                if (!preSelectedBranchId && sortedBranches.length > 0) {
                    const firstBtn = list.querySelector('button');
                    if (firstBtn) firstBtn.click();
                }
            }
        } catch (e) {
            console.error('Failed to load branches', e);
            list.innerHTML = '<div class="p-3 text-danger fs-12">Failed to load nodes.</div>';
        }
    }

    window.selectBranch = function (branchId, btn) {
        // UI Sidebar update
        document.querySelectorAll('#branchList .list-group-item').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');

        // Form update
        const select = document.getElementById('branchSelect');
        select.value = branchId;

        // Regenerate username when branch changes
        generateUsername();
    }

    function generateUsername() {
        const branchSelect = document.getElementById('branchSelect');
        const emailInput = document.getElementById('emailInput');
        const usernameInput = document.getElementById('usernameInput');

        // Get selected branch code
        const selectedOption = branchSelect.options[branchSelect.selectedIndex];
        const branchText = selectedOption ? selectedOption.text : '';

        // Extract branch code (first 4 letters, lowercase)
        let branchCode = branchText.toLowerCase().replace(/[^a-z]/g, '').substring(0, 4);
        if (!branchCode) branchCode = 'main';

        // Get email prefix (before @)
        const email = emailInput.value.trim();
        const emailPrefix = email ? email.split('@')[0].toLowerCase().replace(/[^a-z0-9]/g, '') : '';

        // Generate username: branchcode_emailprefix
        if (branchCode && emailPrefix) {
            usernameInput.value = `${branchCode}_${emailPrefix}`;
        } else if (emailPrefix) {
            usernameInput.value = emailPrefix;
        } else {
            usernameInput.value = '';
        }
    }

    function handleRoleChange() {
        const role = document.getElementById('roleSelect').value;
        const sections = document.querySelectorAll('.role-section');
        const desc = document.getElementById('roleDescription');
        const departmentField = document.getElementById('departmentField');
        const branchField = document.getElementById('branchField');

        // Roles that don't need department
        const noDepartmentRoles = ['pharmacist', 'nurse', 'receptionist', 'front_office'];

        // Show/hide department field based on role
        if (noDepartmentRoles.includes(role)) {
            departmentField.classList.add('d-none');
            // Adjust branch field to take more space
            branchField.classList.remove('col-md-4');
            branchField.classList.add('col-md-8');
        } else {
            departmentField.classList.remove('d-none');
            // Reset branch field width
            branchField.classList.remove('col-md-8');
            branchField.classList.add('col-md-4');
        }

        // Reset all sections and remove required attributes
        sections.forEach(el => {
            el.classList.add('d-none');
            // Remove required from all inputs in hidden sections
            el.querySelectorAll('input, select, textarea').forEach(input => {
                input.removeAttribute('required');
            });
        });

        // Show relevant section and make its fields required
        if (role === 'doctor') {
            const section = document.getElementById('section-doctor');
            section.classList.remove('d-none');
            // Make doctor-specific fields required
            section.querySelector('[name="specialization"]').setAttribute('required', 'required');
            section.querySelector('[name="license_no"]').setAttribute('required', 'required');
            desc.textContent = "Configuring Medical Practitioner. Requires License No & Fee setup.";
            desc.classList.remove('text-muted');
            desc.classList.add('text-primary', 'fw-semibold');
        } else if (role === 'pharmacist') {
            const section = document.getElementById('section-pharmacist');
            section.classList.remove('d-none');
            // Make pharmacist license required
            section.querySelector('input[type="text"]').setAttribute('required', 'required');
            desc.textContent = "Pharmacy Staff. Requires Drug License mapping if responsible for POs.";
            desc.classList.remove('text-muted');
            desc.classList.add('text-success', 'fw-semibold');
        } else if (role === 'receptionist' || role === 'front_office') {
            const section = document.getElementById('section-front_office');
            section.classList.remove('d-none');
            desc.textContent = "Front Office Staff. Configure Counters & Shift.";
            desc.classList.remove('text-muted');
            desc.classList.add('text-info', 'fw-semibold');
        } else if (role === 'nurse') {
            // Nurse role - show basic info only, no special fields needed
            desc.textContent = "Nursing Staff. Standard access to patient vitals and care records.";
            desc.classList.remove('text-muted');
            desc.classList.add('text-warning', 'fw-semibold');
        } else if (role === 'admin' || role === 'super_admin') {
            // Admin roles - no special fields
            desc.textContent = "Administrative Role. Full system access with elevated privileges.";
            desc.classList.remove('text-muted');
            desc.classList.add('text-danger', 'fw-semibold');
        } else {
            desc.textContent = `${role.charAt(0).toUpperCase() + role.slice(1)} role selected. Standard access applies.`;
            desc.classList.remove('text-primary', 'text-success', 'text-info', 'text-warning', 'text-danger', 'fw-semibold');
            desc.classList.add('text-muted');
        }
    }

    function togglePassword() {
        const input = document.getElementById('passwordField');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    // Real API Submit Handler
    document.getElementById('createUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn = this.querySelector('button[type="submit"]');
        const form = new FormData(this);

        // Basic Validation
        if (!document.getElementById('roleSelect').value) {
            showToast('Please select a Role first', 'error');
            return;
        }

        const btnOriginal = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';
        btn.disabled = true;

        // Build Payload
        const payload = {
            role: document.getElementById('roleSelect').value,
            full_name: form.get('full_name'),
            mobile: form.get('mobile'),
            email: form.get('email'),
            username: form.get('username') || form.get('email').split('@')[0], // Auto-gen username if empty
            password: document.getElementById('passwordField').value,
            branch_id: document.getElementById('branchSelect').value || 1, // Use selected branch

            // Specifics
            specialization: form.get('specialization'),
            department: document.getElementById('departmentSelect').value,
            license_no: form.get('license_no'),
            consultation_fee: form.get('consultation_fee'),

            // Flags
            send_email: document.getElementById('sendWelcomeEmail').checked
        };

        try {
            const res = await fetch('/api/v1/users', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast('User account created successfully! Credentials sent.', 'success');
                setTimeout(() => {
                    window.location.href = '/users';
                }, 1000);
            } else {
                showToast(data.message || 'Failed to create user', 'error');
                btn.innerHTML = btnOriginal;
                btn.disabled = false;
            }
        } catch (err) {
            console.error(err);
            showToast('Connection failed. Please try again.', 'error');
            btn.innerHTML = btnOriginal;
            btn.disabled = false;
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>