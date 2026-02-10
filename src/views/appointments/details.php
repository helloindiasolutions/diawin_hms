<?php
/**
 * Appointment Details View
 */
$pageTitle = 'Appointment Details';
ob_start();
?>
<link rel="stylesheet" href="<?= asset('libs/sweetalert2/sweetalert2.min.css') ?>">
<style>
    /* Patient Card */
    .patient-header-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
    }

    .patient-header-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05) 0%, rgba(var(--primary-rgb), 0) 100%);
        border-radius: 0 0 0 100%;
        pointer-events: none;
    }

    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
    }

    /* Timeline Styles */
    .timeline-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin: 20px 0;
    }

    .timeline-steps::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e2e8f0;
        z-index: 0;
    }

    .step-item {
        position: relative;
        z-index: 1;
        text-align: center;
        width: 100px;
    }

    .step-circle {
        width: 32px;
        height: 32px;
        background: #fff;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px auto;
        font-weight: 600;
        color: #94a3b8;
        transition: all 0.3s;
    }

    .step-item.active .step-circle {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: #fff;
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.2);
    }

    .step-item.completed .step-circle {
        border-color: #10b981;
        background: #10b981;
        color: #fff;
    }

    .step-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    .step-item.active .step-label {
        color: var(--primary-color);
        font-weight: 700;
    }

    .status-badge-lg {
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .avatar-xl {
        width: 80px;
        height: 80px;
        font-size: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= baseUrl('/appointments') ?>" class="btn btn-light btn-icon rounded-circle shadow-sm">
            <i class="ri-arrow-left-line"></i>
        </a>
        <div>
            <h1 class="page-title fw-bold fs-20 mb-0">Appointment #<span id="aptIdDisplay">--</span></h1>
            <p class="text-muted mb-0 fs-13">View details and manage visit</p>
        </div>
    </div>
    <div class="d-flex gap-2" id="actionButtons">
        <!-- Actions injected via JS -->
    </div>
</div>

<div id="loadingState" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2 text-muted">Loading appointment details...</p>
</div>

<div id="detailsContent" class="d-none">
    <div class="row g-4">
        <!-- Left Column: Patient & Info -->
        <div class="col-lg-4">
            <!-- Patient Card -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 bg-light text-center border-bottom">
                        <div class="mb-3">
                            <div class="avatar-xl bg-primary-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center text-primary fs-24 fw-bold"
                                id="patientAvatar">
                                --
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1" id="patientName">--</h5>
                        <p class="text-muted mb-2" id="patientMRN">MRN: --</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="btn btn-sm btn-white border shadow-sm rounded-pill" id="btnProfile"><i
                                    class="ri-user-line me-1"></i> Profile</a>
                            <a href="#" class="btn btn-sm btn-white border shadow-sm rounded-pill" id="btnHistory"><i
                                    class="ri-history-line me-1"></i> History</a>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-label">Gender</div>
                                <div class="info-value" id="patientGender">--</div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Age</div>
                                <div class="info-value" id="patientAge">--</div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value d-flex align-items-center gap-2">
                                    <i class="ri-phone-fill text-muted"></i> <span id="patientPhone">--</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Email</div>
                                <div class="info-value text-break" id="patientEmail">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Provider Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0">Assigned Provider</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar bg-soft-info text-info rounded-lg">
                            <i class="ri-stethoscope-line fs-20"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" id="providerName">--</div>
                            <div class="text-muted fs-12" id="providerSpec">--</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Appointment Details -->
        <div class="col-lg-8">
            <!-- Status Flow -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Visit Status</h6>
                        <span id="statusBadge" class="status-badge-lg bg-light text-muted">Loading...</span>
                    </div>

                    <div class="timeline-steps">
                        <div class="step-item" id="step_scheduled">
                            <div class="step-circle"><i class="ri-calendar-event-line"></i></div>
                            <div class="step-label">Scheduled</div>
                        </div>
                        <div class="step-item" id="step_checked-in">
                            <div class="step-circle"><i class="ri-login-box-line"></i></div>
                            <div class="step-label">Checked In</div>
                        </div>
                        <div class="step-item" id="step_in-progress">
                            <div class="step-circle"><i class="ri-pulse-line"></i></div>
                            <div class="step-label">In Consult</div>
                        </div>
                        <div class="step-item" id="step_completed">
                            <div class="step-circle"><i class="ri-check-double-line"></i></div>
                            <div class="step-label">Completed</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical/Visit Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div
                    class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Appointment Information</h6>
                    <span class="badge bg-light text-dark border px-3 py-2" id="aptToken">Token: --</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">Date & Time</div>
                            <div class="info-value fs-16"><i class="ri-calendar-line text-primary me-2"></i> <span
                                    id="aptDate">--</span></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Visit Type</div>
                            <div class="info-value" id="aptType">General Consultation</div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">Chief Complaint / Notes</div>
                            <div class="p-3 bg-light rounded border border-dashed mt-2" id="aptNotes">
                                <span class="text-muted fst-italic">No notes provided for this appointment.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Placeholder -->
            <div class="card border-0 shadow-sm bg-primary-transparent border-primary border-opacity-25">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar bg-white text-primary rounded-circle shadow-sm">
                            <i class="ri-bill-line"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-primary-dark">Billing Status</h6>
                            <div class="text-primary-dark opacity-75 fs-13">Invoice not yet generated</div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm shadow-sm">Generate Invoice</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="<?= asset('libs/sweetalert2/sweetalert2.min.js') ?>"></script>
<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    const aptId = <?= json_encode($appointment_id) ?>;

    const initDetails = async () => {
        try {
            // Fetch appointment details
            // NOTE: Using the list endpoint with ID filter since specific endpoint might not be detailed enough or standard
            // Adjust this if you have a specific /show endpoint
            const res = await fetch(`/api/v1/appointments?id=${aptId}`);
            const data = await res.json();

            // If API returns array inside 'data' (list style), grab first. If object, use directly.
            // Based on index.php it returns {data: {appointments: []}}

            let apt = null;
            if (data.success) {
                if (data.data.appointments && data.data.appointments.length > 0) {
                    apt = data.data.appointments.find(a => a.appointment_id == aptId) || data.data.appointments[0];
                } else if (data.data.appointment) {
                    apt = data.data.appointment;
                }
            }

            if (!apt) {
                document.getElementById('loadingState').innerHTML = `
                    <div class="text-danger py-5">
                        <i class="ri-error-warning-line fs-48 mb-3"></i>
                        <h5>Appointment Not Found</h5>
                        <p class="text-muted">Could not retrieve details for ID #${aptId}</p>
                        <a href="<?= baseUrl('/appointments') ?>" class="btn btn-light mt-3">Back to List</a>
                    </div>
                `;
                return;
            }

            renderDetails(apt);

        } catch (e) {
            console.error(e);
            document.getElementById('loadingState').innerHTML = '<p class="text-danger">Error loading data. Please try again.</p>';
        }
    };

    function renderDetails(apt) {
        document.getElementById('loadingState').classList.add('d-none');
        document.getElementById('detailsContent').classList.remove('d-none');

        // Basic Info
        document.getElementById('aptIdDisplay').textContent = apt.appointment_id;

        // Patient
        document.getElementById('patientName').textContent = apt.patient.full_name;
        document.getElementById('patientMRN').textContent = `MRN: ${apt.patient.mrn || 'N/A'}`;
        document.getElementById('patientAvatar').textContent = apt.patient.full_name.charAt(0);
        document.getElementById('patientGender').textContent = apt.patient.gender;
        document.getElementById('patientAge').textContent = apt.patient.age || '--';
        document.getElementById('patientPhone').textContent = apt.patient.mobile || 'Not available';
        document.getElementById('patientEmail').textContent = apt.patient.email || 'Not available';

        document.getElementById('btnProfile').href = `/patients/${apt.patient_id}`;

        // Provider
        document.getElementById('providerName').textContent = apt.provider.name || 'General Provider';
        document.getElementById('providerSpec').textContent = apt.provider.specialization || 'General Practice';

        // Appointment
        document.getElementById('aptDate').textContent = new Date(apt.scheduled_at).toLocaleString();
        document.getElementById('aptToken').textContent = `Token: ${apt.queue?.token_no || '--'}`;
        if (apt.notes) document.getElementById('aptNotes').textContent = apt.notes;

        // Status Logic
        updateStatusUI(apt.status);
        renderActionButtons(apt);
    }

    function updateStatusUI(status) {
        const badge = document.getElementById('statusBadge');
        const config = {
            'scheduled': { class: 'bg-primary-transparent text-primary border-primary', label: 'Scheduled', step: 1 },
            'checked-in': { class: 'bg-warning-transparent text-warning border-warning', label: 'Checked In', step: 2 },
            'in-progress': { class: 'bg-info-transparent text-info border-info', label: 'In Consultation', step: 3 },
            'completed': { class: 'bg-success-transparent text-success border-success', label: 'Completed', step: 4 },
            'cancelled': { class: 'bg-danger-transparent text-danger border-danger', label: 'Cancelled', step: 0 }
        }[status] || { class: 'bg-light text-muted', label: status, step: 0 };

        badge.className = `status-badge-lg border ${config.class}`;
        badge.innerHTML = `<i class="ri-checkbox-circle-line"></i> ${config.label}`;

        // Timeline
        const steps = ['scheduled', 'checked-in', 'in-progress', 'completed'];
        const currentStepIdx = steps.indexOf(status);

        steps.forEach((s, idx) => {
            const el = document.getElementById(`step_${s}`);
            el.classList.remove('active', 'completed');
            if (status === 'cancelled') return; // Reset all if cancelled

            if (idx < currentStepIdx) el.classList.add('completed');
            if (idx === currentStepIdx) el.classList.add('active');
        });
    }

    function renderActionButtons(apt) {
        const container = document.getElementById('actionButtons');
        let html = '';

        if (apt.status === 'scheduled') {
            html += `
                <button class="btn btn-danger-light" onclick="updateAptStatus(${apt.appointment_id}, 'cancelled')">Cancel</button>
                <button class="btn btn-primary" onclick="updateAptStatus(${apt.appointment_id}, 'checked-in')"><i class="ri-login-box-line me-1"></i> Check In</button>
            `;
        } else if (apt.status === 'checked-in') {
            html += `
                <button class="btn btn-success" onclick="updateAptStatus(${apt.appointment_id}, 'completed')"><i class="ri-check-double-line me-1"></i> Complete Visit</button>
            `;
        } else if (apt.status === 'completed') {
            html += `
                <button class="btn btn-white border shadow-sm"><i class="ri-printer-line me-1"></i> Print Summary</button>
            `;
        } else if (apt.status === 'cancelled') {
            html += `<span class="badge bg-danger-transparent text-danger">Cancelled</span>`;
        }

        container.innerHTML = html;
    }

    window.updateAptStatus = async (id, status) => {
        const result = await Swal.fire({
            title: 'Update Status?',
            text: `Change status to ${status}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Update'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch(`/api/v1/appointments/${id}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status })
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Updated', 'Status updated successfully', 'success');
                    initDetails(); // Reload
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Network error', 'error');
            }
        }
    };

    // SPA Hook
    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initDetails);
    } else {
        document.addEventListener('DOMContentLoaded', initDetails);
    }
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>