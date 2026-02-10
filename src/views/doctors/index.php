<?php
/**
 * Doctor Master - Commercial Industry Standard UI
 * High-end management interface for medical staff
 */
$pageTitle = 'Doctor Master';
ob_start();
?>

<!-- Start::page-header -->
<div class="d-flex align-items-center justify-content-between mb-4 page-header-breadcrumb flex-wrap gap-2">
    <div>
        <h1 class="page-title fw-medium fs-20 mb-0">Doctor Master</h1>
        <p class="text-muted mb-0 fs-12">Manage clinical staff, specialties, and schedules.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button class="btn btn-primary btn-wave shadow-sm" onclick="showAddModal()">
            <i class="ri-add-line me-1 align-middle"></i>Add New Doctor
        </button>
    </div>
</div>
<!-- End::page-header -->

<!-- Start:: row-1 (Stats) -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-primary-transparent text-primary">
                        <i class="ri-team-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="totalDoctors">0</h4>
                        <span class="fs-12 fw-medium text-muted">Total Specialists</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-success-transparent text-success">
                        <i class="ri-user-follow-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="activeDoctors">0</h4>
                        <span class="fs-12 fw-medium text-muted">Active & Available</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-info-transparent text-info">
                        <i class="ri-microscope-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="deptsCount">0</h4>
                        <span class="fs-12 fw-medium text-muted">Unique Specialties</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-warning-transparent text-warning">
                        <i class="ri-timer-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="inactiveDoctors">0</h4>
                        <span class="fs-12 fw-medium text-muted">Paused / On Leave</span>
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
                        <input type="text" class="form-control" id="doctorSearch"
                            placeholder="Search by name, specialty, ID..." onkeyup="debounce(fetchDoctors)">
                        <span class="input-group-text bg-light text-muted border-start-0"><i
                                class="ri-search-line"></i></span>
                    </div>
                    <select class="form-select form-select-sm border-0 bg-light" id="specialtyFilter"
                        style="width: 160px;" onchange="fetchDoctors()">
                        <option value="">All Specialties</option>
                    </select>
                </div>
                <div class="btn-group btn-group-sm rounded-pill overflow-hidden border">
                    <button type="button" class="btn btn-primary" onclick="setStatusFilter('', this)">All</button>
                    <button type="button" class="btn btn-light"
                        onclick="setStatusFilter('active', this)">Active</button>
                    <button type="button" class="btn btn-light"
                        onclick="setStatusFilter('inactive', this)">Inactive</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" class="ps-4 fs-11 text-muted text-uppercase">Doctor Details</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Specialty / Dept</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Experience</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Contact Info</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Reg No / License</th>
                                <th scope="col" class="text-center fs-11 text-muted text-uppercase">Status</th>
                                <th scope="col" class="text-end pe-4 fs-11 text-muted text-uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="doctorList">
                            <!-- JS Filled -->
                        </tbody>
                    </table>
                </div>
                <!-- Empty/Loading states integrated into render logic -->
                <div id="loadingState" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 text-muted">Loading specialists...</span>
                </div>
                <div id="emptyState" class="text-center py-5 d-none">
                    <i class="ri-folder-open-line fs-40 text-muted op-3"></i>
                    <p class="text-muted mt-2">No specialist found.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End:: row-2 -->

<!-- Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title fw-medium fs-15 text-white" id="modalTitle">Add New Specialist</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light-transparent">
                <form id="doctorForm" class="row g-3 needs-validation" novalidate>
                    <input type="hidden" id="doctorId">

                    <div class="col-md-6">
                        <label class="form-label fs-13">Doctor Full Name</label>
                        <input type="text" class="form-control" id="fullName" placeholder="Enter name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-13">Specialization</label>
                        <input type="text" class="form-control" id="specialization" placeholder="e.g. Cardiologist"
                            list="specList">
                        <datalist id="specList"></datalist>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-13">Department</label>
                        <input type="text" class="form-control" id="department" placeholder="Enter department">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-13">License / Reg No.</label>
                        <input type="text" class="form-control" id="licenseNo" placeholder="Enter license no">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-13">Experience (Yrs)</label>
                        <input type="number" class="form-control" id="experience" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-13">Consultation Fee</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="consultationFee" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-13">Status</label>
                        <select class="form-select" id="isActive">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-13">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                            placeholder="Enter 10-digit mobile number" maxlength="10" pattern="[0-9]{10}" required>
                        <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-13">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="doctor@example.com" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave shadow-sm px-4">Save Doctor
                            Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    let statusFilter = '';
    let currentDoctors = [];

    document.addEventListener('DOMContentLoaded', () => {
        fetchStats();
        fetchDoctors();
        fetchSpecialties();

        document.getElementById('doctorForm').addEventListener('submit', handleFormSubmit);
    });

    async function fetchStats() {
        try {
            const res = await fetch('/api/v1/doctors/stats');
            const data = await res.json();
            if (data.success) {
                document.getElementById('totalDoctors').textContent = data.data.total;
                document.getElementById('activeDoctors').textContent = data.data.active;
                document.getElementById('deptsCount').textContent = data.data.specialties_count;
                document.getElementById('inactiveDoctors').textContent = data.data.inactive;
            }
        } catch (e) { console.error('Stats error:', e); }
    }

    async function fetchDoctors() {
        const loading = document.getElementById('loadingState');
        const empty = document.getElementById('emptyState');
        const list = document.getElementById('doctorList');

        loading.classList.remove('d-none');
        empty.classList.add('d-none');
        list.innerHTML = '';

        const search = document.getElementById('doctorSearch').value;
        const specialty = document.getElementById('specialtyFilter').value;
        const url = `/api/v1/doctors?search=${encodeURIComponent(search)}&specialty=${encodeURIComponent(specialty)}&status=${statusFilter}`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            loading.classList.add('d-none');

            if (data.success && data.data.doctors.length > 0) {
                currentDoctors = data.data.doctors;
                renderDoctors(data.data.doctors);
            } else {
                empty.classList.remove('d-none');
            }
        } catch (e) {
            loading.classList.add('d-none');
            console.error('Fetch error:', e);
        }
    }

    async function fetchSpecialties() {
        try {
            const res = await fetch('/api/v1/doctors/specialties');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('specialtyFilter');
                const datalist = document.getElementById('specList');

                data.data.specialties.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s;
                    select.appendChild(opt);

                    const dopt = document.createElement('option');
                    dopt.value = s;
                    datalist.appendChild(dopt);
                });
            }
        } catch (e) { console.error('Spec fetch error:', e); }
    }

    function renderDoctors(doctors) {
        document.getElementById('doctorList').innerHTML = doctors.map(d => `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm avatar-rounded bg-primary-transparent text-primary fw-medium">
                            ${d.full_name.charAt(0)}
                        </span>
                        <div>
                            <span class="d-block fw-medium">${d.full_name}</span>
                            <span class="text-muted fs-11">DOC-ID: ${String(d.provider_id).padStart(4, '0')}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-primary-transparent">${d.specialization || 'General'}</span>
                    <span class="d-block text-muted fs-11 mt-1">${d.department || '-'}</span>
                </td>
                <td><span class="fw-medium">${d.experience || 0} Years</span></td>
                <td>
                    <span class="d-block fs-12"><i class="ri-phone-line me-1 text-muted"></i>${d.phone || '-'}</span>
                    <span class="d-block text-muted fs-11 mt-1"><i class="ri-mail-line me-1 text-muted"></i>${d.email || '-'}</span>
                </td>
                <td><span class="badge bg-light text-default border">${d.license_no || 'N/A'}</span></td>
                <td class="text-center">
                    <span class="badge bg-${d.is_active ? 'primary' : 'light text-muted'}-transparent">
                        ${d.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="text-end pe-4">
                    <div class="btn-list">
                        <button class="btn btn-sm btn-icon btn-primary-light" onclick="editDoctor(${d.provider_id})"><i class="ri-pencil-line"></i></button>
                        <button class="btn btn-sm btn-icon btn-danger-light" onclick="deleteDoctor(${d.provider_id})"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function setStatusFilter(status, btn) {
        statusFilter = status;
        const btnGroup = btn.closest('.btn-group');
        btnGroup.querySelectorAll('button').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-primary');
        });
        btn.classList.add('btn-primary');
        btn.classList.remove('btn-outline-primary');
        fetchDoctors();
    }

    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Specialist';
        document.getElementById('doctorForm').reset();
        document.getElementById('doctorId').value = '';
        const modal = new bootstrap.Modal(document.getElementById('doctorModal'));
        modal.show();
    }

    function editDoctor(id) {
        const d = currentDoctors.find(doc => doc.provider_id == id);
        if (!d) return;

        document.getElementById('modalTitle').textContent = 'Edit Specialist Details';
        document.getElementById('doctorId').value = d.provider_id;
        document.getElementById('fullName').value = d.full_name;
        document.getElementById('specialization').value = d.specialization;
        document.getElementById('department').value = d.department;
        document.getElementById('licenseNo').value = d.license_no;
        document.getElementById('experience').value = d.experience;
        document.getElementById('consultationFee').value = d.consultation_fee;
        document.getElementById('isActive').value = d.is_active;
        document.getElementById('phone').value = d.phone;
        document.getElementById('email').value = d.email;

        const modal = new bootstrap.Modal(document.getElementById('doctorModal'));
        modal.show();
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('doctorId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/api/v1/doctors/${id}` : '/api/v1/doctors';

        const data = {
            full_name: document.getElementById('fullName').value,
            specialization: document.getElementById('specialization').value,
            department: document.getElementById('department').value,
            license_no: document.getElementById('licenseNo').value,
            experience: document.getElementById('experience').value,
            consultation_fee: document.getElementById('consultationFee').value,
            is_active: document.getElementById('isActive').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value
        };

        try {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();

            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('doctorModal')).hide();
                showToast('Doctor profile saved successfully!', 'success');
                fetchDoctors();
                fetchStats();
            } else {
                showToast(result.message, 'error');
            }
        } catch (e) { showToast('Connection failed!', 'error'); }
    }

    async function deleteDoctor(id) {
        const confirm = await Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to remove this specialist profile?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Delete'
        });

        if (confirm.isConfirmed) {
            try {
                const res = await fetch(`/api/v1/doctors/${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    fetchDoctors();
                    fetchStats();
                }
            } catch (e) { showToast('Delete failed!', 'error'); }
        }
    }

    function resetFilters() {
        document.getElementById('doctorSearch').value = '';
        document.getElementById('specialtyFilter').value = '';
        statusFilter = '';
        document.querySelectorAll('.btn-group .btn').forEach(b => {
            b.classList.remove('active');
            if (b.textContent === 'All') b.classList.add('active');
        });
        fetchDoctors();
    }

    // Helpers
    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    function showToast(msg, type) {
        const bg = { success: '#10b981', error: '#ef4444' }[type] || '#333';
        Toastify({
            text: msg,
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: bg,
        }).showToast();
    }
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>