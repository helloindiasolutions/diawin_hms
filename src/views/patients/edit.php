<?php
/**
 * Patient Edit - Full Page Form
 * Zoho-style edit experience
 */
$pageTitle = 'Edit Patient';
$patientId = $patient_id ?? 0;
ob_start();
?>

<style>
    .form-section {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-section-title i {
        font-size: 18px;
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<!-- Page Header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= baseUrl('/patients') ?>" class="btn btn-icon btn-light rounded-circle">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="page-title fw-medium fs-18 mb-0">Edit Patient</h1>
                <span class="text-muted fs-13" id="patientMRN">Loading...</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-light" onclick="window.location.href='<?= baseUrl('/patients') ?>'">Cancel</button>
            <button class="btn btn-primary" id="saveBtn" onclick="savePatient()">
                <span class="spinner-border spinner-border-sm d-none me-2" id="saveSpinner"></span>
                <i class="ri-save-line me-1" id="saveIcon"></i>Save Changes
            </button>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <form id="patientForm" class="needs-validation" novalidate>
            <!-- Basic Information -->
            <div class="form-section">
                <div class="form-section-title"><i class="ri-user-line"></i> Basic Information</div>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Title</label>
                        <select class="form-select" name="title" id="title">
                            <option value="">--</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Ms.">Ms.</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Master">Master</option>
                            <option value="Baby">Baby</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="first_name" id="first_name" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="last_name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender" id="gender">
                            <option value="unknown">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="dob" id="dob" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Age</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="age" id="age" min="0" max="150">
                            <span class="input-group-text">Years</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Blood Group</label>
                        <select class="form-select" name="blood_group" id="blood_group">
                            <option value="">Unknown</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <div class="form-section-title"><i class="ri-phone-line"></i> Contact Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="mobile" id="mobile" maxlength="10"
                            pattern="[0-9]{10}" required placeholder="10-digit number">
                        <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email"
                            placeholder="example@email.com">
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="form-section">
                <div class="form-section-title"><i class="ri-map-pin-line"></i> Address</div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Street Address</label>
                        <textarea class="form-control" name="address" id="address" rows="2"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" id="city">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">State</label>
                        <input type="text" class="form-control" name="state" id="state">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" name="country" id="country">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pincode</label>
                        <input type="text" class="form-control" name="pincode" id="pincode">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script src="<?= asset('libs/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>
    const patientId = <?= (int) $patientId ?>;

    document.addEventListener('DOMContentLoaded', function () {
        loadPatient();

        // DOB change - calculate age
        document.getElementById('dob').addEventListener('change', function () {
            if (this.value) {
                const dob = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
                document.getElementById('age').value = age;
            }
        });
    });

    async function loadPatient() {
        try {
            const res = await fetch('/api/v1/patients/' + patientId);
            const data = await res.json();

            if (data.success) {
                const p = data.data.patient;
                document.getElementById('patientMRN').textContent = 'MRN: ' + p.mrn;

                // Fill form
                ['title', 'first_name', 'last_name', 'gender', 'dob', 'age', 'blood_group',
                    'mobile', 'email', 'address', 'city', 'state', 'country', 'pincode'].forEach(f => {
                        const el = document.getElementById(f);
                        if (el && p[f]) el.value = p[f];
                    });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Patient not found' }).then(() => {
                    window.location.href = '<?= baseUrl('/patients') ?>';
                });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load patient' });
        }
    }

    async function savePatient() {
        const form = document.getElementById('patientForm');
        const formData = {};
        new FormData(form).forEach((v, k) => formData[k] = v);

        if (!formData.first_name?.trim()) {
            Swal.fire({ icon: 'warning', title: 'Required', text: 'First name is required' });
            return;
        }

        document.getElementById('saveSpinner').classList.remove('d-none');
        document.getElementById('saveIcon').classList.add('d-none');
        document.getElementById('saveBtn').disabled = true;

        try {
            const res = await fetch('/api/v1/patients/' + patientId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(formData)
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Saved!', text: 'Patient updated successfully', timer: 1500, showConfirmButton: false }).then(() => {
                    window.location.href = '<?= baseUrl('/patients') ?>';
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Network error' });
        } finally {
            document.getElementById('saveSpinner').classList.add('d-none');
            document.getElementById('saveIcon').classList.remove('d-none');
            document.getElementById('saveBtn').disabled = false;
        }
    }
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>