<?php
$pageTitle = "Admission Details";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Admission Details</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/ip/admissions">In-Patient</a></li>
            <li class="breadcrumb-item"><a href="/ip/admissions">Admissions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-light btn-wave" onclick="window.history.back()">
            <i class="ri-arrow-left-line me-1"></i> Back
        </button>
    </div>
</div>

<!-- Patient & Admission Info -->
<div class="row" id="admissionDetailsContainer">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary"></div>
        <p class="mt-2 text-muted">Loading admission details...</p>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    const admissionId = new URLSearchParams(window.location.search).get('admission_id');

    function initAdmissionDetails() {
        if (!admissionId) {
            showError('No admission ID provided');
            return;
        }
        fetchAdmissionDetails();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdmissionDetails);
    } else {
        initAdmissionDetails();
    }

    async function fetchAdmissionDetails() {
        const container = document.getElementById('admissionDetailsContainer');

        try {
            const res = await fetch(`/api/v1/ipd/admissions/${admissionId}`);
            const data = await res.json();

            if (data.success && data.data.admission) {
                renderAdmissionDetails(data.data.admission);
            } else {
                showError('Admission not found');
            }
        } catch (e) {
            console.error(e);
            showError('Failed to load admission details');
        }
    }

    function renderAdmissionDetails(admission) {
        const container = document.getElementById('admissionDetailsContainer');

        const statusClass = admission.admission_status === 'Active' ? 'success' :
            admission.admission_status === 'Discharged' ? 'secondary' : 'warning';

        container.innerHTML = `
            <!-- Patient Info Card -->
            <div class="col-xl-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Patient Information</div>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar avatar-xxl bg-primary-transparent text-primary rounded-circle mx-auto mb-3">
                                <span class="fs-32 fw-bold">${admission.patient_name ? admission.patient_name[0] : 'P'}</span>
                            </div>
                            <h5 class="mb-1">${admission.patient_name || 'N/A'}</h5>
                            <p class="text-muted mb-0">MRN: ${admission.mrn || 'N/A'}</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="text-muted">Age/Gender:</td>
                                    <td class="fw-medium">${admission.age || '--'} / ${admission.gender || '--'}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Blood Group:</td>
                                    <td class="fw-medium">${admission.blood_group || 'Not recorded'}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone:</td>
                                    <td class="fw-medium">${admission.primary_mobile || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admission Info Card -->
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">Admission Information</div>
                        <span class="badge bg-${statusClass}-transparent">${admission.admission_status || 'Unknown'}</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted fs-12">Admission Number</label>
                                <p class="fw-semibold mb-0">${admission.admission_number || 'N/A'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted fs-12">Admission Date</label>
                                <p class="fw-semibold mb-0">${formatDateTime(admission.admission_date)}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted fs-12">Admission Type</label>
                                <p class="fw-semibold mb-0">${admission.admission_type || 'N/A'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted fs-12">Department</label>
                                <p class="fw-semibold mb-0">${admission.department || 'N/A'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted fs-12">Primary Doctor</label>
                                <p class="fw-semibold mb-0">${admission.doctor_name || 'N/A'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted fs-12">Ward / Bed</label>
                                <p class="fw-semibold mb-0">${admission.ward_name || 'N/A'} / ${admission.bed_number || 'N/A'}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="text-muted fs-12">Chief Complaint</label>
                                <p class="mb-0">${admission.admission_reason || 'Not recorded'}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="text-muted fs-12">Provisional Diagnosis</label>
                                <p class="mb-0">${admission.provisional_diagnosis || 'Not recorded'}</p>
                            </div>
                            ${admission.estimated_discharge_date ? `
                            <div class="col-md-6 mb-3">
                                <label class="text-muted fs-12">Estimated Discharge</label>
                                <p class="fw-semibold mb-0">${formatDate(admission.estimated_discharge_date)}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Quick Actions</div>
                    </div>
                    <div class="card-body">
                        <div class="btn-list">
                            <a href="/ip/nursing-notes?admission_id=${admissionId}" class="btn btn-primary btn-wave">
                                <i class="ri-file-list-3-line me-1"></i> Nursing Notes
                            </a>
                            <a href="/ip/rounds?admission_id=${admissionId}" class="btn btn-info btn-wave">
                                <i class="ri-stethoscope-line me-1"></i> Doctor Rounds
                            </a>
                            <button class="btn btn-success btn-wave" onclick="alert('Billing functionality coming soon')">
                                <i class="ri-money-dollar-circle-line me-1"></i> View Billing
                            </button>
                            <button class="btn btn-warning btn-wave" onclick="alert('Discharge functionality coming soon')">
                                <i class="ri-logout-box-r-line me-1"></i> Discharge Patient
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function showError(message) {
        const container = document.getElementById('admissionDetailsContainer');
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <i class="ri-error-warning-line me-2"></i> ${message}
                </div>
                <button class="btn btn-light" onclick="window.history.back()">
                    <i class="ri-arrow-left-line me-1"></i> Go Back
                </button>
            </div>
        `;
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>