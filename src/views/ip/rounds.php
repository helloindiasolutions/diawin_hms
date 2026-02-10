<?php
$pageTitle = "Doctor Rounds";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Doctor Rounds (IP)</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">In-Patient</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rounds</li>
        </ol>
    </div>
    <div class="mt-2 mt-md-0">
        <button class="btn btn-light border shadow-sm" onclick="window.history.back()">
            <i class="ri-arrow-left-line me-1"></i> Back
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header border-bottom-0 pb-0">
                <div class="card-title">Ward Occupancy</div>
            </div>
            <div class="card-body">
                <div id="roundsPatientList" class="list-group list-group-flush">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div id="roundsPlaceholder"
            class="card custom-card h-100 d-flex align-items-center justify-content-center text-center p-5">
            <div class="avatar avatar-xxl bg-light text-muted mb-4">
                <i class="ri-stethoscope-line fs-48"></i>
            </div>
            <h4>Clinic rounds Workspace</h4>
            <p class="text-muted">Select a patient to enter daily clinical summaries or plan of care.</p>
        </div>

        <div id="roundsWorkspace" class="d-none">
            <div class="card custom-card">
                <div class="card-header justify-content-between bg-primary-transparent border-primary">
                    <div class="card-title" id="r_patientName">Patient Name</div>
                    <div class="text-end">
                        <span class="badge bg-primary" id="r_ward">Ward</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>New Round Note</h6>
                        <textarea class="form-control mb-2" id="newRoundNote" rows="6"
                            placeholder="Document clinical progress, treatment changes, or discharge planning..."></textarea>
                        <div class="text-end">
                            <button class="btn btn-primary"
                                onclick="alert('Submission logic pending POST Controller setup')">Save Round
                                Summary</button>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-4">
                        <h6>Previous Rounds History</h6>
                        <div id="roundsHistory" class="mt-3">
                            <!-- Populated via JS -->
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
    document.addEventListener('DOMContentLoaded', fetchRoundsPatients);

    // SPA Support: Run immediately if already loaded
    if (document.readyState !== 'loading') {
        fetchRoundsPatients();
    }

    async function fetchRoundsPatients() {
        const list = document.getElementById('roundsPatientList');
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
                        <div class="avatar avatar-sm bg-primary-transparent text-primary rounded-circle me-3">${a.first_name[0]}</div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">${a.first_name} ${a.last_name || ''}</div>
                            <div class="text-muted fs-11">${a.ward_name || 'N/A'} | ${a.bed_number || 'No Bed'} | Dr. ${a.doctor_name || 'N/A'}</div>
                        </div>
                    </div>
                `;
                    item.onclick = () => selectRoundsPatient(a, item);
                    list.appendChild(item);

                    if (preSelectId && a.admission_id == preSelectId) {
                        selectRoundsPatient(a, item);
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

    function selectRoundsPatient(a, el) {
        document.querySelectorAll('#roundsPatientList .list-group-item').forEach(i => i.classList.remove('bg-primary-transparent'));
        el.classList.add('bg-primary-transparent');

        document.getElementById('roundsPlaceholder').classList.add('d-none');
        document.getElementById('roundsWorkspace').classList.remove('d-none');
        document.getElementById('r_patientName').innerText = `${a.first_name} ${a.last_name || ''}`;
        document.getElementById('r_ward').innerText = `${a.ward_name || 'N/A'} / ${a.bed_number || 'No Bed'}`;

        fetchRoundsHistory(a.admission_id);
    }

    async function fetchRoundsHistory(id) {
        const history = document.getElementById('roundsHistory');
        history.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const res = await fetch(`/api/v1/ipd/admissions/${id}/rounds`);
            const data = await res.json();
            history.innerHTML = '';

            if (data.success && data.data.rounds.length > 0) {
                data.data.rounds.forEach(r => {
                    const date = new Date(r.round_date).toLocaleString();
                    const doctorName = r.doctor_name || 'Doctor';
                    const card = document.createElement('div');
                    card.className = 'card custom-card shadow-none border bg-light-transparent mb-3';
                    card.innerHTML = `
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold text-primary">Dr. ${doctorName}</span>
                            <span class="badge bg-info-transparent">${r.round_type || 'Morning'}</span>
                        </div>
                        <div class="text-muted fs-11 mb-2">${date}</div>
                        ${r.chief_complaint ? `<div class="mb-2"><strong>Chief Complaint:</strong> ${r.chief_complaint}</div>` : ''}
                        ${r.examination_findings ? `<div class="mb-2"><strong>Findings:</strong> ${r.examination_findings}</div>` : ''}
                        ${r.diagnosis ? `<div class="mb-2"><strong>Diagnosis:</strong> ${r.diagnosis}</div>` : ''}
                        ${r.treatment_plan ? `<div class="mb-2"><strong>Treatment Plan:</strong> ${r.treatment_plan}</div>` : ''}
                        ${r.patient_condition ? `<div class="mt-2"><span class="badge bg-${r.patient_condition === 'Improving' ? 'success' : r.patient_condition === 'Stable' ? 'info' : 'warning'}">${r.patient_condition}</span></div>` : ''}
                    </div>
                `;
                    history.appendChild(card);
                });
            } else {
                history.innerHTML = '<div class="text-center p-3 text-muted">No rounds recorded yet for this patient.</div>';
            }
        } catch (e) {
            console.error(e);
            history.innerHTML = '<div class="text-center p-3 text-danger">Failed to load rounds history.</div>';
        }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>