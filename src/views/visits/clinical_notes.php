<?php
$pageTitle = "Clinical Notes";
$visitId = $_GET['visit_id'] ?? null;
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Clinical Documentation</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/visits">Clinical Visits</a></li>
            <li class="breadcrumb-item active" aria-current="page">Notes</li>
        </ol>
    </div>
    <div class="btn-list">
        <a href="/visits" class="btn btn-light btn-wave">
            <i class="ri-arrow-left-line me-1"></i>Back to Visits
        </a>
    </div>
</div>

<?php if ($visitId): ?>
    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card custom-card">
                <div class="card-header bg-primary-transparent border-primary">
                    <div class="card-title d-flex align-items-center">
                        <i class="ri-user-follow-line me-2"></i>
                        <span id="activePatientName">Patient Information</span>
                    </div>
                </div>
                <div class="card-body" id="patientInfo">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Add New Progress Note</h6>
                        <form id="noteForm" onsubmit="saveNote(event)">
                            <div class="mb-2">
                                <select class="form-select form-select-sm" id="note_type" style="width: 200px;">
                                    <option value="progress">Progress Note</option>
                                    <option value="admission">Admission Note</option>
                                    <option value="procedure">Procedure Note</option>
                                    <option value="discharge">Discharge Summary</option>
                                    <option value="general">General Note</option>
                                </select>
                            </div>
                            <textarea class="form-control mb-2" id="note_text" rows="5"
                                placeholder="Enter clinical observations, assessment or plan..."></textarea>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-wave">Save Note</button>
                            </div>
                        </form>
                    </div>

                    <hr>

                    <div class="mt-4">
                        <h6>Note History</h6>
                        <div id="historyList" class="timeline-container mt-3">
                            <!-- Loaded via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card custom-card">
                <div class="card-header border-bottom-0 pb-0">
                    <div class="card-title">Recent Encounters</div>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="patientSearch" placeholder="Search Patient..."
                            onkeyup="fetchEncounters()">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                    </div>
                    <div id="encounterList" class="list-group list-group-flush custom-list">
                        <!-- Loaded via JS -->
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div id="notesPlaceholder"
                class="card custom-card h-100 d-flex align-items-center justify-content-center text-center p-5">
                <div class="avatar avatar-xxl bg-light text-muted mb-4">
                    <i class="ri-file-edit-line fs-48"></i>
                </div>
                <h4>Select an Encounter</h4>
                <p class="text-muted">Select a patient visit from the left panel to view or add clinical notes.</p>
            </div>

            <div id="notesContainer" class="d-none">
                <div class="card custom-card">
                    <div class="card-header justify-content-between bg-primary-transparent border-primary">
                        <div class="card-title d-flex align-items-center">
                            <i class="ri-user-follow-line me-2"></i>
                            <span id="activePatientName">Patient Name</span>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-warning text-dark"
                                onclick="window.location.href='/billing/invoice-create?type=estimate'">
                                <i class="ri-file-list-3-line me-1"></i> Estimate
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6>Add New Progress Note</h6>
                            <form id="noteForm" onsubmit="saveNote(event)">
                                <div class="mb-2">
                                    <select class="form-select form-select-sm" id="note_type" style="width: 200px;">
                                        <option value="progress">Progress Note</option>
                                        <option value="admission">Admission Note</option>
                                        <option value="procedure">Procedure Note</option>
                                        <option value="discharge">Discharge Summary</option>
                                        <option value="general">General Note</option>
                                    </select>
                                </div>
                                <textarea class="form-control mb-2" id="note_text" rows="5"
                                    placeholder="Enter clinical observations, assessment or plan..."></textarea>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-wave">Save Note</button>
                                </div>
                            </form>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <h6>Note History</h6>
                            <div id="historyList" class="timeline-container mt-3">
                                <!-- Loaded via JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    .custom-list .list-group-item {
        cursor: pointer;
        border-radius: 8px !important;
        margin-bottom: 5px;
        border: 1px solid transparent;
        transition: all 0.2s;
    }

    .custom-list .list-group-item:hover {
        background-color: rgba(var(--primary-rgb), 0.05);
    }

    .custom-list .list-group-item.active {
        background-color: var(--primary-color) !important;
        color: white;
        border-color: var(--primary-color);
    }

    .custom-list .list-group-item.active .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .timeline-item {
        position: relative;
        padding-left: 20px;
        border-left: 2px solid #eee;
        padding-bottom: 20px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -7px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary-color);
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    const visitId = <?= $visitId ? (int) $visitId : 'null' ?>;
    let selectedVisitId = visitId;

    document.addEventListener('DOMContentLoaded', () => {
        if (visitId) {
            loadPatientInfoAndNotes();
        } else {
            fetchEncounters();
        }
    });

    async function loadPatientInfoAndNotes() {
        try {
            const res = await fetch(`/api/v1/visits?search=&status=`);
            const data = await res.json();

            if (data.success) {
                const visit = data.data.visits.find(v => v.visit_id == visitId);
                if (visit) {
                    const age = calculateAge(visit.dob);
                    const patientName = `${visit.first_name} ${visit.last_name || ''}`;

                    document.getElementById('activePatientName').innerText = patientName;
                    document.getElementById('patientInfo').innerHTML = `
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar avatar-lg avatar-rounded bg-primary text-white fw-bold">
                                ${visit.first_name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">${patientName}</h5>
                                <span class="text-muted fs-12">MRN: ${visit.mrn}</span>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <div class="fs-11 text-muted">Age/Gender</div>
                                    <div class="fw-medium">${age} / ${visit.gender || '-'}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <div class="fs-11 text-muted">Visit Type</div>
                                    <div class="fw-medium">${visit.visit_type}</div>
                                </div>
                            </div>
                        </div>
                    `;

                    fetchNotesHistory(visitId);
                }
            }
        } catch (e) {
            console.error(e);
        }
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

    async function fetchEncounters() {
        const list = document.getElementById('encounterList');
        const search = document.getElementById('patientSearch').value;

        try {
            const res = await fetch(`/api/v1/visits?search=${encodeURIComponent(search)}&status=open`);
            const data = await res.json();
            list.innerHTML = '';

            if (data.success && data.data.visits.length > 0) {
                data.data.visits.forEach(v => {
                    const item = document.createElement('a');
                    item.className = 'list-group-item';
                    item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-medium">${v.first_name} ${v.last_name || ''}</div>
                        <span class="badge bg-light text-dark fs-10">${v.visit_type}</span>
                    </div>
                    <div class="text-muted fs-11 mt-1">Visit ID: #${v.visit_id} | MRN: ${v.mrn}</div>
                `;
                    item.onclick = () => selectVisit(v.visit_id, `${v.first_name} ${v.last_name || ''}`, item);
                    list.appendChild(item);
                });
            }
        } catch (e) { console.error(e); }
    }

    function selectVisit(visitId, name, el) {
        selectedVisitId = visitId;
        document.querySelectorAll('.custom-list .list-group-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        document.getElementById('notesPlaceholder').classList.add('d-none');
        document.getElementById('notesContainer').classList.remove('d-none');
        document.getElementById('activePatientName').innerText = name;

        fetchNotesHistory(visitId);
    }

    async function fetchNotesHistory(visitId) {
        const history = document.getElementById('historyList');
        history.innerHTML = '<div class="text-center p-3 text-muted">Loading history...</div>';

        try {
            const res = await fetch(`/api/v1/visits/${visitId}/clinical-notes`);
            const data = await res.json();
            history.innerHTML = '';

            if (data.success && data.data.notes.length > 0) {
                data.data.notes.forEach(n => {
                    const date = new Date(n.created_at).toLocaleString();
                    const item = document.createElement('div');
                    item.className = 'timeline-item';
                    item.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-primary-transparent mb-2">${n.note_type.toUpperCase()}</span>
                        <span class="fs-11 text-muted">${date}</span>
                    </div>
                    <div class="card p-3 shadow-none border mb-0 bg-light-transparent">
                        <p class="mb-0 fs-13">${n.note_text}</p>
                    </div>
                `;
                    history.appendChild(item);
                });
            } else {
                history.innerHTML = '<div class="text-center p-3 text-muted">No notes recorded yet.</div>';
            }
        } catch (e) { console.error(e); }
    }

    async function saveNote(e) {
        e.preventDefault();
        if (!selectedVisitId) return;

        const noteText = document.getElementById('note_text').value;
        if (!noteText.trim()) return;

        const formData = {
            visit_id: selectedVisitId,
            note_type: document.getElementById('note_type').value,
            note_text: noteText
        };

        try {
            const res = await fetch('/api/v1/visits/clinical-notes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('noteForm').reset();
                fetchNotesHistory(selectedVisitId);
                if (typeof Toastify !== 'undefined') {
                    Toastify({ text: "Note recorded successfully", bg: "primary" }).showToast();
                }
            }
        } catch (e) { console.error(e); }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>