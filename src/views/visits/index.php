<?php
$pageTitle = "All Visits";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Clinical Visits</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Clinical</a></li>
            <li class="breadcrumb-item active" aria-current="page">All Visits</li>
        </ol>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="btn-list">
            <button class="btn btn-outline-primary btn-wave me-0" onclick="fetchVisits()">
                <i class="ri-refresh-line align-middle me-1"></i> Refresh
            </button>
            <button class="btn btn-primary d-inline-flex align-items-center btn-wave"
                onclick="window.location.href='/registrations/create'">
                <i class="ri-add-line me-1 align-middle"></i>Add New Visit
            </button>
        </div>
    </div>
</div>

<!-- Start:: row-1 (Stats) -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-primary-transparent text-primary">
                        <i class="ri-hospital-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="totalVisits">0</h4>
                        <span class="fs-12 fw-medium text-muted">Total Visits</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-success-transparent text-success">
                        <i class="ri-user-heart-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="activeEncounters">0</h4>
                        <span class="fs-12 fw-medium text-muted">Active Encounters</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-info-transparent text-info">
                        <i class="ri-calendar-event-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="todayVisits">0</h4>
                        <span class="fs-12 fw-medium text-muted">Today's Visits</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-danger-transparent text-danger">
                        <i class="ri-pulse-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <h4 class="fw-semibold mb-0" id="emergencyCount">0</h4>
                        <span class="fs-12 fw-medium text-muted">Emergency (ER)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start:: row-2 (Filters & Table) -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        <input type="text" class="form-control" id="visitSearch"
                            placeholder="Search Patient, MRN, Doctor..." onkeyup="debounce(fetchVisits)">
                        <span class="input-group-text bg-light text-muted border-start-0"><i
                                class="ri-search-line"></i></span>
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 250px;">
                        <span class="input-group-text bg-light text-muted border-end-0"><i
                                class="ri-calendar-line"></i></span>
                        <input type="text" class="form-control border-start-0" id="dateFilter"
                            placeholder="Select Date Range">
                    </div>
                    <select class="form-select form-select-sm border-0 bg-light" id="statusFilter" style="width: 140px;"
                        onchange="fetchVisits()">
                        <option value="">All Status</option>
                        <option value="open">Active / Open</option>
                        <option value="closed">Closed / Discharged</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="btn-group btn-group-sm rounded-pill overflow-hidden border">
                    <button type="button" class="btn btn-primary" onclick="setTypeFilter('', this)">All</button>
                    <button type="button" class="btn btn-light" onclick="setTypeFilter('OP', this)">OP</button>
                    <button type="button" class="btn btn-light" onclick="setTypeFilter('IP', this)">IP</button>
                    <button type="button" class="btn btn-light" onclick="setTypeFilter('ER', this)">ER</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" class="ps-4 fs-11 text-muted text-uppercase">Visit Details</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Patient Information</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Attending Provider</th>
                                <th scope="col" class="fs-11 text-muted text-uppercase">Duration / Timing</th>
                                <th scope="col" class="text-center fs-11 text-muted text-uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody id="visitList">
                            <!-- Loading State -->
                            <tr id="loadingState">
                                <td colspan="5" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Fetching visit data...</p>
                                </td>
                            </tr>
                            <!-- Empty State -->
                            <tr id="emptyState" class="d-none">
                                <td colspan="5" class="text-center py-5">
                                    <div class="avatar avatar-xl bg-light text-muted mb-3">
                                        <i class="ri-folder-open-line fs-32"></i>
                                    </div>
                                    <h5 class="fw-semibold">No visits found</h5>
                                    <p class="text-muted">Adjust your filters or try a different search term.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex align-items-center justify-content-between p-3 border-top-0">
                <span class="fs-12 text-muted" id="paginationInfo">Showing 0 visits</span>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Visit Details Modal -->
<div class="modal fade" id="visitDetailsModal" tabindex="-1" aria-labelledby="visitDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="visitDetailsModalLabel">Visit Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <span id="modalPatientName">-</span>
                                <a href="#" id="modalPatientLink" class="ms-1 text-primary fs-16"
                                    title="View Patient Profile">
                                    <i class="ri-external-link-line"></i>
                                </a>
                            </h4>
                            <p class="mb-0 text-muted">
                                <span class="me-3"><strong>MRN:</strong> <span id="modalMRN">-</span></span>
                                <span><strong>Date:</strong> <span id="modalVisitDate">-</span></span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-success" onclick="printCaseSheet()">
                                <i class="ri-printer-line me-1"></i> Print Case Sheet
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-3">
                    <ul class="nav nav-tabs nav-tabs-header mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-vitals" role="tab">
                                <i class="ri-pulse-line me-1"></i> Vital Signs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-notes" role="tab">
                                <i class="ri-file-text-line me-1"></i> Clinical Notes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-prescriptions" role="tab">
                                <i class="ri-medicine-bottle-line me-1"></i> Prescriptions
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane active" id="tab-vitals" role="tabpanel">
                            <div id="vitalsContent">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2">Loading vitals...</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-notes" role="tabpanel">
                            <div id="notesContent">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2">Loading notes...</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-prescriptions" role="tabpanel">
                            <div id="prescriptionsContent">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2">Loading prescriptions...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    let currentType = '';
    let visitTimer = null;
    let selectedVisitId = null;

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize date range picker
        flatpickr("#dateFilter", {
            mode: "range",
            dateFormat: "Y-m-d",
            onClose: (selectedDates, dateStr) => {
                if (selectedDates.length === 2 || dateStr === '') {
                    fetchVisits();
                }
            }
        });

        fetchStats();
        fetchVisits();
    });

    function debounce(func, timeout = 300) {
        clearTimeout(visitTimer);
        visitTimer = setTimeout(() => { func(); }, timeout);
    }

    async function fetchStats() {
        try {
            const res = await fetch('/api/v1/visits/stats');
            const data = await res.json();
            if (data.success) {
                document.getElementById('totalVisits').innerText = data.data.total_visits || 0;
                document.getElementById('activeEncounters').innerText = data.data.active_encounters || 0;
                document.getElementById('todayVisits').innerText = data.data.today_visits || 0;
                document.getElementById('emergencyCount').innerText = data.data.emergency || 0;
            }
        } catch (e) {
            console.error('Stats fetch failed:', e);
        }
    }

    async function fetchVisits() {
        const list = document.getElementById('visitList');
        const loading = document.getElementById('loadingState');
        const empty = document.getElementById('emptyState');

        const rows = list.querySelectorAll('tr:not(#loadingState):not(#emptyState)');
        rows.forEach(r => r.remove());

        loading.classList.remove('d-none');
        empty.classList.add('d-none');

        const search = document.getElementById('visitSearch').value;
        const status = document.getElementById('statusFilter').value;
        const dateRange = document.getElementById('dateFilter').value;

        const url = `/api/v1/visits?search=${encodeURIComponent(search)}&status=${status}&type=${currentType}&date_range=${encodeURIComponent(dateRange)}`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            loading.classList.add('d-none');

            if (data.success && data.data.visits && data.data.visits.length > 0) {
                renderVisits(data.data.visits);
                document.getElementById('paginationInfo').innerText = `Showing ${data.data.visits.length} clinical visits`;
            } else {
                empty.classList.remove('d-none');
                document.getElementById('paginationInfo').innerText = `Showing 0 clinical visits`;
            }
        } catch (e) {
            loading.classList.add('d-none');
            console.error('Visits fetch failed:', e);
        }
    }

    function renderVisits(visits) {
        const list = document.getElementById('visitList');
        visits.forEach(v => {
            const startTime = new Date(v.visit_start);
            const timeStr = startTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const dateStr = startTime.toLocaleDateString([], { day: '2-digit', month: 'short', year: 'numeric' });

            let statusBadge = '';
            if (v.visit_status === 'open') statusBadge = '<span class="badge bg-success-transparent">Active</span>';
            else if (v.visit_status === 'closed') statusBadge = '<span class="badge bg-light text-muted">Closed</span>';
            else statusBadge = '<span class="badge bg-danger-transparent">Cancelled</span>';

            let typeBadge = '';
            if (v.visit_type === 'OP') typeBadge = '<span class="badge bg-primary-transparent">OP</span>';
            else if (v.visit_type === 'IP') typeBadge = '<span class="badge bg-secondary-transparent">IP</span>';
            else typeBadge = '<span class="badge bg-danger-transparent">ER</span>';

            const row = document.createElement('tr');
            row.style.cursor = 'pointer';
            row.onclick = () => openVisitModal(v);
            row.innerHTML = `
            <td class="ps-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm avatar-rounded bg-light text-muted">
                        ${typeBadge}
                    </div>
                    <div>
                        <span class="d-block fw-semibold">VISIT-${String(v.visit_id).padStart(5, '0')}</span>
                        <span class="text-muted fs-11">${dateStr} at ${timeStr}</span>
                    </div>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm avatar-rounded bg-primary-transparent text-primary fw-medium">
                        ${v.first_name.charAt(0)}
                    </div>
                    <div>
                        <span class="d-block fw-medium">${v.first_name} ${v.last_name || ''}</span>
                        <span class="text-muted fs-11">MRN: ${v.mrn}</span>
                    </div>
                </div>
            </td>
            <td>
                <span class="d-block fw-medium">${v.provider_name || 'Not Assigned'}</span>
                <span class="text-muted fs-11">${v.specialization || 'Clinical'}</span>
            </td>
            <td>
                <span class="d-block fs-12 fw-medium"><i class="ri-history-line me-1 text-muted"></i>Started: ${timeStr}</span>
                <span class="text-muted fs-11">Duration: ${v.visit_end ? 'Completed' : 'Ongoing'}</span>
            </td>
            <td class="text-center">${statusBadge}</td>
        `;
            list.appendChild(row);
        });
    }

    async function openVisitModal(visit) {
        selectedVisitId = visit.visit_id;
        document.getElementById('modalPatientName').innerText = `${visit.first_name} ${visit.last_name || ''}`;
        document.getElementById('modalPatientLink').href = `/patients/${visit.patient_id}`;
        document.getElementById('modalMRN').innerText = visit.mrn;
        document.getElementById('modalVisitDate').innerText = new Date(visit.visit_start).toLocaleString();

        const modal = new bootstrap.Modal(document.getElementById('visitDetailsModal'));
        modal.show();

        // Reset content to loading state
        ['vitals', 'notes', 'prescriptions'].forEach(tab => {
            document.getElementById(`${tab}Content`).innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading ${tab}...</p>
                </div>
            `;
        });

        // Fetch Data
        fetchVitals(visit.visit_id);
        fetchNotes(visit.visit_id);
        fetchPrescriptions(visit.visit_id);
    }

    async function fetchVitals(visitId) {
        try {
            const res = await fetch(`/api/v1/visits/${visitId}/vitals`);
            const data = await res.json();
            const container = document.getElementById('vitalsContent');

            if (data.success && data.data.vitals && data.data.vitals.length > 0) {
                const v = data.data.vitals[0];
                container.innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light-info text-center">
                                <span class="d-block text-muted fs-11 fw-bold text-uppercase mb-1">Blood Pressure</span>
                                <h5 class="mb-0 text-primary">${v.bp_systolic || '-'}/${v.bp_diastolic || '-'} <small class="fs-12">mmHg</small></h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light-danger text-center">
                                <span class="d-block text-muted fs-11 fw-bold text-uppercase mb-1">Pulse Rate</span>
                                <h5 class="mb-0 text-danger">${v.pulse_per_min || '-'} <small class="fs-12">bpm</small></h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light-warning text-center">
                                <span class="d-block text-muted fs-11 fw-bold text-uppercase mb-1">Temperature</span>
                                <h5 class="mb-0 text-warning">${v.temperature_c || '-'} <small class="fs-12">°C</small></h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light-success text-center">
                                <span class="d-block text-muted fs-11 fw-bold text-uppercase mb-1">SPO2</span>
                                <h5 class="mb-0 text-success">${v.spo2 || '-'} <small class="fs-12">%</small></h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <span class="d-block text-muted fs-11 fw-bold text-uppercase mb-1">Weight</span>
                                <h5 class="mb-0">${v.weight_kg || '-'} <small class="fs-12">kg</small></h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <span class="d-block text-muted fs-11 fw-bold text-uppercase mb-1">Height</span>
                                <h5 class="mb-0">${v.height_cm || '-'} <small class="fs-12">cm</small></h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <span class="d-block text-muted fs-11 fw-bold text-uppercase mb-1">Respiratory Rate</span>
                                <h5 class="mb-0">${v.respiratory_rate || '-'} <small class="fs-12">/min</small></h5>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = '<div class="alert alert-light text-center border-dashed">No vitals recorded</div>';
            }
        } catch (e) {
            console.error(e);
            document.getElementById('vitalsContent').innerHTML = '<div class="alert alert-danger">Error loading vitals</div>';
        }
    }

    async function fetchNotes(visitId) {
        try {
            const res = await fetch(`/api/v1/visits/${visitId}/clinical-notes`);
            const data = await res.json();
            const container = document.getElementById('notesContent');

            if (data.success) {
                let html = '';

                // Show Siddha Notes if they exist
                if (data.data.siddha) {
                    const s = data.data.siddha;
                    html += `
                        <div class="card mb-3 border border-primary shadow-none bg-primary-transparent">
                            <div class="card-header py-2">
                                <span class="fw-bold text-primary"><i class="ri-pulse-line me-1"></i> Siddha Clinical Assessment</span>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-3">
                                    ${s.pulse_diagnosis ? `<div class="col-md-6"><small class="text-muted d-block text-uppercase" style="font-size: 10px;">Nadi / Pulse</small><strong>${s.pulse_diagnosis}</strong></div>` : ''}
                                    ${s.tongue ? `<div class="col-md-6"><small class="text-muted d-block text-uppercase" style="font-size: 10px;">Tongue</small><strong>${s.tongue}</strong></div>` : ''}
                                    ${s.prakriti ? `<div class="col-md-6"><small class="text-muted d-block text-uppercase" style="font-size: 10px;">Prakriti</small><strong>${s.prakriti}</strong></div>` : ''}
                                    ${s.anupanam ? `<div class="col-md-6"><small class="text-muted d-block text-uppercase" style="font-size: 10px;">Anupanam</small><strong>${s.anupanam}</strong></div>` : ''}
                                    ${s.note_text ? `<div class="col-12 mt-2"><small class="text-muted d-block border-top pt-2 text-uppercase" style="font-size: 10px;">Clinical Observation</small><p class="mb-0 fs-13">${s.note_text}</p></div>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Show General Clinical Notes
                if (data.data.notes && data.data.notes.length > 0) {
                    html += data.data.notes.map(note => `
                        <div class="card mb-3 border shadow-none">
                            <div class="card-header d-flex justify-content-between py-2 bg-light">
                                <span class="badge bg-primary-transparent text-uppercase fs-10">${note.note_type}</span>
                                <small class="text-muted">${new Date(note.created_at).toLocaleString()}</small>
                            </div>
                            <div class="card-body py-2">
                                <p class="mb-0">${note.note_text}</p>
                            </div>
                        </div>
                    `).join('');
                }

                if (!html) {
                    container.innerHTML = '<div class="alert alert-light text-center border-dashed">No clinical notes recorded</div>';
                } else {
                    container.innerHTML = html;
                }
            } else {
                container.innerHTML = '<div class="alert alert-light text-center border-dashed">No clinical notes recorded</div>';
            }
        } catch (e) {
            console.error(e);
            document.getElementById('notesContent').innerHTML = '<div class="alert alert-danger">Error loading notes</div>';
        }
    }

    async function fetchPrescriptions(visitId) {
        try {
            const res = await fetch(`/api/v1/visits/${visitId}/prescriptions`);
            const data = await res.json();
            const container = document.getElementById('prescriptionsContent');

            if (data.success && data.data.prescriptions && data.data.prescriptions.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle fs-13"><thead><tr class="bg-light"><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th></tr></thead><tbody>';
                data.data.prescriptions.forEach(rx => {
                    rx.items.forEach(item => {
                        html += `<tr>
                            <td><strong>${item.product_name}</strong></td>
                            <td>${item.dosage || '-'}</td>
                            <td>${item.frequency || '-'}</td>
                            <td>${item.duration_days ? item.duration_days + ' days' : '-'}</td>
                        </tr>`;
                    });
                });
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-light text-center border-dashed">No prescriptions recorded</div>';
            }
        } catch (e) {
            console.error(e);
            document.getElementById('prescriptionsContent').innerHTML = '<div class="alert alert-danger">Error loading prescriptions</div>';
        }
    }

    function printCaseSheet() {
        if (!selectedVisitId) return;
        window.open(`/print/case-sheet?visit_id=${selectedVisitId}`, '_blank');
    }

    function viewFullCaseSheet(visitId) {
        window.open(`/visits/case-sheet?visit_id=${visitId}`, '_blank');
    }

    function closeVisit(visitId) {
        if (!confirm('Are you sure you want to close this visit? This action cannot be undone.')) {
            return;
        }

        fetch(`/api/v1/visits/${visitId}/close`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof modalNotify !== 'undefined') {
                        modalNotify.success('Visit closed successfully');
                    } else {
                        alert('Visit closed successfully');
                    }
                    fetchVisits();
                } else {
                    alert(data.message || 'Failed to close visit');
                }
            })
            .catch(e => {
                console.error('Error closing visit:', e);
                alert('Error closing visit');
            });
    }

    function setTypeFilter(type, btn) {
        currentType = type;
        document.querySelectorAll('.btn-group .btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-light');
        });
        btn.classList.remove('btn-light');
        btn.classList.add('btn-primary');
        fetchVisits();
    }
</script>

<style>
    .bg-light-info {
        background: #e0f4ff;
    }

    .bg-light-danger {
        background: #ffebeb;
    }

    .bg-light-warning {
        background: #fff8e6;
    }

    .bg-light-success {
        background: #e6ffed;
    }

    .border-dashed {
        border-style: dashed !important;
        border-width: 2px !important;
    }
</style>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>