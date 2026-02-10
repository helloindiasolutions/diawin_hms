<?php
$pageTitle = "IP Admissions";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">In-Patient Admissions</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">In-Patient</a></li>
            <li class="breadcrumb-item active" aria-current="page">Admissions</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" onclick="IPAdmission.open()" accesskey="n">
            <i class="ri-add-line align-middle me-1"></i> New Admission <span
                class="badge bg-white text-primary ms-1">Alt+N</span>
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-fill">
                        <p class="mb-0 text-muted fs-12">Currently Admitted</p>
                        <h4 class="fw-semibold mb-0" id="currentAdmissions">0</h4>
                    </div>
                    <div class="avatar avatar-md bg-primary-transparent text-primary">
                        <i class="ri-hotel-bed-line fs-18"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-fill">
                        <p class="mb-0 text-muted fs-12">Pending Discharges</p>
                        <h4 class="fw-semibold mb-0">0</h4>
                    </div>
                    <div class="avatar avatar-md bg-warning-transparent text-warning">
                        <i class="ri-logout-box-r-line fs-18"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Admission Records</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Patient Details</th>
                                <th>Ward / Room</th>
                                <th>Admission Date</th>
                                <th>Admitting Doctor</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="admissionsList">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .clickable-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .clickable-row:hover {
        background-color: rgba(var(--primary-rgb), 0.05) !important;
    }
</style>


<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    function initAdmissionsPage() {
        fetchStats();
        fetchAdmissions();

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Alt+N for new admission
            if (e.altKey && e.key === 'n') {
                e.preventDefault();
                IPAdmission.open();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdmissionsPage);
    } else {
        initAdmissionsPage();
    }

    async function fetchStats() {
        try {
            const res = await fetch('/api/v1/ipd/stats');
            const data = await res.json();
            if (data.success) {
                document.getElementById('currentAdmissions').innerText = data.data.currently_admitted || 0;
                document.querySelector('.col-xl-3:nth-child(2) h4').innerText = data.data.pending_discharges || 0;
            }
        } catch (e) {
            console.error('Failed to load stats:', e);
        }
    }

    async function fetchAdmissions() {
        const list = document.getElementById('admissionsList');
        list.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const res = await fetch('/api/v1/ipd/admissions?status=Active');
            const data = await res.json();
            list.innerHTML = '';

            if (data.success && data.data.admissions.length > 0) {
                data.data.admissions.forEach(a => {
                    const date = new Date(a.admission_date).toLocaleDateString();
                    const statusClass = a.admission_status === 'Active' ? 'success' : 'secondary';

                    const row = document.createElement('tr');
                    row.className = 'clickable-row';
                    row.onclick = (e) => {
                        // Prevent click event if an action button was clicked
                        if (e.target.closest('button')) return;
                        viewAdmission(a.admission_id);
                    };
                    row.innerHTML = `
                    <td class="ps-4">
                        <div class="fw-semibold">${a.first_name} ${a.last_name || ''}</div>
                        <div class="text-muted fs-11">MRN: ${a.mrn} | Admission #${a.admission_number}</div>
                    </td>
                    <td>
                        <span class="d-block fw-medium">${a.ward_name || 'Not Assigned'}</span>
                        <span class="text-muted fs-11">Bed: ${a.bed_number || 'N/A'}</span>
                    </td>
                    <td>${date}</td>
                    <td>${a.doctor_name || 'Not Assigned'}</td>
                    <td><span class="badge bg-${statusClass}-transparent">${a.admission_status}</span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-icon btn-primary-light rounded-pill" onclick="viewAdmission(${a.admission_id})" title="View Details">
                            <i class="ri-eye-line"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-info-light rounded-pill" onclick="window.location.href='/ip/nursing-notes?admission_id=${a.admission_id}'" title="Nursing Notes">
                            <i class="ri-nurse-line"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-success-light rounded-pill" onclick="window.location.href='/ip/rounds?admission_id=${a.admission_id}'" title="Doctor Rounds">
                            <i class="ri-stethoscope-line"></i>
                        </button>
                    </td>
                `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No admission records found.</td></tr>';
            }
        } catch (e) {
            console.error(e);
            list.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load admissions.</td></tr>';
        }
    }

    function viewAdmission(admissionId) {
        window.location.href = `/ip/admission-details?admission_id=${admissionId}`;
    }
</script>
<?php include_once ROOT_PATH . '/src/views/ip/partials/admission_modal.php'; ?>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>