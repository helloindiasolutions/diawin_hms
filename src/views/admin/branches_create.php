<?php
/**
 * Create New Branch
 */
$pageTitle = "Add New Branch";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Add New Hospital Branch</h2>
        <span class="text-muted fs-12">Expand your medical network by configuring a new facility location</span>
    </div>
    <div class="ms-auto">
        <a href="/branches" class="btn btn-light btn-sm btn-wave waves-effect waves-light">
            <i class="ri-arrow-left-line align-middle me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Establishment Profile</div>
            </div>
            <div class="card-body p-4">
                <form id="createBranchForm" class="row g-4 needs-validation" novalidate>
                    <!-- Identity -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                            placeholder="e.g. Melina Hospital - North Wing" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Short Code</label>
                        <input type="text" name="code" id="branchCode" class="form-control bg-light"
                            placeholder="Auto-generated" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Type</label>
                        <select class="form-select" name="type">
                            <option value="multi">Multi-Specialty</option>
                            <option value="clinic">Clinic / OPD Only</option>
                            <option value="diag">Diagnostic Center</option>
                        </select>
                    </div>

                    <!-- Contact -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Primary Contact Email</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@northwing.com">
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Contact Person Name</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="In-charge Name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Phone Support <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile number"
                            maxlength="10" pattern="[0-9]{10}" required>
                        <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                    </div>

                    <!-- Logistics -->
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Physical Address</label>
                        <textarea name="address" class="form-control" rows="3"
                            placeholder="Full postal address of the branch location..."></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">City / District</label>
                        <input type="text" name="city" class="form-control" placeholder="Chennai">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">State</label>
                        <input type="text" name="state" class="form-control" placeholder="Tamil Nadu">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Pincode</label>
                        <input type="text" name="pincode" class="form-control" placeholder="600XXX">
                    </div>

                    <hr class="my-4 op-1">

                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-switch form-check-input" type="checkbox" role="switch"
                                id="activeStatus" checked>
                            <label class="form-check-label ps-2 fw-semibold" for="activeStatus">Initial Operational
                                Status (Active)</label>
                        </div>
                        <div class="btn-list">
                            <button type="button" class="btn btn-light me-2"
                                onclick="window.location.href='/branches'">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Register &
                                Initialize Branch</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-gen Short Code
    document.querySelector('input[name="name"]').addEventListener('input', function () {
        const name = this.value.trim();
        const codeField = document.getElementById('branchCode');
        if (!name) { codeField.value = ''; return; }

        let code = '';
        const words = name.split(/\s+/);
        if (words.length > 1) {
            code = words.map(w => w[0]).join('').substring(0, 4);
        } else {
            code = name.substring(0, 4);
        }
        codeField.value = code.toUpperCase() + '-' + Math.floor(100 + Math.random() * 900);
    });

    document.getElementById('createBranchForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('/api/v1/branches', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast('New branch initialized successfully!', 'success');
                setTimeout(() => window.location.href = '/branches', 1500);
            } else {
                showToast(data.message || 'Validation failed', 'error');
                btn.disabled = false;
                btn.innerHTML = original;
            }
        } catch (err) {
            showToast('System connectivity error', 'error');
            btn.disabled = false;
            btn.innerHTML = original;
        }
    });
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>