<?php
$pageTitle = "Nursing Notes";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Nursing Documentation</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">In-Patient</a></li>
            <li class="breadcrumb-item active" aria-current="page">Nursing Notes</li>
        </ol>
    </div>
    <div class="mt-2 mt-md-0">
        <button class="btn btn-light border shadow-sm" onclick="window.history.back()">
            <i class="ri-arrow-left-line me-1"></i> Back
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-lg-5">
        <div class="card custom-card">
            <div class="card-header border-bottom-0">
                <div class="card-title">Current In-Patients</div>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="patientSearch" placeholder="Search Admitted Patient..."
                        onkeyup="fetchInPatients()">
                </div>
                <div id="patientList" class="list-group list-group-flush">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div id="nursePlaceholder"
            class="card custom-card h-100 d-flex align-items-center justify-content-center text-center p-5">
            <div class="avatar avatar-xxl bg-light text-muted mb-4">
                <i class="ri-nurse-line fs-48"></i>
            </div>
            <h4>Select a Patient</h4>
            <p class="text-muted">Select an admitted patient to record nursing observations or check clinical history.
            </p>
        </div>

        <div id="nurseNotesView" class="d-none">
            <div class="card custom-card">
                <div class="card-header justify-content-between bg-info-transparent">
                    <div class="card-title" id="activePatientName">Patient Name</div>
                    <span class="badge bg-info" id="activeWardRoom">Ward/Room</span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>New Nursing Note</h6>
                        <textarea class="form-control mb-2" id="newNoteText" rows="4"
                            placeholder="Enter observations, vitals check, or medication administered..."></textarea>
                        <div class="text-end">
                            <button class="btn btn-info text-white" onclick="saveNote()">Submit Note</button>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-4">
                        <h6>Nursing Activity Feed</h6>
                        <div id="activityFeed" class="p-3">
                            <!-- Timeline items will be here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    let selectedAdmissionId = null;

    document.addEventListener('DOMContentLoaded', fetchInPatients);

    // SPA Support: Run immediately if already loaded
    if (document.readyState !== 'loading') {
        fetchInPatients();
    }

    async function fetchInPatients() {
        const list = document.getElementById('patientList');
        try {
            const res = await fetch('/api/v1/ipd/admissions?status=Active');
            const data = await res.json();
            list.innerHTML = '';

            const urlParams = new URLSearchParams(window.location.search);
            const preSelectId = urlParams.get('admission_id');

            if (data.success && data.data.admissions.length > 0) {
                data.data.admissions.forEach(a => {
                    const item = document.createElement('a');
                    item.className = 'list-group-item list-group-item-action border-0 px-0';
                    item.id = `patient-item-${a.admission_id}`;
                    item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-info-transparent text-info rounded-circle me-3">${a.first_name[0]}</div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">${a.first_name} ${a.last_name || ''}</div>
                            <div class="text-muted fs-11">${a.ward_name || 'N/A'} | ${a.bed_number || 'No Bed'}</div>
                        </div>
                    </div>
                `;
                    item.onclick = () => selectPatient(a, item);
                    list.appendChild(item);

                    // Auto-select if matches URL param
                    if (preSelectId && a.admission_id == preSelectId) {
                        selectPatient(a, item);
                    }
                });
            } else {
                list.innerHTML = '<div class="text-center py-4 text-muted">No active admissions</div>';
            }
        } catch (e) {
            console.error(e);
            list.innerHTML = '<div class="text-center py-4 text-danger">Failed to load patients</div>';
        }
    }

    function selectPatient(a, el) {
        selectedAdmissionId = a.admission_id;
        document.querySelectorAll('#patientList .list-group-item').forEach(i => i.classList.remove('active', 'bg-info-transparent'));
        el.classList.add('bg-info-transparent');

        document.getElementById('nursePlaceholder').classList.add('d-none');
        document.getElementById('nurseNotesView').classList.remove('d-none');
        document.getElementById('activePatientName').innerText = `${a.first_name} ${a.last_name || ''}`;
        document.getElementById('activeWardRoom').innerText = `${a.ward_name || 'N/A'} / ${a.bed_number || 'No Bed'}`;

        fetchActivityHistory(a.admission_id);
    }

    async function fetchActivityHistory(id) {
        const feed = document.getElementById('activityFeed');
        feed.innerHTML = '<div class="text-center text-muted"><div class="spinner-border spinner-border-sm"></div></div>';

        try {
            const res = await fetch(`/api/v1/ipd/admissions/${id}/nursing-notes`);
            const data = await res.json();
            feed.innerHTML = '';

            if (data.success && data.data.notes.length > 0) {
                data.data.notes.forEach(n => {
                    const date = new Date(n.note_date).toLocaleString();
                    const nurseName = n.nurse_name || 'Nurse';
                    const item = document.createElement('div');
                    item.className = 'border-start border-2 border-info ps-3 pb-4 ms-2 position-relative';
                    item.innerHTML = `
                    <div class="position-absolute bg-info" style="width:10px; height:10px; border-radius:50%; left:-6px; top:5px;"></div>
                    <div class="fs-12 text-muted mb-1">${date} - ${nurseName}</div>
                    <p class="mb-0 fs-13">${n.observations || 'No observations recorded'}</p>
                    ${n.vital_signs ? `<div class="fs-11 text-muted mt-1">Vitals: ${n.vital_signs}</div>` : ''}
                `;
                    feed.appendChild(item);
                });
            } else {
                feed.innerHTML = '<div class="text-center text-muted py-3">No nursing notes recorded for this admission.</div>';
            }
        } catch (e) {
            console.error(e);
            feed.innerHTML = '<div class="text-center text-danger py-3">Failed to load nursing notes.</div>';
        }
    }

    async function saveNote() {
        const noteText = document.getElementById('newNoteText').value.trim();
        if (!noteText) {
            alert('Please enter nursing observations');
            return;
        }

        if (!selectedAdmissionId) {
            alert('No patient selected');
            return;
        }

        try {
            const res = await fetch('/api/v1/ipd/nursing-notes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    admission_id: selectedAdmissionId,
                    patient_id: selectedAdmissionId, // Will be fetched from admission
                    observations: noteText,
                    shift: new Date().getHours() < 12 ? 'Morning' : (new Date().getHours() < 18 ? 'Afternoon' : 'Night')
                })
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('newNoteText').value = '';
                fetchActivityHistory(selectedAdmissionId);
                alert('Nursing note saved successfully');
            } else {
                alert('Failed to save note: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error(e);
            alert('Failed to save nursing note');
        }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>