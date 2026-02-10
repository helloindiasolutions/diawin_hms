<?php
/**
 * Patient Master - List View
 * Zoho-style professional UI with filters, search, and data table
 */
$pageTitle = 'Patients';
ob_start();
?>

<link rel="stylesheet" href="<?= asset('libs/sweetalert2/sweetalert2.min.css') ?>">
<style>
    /* Compact table styling */
    #patientsTable {
        font-size: 0.8125rem;
    }

    #patientsTable th {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6c757d;
    }

    #patientsTable td {
        font-size: 0.8125rem;
        vertical-align: middle;
        padding: 0.625rem 0.75rem;
    }

    #patientsTable .fw-medium {
        font-size: 0.8125rem;
    }

    #patientsTable .fs-11 {
        font-size: 0.6875rem !important;
    }

    #patientsTable .fs-12 {
        font-size: 0.75rem !important;
    }

    #patientsTable .fs-13 {
        font-size: 0.8125rem !important;
    }

    /* Clickable rows */
    #patientsTable tbody tr.patient-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    #patientsTable tbody tr.patient-row:hover {
        background-color: rgba(var(--primary-rgb), 0.04);
    }

    /* Responsive badge font sizes */
    .badge {
        font-size: 0.6875rem;
        padding: 0.25em 0.5em;
    }

    @media (max-width: 991px) {
        .badge {
            font-size: 0.65rem;
        }
    }

    @media (max-width: 768px) {
        .badge {
            font-size: 0.625rem;
            padding: 0.2em 0.45em;
        }

        #patientsTable {
            font-size: 0.75rem;
        }

        #patientsTable td {
            padding: 0.5rem 0.625rem;
        }
    }

    @media (max-width: 576px) {
        .badge {
            font-size: 0.6rem;
            padding: 0.2em 0.4em;
        }

        #patientsTable {
            font-size: 0.7rem;
        }
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<!-- Page Header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-0">Patients</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= baseUrl('/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Patients</li>
                </ol>
            </nav>
        </div>
        <div class="btn-list">
            <a href="<?= baseUrl('/patients/create') ?>" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>New Patient
            </a>
            <button class="btn btn-success-light" onclick="exportPatients()">
                <i class="ri-download-2-line me-1"></i>Export
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-3" id="statsRow">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card dashboard-main-card primary">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="fs-13 fw-medium">Total Patients</span>
                        <h4 class="fw-semibold my-2 lh-1" id="statTotal">--</h4>
                        <span class="text-muted fs-12">All registered patients</span>
                    </div>
                    <span class="avatar avatar-md bg-primary-transparent">
                        <i class="ri-user-heart-line fs-20 text-primary"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card dashboard-main-card success">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="fs-13 fw-medium">Active Patients</span>
                        <h4 class="fw-semibold my-2 lh-1" id="statActive">--</h4>
                        <span class="text-muted fs-12">Currently active</span>
                    </div>
                    <span class="avatar avatar-md bg-success-transparent">
                        <i class="ri-user-follow-line fs-20 text-success"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card dashboard-main-card warning">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="fs-13 fw-medium">New This Month</span>
                        <h4 class="fw-semibold my-2 lh-1" id="statNewMonth">--</h4>
                        <span class="text-muted fs-12">Registered this month</span>
                    </div>
                    <span class="avatar avatar-md bg-warning-transparent">
                        <i class="ri-user-add-line fs-20 text-warning"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card dashboard-main-card secondary">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="fs-13 fw-medium">Inactive</span>
                        <h4 class="fw-semibold my-2 lh-1" id="statInactive">--</h4>
                        <span class="text-muted fs-12">Deactivated patients</span>
                    </div>
                    <span class="avatar avatar-md bg-secondary-transparent">
                        <i class="ri-user-unfollow-line fs-20 text-secondary"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Card with Filters and Table -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group" style="width: 280px;">
                        <span class="input-group-text bg-transparent border-end-0"><i class="ri-search-line"></i></span>
                        <input type="text" class="form-control border-start-0" id="searchInput"
                            placeholder="Search by MRN, name, mobile...">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select class="form-select" id="filterStatus" style="width: 130px;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select class="form-select" id="filterGender" style="width: 130px;">
                        <option value="">All Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <select class="form-select" id="filterBloodGroup" style="width: 140px;">
                        <option value="">Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ri-sort-desc me-1"></i>Sort
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item sort-option" href="#" data-sort="created_at"
                                    data-order="DESC">Newest First</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="created_at"
                                    data-order="ASC">Oldest First</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="first_name"
                                    data-order="ASC">Name A-Z</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="first_name"
                                    data-order="DESC">Name Z-A</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="mrn" data-order="DESC">MRN
                                    (Latest)</a></li>
                        </ul>
                    </div>
                    <button class="btn btn-outline-secondary" onclick="resetFilters()" title="Reset Filters">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0" id="patientsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </th>
                                <th>Patient</th>
                                <th>MRN</th>
                                <th>Contact</th>
                                <th>Gender / Age</th>
                                <th>Blood Group</th>
                                <th>Last Visit</th>
                                <th>Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="patientsTableBody">
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="ms-2">Loading patients...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="text-muted fs-13" id="paginationInfo">Showing 0 of 0 patients</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Patient Detail Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="patientDetailOffcanvas" style="width: 450px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Patient Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0" id="patientDetailContent">
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script src="<?= asset('libs/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>
    // State
    let currentPage = 1;
    let currentSort = 'created_at';
    let currentOrder = 'DESC';
    let searchTimeout = null;

    // Initialize
    // Initialize
    const initPatientsPage = function () {
        loadStats();
        loadPatients();
        setupEventListeners();
    };

    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initPatientsPage);
    } else {
        document.addEventListener('DOMContentLoaded', initPatientsPage);
    }

    function setupEventListeners() {
        // Search with debounce
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => { currentPage = 1; loadPatients(); }, 400);
        });

        // Filters
        ['filterStatus', 'filterGender', 'filterBloodGroup'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => { currentPage = 1; loadPatients(); });
        });

        // Sort options
        document.querySelectorAll('.sort-option').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                currentSort = this.dataset.sort;
                currentOrder = this.dataset.order;
                loadPatients();
            });
        });

        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', function () {
            document.querySelectorAll('.patient-checkbox').forEach(cb => cb.checked = this.checked);
        });
    }

    // Calculate age from DOB
    function calculateAgeFromDOB(dob) {
        const birthDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age >= 0 ? age : 0;
    }

    // Email validation
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Mobile validation
    function isValidMobile(mobile) {
        return /^[+]?[\d]{10,13}$/.test(mobile.replace(/\s/g, ''));
    }

    async function loadStats() {
        try {
            const res = await fetch('/api/v1/patients/stats');
            const data = await res.json();
            if (data.success) {
                const elTotal = document.getElementById('statTotal');
                const elActive = document.getElementById('statActive');
                const elNewMonth = document.getElementById('statNewMonth');
                const elInactive = document.getElementById('statInactive');

                if (elTotal) elTotal.textContent = data.data.total_patients.toLocaleString();
                if (elActive) elActive.textContent = data.data.active_patients.toLocaleString();
                if (elNewMonth) elNewMonth.textContent = data.data.new_this_month.toLocaleString();
                if (elInactive) elInactive.textContent = data.data.inactive_patients.toLocaleString();
            }
        } catch (e) { console.error('Failed to load stats:', e); }
    }

    async function loadPatients() {
        const tbody = document.getElementById('patientsTableBody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div><span class="ms-2">Loading...</span></td></tr>';

        const params = new URLSearchParams({
            page: currentPage,
            limit: 20,
            sort_by: currentSort,
            sort_order: currentOrder,
            search: document.getElementById('searchInput')?.value || '',
            status: document.getElementById('filterStatus')?.value || '',
            gender: document.getElementById('filterGender')?.value || '',
            blood_group: document.getElementById('filterBloodGroup')?.value || ''
        });

        try {
            const res = await fetch('/api/v1/patients?' + params);
            const data = await res.json();

            if (data.success) {
                renderPatients(data.data.patients);
                renderPagination(data.data.pagination);
            } else {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load patients</td></tr>';
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Error loading patients</td></tr>';
        }
    }

    function renderPatients(patients) {
        const tbody = document.getElementById('patientsTableBody');

        if (!patients.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-5"><i class="ri-user-search-line fs-48 text-muted d-block mb-2"></i><span class="text-muted">No patients found</span></td></tr>';
            return;
        }

        tbody.innerHTML = patients.map(p => `
        <tr class="patient-row" data-id="${p.patient_id}" onclick="goToPatient(${p.patient_id})">
            <td onclick="event.stopPropagation()"><input class="form-check-input patient-checkbox" type="checkbox" value="${p.patient_id}"></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm avatar-rounded ${getGenderBg(p.gender)}">
                        ${getInitials(p.full_name)}
                    </span>
                    <div>
                        <span class="fw-medium text-dark">${escapeHtml(p.full_name)}</span>
                        <span class="d-block text-muted fs-11">${p.city || ''}</span>
                    </div>
                </div>
            </td>
            <td><span class="badge bg-light text-dark">${p.mrn}</span></td>
            <td>
                <div class="fs-13">${p.mobile ? '<i class="ri-phone-line me-1 text-muted"></i>' + escapeHtml(p.mobile) : '<span class="text-muted">--</span>'}</div>
                ${p.email ? '<div class="fs-11 text-muted text-truncate" style="max-width:150px;"><i class="ri-mail-line me-1"></i>' + escapeHtml(p.email) + '</div>' : ''}
            </td>
            <td>
                <span class="badge ${getGenderBadge(p.gender)}">${capitalize(p.gender)}</span>
                ${p.age ? '<span class="ms-1 text-muted">' + p.age + ' yrs</span>' : ''}
            </td>
            <td>${p.blood_group ? '<span class="badge bg-danger-transparent">' + p.blood_group + '</span>' : '<span class="text-muted">--</span>'}</td>
            <td>${p.last_visit ? formatDate(p.last_visit) : '<span class="text-muted fs-12">No visits</span>'}</td>
            <td>
                <span class="badge rounded-pill ${p.is_active ? 'bg-success-transparent' : 'bg-secondary-transparent'}">
                    ${p.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td onclick="event.stopPropagation()">
                <div class="btn-list">
                    <button class="btn btn-sm btn-primary-light btn-icon" onclick="goToPatient(${p.patient_id})" title="View"><i class="ri-eye-line"></i></button>
                    <button class="btn btn-sm btn-info-light btn-icon" onclick="editPatient(${p.patient_id})" title="Edit"><i class="ri-pencil-line"></i></button>
                    <button class="btn btn-sm btn-danger-light btn-icon" onclick="deletePatient(${p.patient_id}, '${escapeHtml(p.full_name)}')" title="Delete"><i class="ri-delete-bin-line"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
    }

    function renderPagination(pagination) {
        const { total, page, limit, total_pages } = pagination;
        const start = (page - 1) * limit + 1;
        const end = Math.min(page * limit, total);

        const elInfo = document.getElementById('paginationInfo');
        if (elInfo) elInfo.textContent = `Showing ${total ? start : 0}-${end} of ${total} patients`;

        const paginationEl = document.getElementById('pagination');
        if (!paginationEl) return;
        if (total_pages <= 1) { paginationEl.innerHTML = ''; return; }

        let html = '';
        html += `<li class="page-item ${page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="goToPage(${page - 1})">«</a></li>`;

        for (let i = Math.max(1, page - 2); i <= Math.min(total_pages, page + 2); i++) {
            html += `<li class="page-item ${i === page ? 'active' : ''}"><a class="page-link" href="#" onclick="goToPage(${i})">${i}</a></li>`;
        }

        html += `<li class="page-item ${page === total_pages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="goToPage(${page + 1})">»</a></li>`;
        paginationEl.innerHTML = html;
    }

    function goToPage(page) { currentPage = page; loadPatients(); }
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterGender').value = '';
        document.getElementById('filterBloodGroup').value = '';
        currentPage = 1; currentSort = 'created_at'; currentOrder = 'DESC';
        loadPatients();
    }

    // View patient details
    async function viewPatient(id) {
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('patientDetailOffcanvas'));
        offcanvas.show();

        document.getElementById('patientDetailContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        try {
            const res = await fetch('/api/v1/patients/' + id);
            const data = await res.json();
            if (data.success) renderPatientDetail(data.data);
        } catch (e) {
            document.getElementById('patientDetailContent').innerHTML = '<div class="text-center py-5 text-danger">Failed to load patient</div>';
        }
    }

    function renderPatientDetail(data) {
        const p = data.patient;
        document.getElementById('patientDetailContent').innerHTML = `
        <div class="p-3 bg-light border-bottom text-center">
            <span class="avatar avatar-xl avatar-rounded ${getGenderBg(p.gender)} mb-2">${getInitials(p.full_name)}</span>
            <h5 class="mb-1">${escapeHtml(p.full_name)}</h5>
            <span class="badge bg-light text-dark">${p.mrn}</span>
            <span class="badge ${p.is_active ? 'bg-success' : 'bg-secondary'} ms-1">${p.is_active ? 'Active' : 'Inactive'}</span>
        </div>
        <div class="p-3">
            <div class="row g-3 mb-3">
                <div class="col-6"><span class="text-muted d-block fs-12">Gender</span><span class="fw-medium">${capitalize(p.gender)}</span></div>
                <div class="col-6"><span class="text-muted d-block fs-12">Age</span><span class="fw-medium">${p.age ? p.age + ' years' : '--'}</span></div>
                <div class="col-6"><span class="text-muted d-block fs-12">DOB</span><span class="fw-medium">${p.dob ? formatDate(p.dob) : '--'}</span></div>
                <div class="col-6"><span class="text-muted d-block fs-12">Blood Group</span><span class="fw-medium">${p.blood_group || '--'}</span></div>
            </div>
            <hr>
            <h6 class="fw-semibold mb-3"><i class="ri-phone-line me-2 text-primary"></i>Contact</h6>
            <div class="mb-2">${p.mobile ? '<i class="ri-phone-line me-2 text-muted"></i>' + p.mobile : '<span class="text-muted">No mobile</span>'}</div>
            <div class="mb-3">${p.email ? '<i class="ri-mail-line me-2 text-muted"></i>' + p.email : '<span class="text-muted">No email</span>'}</div>
            <hr>
            <h6 class="fw-semibold mb-3"><i class="ri-map-pin-line me-2 text-primary"></i>Address</h6>
            <p class="text-muted mb-3">${[p.address, p.city, p.state, p.pincode].filter(Boolean).join(', ') || 'No address'}</p>
            <hr>
            <h6 class="fw-semibold mb-3"><i class="ri-calendar-check-line me-2 text-primary"></i>Visit Summary</h6>
            <div class="row g-2 mb-3">
                <div class="col-6"><div class="bg-light rounded p-2 text-center"><span class="d-block fs-20 fw-semibold text-primary">${p.total_visits}</span><span class="fs-12 text-muted">Total Visits</span></div></div>
                <div class="col-6"><div class="bg-light rounded p-2 text-center"><span class="d-block fs-13 fw-medium">${p.last_visit ? formatDate(p.last_visit) : '--'}</span><span class="fs-12 text-muted">Last Visit</span></div></div>
            </div>
            ${data.recent_visits.length ? '<h6 class="fw-semibold mb-2">Recent Visits</h6><ul class="list-group list-group-flush">' + data.recent_visits.map(v => `<li class="list-group-item px-0 d-flex justify-content-between"><span>${formatDate(v.visit_start)} - ${v.visit_type}</span><span class="badge bg-${v.visit_status === 'closed' ? 'success' : 'warning'}-transparent">${v.visit_status}</span></li>`).join('') + '</ul>' : ''}
        </div>
        <div class="p-3 border-top">
            <div class="btn-list">
                <button class="btn btn-primary btn-sm" onclick="editPatient(${p.patient_id})"><i class="ri-pencil-line me-1"></i>Edit</button>
                <a href="<?= baseUrl('/appointments/create') ?>?patient_id=${p.patient_id}" class="btn btn-success btn-sm"><i class="ri-calendar-line me-1"></i>Book Appointment</a>
            </div>
        </div>
    `;
    }

    function editPatient(id) {
        // Redirect to edit page
        window.location.href = '<?= baseUrl('/patients') ?>/' + id + '/edit';
    }

    async function deletePatient(id, name) {
        const result = await Swal.fire({
            title: 'Deactivate Patient?',
            text: `Are you sure you want to deactivate "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, deactivate'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch('/api/v1/patients/' + id, { method: 'DELETE', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' } });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Deactivated!', timer: 1500, showConfirmButton: false });
                    loadPatients();
                    loadStats();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            } catch (e) { Swal.fire({ icon: 'error', title: 'Error', text: 'Network error' }); }
        }
    }

    function exportPatients() {
        const params = new URLSearchParams({
            search: document.getElementById('searchInput').value,
            status: document.getElementById('filterStatus').value,
            gender: document.getElementById('filterGender').value,
            blood_group: document.getElementById('filterBloodGroup').value,
            export: 'csv'
        });
        window.open('/api/v1/patients/export?' + params, '_blank');
    }

    // Helpers
    function escapeHtml(str) { return str ? String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]) : ''; }
    function capitalize(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }
    function getInitials(name) { return name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : '?'; }
    function getGenderBg(g) { return g === 'male' ? 'bg-primary-transparent' : g === 'female' ? 'bg-pink-transparent' : 'bg-secondary-transparent'; }
    function getGenderBadge(g) { return g === 'male' ? 'bg-primary-transparent' : g === 'female' ? 'bg-pink-transparent' : 'bg-secondary-transparent'; }
    function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : ''; }

    // Handle row click to open offcanvas
    function handleRowClick(event, patientId) {
        // Don't trigger if clicking on checkbox or action buttons
        if (event.target.closest('.form-check-input') || event.target.closest('.btn-list')) {
            return;
        }
        viewPatient(patientId);
    }

    // Navigate to patient detail page
    function goToPatient(patientId) {
        Melina.navigate('<?= baseUrl('/patients') ?>/' + patientId);
    }
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>