<?php
/**
 * Doctor's Personal Patient List
 * Professional Commercial Interface - Database Integrated
 */
$pageTitle = "My Patients";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">My Patients</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/visits">OPD</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Patients</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-outline-info btn-wave" onclick="filterTodayVisits()">
            <i class="ri-calendar-event-line align-middle me-1"></i>Today's Consults
        </button>
        <a href="/registrations/create" class="btn btn-primary btn-wave">
            <i class="ri-user-add-line align-middle me-1"></i>New Registration
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card dashboard-main-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-primary-transparent text-primary rounded-circle">
                        <i class="ri-user-heart-line fs-24"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0" id="statTotal">-</h3>
                        <span class="text-muted fs-12">Total Patients</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card dashboard-main-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-success-transparent text-success rounded-circle">
                        <i class="ri-calendar-check-line fs-24"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0" id="statToday">-</h3>
                        <span class="text-muted fs-12">Today's Visits</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card dashboard-main-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-warning-transparent text-warning rounded-circle">
                        <i class="ri-time-line fs-24"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0" id="statActive">-</h3>
                        <span class="text-muted fs-12">Active Encounters</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card dashboard-main-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-info-transparent text-info rounded-circle">
                        <i class="ri-calendar-todo-line fs-24"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0" id="statFollowup">-</h3>
                        <span class="text-muted fs-12">Review Due</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient Directory -->
<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">Patient Directory</div>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" id="genderFilter" style="width: 120px;"
                onchange="fetchPatients()">
                <option value="">All Genders</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <div class="input-group input-group-sm" style="max-width: 300px;">
                <span class="input-group-text bg-light border-0"><i class="ri-search-line"></i></span>
                <input type="text" class="form-control border-0" id="searchInput" placeholder="Search name, MRN...">
                <button class="btn btn-primary border-0" onclick="fetchPatients()">Find</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered text-nowrap w-100 mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Patient Name</th>
                        <th>Gender/Age</th>
                        <th>Contact</th>
                        <th>Last Visit</th>
                        <th>Total Visits</th>
                    </tr>
                </thead>
                <tbody id="patientList">
                    <tr id="loadingState">
                        <td colspan="5" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Loading patients...</p>
                        </td>
                    </tr>
                    <tr id="emptyState" class="d-none">
                        <td colspan="5" class="text-center py-5">
                            <i class="ri-user-search-line fs-48 text-muted d-block mb-2"></i>
                            <p class="text-muted mb-0">No patients found</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-light d-flex justify-content-between align-items-center">
        <div id="paginationInfo" class="text-muted fs-13">Showing 0 patients</div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- Patient Summary Modal -->
<div class="modal fade" id="patientSummaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fs-15 fw-semibold" id="modalPatientName">Patient Summary</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalContent">
                <div class="text-center mb-4">
                    <div id="modalAvatar"
                        class="avatar avatar-xl rounded-circle mb-2 mx-auto fs-24 fw-bold d-flex align-items-center justify-content-center">
                    </div>
                    <h5 class="mb-0" id="modalFullName"></h5>
                    <span class="text-muted fs-12" id="modalMRN"></span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-2 bg-light rounded shadow-sm">
                            <small class="text-muted d-block fs-10 text-uppercase fw-bold">Gender / Age</small>
                            <span class="fs-13 fw-semibold" id="modalGenderAge"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded shadow-sm">
                            <small class="text-muted d-block fs-10 text-uppercase fw-bold">Mobile</small>
                            <span class="fs-13 fw-semibold" id="modalMobile"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-2 bg-light rounded shadow-sm">
                            <small class="text-muted d-block fs-10 text-uppercase fw-bold">Recent Visit</small>
                            <span class="fs-13 fw-semibold" id="modalLastVisit"></span>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button class="btn btn-primary d-flex align-items-center justify-content-center gap-2"
                        onclick="executeAction('visit')">
                        <i class="ri-stethoscope-line"></i> Start New Consultation
                    </button>
                    <button class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2"
                        onclick="executeAction('profile')">
                        <i class="ri-user-settings-line"></i> View Profile
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-main-card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.2s;
    }

    .dashboard-main-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .patient-row {
        cursor: pointer;
        transition: background 0.2s;
    }

    .patient-row:hover {
        background-color: rgba(var(--primary-rgb), 0.05) !important;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    let searchTimer = null;
    let preloadedPatients = [];
    let currentPatientId = null;

    document.addEventListener('DOMContentLoaded', () => {
        preloadPatients();
        fetchStats();
    });

    async function preloadPatients() {
        try {
            const res = await fetch('/api/v1/patients?limit=500');
            const data = await res.json();
            if (data.success) {
                preloadedPatients = data.data.patients || [];
                renderPatients(preloadedPatients);
                document.getElementById('loadingState').classList.add('d-none');
                document.getElementById('paginationInfo').innerText = `Showing ${preloadedPatients.length} patients`;
            }
        } catch (e) {
            console.error('Patient preload error:', e);
            fetchPatients();
        }
    }

    document.getElementById('searchInput').addEventListener('keyup', () => {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();

        if (query.length === 0) {
            renderPatients(preloadedPatients);
            return;
        }

        // ⚡ INSTANT LOCAL FILTER
        const filtered = preloadedPatients.filter(p =>
            (p.first_name + ' ' + (p.last_name || '')).toLowerCase().includes(query) ||
            (p.mrn && p.mrn.toLowerCase().includes(query)) ||
            (p.mobile && p.mobile.includes(query))
        );

        if (filtered.length > 0) {
            renderPatients(filtered);
            document.getElementById('emptyState').classList.add('d-none');
        } else {
            // Fallback to server search if no local results
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchPatients, 300);
        }
    });

    async function fetchStats() {
        try {
            // Get visit stats
            const visitsRes = await fetch('/api/v1/visits/stats');
            const visitsData = await visitsRes.json();
            if (visitsData.success) {
                document.getElementById('statToday').innerText = visitsData.data.today_visits || 0;
                document.getElementById('statActive').innerText = visitsData.data.active_encounters || 0;
                document.getElementById('statTotal').innerText = visitsData.data.total_visits || 0;
                document.getElementById('statFollowup').innerText = '0'; // Can be enhanced later
            }
        } catch (e) {
            console.error('Stats fetch error:', e);
        }
    }

    async function fetchPatients() {
        const list = document.getElementById('patientList');
        const loading = document.getElementById('loadingState');
        const empty = document.getElementById('emptyState');

        // Clear previous rows except loading/empty
        const rows = list.querySelectorAll('tr:not(#loadingState):not(#emptyState)');
        rows.forEach(r => r.remove());

        loading.classList.remove('d-none');
        empty.classList.add('d-none');

        const search = document.getElementById('searchInput').value;
        const gender = document.getElementById('genderFilter').value;

        let url = `/api/v1/patients?search=${encodeURIComponent(search)}`;
        if (gender) url += `&gender=${encodeURIComponent(gender)}`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            loading.classList.add('d-none');

            if (data.success && data.data.patients && data.data.patients.length > 0) {
                renderPatients(data.data.patients);
                document.getElementById('paginationInfo').innerText = `Showing ${data.data.patients.length} patients`;
            } else {
                empty.classList.remove('d-none');
                document.getElementById('paginationInfo').innerText = `Showing 0 patients`;
            }
        } catch (e) {
            loading.classList.add('d-none');
            console.error('Patient fetch error:', e);
        }
    }

    function renderPatients(patients) {
        const list = document.getElementById('patientList');
        // Clear previous rows except placeholders
        const existingRows = list.querySelectorAll('.patient-row');
        existingRows.forEach(row => row.remove());

        patients.forEach(p => {
            const age = calculateAge(p.dob);
            const initials = getInitials(p.first_name, p.last_name);
            const bgColor = getAvatarColor(p.gender);

            const row = document.createElement('tr');
            row.className = 'patient-row';
            row.onclick = () => openPatientSummary(p);
            row.innerHTML = `
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm rounded-circle ${bgColor} text-white me-2">${initials}</div>
                        <div>
                            <span class="fw-bold d-block text-primary">${p.first_name} ${p.last_name || ''}</span>
                            <small class="text-muted fs-10">#${p.mrn}</small>
                        </div>
                    </div>
                </td>
                <td>${p.gender || '-'} / ${age}</td>
                <td>
                    <span class="d-block fs-12 fw-medium">${p.mobile || '-'}</span>
                    <small class="text-muted">${p.email || '-'}</small>
                </td>
                <td><span class="badge bg-light text-dark fs-11">${p.last_visit ? formatDate(p.last_visit) : 'No visits'}</span></td>
                <td class="text-center"><span class="fw-bold">${p.total_visits || 0}</span></td>
            `;
            list.appendChild(row);
        });
    }

    function openPatientSummary(patient) {
        if (!patient) return;
        currentPatientId = patient.patient_id;

        const initials = getInitials(patient.first_name, patient.last_name);
        const bgColor = getAvatarColor(patient.gender);
        const age = calculateAge(patient.dob);

        document.getElementById('modalAvatar').className = `avatar avatar-xl rounded-circle mb-2 mx-auto fs-24 fw-bold d-flex align-items-center justify-content-center ${bgColor} text-white`;
        document.getElementById('modalAvatar').innerText = initials;
        document.getElementById('modalFullName').innerText = `${patient.first_name} ${patient.last_name || ''}`;
        document.getElementById('modalMRN').innerText = `MRN: #${patient.mrn}`;
        document.getElementById('modalGenderAge').innerText = `${patient.gender || '-'} / ${age}`;
        document.getElementById('modalMobile').innerText = patient.mobile || '-';
        document.getElementById('modalLastVisit').innerText = patient.last_visit ? formatDate(patient.last_visit) : 'No previous visits';

        const modal = new bootstrap.Modal(document.getElementById('patientSummaryModal'));
        modal.show();
    }

    function executeAction(type) {
        if (!currentPatientId) return;

        switch (type) {
            case 'visit': startVisit(currentPatientId); break;
            case 'profile': viewPatient(currentPatientId); break;
        }

        const modalEl = document.getElementById('patientSummaryModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    function calculateAge(dob) {
        if (!dob) return '-';
        const today = new Date();
        const birth = new Date(dob);
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
        return age + 'Y';
    }

    function getInitials(first, last) {
        return ((first ? first.charAt(0) : '') + (last ? last.charAt(0) : '')).toUpperCase() || 'P';
    }

    function getAvatarColor(gender) {
        if (gender === 'Male') return 'bg-primary';
        if (gender === 'Female') return 'bg-pink';
        return 'bg-secondary';
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function filterTodayVisits() {
        window.location.href = '/visits?date_range=' + new Date().toISOString().split('T')[0];
    }

    function viewPatient(patientId) {
        window.location.href = `/patients/${patientId}`;
    }

    function startVisit(patientId) {
        window.location.href = `/visits?start_visit=${patientId}`;
    }

</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>