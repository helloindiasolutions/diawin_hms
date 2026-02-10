<?php
/**
 * Therapy Sessions Management
 * Tracking Siddha/Ayurvedic Treatment Sessions
 */
$pageTitle = "Therapy Sessions";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Therapy Sessions</h2>
        <span class="text-muted fs-12">Monitor and manage ongoing patient treatment sessions (Kizhi, Varmam,
            etc.)</span>
    </div>
    <div class="btn-list">
        <a href="<?= baseUrl('/therapy/booking') ?>" class="btn btn-primary btn-wave">
            <i class="ri-add-line align-middle me-1"></i> Book New Session
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header border-bottom justify-content-between">
                <div class="card-title">Active & Scheduled Sessions</div>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="sessionSearch"
                        placeholder="Search Patient/MRN..." style="width: 200px;">
                    <select class="form-select form-select-sm" id="statusFilter" style="width: 150px;">
                        <option value="all">All Status</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="no-show">No Show</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100 mb-0">
                        <thead class="bg-light">
                            <tr class="fs-11 text-uppercase">
                                <th>Patient Info</th>
                                <th>Protocol / Therapy</th>
                                <th>Schedule</th>
                                <th>Practitioner</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="sessionsTableBody" class="fs-13">
                            <tr>
                                <td colspan="7" class="text-center p-5">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="ms-2">Loading therapy sessions...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadSessions() {
        const branchId = window.currentBranchId;
        const tbody = document.getElementById('sessionsTableBody');

        try {
            const res = await fetch(`/api/v1/therapy/sessions?branch_id=${branchId}`);
            const data = await res.json();

            if (data.success) {
                renderSessions(data.data.sessions);
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load sessions</td></tr>';
            }
        } catch (e) {
            console.error('Error fetching sessions:', e);
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Connection error</td></tr>';
        }
    }

    function renderSessions(sessions) {
        const tbody = document.getElementById('sessionsTableBody');
        tbody.innerHTML = '';

        if (!sessions || sessions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center p-4 text-muted">No therapy sessions found for this branch.</td></tr>';
            return;
        }

        sessions.forEach(s => {
            let statusBadge = '';
            switch (s.status) {
                case 'scheduled': statusBadge = '<span class="badge bg-primary-transparent">Scheduled</span>'; break;
                case 'completed': statusBadge = '<span class="badge bg-success-transparent">Completed</span>'; break;
                case 'no-show': statusBadge = '<span class="badge bg-danger-transparent">No Show</span>'; break;
                case 'cancelled': statusBadge = '<span class="badge bg-light text-muted">Cancelled</span>'; break;
            }

            const therapyName = s.protocol_name || 'General Therapy';
            const patientName = `${s.first_name} ${s.last_name || ''}`;

            const row = `
                <tr class="align-middle">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-primary-transparent text-primary rounded-circle me-2">
                                ${s.first_name.charAt(0)}
                            </div>
                            <div>
                                <div class="fw-semibold">${patientName}</div>
                                <div class="text-muted fs-11">${s.mrn}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold text-primary">${therapyName}</div>
                        <div class="text-muted fs-11">#${s.session_id}</div>
                    </td>
                    <td>
                        <div class="fw-medium">${formatDate(s.scheduled_on)}</div>
                        <div class="text-muted fs-11">${s.scheduled_time || 'N/A'}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="ri-user-follow-line text-muted me-2"></i>
                            <span>${s.practitioner_name || 'Not Assigned'}</span>
                        </div>
                    </td>
                    <td><span class="badge bg-outline-light text-dark fw-normal">Branch #${s.branch_id}</span></td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <div class="btn-list justify-content-center">
                            <button class="btn btn-sm btn-icon btn-info-light btn-wave" title="View Details" onclick="viewSession(${s.session_id})">
                                <i class="ri-eye-line"></i>
                            </button>
                            ${s.status === 'scheduled' ? `
                            <button class="btn btn-sm btn-icon btn-success-light btn-wave" title="Start/Complete" onclick="completeSession(${s.session_id})">
                                <i class="ri-checkbox-circle-line"></i>
                            </button>` : ''}
                        </div>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function viewSession(id) {
        showToast('Viewing session details #' + id, 'info');
    }

    async function completeSession(id) {
        const { isConfirmed } = await Swal.fire({
            title: 'Complete Session?',
            text: "Mark this therapy session as completed.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, complete'
        });

        if (isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('session_id', id);
                formData.append('status', 'completed');

                const res = await fetch('/api/v1/therapy/sessions/status', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Session marked as completed', 'success');
                    loadSessions();
                } else {
                    showToast(data.message || 'Error updating session', 'error');
                }
            } catch (err) { console.error(err); }
        }
    }

    // Initialize
    if (window.pageInit) {
        window.pageInit.add('therapy-sessions', loadSessions);
    } else {
        document.addEventListener('DOMContentLoaded', loadSessions);
    }
</script>

<style>
    #sessionsTableBody td {
        padding: 12px 16px;
    }
</style>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>