<?php
/**
 * Appointment Calendar - Interactive Drag & Drop View
 */
$pageTitle = 'Calendar View';
ob_start();
?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    :root {
        --fc-border-color: #e5e7eb;
        --fc-button-bg-color: #f3f4f6;
        --fc-button-border-color: #e5e7eb;
        --fc-button-text-color: #374151;
        --fc-button-hover-bg-color: #e5e7eb;
        --fc-button-hover-border-color: #d1d5db;
        --fc-button-active-bg-color: var(--primary-color);
        --fc-button-active-border-color: var(--primary-color);
    }

    #calendar {
        background: #fff;
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border: 1px solid #eef2f6;
    }

    /* FullCalendar Premium Styling */
    .fc {
        font-family: inherit;
        max-width: 100%;
    }

    .fc-header-toolbar {
        margin-bottom: 1.5rem !important;
        gap: 1rem;
    }

    .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 700 !important;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Custom Button Styles (Match Theme) */
    .fc .fc-button {
        padding: 0.5rem 1rem !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        box-shadow: none !important;
        transition: all 0.2s;
    }

    .fc .fc-button-primary {
        background-color: var(--fc-button-bg-color) !important;
        border-color: var(--fc-button-border-color) !important;
        color: var(--fc-button-text-color) !important;
    }

    .fc .fc-button-primary:hover {
        background-color: var(--fc-button-hover-bg-color) !important;
        border-color: var(--fc-button-hover-border-color) !important;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: #fff !important;
    }

    .fc .fc-today-button {
        background-color: rgba(var(--primary-rgb), 0.1) !important;
        border-color: transparent !important;
        color: var(--primary-color) !important;
    }

    /* Table/Slot Headers */
    .fc-col-header-cell {
        background: #f9fafb;
        padding: 10px 0 !important;
    }

    .fc-col-header-cell-cushion {
        font-size: 0.75rem;
        font-weight: 700;
        color: #4b5563;
        text-decoration: none !important;
    }

    .fc-timegrid-slot-label-cushion {
        font-size: 0.7rem;
        font-weight: 600;
        color: #6b7280;
    }

    /* Event Styling */
    .fc-v-event {
        border-radius: 6px !important;
        border: none !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
        margin: 1px 2px !important;
    }

    .event-scheduled {
        background-color: #eff6ff !important;
        border-left: 4px solid #3b82f6 !important;
    }

    .event-scheduled .fc-event-main-frame {
        color: #1d4ed8 !important;
    }

    .event-checked-in {
        background-color: #fffbeb !important;
        border-left: 4px solid #f59e0b !important;
    }

    .event-checked-in .fc-event-main-frame {
        color: #b45309 !important;
    }

    .event-completed {
        background-color: #ecfdf5 !important;
        border-left: 4px solid #10b981 !important;
    }

    .event-completed .fc-event-main-frame {
        color: #047857 !important;
    }

    .fc-event-title {
        font-weight: 700 !important;
        font-size: 0.75rem;
    }

    .fc-event-time {
        font-size: 0.65rem;
        font-weight: 500;
        opacity: 0.8;
    }

    .legend-card {
        background: #fff;
        border: 1px solid #eef2f6;
        border-radius: 10px;
        padding: 0.5rem 1rem;
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title fw-semibold fs-20 mb-1">Appointment Calendar</h1>
        <p class="text-muted mb-0 fs-13">Visual schedule of all patient consultations</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="legend-card d-flex gap-3 align-items-center">
            <div class="legend-item"><span class="badge bg-primary-transparent rounded-pill"><i
                        class="ri-checkbox-blank-circle-fill fs-8 me-1"></i>Scheduled</span></div>
            <div class="legend-item"><span class="badge bg-warning-transparent rounded-pill"><i
                        class="ri-checkbox-blank-circle-fill fs-8 me-1"></i>Arrived</span></div>
            <div class="legend-item"><span class="badge bg-success-transparent rounded-pill"><i
                        class="ri-checkbox-blank-circle-fill fs-8 me-1"></i>Done</span></div>
        </div>
        <a href="<?= baseUrl('/appointments') ?>" class="btn btn-success btn-wave shadow-sm">
            <i class="ri-table-line me-1"></i>Table View
        </a>
    </div>
</div>

<style>
    /* Sidebar Styles */
    .calendar-sidebar {
        height: 100%;
    }

    .insight-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #eef2f6;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }

    .insight-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .insight-title i {
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    .stat-box {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 10px;
        margin-bottom: 0.75rem;
        border: 1px solid transparent;
        transition: all 0.2s;
    }

    .stat-box:hover {
        border-color: var(--primary-color);
        background: #fff;
        transform: translateX(4px);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 2px;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
    }

    .upcoming-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .upcoming-item:last-child {
        border-bottom: none;
    }

    .patient-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .btn-quick {
        width: 100%;
        text-align: left;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
    }
</style>

<div class="row g-4">
    <div class="col-xl-9">
        <div class="card border-0 shadow-none mb-0">
            <div class="card-body p-0">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="calendar-sidebar">
            <!-- Daily Stats -->
            <div class="insight-card">
                <div class="insight-title"><i class="ri-pie-chart-2-line"></i> Daily Insight</div>
                <div class="stat-box">
                    <div class="stat-icon bg-primary-transparent"><i class="ri-calendar-check-line"></i></div>
                    <div>
                        <div class="stat-label">Total Booked</div>
                        <div class="stat-value" id="statsTotal">24</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon bg-warning-transparent"><i class="ri-user-follow-line"></i></div>
                    <div>
                        <div class="stat-label">In Waiting</div>
                        <div class="stat-value" id="statsWaiting">08</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon bg-success-transparent"><i class="ri-checkbox-circle-line"></i></div>
                    <div>
                        <div class="stat-label">Consulted</div>
                        <div class="stat-value" id="statsDone">12</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="insight-card">
                <div class="insight-title"><i class="ri-flashlight-line"></i> Quick Operations</div>
                <a href="<?= baseUrl('/registrations/create') ?>" class="btn btn-primary btn-quick">
                    <span>New Registration</span>
                    <i class="ri-add-circle-line"></i>
                </a>
                <button class="btn btn-outline-primary btn-quick" onclick="window.location.reload()">
                    <span>Refresh Schedule</span>
                    <i class="ri-refresh-line"></i>
                </button>
            </div>

            <!-- Upcoming Arrivals -->
            <div class="insight-card">
                <div class="insight-title"><i class="ri-time-line"></i> Next in Queue</div>
                <div id="upcomingArrivals">
                    <div class="upcoming-item">
                        <div class="patient-avatar">RS</div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-13">Rahul Sharma</div>
                            <div class="text-muted fs-11">09:15 AM • Dr. Karthik</div>
                        </div>
                    </div>
                    <div class="upcoming-item">
                        <div class="patient-avatar">AM</div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-13">Anjali Menon</div>
                            <div class="text-muted fs-11">09:30 AM • Dr. Priya</div>
                        </div>
                    </div>
                    <div class="upcoming-item">
                        <div class="patient-avatar">VK</div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-13">Vijay Kumar</div>
                            <div class="text-muted fs-11">10:00 AM • Dr. Karthik</div>
                        </div>
                    </div>
                </div>
            </div>
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
    // State management for SPA
    let calendarInstance = null;
    let fcLoadingPromise = null;

    // Dynamic Library & CSS Loader
    const loadFullCalendar = () => {
        if (fcLoadingPromise) return fcLoadingPromise;

        fcLoadingPromise = new Promise((resolve, reject) => {
            if (typeof FullCalendar !== 'undefined') {
                return resolve();
            }

            // Inject CSS if not present
            if (!document.querySelector('link[href*="fullcalendar"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css';
                document.head.appendChild(link);
            }

            // Check if script is already present but loading
            const existingScript = document.querySelector('script[src*="fullcalendar"]');
            if (existingScript) {
                let retries = 0;
                const check = setInterval(() => {
                    if (typeof FullCalendar !== 'undefined') {
                        clearInterval(check);
                        resolve();
                    }
                    if (retries++ > 50) { // 5 seconds timeout
                        clearInterval(check);
                        reject('Timeout waiting for FullCalendar');
                    }
                }, 100);
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js';
            script.onload = () => resolve();
            script.onerror = () => {
                fcLoadingPromise = null;
                reject('Failed to load FullCalendar script');
            };
            document.head.appendChild(script);
        });

        return fcLoadingPromise;
    };

    // Main Init
    const initCalendarPage = async () => {
        // Exit early if calendar element doesn't exist (wrong page)
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            return;
        }

        try {
            await loadFullCalendar();
            initCalendar();

            // Register cleanup
            if (typeof Melina !== 'undefined' && typeof Melina.onPageUnload === 'function') {
                Melina.onPageUnload(() => {
                    if (calendarInstance) {
                        calendarInstance.destroy();
                        calendarInstance = null;
                    }
                });
            } else if (typeof onPageUnload === 'function') {
                onPageUnload(() => {
                    if (calendarInstance) {
                        calendarInstance.destroy();
                        calendarInstance = null;
                    }
                });
            }
        } catch (e) {
            console.error('Calendar Init Error:', e);
            const el = document.getElementById('calendar');
            if (el) el.innerHTML = `<div class="text-center py-5 text-danger">Error: ${e}</div>`;
        }
    };

    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initCalendarPage);
    } else {
        document.addEventListener('DOMContentLoaded', initCalendarPage);
    }

    function initCalendar() {
        if (typeof FullCalendar === 'undefined') return;

        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        // Cleanup existing instance if any
        if (calendarInstance) {
            calendarInstance.destroy();
        }

        try {
            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                height: 750,
                contentHeight: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                slotMinTime: '10:00:00',
                slotMaxTime: '21:00:00',
                slotDuration: '00:15:00',
                expandRows: true,
                allDaySlot: false,
                stickyHeaderDates: true,
                handleWindowResize: true,
                nowIndicator: true,
                dayMaxEvents: true,
                events: fetchEvents,
                eventClick: function (info) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: info.event.title,
                            html: `
<div class="text-start">
    <p class="mb-2"><strong>Doctor:</strong> ${info.event.extendedProps.provider}</p>
    <p class="mb-2"><strong>Status:</strong> <span
            class="badge bg-${getStatusColor(info.event.extendedProps.status)}-transparent">${info.event.extendedProps.status.toUpperCase()}</span>
    </p>
    <p class="mb-0"><strong>Time:</strong> ${info.event.start.toLocaleTimeString()} -
        ${info.event.end.toLocaleTimeString()}</p>
</div>
`,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'View Full Detail',
                            confirmButtonColor: '#10b981',
                            cancelButtonText: 'Close'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                viewAppointment(info.event.id);
                            }
                        });
                    }
                },
                eventClassNames: function (arg) {
                    return ['event-' + arg.event.extendedProps.status];
                }
            });
            calendarInstance.render();
        } catch (error) {
            console.error('Error initializing calendar:', error);
        }
    }

    function getStatusColor(s) {
        return { 'scheduled': 'primary', 'checked-in': 'warning', 'completed': 'success', 'cancelled': 'danger' }[s] ||
            'secondary';
    }

    function capitalize(str) { return str ? str.split('-').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join('-') : ''; }

    async function updateStatus(id, status, patientName = '', patientPhone = '') {
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
                    if (window.showToast) window.showToast(data.message || 'Status updated successfully');
                    if (calendarInstance) calendarInstance.refetchEvents();
                } else {
                    Swal.fire('Error', data.message || 'Failed to update status', 'error');
                }
            } catch (e) {
                Swal.fire('System Error', 'Could not connect to server', 'error');
            }
        }
    }

    async function viewAppointment(id) {
        const modalEl = document.getElementById('aptDetailModal');
        if (!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);
        const body = document.getElementById('aptModalBody');
        const actions = document.getElementById('aptModalActions');

        body.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-primary"></div>
                <p class="text-muted mt-2">Fetching appointment #${id}...</p>
            </div>
        `;
        actions.innerHTML = '';
        modal.show();

        try {
            const res = await fetch(`/api/v1/appointments?id=${id}`);
            const data = await res.json();

            if (!data.success || !data.data.appointments.length) {
                body.innerHTML = '<div class="alert alert-danger m-3">Appointment details not found.</div>';
                return;
            }

            const apt = data.data.appointments.find(a => a.appointment_id == id) || data.data.appointments[0];

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

    async function fetchEvents(info, successCallback, failureCallback) {
        try {
            const start = info.startStr.split('T')[0];
            const end = info.endStr.split('T')[0];
            const res = await fetch(`/api/v1/appointments?start=${start}&end=${end}`);
            if (!res.ok) throw new Error('API request failed');

            const data = await res.json();

            // Check if page element still exists before updating
            if (!document.getElementById('calendar')) return;

            if (data.success) {
                const appointments = data.data.appointments || [];
                const stats = { total: appointments.length, waiting: 0, done: 0 };
                const upcoming = [];

                const events = appointments.map(a => {
                    if (a.status === 'checked-in') stats.waiting++;
                    if (a.status === 'completed') stats.done++;
                    if (a.status === 'scheduled') upcoming.push(a);

                    return {
                        id: a.appointment_id,
                        title: a.patient?.full_name || 'Unknown Patient',
                        start: a.scheduled_at,
                        end: calculateEnd(a.scheduled_at, a.duration || 15),
                        extendedProps: {
                            status: a.status,
                            provider: a.provider?.name || 'Unknown Doctor'
                        }
                    };
                });

                // Update Stats
                const updateStat = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val.toString().padStart(2, '0');
                };
                updateStat('statsTotal', stats.total);
                updateStat('statsWaiting', stats.waiting);
                updateStat('statsDone', stats.done);

                // Update Upcoming
                const upCont = document.getElementById('upcomingArrivals');
                if (upCont) {
                    upCont.innerHTML = upcoming.length > 0
                        ? upcoming.slice(0, 3).map(a => `
<div class="upcoming-item">
    <div class="patient-avatar">${getInitials(a.patient?.full_name || 'U')}</div>
    <div class="flex-grow-1">
        <div class="fw-bold fs-13 text-truncate" style="max-width: 120px;">${escapeHtml(a.patient?.full_name ||
                            'Unknown')}</div>
        <div class="text-muted fs-11">${new Date(a.scheduled_at).toLocaleTimeString([], {
                                hour: '2-digit', minute:
                                    '2-digit'
                            })} • ${escapeHtml(a.provider?.name || 'Unknown')}</div>
    </div>
</div>
`).join('')
                        : '<div class="text-muted fs-12 py-3 text-center">No pending arrivals</div>';
                }

                successCallback(events);
            }
        } catch (e) {
            console.error('Fetch Events Error:', e);
            failureCallback(e);
        }
    }

    function getInitials(name) {
        if (!name) return 'U';
        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    }

    function calculateEnd(start, duration) {
        const d = new Date(start);
        d.setMinutes(d.getMinutes() + (duration || 15));
        return d.toISOString();
    }

    function escapeHtml(str) {
        return str ? String(str).replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[m]) : '';
    } </script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>