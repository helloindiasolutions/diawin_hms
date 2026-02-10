<?php
$pageTitle = "Ward Details";
$wardId = $_GET['ward_id'] ?? null;
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1" id="wardName">Loading...</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">In-Patient</a></li>
            <li class="breadcrumb-item"><a href="/ip/wards">Wards</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ward Details</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-light btn-wave" onclick="window.history.back()">
            <i class="ri-arrow-left-line me-1"></i> Back
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card custom-card border-0 overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle">
                            <i class="ri-hotel-bed-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" id="ward_total_beds">0</h5>
                        <p class="mb-0 text-muted fs-12">Total Beds</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card custom-card border-0 overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-success-transparent text-success rounded-circle">
                            <i class="ri-checkbox-circle-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" id="ward_available_beds">0</h5>
                        <p class="mb-0 text-muted fs-12">Available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card custom-card border-0 overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-danger-transparent text-danger rounded-circle">
                            <i class="ri-user-follow-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" id="ward_occupied_beds">0</h5>
                        <p class="mb-0 text-muted fs-12">Occupied</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card custom-card border-0 overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-info-transparent text-info rounded-circle">
                            <i class="ri-percent-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" id="ward_occupancy_rate">0%</h5>
                        <p class="mb-0 text-muted fs-12">Occupancy Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Beds Grid -->
<div class="card custom-card">
    <div class="card-header">
        <div class="card-title">Beds in this Ward</div>
    </div>
    <div class="card-body">
        <div class="row" id="wardBedsGrid">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
    </div>
</div>

<!-- Bed Details Modal -->
<div class="modal fade" id="bedDetailsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <div>
                    <h5 class="modal-title mb-0" id="bedDetailsTitle">Bed Details</h5>
                    <small id="bedDetailsSubtitle" class="text-white-50"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    <!-- Left Panel: Current Patient Info (Fixed & Scrollable) -->
                    <div class="col-md-5 border-end bg-light d-flex flex-column h-100"
                        style="max-height: 85vh; overflow-y: auto;">
                        <div class="p-4">
                            <h6 class="fw-bold mb-3 text-primary position-sticky top-0 bg-light py-2 z-1 border-bottom">
                                <i class="ri-user-line me-2"></i>Current Occupant
                            </h6>
                            <div id="currentPatientInfo">
                                <div class="text-center py-5 text-muted">
                                    <i class="ri-user-unfollow-line fs-48 mb-3 d-block opacity-25"></i>
                                    <p>Bed is currently available</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel: Tabs for History (Fixed Header, Scrollable Content) -->
                    <div class="col-md-7 d-flex flex-column h-100" style="max-height: 85vh;">
                        <div class="p-4 flex-grow-1 d-flex flex-column overflow-hidden">
                            <ul class="nav nav-tabs mb-3 flex-shrink-0" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="bedHistory-tab" data-bs-toggle="tab"
                                        data-bs-target="#bedHistory" type="button" role="tab" aria-controls="bedHistory"
                                        aria-selected="true">
                                        <i class="ri-history-line me-1"></i>Bed History
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="bedStats-tab" data-bs-toggle="tab"
                                        data-bs-target="#bedStats" type="button" role="tab" aria-controls="bedStats"
                                        aria-selected="false">
                                        <i class="ri-bar-chart-line me-1"></i>Statistics
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content flex-grow-1 overflow-auto pe-2" style="scrollbar-width: thin;">
                                <!-- Bed History Tab -->
                                <div class="tab-pane fade show active" id="bedHistory" role="tabpanel"
                                    aria-labelledby="bedHistory-tab">
                                    <div id="bedHistoryContent">
                                        <div class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-primary"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistics Tab -->
                                <div class="tab-pane fade" id="bedStats" role="tabpanel" aria-labelledby="bedStats-tab">
                                    <div id="bedStatsContent">
                                        <div class="text-center py-4 text-muted">
                                            Loading statistics...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<style>
    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .hover-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15) !important;
    }

    .bed-card-patient-info {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 8px;
        padding: 8px;
    }

    .timeline {
        position: relative;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-item:not(:last-child) .timeline-marker::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 40px;
        bottom: -40px;
        width: 2px;
        background: #e9ecef;
        transform: translateX(-50%);
    }

    .timeline-marker {
        position: relative;
        z-index: 1;
    }
</style>
<script>
    const wardId = <?= json_encode($wardId) ?>;

    function initWardDetailsPage() {
        if (!wardId) {
            alert('Ward ID is required');
            window.history.back();
            return;
        }

        loadWardDetails();

        // Prevent tab clicks from closing modal
        const bedDetailsModal = document.getElementById('bedDetailsModal');
        if (bedDetailsModal) {
            bedDetailsModal.addEventListener('show.bs.modal', function () {
                const tabButtons = bedDetailsModal.querySelectorAll('[data-bs-toggle="tab"]');
                tabButtons.forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });
                });
            });
        }
    }

    // SPA Support: Run immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWardDetailsPage);
    } else {
        initWardDetailsPage();
    }

    async function loadWardDetails() {
        try {
            // Fetch ward info
            const wardRes = await fetch('/api/v1/ipd/wards');
            const wardData = await wardRes.json();

            if (wardData.success && wardData.data.wards) {
                const ward = wardData.data.wards.find(w => w.ward_id == wardId);
                if (ward) {
                    document.getElementById('wardName').textContent = ward.ward_name;
                }
            }

            // Load ward beds
            await loadWardBeds();
        } catch (e) {
            console.error('Failed to load ward details:', e);
            alert('Failed to load ward details');
        }
    }

    async function loadWardBeds() {
        const container = document.getElementById('wardBedsGrid');
        container.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';

        try {
            // Fetch beds for this ward
            const res = await fetch(`/api/v1/ipd/beds?ward_id=${wardId}`);
            const data = await res.json();

            if (data.success && data.data.beds && data.data.beds.length > 0) {
                const beds = data.data.beds;

                // Update stats
                const totalBeds = beds.length;
                const availableBeds = beds.filter(b => b.bed_status === 'Available').length;
                const occupiedBeds = totalBeds - availableBeds;
                const occupancyRate = totalBeds > 0 ? ((occupiedBeds / totalBeds) * 100).toFixed(1) : 0;

                document.getElementById('ward_total_beds').textContent = totalBeds;
                document.getElementById('ward_available_beds').textContent = availableBeds;
                document.getElementById('ward_occupied_beds').textContent = occupiedBeds;
                document.getElementById('ward_occupancy_rate').textContent = occupancyRate + '%';

                // Render beds
                container.innerHTML = '';

                for (const bed of beds) {
                    const isAvailable = bed.bed_status === 'Available';
                    const statusClass = isAvailable ? 'bg-success-transparent' : 'bg-danger-transparent';
                    const iconClass = isAvailable ? 'text-success' : 'text-danger';

                    const bedCard = document.createElement('div');
                    bedCard.className = 'col-xxl-2 col-xl-3 col-lg-4 col-md-6 mb-3';

                    // If bed is occupied, fetch patient details
                    let patientInfo = '';
                    if (!isAvailable) {
                        try {
                            const admissionRes = await fetch(`/api/v1/ipd/admissions?bed_id=${bed.bed_id}&status=Active`);
                            const admissionData = await admissionRes.json();

                            if (admissionData.success && admissionData.data.admissions && admissionData.data.admissions.length > 0) {
                                const admission = admissionData.data.admissions[0];
                                const admissionDate = new Date(admission.admission_date);
                                const daysStayed = Math.ceil((new Date() - admissionDate) / (1000 * 60 * 60 * 24));
                                const dailyRate = parseFloat(bed.daily_rate || 500);
                                const estimatedCharges = dailyRate * daysStayed;

                                patientInfo = `
                                    <div class="border-top mt-3 pt-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar avatar-sm bg-primary rounded-circle me-2">
                                                <span class="fs-12 fw-bold text-white">${admission.first_name[0]}</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fs-12 fw-bold text-truncate">${admission.first_name} ${admission.last_name || ''}</div>
                                                <div class="fs-10 text-muted">MRN: ${admission.mrn}</div>
                                            </div>
                                        </div>
                                        <div class="bed-card-patient-info">
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <div class="fs-9 text-muted">Days Stayed</div>
                                                    <div class="fs-11 fw-bold text-primary">${daysStayed} Days</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fs-9 text-muted">Est. Charges</div>
                                                    <div class="fs-11 fw-bold text-success">₹${estimatedCharges.toLocaleString('en-IN')}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fs-9 text-muted">Age / Gender</div>
                                                    <div class="fs-11 fw-bold">${admission.age || '--'} / ${admission.gender || '--'}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fs-9 text-muted">Blood Group</div>
                                                    <div class="fs-11 fw-bold">${admission.blood_group || 'N/A'}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                        } catch (e) {
                            console.error('Failed to load patient for bed:', bed.bed_id, e);
                        }
                    }

                    bedCard.innerHTML = `
                        <div class="card custom-card hover-card ${statusClass} border-0 shadow-sm h-100" style="cursor: pointer;" onclick="viewBedDetails(${bed.bed_id}, '${bed.bed_number}', '${bed.ward_name}', '${bed.bed_status}')">
                            <div class="card-body p-3">
                                <div class="text-center mb-2">
                                    <i class="ri-hotel-bed-line fs-32 ${iconClass} mb-2 d-block"></i>
                                    <h6 class="mb-1 fw-bold">${bed.bed_number}</h6>
                                    <p class="fs-11 text-muted mb-2">${bed.bed_type.toUpperCase()}</p>
                                    <span class="badge ${isAvailable ? 'bg-success' : 'bg-danger'} fs-10 rounded-pill">${bed.bed_status}</span>
                                    ${bed.daily_rate ? `<div class="fs-10 text-muted mt-1">₹${parseFloat(bed.daily_rate).toFixed(0)}/day</div>` : ''}
                                </div>
                                ${patientInfo}
                            </div>
                        </div>
                    `;
                    container.appendChild(bedCard);
                }
            } else {
                container.innerHTML = `
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="ri-hotel-bed-line fs-48 mb-3 d-block opacity-25"></i>
                        <p>No beds configured in this ward</p>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Failed to load ward beds:', e);
            container.innerHTML = `
                <div class="col-12 text-center py-5 text-danger">
                    <i class="ri-error-warning-line fs-48 mb-3 d-block"></i>
                    <p>Failed to load ward beds</p>
                </div>
            `;
        }
    }

    // View Bed Details Function
    async function viewBedDetails(bedId, bedNumber, wardName, bedStatus) {
        const modalElement = document.getElementById('bedDetailsModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });

        // Set modal title
        document.getElementById('bedDetailsTitle').textContent = bedNumber;
        document.getElementById('bedDetailsSubtitle').textContent = `${wardName} • ${bedStatus}`;

        // Reset tabs to first tab
        const firstTab = document.querySelector('#bedHistory-tab');
        if (firstTab) {
            const tab = new bootstrap.Tab(firstTab);
            tab.show();
        }

        modal.show();

        // Load current patient info
        await loadCurrentPatient(bedId, bedStatus);

        // Load bed history
        await loadBedHistory(bedId);

        // Load bed statistics
        await loadBedStatistics(bedId);

        // Ensure backdrop is properly removed when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }, { once: true });
    }

    async function loadCurrentPatient(bedId, bedStatus) {
        const container = document.getElementById('currentPatientInfo');

        if (bedStatus === 'Available') {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="ri-user-unfollow-line fs-48 mb-3 d-block opacity-25"></i>
                    <p>Bed is currently available</p>
                </div>
            `;
            return;
        }

        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const res = await fetch(`/api/v1/ipd/admissions?bed_id=${bedId}&status=Active`);
            const data = await res.json();

            if (data.success && data.data.admissions && data.data.admissions.length > 0) {
                const admission = data.data.admissions[0];
                const patient = admission;

                const admissionDate = new Date(admission.admission_date);
                const today = new Date();
                const daysStayed = Math.ceil((today - admissionDate) / (1000 * 60 * 60 * 24));

                const dailyRate = parseFloat(admission.daily_rate || 500);
                const estimatedCharges = dailyRate * daysStayed;

                container.innerHTML = `
                    <div class="card custom-card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-lg bg-primary rounded-circle me-3">
                                    <span class="fs-20 fw-bold text-white">${patient.first_name[0]}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-0 fw-bold">${patient.first_name} ${patient.last_name || ''}</h5>
                                    <small class="text-muted">MRN: ${patient.mrn}</small>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded">
                                        <small class="text-muted d-block">Age / Gender</small>
                                        <strong>${patient.age || '--'} / ${patient.gender || '--'}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded">
                                        <small class="text-muted d-block">Blood Group</small>
                                        <strong>${patient.blood_group || 'N/A'}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border-top pt-3 mb-3">
                                <h6 class="fw-bold mb-2">Admission Details</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Admission #</small>
                                        <strong>${admission.admission_number}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Days Stayed</small>
                                        <strong class="text-primary">${daysStayed} Days</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Admitted On</small>
                                        <strong>${new Date(admission.admission_date).toLocaleDateString()}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Primary Doctor</small>
                                        <strong>${admission.doctor_name || 'Not Assigned'}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border-top pt-3 mb-3">
                                <h6 class="fw-bold mb-2">Financial Summary</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Daily Rate</small>
                                        <strong>₹${dailyRate.toLocaleString('en-IN')}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Est. Bed Charges</small>
                                        <strong class="text-success">₹${estimatedCharges.toLocaleString('en-IN')}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            ${admission.admission_reason ? `
                            <div class="border-top pt-3 mb-3">
                                <h6 class="fw-bold mb-2">Chief Complaint</h6>
                                <p class="mb-0 fs-13">${admission.admission_reason}</p>
                            </div>
                            ` : ''}
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-wave" onclick="navigateToPatient(${patient.patient_id})">
                                    <i class="ri-user-line me-2"></i>View Full Patient Profile
                                </button>
                                <button class="btn btn-outline-primary btn-wave" onclick="navigateToAdmission(${admission.admission_id})">
                                    <i class="ri-file-list-line me-2"></i>View Admission Details
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="ri-error-warning-line fs-48 mb-3 d-block opacity-25"></i>
                        <p>No active admission found for this bed</p>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Failed to load current patient:', e);
            container.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="ri-error-warning-line fs-48 mb-3 d-block"></i>
                    <p>Failed to load patient information</p>
                </div>
            `;
        }
    }

    async function loadBedHistory(bedId) {
        const container = document.getElementById('bedHistoryContent');
        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const res = await fetch(`/api/v1/ipd/admissions?bed_id=${bedId}`);
            const data = await res.json();

            if (data.success && data.data.admissions && data.data.admissions.length > 0) {
                let historyHtml = '<div class="timeline">';

                data.data.admissions.forEach((admission, index) => {
                    const admissionDate = new Date(admission.admission_date);
                    const dischargeDate = admission.discharge_date ? new Date(admission.discharge_date) : null;
                    const daysStayed = dischargeDate
                        ? Math.ceil((dischargeDate - admissionDate) / (1000 * 60 * 60 * 24))
                        : Math.ceil((new Date() - admissionDate) / (1000 * 60 * 60 * 24));

                    const statusClass = admission.admission_status === 'Active' ? 'success' : 'secondary';

                    historyHtml += `
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker">
                                    <div class="avatar avatar-sm bg-${statusClass}-transparent text-${statusClass} rounded-circle">
                                        <i class="ri-user-line"></i>
                                    </div>
                                </div>
                                <div class="timeline-content flex-grow-1 ms-3">
                                    <div class="card custom-card border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">${admission.first_name} ${admission.last_name || ''}</h6>
                                                    <small class="text-muted">MRN: ${admission.mrn} | Admission #${admission.admission_number}</small>
                                                </div>
                                                <span class="badge bg-${statusClass}">${admission.admission_status}</span>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Admitted</small>
                                                    <strong class="fs-12">${admissionDate.toLocaleDateString()}</strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Duration</small>
                                                    <strong class="fs-12 text-primary">${daysStayed} Days</strong>
                                                </div>
                                                ${dischargeDate ? `
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Discharged</small>
                                                    <strong class="fs-12">${dischargeDate.toLocaleDateString()}</strong>
                                                </div>
                                                ` : ''}
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Doctor</small>
                                                    <strong class="fs-12">${admission.doctor_name || 'N/A'}</strong>
                                                </div>
                                            </div>
                                            ${admission.admission_reason ? `
                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-muted d-block mb-1">Chief Complaint</small>
                                                <p class="mb-0 fs-12">${admission.admission_reason}</p>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                historyHtml += '</div>';
                container.innerHTML = historyHtml;
            } else {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="ri-history-line fs-48 mb-3 d-block opacity-25"></i>
                        <p>No admission history for this bed</p>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Failed to load bed history:', e);
            container.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <p>Failed to load bed history</p>
                </div>
            `;
        }
    }

    async function loadBedStatistics(bedId) {
        const container = document.getElementById('bedStatsContent');

        try {
            const res = await fetch(`/api/v1/ipd/admissions?bed_id=${bedId}`);
            const data = await res.json();

            if (data.success && data.data.admissions) {
                const admissions = data.data.admissions;
                const totalAdmissions = admissions.length;
                const activeAdmissions = admissions.filter(a => a.admission_status === 'Active').length;
                const completedAdmissions = admissions.filter(a => a.admission_status === 'Discharged').length;

                let totalDays = 0;
                admissions.forEach(a => {
                    const admissionDate = new Date(a.admission_date);
                    const endDate = a.discharge_date ? new Date(a.discharge_date) : new Date();
                    const days = Math.ceil((endDate - admissionDate) / (1000 * 60 * 60 * 24));
                    totalDays += days;
                });
                const avgStay = totalAdmissions > 0 ? (totalDays / totalAdmissions).toFixed(1) : 0;

                const occupancyRate = totalAdmissions > 0 ? ((totalDays / 30) * 100).toFixed(1) : 0;

                container.innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card custom-card border-0 bg-primary-transparent">
                                <div class="card-body text-center">
                                    <h3 class="mb-1 fw-bold text-primary">${totalAdmissions}</h3>
                                    <p class="mb-0 text-muted fs-13">Total Admissions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card custom-card border-0 bg-success-transparent">
                                <div class="card-body text-center">
                                    <h3 class="mb-1 fw-bold text-success">${activeAdmissions}</h3>
                                    <p class="mb-0 text-muted fs-13">Currently Active</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card custom-card border-0 bg-info-transparent">
                                <div class="card-body text-center">
                                    <h3 class="mb-1 fw-bold text-info">${avgStay}</h3>
                                    <p class="mb-0 text-muted fs-13">Avg Stay (Days)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card custom-card border-0 bg-warning-transparent">
                                <div class="card-body text-center">
                                    <h3 class="mb-1 fw-bold text-warning">${occupancyRate}%</h3>
                                    <p class="mb-0 text-muted fs-13">Occupancy Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card custom-card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Admission Breakdown</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Completed Discharges</span>
                                        <strong>${completedAdmissions}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Active Admissions</span>
                                        <strong>${activeAdmissions}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <p>No statistics available</p>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Failed to load bed statistics:', e);
            container.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <p>Failed to load statistics</p>
                </div>
            `;
        }
    }

    // Navigation helper functions that close modal before navigating
    function navigateToPatient(patientId) {
        closeModalAndNavigate(`/patients/${patientId}`);
    }

    function navigateToAdmission(admissionId) {
        closeModalAndNavigate(`/ip/admission-details?admission_id=${admissionId}`);
    }

    function closeModalAndNavigate(url) {
        const modalElement = document.getElementById('bedDetailsModal');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);

        if (modalInstance) {
            // Hide the modal
            modalInstance.hide();

            // Wait for modal to fully hide before navigating
            modalElement.addEventListener('hidden.bs.modal', function () {
                // Clean up any leftover backdrops
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');

                // Navigate to the URL
                window.location.href = url;
            }, { once: true });
        } else {
            // If modal instance not found, just navigate
            window.location.href = url;
        }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>