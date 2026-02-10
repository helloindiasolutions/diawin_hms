<?php
/**
 * Siddha Notes - Traditional Medicine Documentation
 * Pulse Diagnosis, Prakriti Assessment, Tongue Examination, Anupanam Documentation
 */
$pageTitle = 'Siddha Notes';
$visitId = $_GET['visit_id'] ?? null;
$activeTab = $active_tab ?? 'pulse';
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Siddha Clinical Notes</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/visits">OPD</a></li>
            <li class="breadcrumb-item active" aria-current="page">Siddha Notes</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-outline-success btn-wave" onclick="printSiddhaNotes()">
            <i class="ri-printer-line me-1"></i>Print Notes
        </button>
        <a href="/visits" class="btn btn-light btn-wave">
            <i class="ri-arrow-left-line me-1"></i>Back to Visits
        </a>
    </div>
</div>

<?php if ($visitId): ?>
<div class="row">
    <!-- Left Panel: Patient Info -->
    <div class="col-xl-4 col-lg-5">
        <!-- Quick Stats for Selected Patient -->
        <div id="patientQuickInfo" class="card custom-card">
            <div class="card-header bg-success-transparent">
                <div class="card-title"><i class="ri-user-heart-line me-1"></i>Patient Information</div>
            </div>
            <div class="card-body" id="patientInfo">
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-success"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Siddha Notes Content -->
    <div class="col-xl-8 col-lg-7">
            <div class="card custom-card">
                <div class="card-header bg-primary text-white">
                    <div class="card-title d-flex align-items-center text-white">
                        <i class="ri-ancient-gate-line me-2"></i>
                        <span id="activePatientName">Patient Name</span> - Siddha Documentation
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-justified mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $activeTab === 'pulse' ? 'active' : '' ?>" data-bs-toggle="tab"
                                data-bs-target="#pulseTab" type="button">
                                <i class="ri-pulse-line me-1"></i>Pulse Diagnosis
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $activeTab === 'prakriti' ? 'active' : '' ?>"
                                data-bs-toggle="tab" data-bs-target="#prakritiTab" type="button">
                                <i class="ri-user-star-line me-1"></i>Prakriti
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $activeTab === 'tongue' ? 'active' : '' ?>" data-bs-toggle="tab"
                                data-bs-target="#tongueTab" type="button">
                                <i class="ri-eye-line me-1"></i>Tongue Exam
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $activeTab === 'anupanam' ? 'active' : '' ?>"
                                data-bs-toggle="tab" data-bs-target="#anupanamTab" type="button">
                                <i class="ri-flask-line me-1"></i>Anupanam
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Pulse Diagnosis Tab -->
                        <div class="tab-pane fade <?= $activeTab === 'pulse' ? 'show active' : '' ?>" id="pulseTab"
                            role="tabpanel">
                            <h6 class="fw-bold text-primary mb-3"><i class="ri-pulse-line me-1"></i>Nadi Pariksha (Pulse
                                Diagnosis)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Vatha Nadi</label>
                                    <select class="form-select" id="nadi_vatha">
                                        <option value="">-- Select --</option>
                                        <option value="normal">Normal</option>
                                        <option value="elevated">Elevated</option>
                                        <option value="low">Low</option>
                                        <option value="erratic">Erratic</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pitha Nadi</label>
                                    <select class="form-select" id="nadi_pitha">
                                        <option value="">-- Select --</option>
                                        <option value="normal">Normal</option>
                                        <option value="elevated">Elevated</option>
                                        <option value="low">Low</option>
                                        <option value="erratic">Erratic</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kapha Nadi</label>
                                    <select class="form-select" id="nadi_kapha">
                                        <option value="">-- Select --</option>
                                        <option value="normal">Normal</option>
                                        <option value="elevated">Elevated</option>
                                        <option value="low">Low</option>
                                        <option value="erratic">Erratic</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pulse Rate (per min)</label>
                                    <input type="number" class="form-control" id="nadi_rate" placeholder="72">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Pulse Quality Description</label>
                                    <textarea class="form-control" id="pulse_quality" rows="2"
                                        placeholder="Describe pulse characteristics..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Detailed Pulse Notes</label>
                                    <textarea class="form-control" id="pulse_notes" rows="4"
                                        placeholder="Document detailed observations about the pulse diagnosis..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Prakriti Assessment Tab -->
                        <div class="tab-pane fade <?= $activeTab === 'prakriti' ? 'show active' : '' ?>"
                            id="prakritiTab" role="tabpanel">
                            <h6 class="fw-bold text-primary mb-3"><i class="ri-user-star-line me-1"></i>Prakriti
                                Assessment (Body Constitution)</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Primary Prakriti</label>
                                    <select class="form-select" id="prakriti_primary">
                                        <option value="">-- Select --</option>
                                        <option value="Vatha">Vatha</option>
                                        <option value="Pitha">Pitha</option>
                                        <option value="Kapha">Kapha</option>
                                        <option value="Vatha-Pitha">Vatha-Pitha</option>
                                        <option value="Pitha-Kapha">Pitha-Kapha</option>
                                        <option value="Vatha-Kapha">Vatha-Kapha</option>
                                        <option value="Tridosha">Tridosha (Balanced)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Vikriti (Current State)</label>
                                    <select class="form-select" id="prakriti_vikriti">
                                        <option value="">-- Select --</option>
                                        <option value="Vatha Aggravated">Vatha Aggravated</option>
                                        <option value="Pitha Aggravated">Pitha Aggravated</option>
                                        <option value="Kapha Aggravated">Kapha Aggravated</option>
                                        <option value="Balanced">Balanced</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Agni (Digestive Fire)</label>
                                    <select class="form-select" id="prakriti_agni">
                                        <option value="">-- Select --</option>
                                        <option value="Sama (Balanced)">Sama (Balanced)</option>
                                        <option value="Teeksna (Sharp)">Teeksna (Sharp)</option>
                                        <option value="Manda (Low)">Manda (Low)</option>
                                        <option value="Vishama (Erratic)">Vishama (Erratic)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Physical Constitution Observations</label>
                                    <textarea class="form-control" id="prakriti_physical" rows="2"
                                        placeholder="Body type, skin, hair, eyes..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mental Constitution Observations</label>
                                    <textarea class="form-control" id="prakriti_mental" rows="2"
                                        placeholder="Temperament, sleep patterns, habits..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Tongue Examination Tab -->
                        <div class="tab-pane fade <?= $activeTab === 'tongue' ? 'show active' : '' ?>" id="tongueTab"
                            role="tabpanel">
                            <h6 class="fw-bold text-primary mb-3"><i class="ri-eye-line me-1"></i>Jihwa Pariksha (Tongue
                                Examination)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tongue Color</label>
                                    <select class="form-select" id="tongue_color">
                                        <option value="">-- Select --</option>
                                        <option value="Pink (Normal)">Pink (Normal)</option>
                                        <option value="Pale">Pale</option>
                                        <option value="Red">Red</option>
                                        <option value="Dark Red">Dark Red</option>
                                        <option value="Bluish">Bluish</option>
                                        <option value="Yellowish">Yellowish</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Coating</label>
                                    <select class="form-select" id="tongue_coating">
                                        <option value="">-- Select --</option>
                                        <option value="Thin White (Normal)">Thin White (Normal)</option>
                                        <option value="Thick White">Thick White</option>
                                        <option value="Yellow">Yellow</option>
                                        <option value="Thick Yellow">Thick Yellow</option>
                                        <option value="Brown/Black">Brown/Black</option>
                                        <option value="No Coating">No Coating</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Texture/Moisture</label>
                                    <select class="form-select" id="tongue_texture">
                                        <option value="">-- Select --</option>
                                        <option value="Normal Moisture">Normal Moisture</option>
                                        <option value="Dry">Dry</option>
                                        <option value="Wet/Moist">Wet/Moist</option>
                                        <option value="Cracked">Cracked</option>
                                        <option value="Swollen">Swollen</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Shape/Size</label>
                                    <select class="form-select" id="tongue_shape">
                                        <option value="">-- Select --</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Thin">Thin</option>
                                        <option value="Enlarged">Enlarged</option>
                                        <option value="Scalloped Edges">Scalloped Edges</option>
                                        <option value="Pointed">Pointed</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Additional Tongue Observations</label>
                                    <textarea class="form-control" id="tongue_notes" rows="3"
                                        placeholder="Describe any other observations (marks, spots, tremor, etc.)..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Anupanam Tab -->
                        <div class="tab-pane fade <?= $activeTab === 'anupanam' ? 'show active' : '' ?>"
                            id="anupanamTab" role="tabpanel">
                            <h6 class="fw-bold text-primary mb-3"><i class="ri-flask-line me-1"></i>Anupanam (Drug
                                Delivery Medium)</h6>
                            <div class="alert alert-info-transparent mb-3">
                                <i class="ri-lightbulb-line me-1"></i>
                                Anupanam refers to the vehicle or medium used to administer medicines. The choice
                                depends on the patient's dosha and the condition being treated.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Recommended Anupanam</label>
                                    <select class="form-select" id="anupanam_type">
                                        <option value="">-- Select --</option>
                                        <option value="Warm Water">Warm Water</option>
                                        <option value="Honey">Honey</option>
                                        <option value="Ghee">Ghee</option>
                                        <option value="Milk">Milk</option>
                                        <option value="Buttermilk">Buttermilk</option>
                                        <option value="Jaggery Water">Jaggery Water</option>
                                        <option value="Ginger Juice">Ginger Juice</option>
                                        <option value="Rice Water">Rice Water</option>
                                        <option value="Other">Other (specify)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Timing</label>
                                    <select class="form-select" id="anupanam_timing">
                                        <option value="">-- Select --</option>
                                        <option value="Before Food">Before Food (Empty Stomach)</option>
                                        <option value="With Food">With Food</option>
                                        <option value="After Food">After Food</option>
                                        <option value="At Bedtime">At Bedtime</option>
                                        <option value="Early Morning">Early Morning</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Special Instructions</label>
                                    <textarea class="form-control" id="anupanam_notes" rows="3"
                                        placeholder="Enter any special instructions regarding the anupanam or dietary recommendations..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Pathya-Apathya (Dietary Do's & Don'ts)</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <textarea class="form-control" id="pathya" rows="3"
                                                placeholder="Foods to include..."></textarea>
                                            <small class="text-success">✓ Pathya (Recommended)</small>
                                        </div>
                                        <div class="col-md-6">
                                            <textarea class="form-control" id="apathya" rows="3"
                                                placeholder="Foods to avoid..."></textarea>
                                            <small class="text-danger">✗ Apathya (Avoid)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- General Notes -->
                    <div class="border-top pt-3 mt-4">
                        <h6 class="fw-bold mb-3">Additional Clinical Notes</h6>
                        <textarea class="form-control" id="siddha_general_notes" rows="3"
                            placeholder="Any additional observations or notes..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <div>
                            <span class="text-muted fs-12" id="saveStatus"></span>
                        </div>
                        <div class="btn-list">
                            <button type="button" class="btn btn-light btn-wave" onclick="clearForm()">
                                <i class="ri-refresh-line me-1"></i>Clear
                            </button>
                            <button type="button" class="btn btn-success btn-wave" onclick="saveSiddhaNotes()">
                                <i class="ri-save-line me-1"></i>Save Siddha Notes
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes History -->
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Previous Siddha Notes</div>
                </div>
                <div class="card-body" id="historyContainer">
                    <div class="text-center py-3 text-muted">Select a visit to see history</div>
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
                <div class="card-title">Select Patient Visit</div>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="patientSearch"
                        placeholder="Search Patient (Name/MRN)..." onkeyup="debounce(fetchEncounters, 300)">
                    <span class="input-group-text bg-primary text-white"><i class="ri-search-line"></i></span>
                </div>
                <div id="encounterList" class="list-group list-group-flush custom-list"
                    style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center py-4 text-muted">
                        <i class="ri-user-search-line fs-28 d-block mb-2"></i>
                        Loading active visits...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div id="notesPlaceholder"
            class="card custom-card h-100 d-flex align-items-center justify-content-center text-center p-5">
            <div class="avatar avatar-xxl bg-light text-muted mb-4">
                <i class="ri-ancient-pavilion-line fs-48"></i>
            </div>
            <h4>Select an Active Visit</h4>
            <p class="text-muted">Select a patient visit from the left panel to record Siddha clinical observations.</p>
        </div>

        <div id="notesContainer" class="d-none">
            <!-- Content will be shown when visit is selected via JavaScript -->
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
        border-color: rgba(var(--primary-rgb), 0.2);
    }

    .custom-list .list-group-item.active {
        background-color: var(--primary-color) !important;
        color: white;
        border-color: var(--primary-color);
    }

    .custom-list .list-group-item.active .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .nav-tabs .nav-link {
        font-size: 0.85rem;
        padding: 0.75rem 1rem;
        border-radius: 8px 8px 0 0;
    }

    .nav-tabs .nav-link.active {
        font-weight: 600;
        background-color: rgba(var(--primary-rgb), 0.1);
        border-color: rgba(var(--primary-rgb), 0.3) rgba(var(--primary-rgb), 0.3) transparent;
        color: var(--primary-color);
    }

    .form-label {
        font-weight: 500;
        font-size: 0.85rem;
        color: #555;
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    const visitId = <?= $visitId ? (int)$visitId : 'null' ?>;
    let selectedVisitId = visitId;
    let selectedPatient = null;
    let searchTimer = null;

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
                    selectedPatient = visit;
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
                    
                    loadSiddhaNotes(visitId);
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    function debounce(func, delay) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(func, delay);
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
                    item.href = 'javascript:void(0)';
                    item.className = 'list-group-item p-3';
                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold">${v.first_name} ${v.last_name || ''}</span>
                            <span class="badge bg-${v.visit_type === 'OP' ? 'primary' : 'success'}-transparent">${v.visit_type}</span>
                        </div>
                        <div class="text-muted fs-11">MRN: ${v.mrn} | Visit #${v.visit_id}</div>
                    `;
                    item.onclick = () => selectVisit(v, item);
                    list.appendChild(item);
                });
            } else {
                list.innerHTML = '<div class="text-center py-4 text-muted"><i class="ri-folder-open-line fs-28 d-block mb-2"></i>No active visits found</div>';
            }
        } catch (e) {
            console.error('Error fetching encounters:', e);
            list.innerHTML = '<div class="text-center py-4 text-danger">Error loading visits</div>';
        }
    }

    function selectVisit(visit, element) {
        selectedVisitId = visit.visit_id;
        selectedPatient = visit;

        // Update UI
        document.querySelectorAll('.custom-list .list-group-item').forEach(i => i.classList.remove('active'));
        element.classList.add('active');

        // Show notes container
        document.getElementById('notesPlaceholder').classList.add('d-none');
        document.getElementById('notesContainer').classList.remove('d-none');
        document.getElementById('activePatientName').innerText = `${visit.first_name} ${visit.last_name || ''}`;

        // Show patient info
        document.getElementById('patientQuickInfo').classList.remove('d-none');
        document.getElementById('infoName').innerText = `${visit.first_name} ${visit.last_name || ''}`;
        document.getElementById('infoMrn').innerText = `MRN: ${visit.mrn}`;
        document.getElementById('patientAvatar').innerText = visit.first_name.charAt(0).toUpperCase();
        document.getElementById('infoAge').innerText = calculateAge(visit.dob) + ' / ' + (visit.gender || '-');
        document.getElementById('infoType').innerText = visit.visit_type;

        // Load existing notes
        loadSiddhaNotes(visit.visit_id);
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

    async function loadSiddhaNotes(visitId) {
        clearForm();
        document.getElementById('saveStatus').innerText = 'Loading...';

        try {
            const res = await fetch(`/api/v1/visits/${visitId}/siddha-notes`);
            const data = await res.json();

            if (data.success && data.data.notes) {
                const notes = data.data.notes;

                // Parse pulse diagnosis JSON if exists
                if (notes.pulse_diagnosis) {
                    try {
                        const pulse = JSON.parse(notes.pulse_diagnosis);
                        document.getElementById('nadi_vatha').value = pulse.vatha || '';
                        document.getElementById('nadi_pitha').value = pulse.pitha || '';
                        document.getElementById('nadi_kapha').value = pulse.kapha || '';
                        document.getElementById('nadi_rate').value = pulse.rate || '';
                        document.getElementById('pulse_quality').value = pulse.quality || '';
                        document.getElementById('pulse_notes').value = pulse.notes || '';
                    } catch (e) {
                        document.getElementById('pulse_notes').value = notes.pulse_diagnosis;
                    }
                }

                // Parse prakriti JSON if exists
                if (notes.prakriti) {
                    try {
                        const prakriti = JSON.parse(notes.prakriti);
                        document.getElementById('prakriti_primary').value = prakriti.primary || '';
                        document.getElementById('prakriti_vikriti').value = prakriti.vikriti || '';
                        document.getElementById('prakriti_agni').value = prakriti.agni || '';
                        document.getElementById('prakriti_physical').value = prakriti.physical || '';
                        document.getElementById('prakriti_mental').value = prakriti.mental || '';
                    } catch (e) {
                        document.getElementById('prakriti_primary').value = notes.prakriti;
                    }
                }

                // Parse tongue JSON if exists
                if (notes.tongue) {
                    try {
                        const tongue = JSON.parse(notes.tongue);
                        document.getElementById('tongue_color').value = tongue.color || '';
                        document.getElementById('tongue_coating').value = tongue.coating || '';
                        document.getElementById('tongue_texture').value = tongue.texture || '';
                        document.getElementById('tongue_shape').value = tongue.shape || '';
                        document.getElementById('tongue_notes').value = tongue.notes || '';
                    } catch (e) {
                        document.getElementById('tongue_notes').value = notes.tongue;
                    }
                }

                // Parse anupanam JSON if exists
                if (notes.anupanam) {
                    try {
                        const anupanam = JSON.parse(notes.anupanam);
                        document.getElementById('anupanam_type').value = anupanam.type || '';
                        document.getElementById('anupanam_timing').value = anupanam.timing || '';
                        document.getElementById('anupanam_notes').value = anupanam.notes || '';
                        document.getElementById('pathya').value = anupanam.pathya || '';
                        document.getElementById('apathya').value = anupanam.apathya || '';
                    } catch (e) {
                        document.getElementById('anupanam_notes').value = notes.anupanam;
                    }
                }

                // General notes
                if (notes.note_text) {
                    document.getElementById('siddha_general_notes').value = notes.note_text;
                }

                document.getElementById('saveStatus').innerText = 'Notes loaded';
            } else {
                document.getElementById('saveStatus').innerText = 'No previous notes';
            }

            loadHistory(visitId);
        } catch (e) {
            console.error(e);
            document.getElementById('saveStatus').innerText = 'Error loading notes';
        }
    }

    async function loadHistory(visitId) {
        const container = document.getElementById('historyContainer');
        container.innerHTML = '<div class="text-center py-2 text-muted">Loading...</div>';

        try {
            // For now, show "no previous records" - can enhance with actual history
            container.innerHTML = '<div class="text-center py-3 text-muted fs-13">Showing latest notes. History tracking coming soon.</div>';
        } catch (e) {
            console.error(e);
        }
    }

    function clearForm() {
        // Clear all form fields
        const fields = ['nadi_vatha', 'nadi_pitha', 'nadi_kapha', 'nadi_rate', 'pulse_quality', 'pulse_notes',
            'prakriti_primary', 'prakriti_vikriti', 'prakriti_agni', 'prakriti_physical', 'prakriti_mental',
            'tongue_color', 'tongue_coating', 'tongue_texture', 'tongue_shape', 'tongue_notes',
            'anupanam_type', 'anupanam_timing', 'anupanam_notes', 'pathya', 'apathya', 'siddha_general_notes'
        ];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        document.getElementById('saveStatus').innerText = '';
    }

    async function saveSiddhaNotes() {
        if (!selectedVisitId) {
            alert('Please select a patient visit first');
            return;
        }

        document.getElementById('saveStatus').innerText = 'Saving...';

        // Collect pulse data
        const pulseData = {
            vatha: document.getElementById('nadi_vatha').value,
            pitha: document.getElementById('nadi_pitha').value,
            kapha: document.getElementById('nadi_kapha').value,
            rate: document.getElementById('nadi_rate').value,
            quality: document.getElementById('pulse_quality').value,
            notes: document.getElementById('pulse_notes').value
        };

        // Collect prakriti data
        const prakritiData = {
            primary: document.getElementById('prakriti_primary').value,
            vikriti: document.getElementById('prakriti_vikriti').value,
            agni: document.getElementById('prakriti_agni').value,
            physical: document.getElementById('prakriti_physical').value,
            mental: document.getElementById('prakriti_mental').value
        };

        // Collect tongue data
        const tongueData = {
            color: document.getElementById('tongue_color').value,
            coating: document.getElementById('tongue_coating').value,
            texture: document.getElementById('tongue_texture').value,
            shape: document.getElementById('tongue_shape').value,
            notes: document.getElementById('tongue_notes').value
        };

        // Collect anupanam data
        const anupanamData = {
            type: document.getElementById('anupanam_type').value,
            timing: document.getElementById('anupanam_timing').value,
            notes: document.getElementById('anupanam_notes').value,
            pathya: document.getElementById('pathya').value,
            apathya: document.getElementById('apathya').value
        };

        const payload = {
            visit_id: selectedVisitId,
            pulse_diagnosis: JSON.stringify(pulseData),
            prakriti: JSON.stringify(prakritiData),
            tongue: JSON.stringify(tongueData),
            anupanam: JSON.stringify(anupanamData),
            note_text: document.getElementById('siddha_general_notes').value
        };

        try {
            const res = await fetch('/api/v1/visits/siddha-notes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                document.getElementById('saveStatus').innerText = '✓ Saved successfully at ' + new Date().toLocaleTimeString();
                // Show toast if available
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Siddha notes saved successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else {
                document.getElementById('saveStatus').innerText = '✗ Error: ' + (data.message || 'Failed to save');
            }
        } catch (e) {
            console.error(e);
            document.getElementById('saveStatus').innerText = '✗ Network error';
        }
    }

    function printSiddhaNotes() {
        if (!selectedVisitId) {
            alert('Please select a patient visit first');
            return;
        }
        window.print();
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>