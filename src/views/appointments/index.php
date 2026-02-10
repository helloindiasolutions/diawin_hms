<?php
/**
 * Appointments Master - Professional Industry Standard UI
 * Features: Filters, Stats, integrated New Appointment, Status management
 */
$pageTitle = 'Appointments';
ob_start();
?>

<!-- Auto Complete CSS -->
<link rel="stylesheet" href="<?= asset('libs/@tarekraafat/autocomplete.js/css/autoComplete.css') ?>">
<link rel="stylesheet" href="<?= asset('libs/sweetalert2/sweetalert2.min.css') ?>">

<style>
    /* Professional Medical Clinic Styles */
    :root {
        --apt-scheduled: #3b82f6;
        --apt-checked-in: #f59e0b;
        --apt-completed: #10b981;
        --apt-cancelled: #ef4444;
        --apt-no-show: #6b7280;
    }

    #appointmentsTable {
        font-size: 0.85rem;
    }

    #appointmentsTable th {
        background-color: rgba(var(--primary-rgb), 0.02);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4b5563;
        padding: 1rem 0.75rem;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    .bg-scheduled {
        background-color: var(--apt-scheduled);
    }

    .bg-checked-in {
        background-color: var(--apt-checked-in);
    }

    .bg-completed {
        background-color: var(--apt-completed);
    }

    .bg-cancelled {
        background-color: var(--apt-cancelled);
    }

    .bg-no-show {
        background-color: var(--apt-no-show);
    }

    /* Fix: Ensure flatpickr is always above the modal */
    .flatpickr-calendar {
        z-index: 10000 !important;
    }

    .token-badge {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: 'Courier New', Courier, monospace;
    }

    .patient-name {
        font-weight: 600;
        color: var(--default-text-color);
        display: block;
    }

    .patient-meta {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .autoComplete_wrapper {
        width: 100%;
    }

    .autoComplete_wrapper>input {
        width: 100%;
        height: 52px;
        /* Increased for better touch/click target */
        font-size: 16px;
        padding: 10px 15px;
        border-radius: 12px;
        border: 2px solid rgba(var(--primary-rgb), 0.1);
        transition: all 0.3s ease;
        background-color: #f8fafc;
    }

    .autoComplete_wrapper>input:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
    }

    /* AutoComplete Result Styling - Premium View */
    .autoComplete_wrapper>ul {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        padding: 0 !important;
        margin-top: 8px !important;
        overflow: hidden !important;
        background: #fff !important;
        z-index: 1070 !important;
    }

    .autoComplete_result_item {
        transition: all 0.2s ease !important;
        border-bottom: 1px solid #f1f5f9 !important;
        cursor: pointer !important;
        background-color: #fff !important;
        color: #334155 !important;
        padding: 0 !important;
        /* Managed by inner d-flex */
    }

    .autoComplete_result_item:last-child {
        border-bottom: none !important;
    }

    /* FIX: Proper highlighting for keyboard navigation */
    .autoComplete_wrapper>ul>li[aria-selected="true"],
    .autoComplete_wrapper>ul>li:hover {
        background-color: rgba(var(--primary-rgb), 0.12) !important;
        color: var(--primary-color) !important;
    }

    .autoComplete_wrapper>ul>li[aria-selected="true"] .text-dark,
    .autoComplete_wrapper>ul>li:hover .text-dark {
        color: var(--primary-color) !important;
    }

    /* Form Label Styling */
    .modal-body .form-label {
        font-weight: 600;
        color: #475569;
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
    }

    .quick-entry-field {
        border: 2px solid #f1f5f9 !important;
        border-radius: 10px !important;
        padding: 0.6rem 0.8rem !important;
        font-size: 0.9rem !important;
        transition: all 0.2s ease !important;
    }

    .quick-entry-field:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1) !important;
        background-color: #fff !important;
    }

    /* Fix: Modal and Table Dropdown Clipping */
    .table-responsive {
        overflow: visible !important;
    }

    .modal-open {
        overflow: hidden;
    }

    .dropdown-menu {
        z-index: 1065 !important;
    }

    /* Card Hover Feedback */
    .custom-card.stat-card:hover {
        transform: translateY(-3px);
        transition: all 0.3s ease;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .cursor-pointer {
        cursor: pointer !important;
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<!-- Header & Actions -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title fw-semibold fs-20 mb-1">Appointments</h1>
        <p class="text-muted mb-0 fs-13">Manage patient visits, schedules, and clinic flow <span
                class="badge bg-primary-transparent ms-2">Press Alt+N for Quick New</span></p>
    </div>
    <div class="btn-list">
        <button class="btn btn-success btn-wave px-4" id="btnNewApt" onclick="openQuickApt()">
            <i class="ri-add-line me-1"></i>New Appointment <kbd
                class="ms-2 fs-10 bg-white text-success border opacity-75">Alt+N</kbd>
        </button>
        <a href="<?= baseUrl('/appointments/calendar') ?>" class="btn btn-outline-primary btn-wave">
            <i class="ri-calendar-line me-1"></i>Calendar View
        </a>
        <a href="<?= baseUrl('/queue') ?>" class="btn btn-outline-primary btn-wave">
            <i class="ri-list-ordered me-1"></i>Queue Manager
        </a>
    </div>
</div>

<!-- Stats Dashboard -->
<div class="row mb-4">
    <!-- ... stats remain same ... -->
    <div class="col-xl-3 col-sm-6">
        <div class="card custom-card dashboard-main-card overflow-hidden primary">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="fs-13 fw-medium">Total Today</span>
                        <h4 class="fw-semibold my-2 lh-1" id="statToday">--</h4>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fs-12 d-block text-muted">All scheduled visits</span>
                        </div>
                    </div>
                    <div>
                        <span class="avatar avatar-md bg-primary-transparent svg-primary">
                            <i class="ri-calendar-todo-line fs-18"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card custom-card dashboard-main-card overflow-hidden warning">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="fs-13 fw-medium">In Waiting Room</span>
                        <h4 class="fw-semibold my-2 lh-1 text-warning" id="statWait">--</h4>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fs-12 d-block text-muted">Patients checked-in</span>
                        </div>
                    </div>
                    <div>
                        <span class="avatar avatar-md bg-warning-transparent svg-warning">
                            <i class="ri-user-received-2-line fs-18"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card custom-card dashboard-main-card overflow-hidden success">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="fs-13 fw-medium">Completed</span>
                        <h4 class="fw-semibold my-2 lh-1 text-success" id="statDone">--</h4>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fs-12 d-block text-muted">Successful visits</span>
                        </div>
                    </div>
                    <div>
                        <span class="avatar avatar-md bg-success-transparent svg-success">
                            <i class="ri-checkbox-circle-line fs-18"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card custom-card dashboard-main-card overflow-hidden danger">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="fs-13 fw-medium">Cancelled / No-Show</span>
                        <h4 class="fw-semibold my-2 lh-1 text-danger" id="statMisc">--</h4>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fs-12 d-block text-muted">Unattended appointments</span>
                        </div>
                    </div>
                    <div>
                        <span class="avatar avatar-md bg-danger-transparent svg-danger">
                            <i class="ri-close-circle-line fs-18"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and List -->
<div class="card custom-card">
    <div class="card-header py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="input-group shadow-none" style="width: 230px;">
                <span class="input-group-text bg-light border-0"><i class="ri-calendar-line"></i></span>
                <input type="text" class="form-control border-0 bg-light fs-13" id="filterDate"
                    placeholder="Select Date Range">
            </div>
            <div class="input-group shadow-none" style="width: 280px;">
                <span class="input-group-text bg-light border-0"><i class="ri-search-line"></i></span>
                <input type="text" class="form-control border-0 bg-light fs-13" id="searchApt"
                    placeholder="Search Patient ( / to focus )">
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select bg-light border-0 fs-13" id="filterProvider" style="width: 180px;">
                <option value="">All Providers</option>
            </select>
            <select class="form-select bg-light border-0 fs-13" id="filterStatus" style="width: 140px;">
                <option value="">All Status</option>
                <option value="scheduled">Scheduled</option>
                <option value="checked-in">Checked-in</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="no-show">No-show</option>
            </select>
            <button class="btn btn-icon btn-light border-0 btn-wave" onclick="reloadData()">
                <i class="ri-refresh-line"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="appointmentsTable">
                <thead>
                    <tr>
                        <th style="width: 80px;">Time</th>
                        <th>Patient Details</th>
                        <th>Provider / Doctor</th>
                        <th>Status</th>
                        <th style="width: 100px;">Token</th>
                    </tr>
                </thead>
                <tbody id="aptListBody">
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2">Connecting to records...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="aptDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 overflow-hidden">
            <div class="modal-header bg-light border-bottom">
                <h6 class="modal-title fw-bold" id="aptModalTitle">Appointment Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="aptModalBody">
                <div class="p-5 text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                    <div>Loading details...</div>
                </div>
            </div>
            <div class="modal-footer p-3 border-top bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <div id="aptModalActions"></div>
            </div>
        </div>
    </div>
</div>


<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    // --- State & Config ---
    var providers = providers || [];
    var autoCompleteInstance = autoCompleteInstance || null;
    var filterTimeout;

    // --- Initialization & SPA Bridge ---
    var appointmentsPageInitialized = false;
    function initPage() {
        if (appointmentsPageInitialized) return;

        console.log('Appointments Page: Initializing logic...');
        appointmentsPageInitialized = true;

        try {
            initFlatpickr();
            initEventListeners();
            loadProviders();
            reloadData();
            setupKeyboardShortcuts();
            console.log('Appointments Page: Initialization complete.');
        } catch (e) {
            console.error('Appointments Page: Initialization failure:', e);
        }
    };

    // Self-start or Register with SPA bridge
    if (window.Melina && typeof Melina.onPageLoad === 'function') {
        Melina.onPageLoad(initPage);

        // SPA Fallback: If DOM is already present and we are likely in an SPA view swap,
        // trigger initialization proactively.
        if (document.getElementById('aptListBody')) {
            setTimeout(initPage, 100);
        }
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPage);
    } else {
        initPage();
    }

    // --- Helper Functions ---
    function getStatusColor(s) {
        return { 'scheduled': 'primary', 'checked-in': 'warning', 'completed': 'success', 'cancelled': 'danger', 'no-show': 'dark' }[s] || 'secondary';
    }

    function capitalize(str) { return str ? str.split('-').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join('-') : ''; }

    function escapeHtml(str) { return str ? String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]) : ''; }

    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key.toLowerCase() === 'n') {
                e.preventDefault();
                openQuickApt();
            }
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                document.getElementById('searchApt').focus();
            }
        });
    }

    function initFlatpickr() {
        if (document.getElementById('filterDate')) {
            flatpickr("#filterDate", {
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: ["today", "today"],
                onClose: function (selectedDates) {
                    if (selectedDates.length === 2) {
                        reloadData();
                    }
                }
            });
        }
        if (document.getElementById('aptDateTime')) {
            flatpickr("#aptDateTime", {
                enableTime: true, dateFormat: "Y-m-d H:i", minDate: "today", minuteIncrement: 5, time_24hr: true, static: true
            });
        }
    }

    // --- Data Loaders ---
    async function loadProviders() {
        try {
            const select = document.getElementById('filterProvider');
            const modalSelect = document.getElementById('aptProvider') || document.getElementById('qaAptProvider');

            // Log for debugging SPA
            console.log('Fetching providers...', { select: !!select, modalSelect: !!modalSelect });

            const res = await fetch('/api/v1/appointments/providers');
            if (!res.ok) throw new Error('API Error');

            const data = await res.json();
            if (data.success && data.data.providers) {
                providers = data.data.providers;

                if (select) {
                    select.innerHTML = '<option value="">All Providers</option>';
                    providers.forEach(p => {
                        select.add(new Option(`${p.full_name} (${p.specialization || 'General'})`, p.provider_id));
                    });
                }

                if (modalSelect) {
                    modalSelect.innerHTML = '<option value="">Select Doctor</option>';
                    providers.forEach(p => {
                        modalSelect.add(new Option(`${p.full_name} (${p.specialization || 'General'})`, p.provider_id));
                    });
                }
            }
        } catch (e) {
            console.error('Failed to load providers:', e);
        }
    }

    async function loadStats() {
        const dateInput = document.getElementById('filterDate');
        if (!dateInput) return;
        const date = dateInput.value;
        try {
            const res = await fetch(`/api/v1/appointments/stats?date=${date}`);
            const data = await res.json();
            if (data.success) {
                const s = data.data;
                const elToday = document.getElementById('statToday');
                const elWait = document.getElementById('statWait');
                const elDone = document.getElementById('statDone');
                const elMisc = document.getElementById('statMisc');

                if (elToday) elToday.textContent = s.total;
                if (elWait) elWait.textContent = s.checked_in;
                if (elDone) elDone.textContent = s.completed;
                if (elMisc) elMisc.textContent = s.cancelled + s.no_show;
            }
        } catch (e) { }
    }

    async function loadAppointments() {
        const tableBody = document.getElementById('aptListBody');
        const filterDate = document.getElementById('filterDate');
        if (!tableBody || !filterDate) return;

        const date = filterDate.value;
        const providerId = document.getElementById('filterProvider')?.value || '';
        const status = document.getElementById('filterStatus')?.value || '';
        const search = document.getElementById('searchApt')?.value || '';

        tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        const params = new URLSearchParams({ date, provider_id: providerId, status, search });
        try {
            const res = await fetch('/api/v1/appointments?' + params);
            const data = await res.json();
            if (data.success) {
                renderAppointments(data.data.appointments);
            }
        } catch (e) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-danger">Error fetching data</td></tr>';
        }
    }

    async function reloadData() {
        loadStats();
        loadAppointments();
    }

    // --- Action Functions ---
    async function updateStatus(id, status, patientName = '', patientPhone = '') {
        // Immediately hide the details modal if it's currently open to prevent overlapping modals
        const modalEl = document.getElementById('aptDetailModal');
        if (modalEl) {
            const instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();
        }

        const config = {
            'checked-in': {
                title: 'Confirm Patient Arrival',
                html: `
                    <div class="text-start bg-light p-3 rounded mb-3">
                        <div class="fw-bold fs-16 text-dark mb-1">${patientName}</div>
                        <div class="text-muted"><i class="ri-phone-line me-1"></i> ${patientPhone || 'No phone'}</div>
                    </div>
                    <div class="alert alert-warning border-0 d-flex align-items-center mb-0">
                        <i class="ri-information-line fs-20 me-2"></i>
                        <div>Mark as Arrived & Generate Token?</div>
                    </div>
                `,
                icon: null,
                confirmButtonText: 'Yes, Arrived',
                color: '#f59e0b',
                footer: '<span class="text-muted fs-11">This will capture the current arrival time.</span>'
            },
            'cancelled': {
                title: 'Cancel Appointment?',
                html: 'Are you sure you want to cancel this appointment? <br><small class="text-danger">This action cannot be undone.</small>',
                icon: 'warning',
                confirmButtonText: 'Yes, Cancel',
                color: '#ef4444'
            },
            'completed': {
                title: 'Complete Visit?',
                text: 'Mark the consultation as finished.',
                icon: 'success',
                confirmButtonText: 'Yes, Complete',
                color: '#10b981'
            }
        }[status] || { title: 'Update Status?', text: 'Are you sure?', icon: 'info', confirmButtonText: 'Confirm', color: '#3b82f6' };

        const result = await Swal.fire({
            title: config.title,
            html: config.html || config.text,
            width: 450,
            icon: config.icon,
            showCancelButton: true,
            confirmButtonColor: config.color,
            cancelButtonColor: '#6c757d',
            confirmButtonText: config.confirmButtonText,
            footer: config.footer,
            focusConfirm: false,
            reverseButtons: true
        });

        if (result.isConfirmed) {
            try {
                Swal.fire({
                    title: 'Processing...',
                    didOpen: () => { Swal.showLoading(); },
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    background: 'transparent',
                    backdrop: 'rgba(0,0,0,0.4)'
                });

                const res = await fetch(`/api/v1/appointments/${id}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status })
                });

                const data = await res.json();

                if (data.success) {
                    Swal.close();
                    window.showToast(data.message || 'Status updated successfully');
                    reloadData();
                } else {
                    Swal.fire('Error', data.message || 'Failed to update status', 'error');
                }
            } catch (e) {
                Swal.fire('System Error', 'Could not connect to server', 'error');
            }
        }
    }

    function renderAppointments(apts) {
        const tbody = document.getElementById('aptListBody');
        if (!apts.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted"><img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" style="height:120px; opacity:0.6" class="d-block mx-auto mb-3">No appointments found for the selected criteria.</td></tr>';
            return;
        }

        tbody.innerHTML = apts.map(a => {
            const time = new Date(a.scheduled_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            return `
            <tr onclick="handleRowClick(event, ${a.appointment_id}, '${a.status}', '${escapeHtml(a.patient.full_name)}', '${a.patient.mobile || ''}')" style="cursor: pointer;">
                <td class="fw-bold text-primary align-middle">${time}</td>
                <td class="align-middle">
                    <div class="patient-info">
                        <span class="patient-name text-uppercase fw-bold text-dark">${escapeHtml(a.patient.full_name)}</span>
                        <div class="patient-meta mt-1">
                            <span class="badge bg-light text-muted border fw-normal">${a.patient.mrn}</span> 
                            <span class="text-muted ms-1 small">| ${a.patient.gender}, ${a.patient.age}y</span> 
                            ${a.patient.mobile ? `<span class="ms-2 text-primary small"><i class="ri-phone-fill"></i> ${a.patient.mobile}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm bg-primary-transparent rounded-circle text-primary">
                             ${(a.provider.name || 'D')[0]}
                        </div>
                        <div>
                             <span class="fw-semibold text-dark d-block fs-13">${escapeHtml(a.provider.name || 'General')}</span>
                             <small class="text-muted fs-11">${escapeHtml(a.provider.specialization || 'Clinical')}</small>
                        </div>
                    </div>
                </td>
                <td class="align-middle">
                    <span class="badge bg-${getStatusColor(a.status)}-transparent text-${getStatusColor(a.status)} border border-${getStatusColor(a.status)} border-opacity-25 rounded-pill px-3 py-1">
                        ${capitalize(a.status)}
                    </span>
                </td>
                <td class="align-middle">
                    ${a.queue.token_no ? `<span class="token-badge bg-primary text-white scale-hover display-inline-block shadow-sm">${a.queue.token_no}</span>` : '<span class="text-muted fs-11 opacity-75">--</span>'}
                </td>
            </tr>
        `;
        }).join('');
    }

    // --- Global Assignments ---
    window.handleRowClick = function (event, id) {
        if (event.target.closest('.btn') || event.target.closest('.dropdown')) return;
        viewAppointment(id);
    };

    /**
     * Show Appointment Details in Offcanvas
     */
    async function viewAppointment(id) {
        const modalEl = document.getElementById('aptDetailModal');
        if (!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);
        const body = document.getElementById('aptModalBody');
        const actions = document.getElementById('aptModalActions');

        // Reset and show
        body.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-primary"></div>
                <p class="text-muted mt-2">Fetching appointment #${id}...</p>
            </div>
        `;
        actions.innerHTML = '';
        modal.show();

        try {
            // Fetch appointment data
            const res = await fetch(`/api/v1/appointments?id=${id}`);
            const data = await res.json();

            if (!data.success || !data.data.appointments.length) {
                body.innerHTML = '<div class="alert alert-danger m-3">Appointment details not found.</div>';
                return;
            }

            const apt = data.data.appointments.find(a => a.appointment_id == id) || data.data.appointments[0];

            // Build Body HTML
            body.innerHTML = `
                <div class="p-4 border-bottom bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-xl bg-primary-transparent rounded-circle text-primary fs-24 fw-bold">
                            ${apt.patient.full_name[0]}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">${escapeHtml(apt.patient.full_name)}</h5>
                            <span class="badge bg-light text-muted border">MRN: ${apt.patient.mrn}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-6">
                            <label class="text-muted small fw-semibold d-block">Status</label>
                            <span class="badge bg-${getStatusColor(apt.status)}-transparent text-${getStatusColor(apt.status)} border border-${getStatusColor(apt.status)} border-opacity-25 rounded-pill px-3 mt-1">
                                ${capitalize(apt.status)}
                            </span>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small fw-semibold d-block">Time</label>
                            <span class="fw-bold text-dark d-block mt-1">${new Date(apt.scheduled_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-semibold d-block">Provider / Doctor</label>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <div class="avatar avatar-xs bg-soft-info rounded-circle"><i class="ri-stethoscope-line"></i></div>
                                <span class="fw-semibold">${escapeHtml(apt.provider.name || 'General Medicine')}</span>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <div class="card bg-light border-0 shadow-none">
                                <div class="card-body p-3">
                                    <label class="text-muted small fw-semibold d-block mb-1">Patient Vitals & Info</label>
                                    <div class="d-flex gap-3 text-dark fs-13">
                                        <span><i class="ri-user-line me-1 text-muted"></i>${apt.patient.gender}, ${apt.patient.age}y</span>
                                        ${apt.patient.mobile ? `<span><i class="ri-phone-line me-1 text-muted"></i>${apt.patient.mobile}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Build Actions HTML
            let actionHtml = '';
            if (apt.status === 'scheduled') {
                actionHtml = `
                    <button class="btn btn-danger-light" onclick="updateStatus(${apt.appointment_id}, 'cancelled')">Cancel Appointment</button>
                    <button class="btn btn-success px-4" onclick="updateStatus(${apt.appointment_id}, 'checked-in', '${escapeHtml(apt.patient.full_name)}', '${apt.patient.mobile || ''}')">
                        <i class="ri-login-box-line me-1"></i> Patient Arrival
                    </button>
                `;
            } else if (apt.status === 'checked-in') {
                actionHtml = `
                    <button class="btn btn-danger-light" onclick="updateStatus(${apt.appointment_id}, 'cancelled')">Cancel Appointment</button>
                    <button class="btn btn-success px-4" onclick="updateStatus(${apt.appointment_id}, 'completed', '${escapeHtml(apt.patient.full_name)}')">
                        <i class="ri-checkbox-circle-line me-1"></i> Complete Visit
                    </button>
                `;
            } else {
                actionHtml = `<button class="btn btn-outline-primary" onclick="window.Melina.navigate('/appointments/${apt.appointment_id}')"><i class="ri-eye-line me-1"></i>View Full Records</button>`;
            }
            actions.innerHTML = actionHtml;

        } catch (e) {
            body.innerHTML = '<div class="alert alert-danger m-3">Error fetching details.</div>';
        }
    }

    function initEventListeners() {
        const searchInput = document.getElementById('searchApt');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(reloadData, 300);
            });
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(filterTimeout);
                    reloadData();
                }
            });
        }

        ['filterProvider', 'filterStatus'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', reloadData);
        });
    }

    // --- Global Exports ---
    window.viewAppointment = viewAppointment;
    window.updateStatus = updateStatus;
    window.reloadData = reloadData;
    window.loadProviders = loadProviders;
    window.initAppointments = initPage;
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>