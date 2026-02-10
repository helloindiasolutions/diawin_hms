<?php
$pageTitle = "Bed Management";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Bed Management</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">In-Patient</a></li>
            <li class="breadcrumb-item active" aria-current="page">Beds</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" onclick="openAddBedModal()"><i
                class="ri-add-line align-middle me-1"></i> Add Bed</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary">
                            <i class="ri-hotel-bed-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0" id="stat_total_beds">0</h5>
                        <p class="text-muted mb-0 fs-12">Total Beds</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-success-transparent text-success">
                            <i class="ri-checkbox-circle-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0" id="stat_available">0</h5>
                        <p class="text-muted mb-0 fs-12">Available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-danger-transparent text-danger">
                            <i class="ri-user-follow-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0" id="stat_occupied">0</h5>
                        <p class="text-muted mb-0 fs-12">Occupied</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-info-transparent text-info">
                            <i class="ri-building-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0" id="stat_wards">0</h5>
                        <p class="text-muted mb-0 fs-12">Active Wards</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="wardContainer">
    <!-- Wards and beds will be rendered here dynamically -->
</div>

<!-- Add Bed Modal -->
<div class="modal fade" id="addBedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Bed</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bedForm">
                    <div class="mb-3">
                        <label class="form-label">Ward <span class="text-danger">*</span></label>
                        <select class="form-select" id="bed_ward_id" required onchange="generateBedNumber()">
                            <option value="">Select Ward</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bed Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light" id="bed_number" placeholder="Auto-generated"
                            required readonly>
                        <small class="text-muted">Auto-generated: DIA-[Branch Code]-[Number]</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bed Type</label>
                        <select class="form-select" id="bed_type">
                            <option value="Standard">Standard</option>
                            <option value="ICU">ICU</option>
                            <option value="Private">Private</option>
                            <option value="Semi-Private">Semi-Private</option>
                            <option value="Deluxe">Deluxe</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Daily Rate (₹)</label>
                        <input type="number" class="form-control" id="daily_rate" value="500" step="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Features</label>
                        <textarea class="form-control" id="features" rows="2"
                            placeholder="e.g., AC, TV, Attached Bathroom"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveBed()">
                    <i class="ri-save-line me-1"></i> Add Bed
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bed Details Modal -->
<div class="modal fade" id="bedDetailsModal" tabindex="-1" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary ">
                <div>
                    <h5 class="modal-title mb-0" id="bedDetailsTitle">Bed Details</h5>
                    <small id="bedDetailsSubtitle" class=" "></small>
                </div>
                <button type="button" class="btn-close  " data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Panel: Current Patient Info -->
                    <div class="col-md-5 border-end bg-light">
                        <div class="p-4">
                            <h6 class="fw-bold mb-3 text-primary">
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

                    <!-- Right Panel: Tabs for History -->
                    <div class="col-md-7">
                        <div class="p-4">
                            <ul class="nav nav-tabs mb-3" role="tablist">
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

                            <div class="tab-content">
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

<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<style>
    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .letter-spacing-1 {
        letter-spacing: 0.5px;
    }

    .uppercase {
        text-transform: uppercase;
    }

    /* Gentle Bed Card UI */
    .bed-grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
        padding: 8px 0;
    }

    .bed-mini-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        position: relative;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        min-height: 120px;
        display: flex;
        flex-direction: column;
    }

    .bed-mini-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }

    .bed-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .bed-number-tag {
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        letter-spacing: 0.5px;
    }

    .bed-mini-icon {
        font-size: 32px;
        line-height: 1;
        opacity: 1;
        margin-top: 0;
    }

    .bed-patient-info {
        flex-grow: 1;
        margin-top: 5px;
    }

    .bed-patient-name {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #1e293b;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .bed-patient-meta {
        font-size: 11px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .bed-doctor-tag {
        font-size: 11px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        color: #334155;
    }

    .bed-doctor-tag i {
        font-size: 13px;
    }

    /* Status Specific Styles */
    .bed-mini-card.available {
        border-left: 5px solid #10b981;
    }

    .bed-mini-card.available .bed-mini-icon {
        color: #10b981;
    }

    .bed-available-text {
        font-size: 13px;
        font-weight: 700;
        color: #059669;
        letter-spacing: 0.5px;
    }

    .bed-mini-card.occupied.male {
        border-left: 5px solid #3b82f6; /* Blue for male */
    }
    .bed-mini-card.occupied.male .bed-mini-icon {
        color: #3b82f6;
    }
    .bed-mini-card.occupied.male .bed-patient-name {
        color: #1d4ed8;
    }

    .bed-mini-card.occupied.female {
        border-left: 5px solid #ec4899; /* Rose/Pink for female */
    }
    .bed-mini-card.occupied.female .bed-mini-icon {
        color: #ec4899;
    }
    .bed-mini-card.occupied.female .bed-patient-name {
        color: #be185d;
    }

    .bed-mini-card.maintenance {
        border-left: 5px solid #64748b; /* Grey for maintenance */
        background-color: #f8fafc;
    }
    .bed-mini-card.maintenance .bed-mini-icon {
        color: #64748b;
    }
    .bed-mini-card.maintenance .bed-patient-name {
        color: #475569;
    }

    .bed-type-badge {
        font-size: 10px;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 600;
    }

    /* Stats Cards */
    .card.custom-card .card-body {
        padding: 1rem;
    }

    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
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
<?php $styles = ob_get_clean(); ?>

<?php ob_start(); ?>
<script>
    var wardsData = []; // Store wards data globally

    function initBedsPage() {
        console.log('Initializing Beds Page...');
        fetchBedStats();
        fetchBeds();
        loadWardsForBedModal();

        // Prevent tab clicks from closing modal
        const bedDetailsModal = document.getElementById('bedDetailsModal');
        if (bedDetailsModal) {
            bedDetailsModal.addEventListener('show.bs.modal', function () {
                // Add event listeners to tabs to prevent modal close
                const tabButtons = bedDetailsModal.querySelectorAll('[data-bs-toggle="tab"]');
                tabButtons.forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });
                });
            });
        }
    }

    // Melina SPA Support
    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initBedsPage);
    } else {
        // Fallback
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBedsPage);
        } else {
            initBedsPage();
        }
    }

    // Check if duplicate execution in fallback
    // (Melina might run it, so we rely on Melina if present)

    // Expose functions globally for UI interactions
    window.initBedsPage = initBedsPage;
    window.openAddBedModal = openAddBedModal;
    window.saveBed = saveBed;
    window.viewBedDetails = viewBedDetails;
    window.generateBedNumber = generateBedNumber;

    async function fetchBedStats() {
        try {
            const branchId = window.currentBranchId || 0;
            const res = await fetch(`/api/v1/ipd/stats?branch_id=${branchId}`);
            const data = await res.json();
            if (data.success) {
                document.getElementById('stat_total_beds').innerText = data.data.total_beds || 0;
                document.getElementById('stat_available').innerText = data.data.available_beds || 0;
                document.getElementById('stat_occupied').innerText = (data.data.total_beds - data.data.available_beds) || 0;

                // Count wards
                const wardsRes = await fetch(`/api/v1/ipd/wards?branch_id=${branchId}`);
                const wardsData = await wardsRes.json();
                if (wardsData.success) {
                    document.getElementById('stat_wards').innerText = wardsData.data.wards.length || 0;
                }
            }
        } catch (e) { console.error(e); }
    }

    async function fetchBeds() {
        const container = document.getElementById('wardContainer');
        const branchId = window.currentBranchId || 0;
        container.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';

        try {
            // Fetch beds and active admissions in parallel with branch filtering
            const [bedsRes, admissionsRes] = await Promise.all([
                fetch(`/api/v1/ipd/beds?branch_id=${branchId}`),
                fetch(`/api/v1/ipd/admissions?status=Active&branch_id=${branchId}`)
            ]);

            const bedsData = await bedsRes.json();
            const admissionsData = await admissionsRes.json();

            container.innerHTML = '';

            if (bedsData.success && bedsData.data.beds.length > 0) {
                // Map active admissions to beds
                const admissionMap = {};
                if (admissionsData.success && admissionsData.data.admissions) {
                    admissionsData.data.admissions.forEach(adm => {
                        if (adm.bed_id) {
                            admissionMap[adm.bed_id] = adm;
                        }
                    });
                }

                // Group beds by ward
                const wards = {};
                bedsData.data.beds.forEach(b => {
                    const wardName = b.ward_name || 'Unassigned';
                    if (!wards[wardName]) wards[wardName] = [];
                    wards[wardName].push(b);
                });

                for (const wardName in wards) {
                    const wardCol = document.createElement('div');
                    wardCol.className = 'col-xl-12 mb-4';

                    let bedCards = '';
                    wards[wardName].forEach(b => {
                        const isAvailable = b.bed_status === 'Available';
                        const iconColor = isAvailable ? '#10b981' : '#ef4444';
                        const adm = !isAvailable ? admissionMap[b.bed_id] : null;

                        // Extract short bed ID
                        const shortBedId = b.bed_number.replace('DIA-', '');

                        if (isAvailable) {
                            bedCards += `
                            <div class="bed-mini-card available" onclick="viewBedDetails(${b.bed_id}, '${b.bed_number}', '${wardName}', '${b.bed_status}')" title="Bed ${b.bed_number}: Available">
                                <div class="bed-card-header">
                                    <span class="bed-number-tag">${shortBedId}</span>
                                    <div class="bed-mini-icon text-success">
                                        <i class="ri-hotel-bed-line"></i>
                                    </div>
                                </div>
                                <div class="bed-patient-info mt-auto">
                                    <div class="bed-patient-name text-muted" style="font-size: 11px;">Available</div>
                                </div>
                            </div>
                        `;
                        } else if (b.bed_status === 'Maintenance') {
                            bedCards += `
                            <div class="bed-mini-card maintenance" onclick="viewBedDetails(${b.bed_id}, '${b.bed_number}', '${wardName}', '${b.bed_status}')" title="Bed ${b.bed_number}: Maintenance">
                                <div class="bed-card-header">
                                    <span class="bed-number-tag">${shortBedId}</span>
                                    <div class="bed-mini-icon">
                                        <i class="ri-tools-line"></i>
                                    </div>
                                </div>
                                <div class="bed-patient-info mt-auto">
                                    <div class="bed-patient-name">Maintenance</div>
                                </div>
                            </div>
                        `;
                        } else {
                            const patientName = adm ? `${adm.first_name} ${adm.last_name || ''}`.trim() : 'Unknown';
                            const doctorName = adm ? adm.doctor_name || 'No Dr.' : 'N/A';
                            const genderClass = adm && adm.gender ? adm.gender.toLowerCase() : '';

                            bedCards += `
                            <div class="bed-mini-card occupied ${genderClass}" onclick="viewBedDetails(${b.bed_id}, '${b.bed_number}', '${wardName}', '${b.bed_status}')" title="Bed ${b.bed_number}: ${patientName}">
                                <div class="bed-card-header">
                                    <span class="bed-number-tag">${shortBedId}</span>
                                    <div class="bed-mini-icon">
                                        <i class="ri-hotel-bed-fill"></i>
                                    </div>
                                </div>
                                <div class="bed-patient-info mt-auto">
                                    <div class="bed-patient-name" title="${patientName}">${patientName}</div>
                                    <div class="bed-doctor-tag py-1 px-2 mt-1" style="font-size: 10px;">
                                        <i class="ri-user-star-line"></i> ${doctorName}
                                    </div>
                                </div>
                            </div>
                        `;
                        }
                    });

                    // Count available and occupied
                    const availableCount = wards[wardName].filter(b => b.bed_status === 'Available').length;
                    const occupiedCount = wards[wardName].length - availableCount;

                    wardCol.innerHTML = `
                    <div class="card custom-card overflow-hidden border-0 shadow-sm mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center py-3 px-3" style="background: #fdfdfd; border-bottom: 1px solid #f1f5f9; border-left: 5px solid #6366f1;">
                            <div class="d-flex align-items-center gap-3">
                                <span class="fw-bold fs-16 text-dark">${wardName}</span>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-success-transparent text-success fw-medium">${availableCount} Available</span>
                                    <span class="badge bg-danger-transparent text-danger fw-medium">${occupiedCount} Occupied</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-muted border fs-11">${wards[wardName][0].ward_type}</span>
                                <a href="javascript:void(0);" onclick="Melina.navigate('/ip/ward-details?ward_id=${wards[wardName][0].ward_id}')" class="fs-12 text-primary fw-medium text-decoration-none">View Ward <i class="ri-arrow-right-s-line"></i></a>
                            </div>
                        </div>
                        <div class="card-body p-3" style="background: #ffffff;">
                            <div class="bed-grid-container">
                                ${bedCards}
                            </div>
                        </div>
                    </div>
                `;
                    container.appendChild(wardCol);
                }
            } else {
                container.innerHTML = `<div class="col-12 text-center py-5">
                    <img src="/assets/images/no-data.svg" style="width: 120px; opacity: 0.2; margin-bottom: 20px;">
                    <p class="text-muted">No beds found for the selected branch filtering.</p>
                </div>`;
            }
        } catch (e) {
            console.error(e);
            container.innerHTML = '<div class="col-12 text-center py-5 text-danger">Failed to load bed information. Please try again.</div>';
        }
    }

    function openAddBedModal() {
        const form = document.getElementById('bedForm');
        const bedNumberInput = document.getElementById('bed_number');
        const modalEl = document.getElementById('addBedModal');

        if (!form || !modalEl) {
            console.error('Add Bed Modal or Form not found in DOM');
            alert('Unable to open Add Bed form. Please refresh the page and try again.');
            return;
        }

        form.reset();
        if (bedNumberInput) bedNumberInput.value = '';

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    async function loadWardsForBedModal() {
        const select = document.getElementById('bed_ward_id');
        if (!select) {
            console.warn('Ward select not found - modal may not be in DOM');
            return;
        }
        select.innerHTML = '<option value="">Loading wards...</option>';

        try {
            const branchId = window.currentBranchId || 0;
            const res = await fetch(`/api/v1/ipd/wards?branch_id=${branchId}`);
            const data = await res.json();

            console.log('Wards API Response:', data); // Debug log

            select.innerHTML = '<option value="">Select Ward</option>';

            if (data.success && data.data.wards && data.data.wards.length > 0) {
                wardsData = data.data.wards; // Store globally
                data.data.wards.forEach(w => {
                    const option = document.createElement('option');
                    option.value = w.ward_id;
                    option.textContent = w.ward_name;
                    option.dataset.wardCode = w.ward_code; // Store ward code
                    select.appendChild(option);
                });
                console.log(`Loaded ${data.data.wards.length} wards`); // Debug log
            } else {
                select.innerHTML = '<option value="">No wards available - Please add wards first</option>';
                console.warn('No wards found in database');
            }
        } catch (e) {
            console.error('Failed to load wards:', e);
            select.innerHTML = '<option value="">Error loading wards</option>';
            alert('Failed to load wards. Please check console for details.');
        }
    }

    async function generateBedNumber() {
        const wardSelect = document.getElementById('bed_ward_id');
        const bedNumberInput = document.getElementById('bed_number');

        if (!wardSelect.value) {
            bedNumberInput.value = '';
            return;
        }

        const wardId = wardSelect.value;

        // Show loading state
        bedNumberInput.value = 'Generating...';

        try {
            // Fetch branch info and existing beds count
            const [bedsRes, branchRes] = await Promise.all([
                fetch('/api/v1/ipd/beds'),
                fetch('/api/v1/ipd/branch-info')
            ]);

            const bedsData = await bedsRes.json();
            const branchData = await branchRes.json();

            console.log('Branch Data:', branchData);
            console.log('Beds Data:', bedsData);

            if (bedsData.success && branchData.success) {
                // Get branch code (e.g., "M" for main branch, or custom code)
                const branchCode = branchData.data.branch_code || 'M';

                // Count ALL beds across all wards (global sequential number)
                const totalBeds = bedsData.data.beds.length;

                // Get next sequential number with leading zeros
                const nextNumber = (totalBeds + 1).toString().padStart(3, '0');

                // Generate bed number: DIA-BRANCH_CODE-NUMBER
                const bedNumber = `DIA-${branchCode}-${nextNumber}`;
                bedNumberInput.value = bedNumber;

                console.log(`Generated bed number: ${bedNumber} (Total beds: ${totalBeds})`);
            } else {
                console.error('API Error:', { bedsData, branchData });
                // Fallback: use default
                const fallbackNumber = '001';
                bedNumberInput.value = `DIA-M-${fallbackNumber}`;
            }
        } catch (e) {
            console.error('Failed to generate bed number:', e);
            // Fallback: use default
            bedNumberInput.value = 'DIA-M-001';
        }
    }

    async function saveBed() {
        const form = document.getElementById('bedForm');
        if (!form) {
            alert('Form not found. Please refresh the page and try again.');
            return;
        }
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = {
            ward_id: document.getElementById('bed_ward_id')?.value,
            bed_number: document.getElementById('bed_number')?.value,
            bed_type: document.getElementById('bed_type')?.value,
            daily_rate: document.getElementById('daily_rate')?.value,
            features: document.getElementById('features')?.value || null
        };

        try {
            const res = await fetch('/api/v1/ipd/beds', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();

            if (data.success) {
                alert('Bed added successfully!');
                const modalEl = document.getElementById('addBedModal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                }
                form.reset();
                fetchBedStats();
                fetchBeds();
            } else {
                alert('Failed to add bed: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error(e);
            alert('Failed to save bed. Please try again.');
        }
    }

    // View Bed Details Function
    async function viewBedDetails(bedId, bedNumber, wardName, bedStatus) {
        const modalElement = document.getElementById('bedDetailsModal');
        if (!modalElement) {
            console.error('Bed Details Modal not found in DOM');
            alert('Unable to view bed details. Please refresh the page and try again.');
            return;
        }

        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });

        // Set modal title
        const titleEl = document.getElementById('bedDetailsTitle');
        const subtitleEl = document.getElementById('bedDetailsSubtitle');
        if (titleEl) titleEl.textContent = bedNumber;
        if (subtitleEl) subtitleEl.textContent = `${wardName} • ${bedStatus}`;

        // Reset tabs to first tab
        const firstTab = document.querySelector('#bedHistory-tab');
        if (firstTab) {
            const tab = new bootstrap.Tab(firstTab);
            tab.show();
        }

        modal.show();

        // Load current patient info
        await loadCurrentPatient(bedId, bedStatus);

        // Maintenance Toggle Button Setup
        const maintenanceBtn = document.getElementById('btn-toggle-maintenance');
        if (maintenanceBtn) {
            if (bedStatus === 'Available') {
                maintenanceBtn.innerHTML = '<i class="ri-tools-line me-2"></i>Mark for Maintenance';
                maintenanceBtn.className = 'btn btn-outline-secondary btn-wave mt-3';
                maintenanceBtn.onclick = () => toggleBedMaintenance(bedId, 'Maintenance');
                maintenanceBtn.style.display = 'block';
            } else if (bedStatus === 'Maintenance') {
                maintenanceBtn.innerHTML = '<i class="ri-checkbox-circle-line me-2"></i>Back to Service';
                maintenanceBtn.className = 'btn btn-outline-success btn-wave mt-3';
                maintenanceBtn.onclick = () => toggleBedMaintenance(bedId, 'Available');
                maintenanceBtn.style.display = 'block';
            } else {
                maintenanceBtn.style.display = 'none'; // Cannot maintenance an occupied bed directly
            }
        }

        // Load bed history
        await loadBedHistory(bedId);

        // Load bed statistics
        await loadBedStatistics(bedId);

        // Ensure backdrop is properly removed when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            // Remove any leftover backdrops
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
                    <button id="btn-toggle-maintenance"></button>
                </div>
            `;
            return;
        }

        if (bedStatus === 'Maintenance') {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="ri-tools-line fs-48 mb-3 d-block opacity-25"></i>
                    <p>Bed is under maintenance</p>
                    <button id="btn-toggle-maintenance"></button>
                </div>
            `;
            return;
        }

        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            // Fetch current admission for this bed
            const res = await fetch(`/api/v1/ipd/admissions?bed_id=${bedId}&status=Active`);
            const data = await res.json();

            if (data.success && data.data.admissions && data.data.admissions.length > 0) {
                const admission = data.data.admissions[0];
                const patient = admission;

                // Calculate days stayed
                const admissionDate = new Date(admission.admission_date);
                const today = new Date();
                const daysStayed = Math.ceil((today - admissionDate) / (1000 * 60 * 60 * 24));

                // Calculate estimated charges
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
            // Fetch all admissions for this bed (including past)
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

                // Calculate average stay duration
                let totalDays = 0;
                admissions.forEach(a => {
                    const admissionDate = new Date(a.admission_date);
                    const endDate = a.discharge_date ? new Date(a.discharge_date) : new Date();
                    const days = Math.ceil((endDate - admissionDate) / (1000 * 60 * 60 * 24));
                    totalDays += days;
                });
                const avgStay = totalAdmissions > 0 ? (totalDays / totalAdmissions).toFixed(1) : 0;

                // Calculate occupancy rate (assuming bed exists for 30 days)
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
    async function toggleBedMaintenance(bedId, newStatus) {
        if (!confirm(`Are you sure you want to change this bed status to ${newStatus}?`)) return;

        try {
            const res = await fetch('/api/v1/ipd/beds/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bed_id: bedId, status: newStatus })
            });
            const data = await res.json();
            if (data.success) {
                alert('Bed status updated successfully');
                const modalEl = document.getElementById('bedDetailsModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
                fetchBedStats();
                fetchBeds();
            } else {
                alert('Failed to update status: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred. Please try again.');
        }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>