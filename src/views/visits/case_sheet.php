<?php
$pageTitle = "Full Case Sheet";
$visitId = $_GET['visit_id'] ?? null;
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Full Case Sheet</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/visits">Clinical Visits</a></li>
            <li class="breadcrumb-item active" aria-current="page">Case Sheet</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-success btn-wave" onclick="window.print()">
            <i class="ri-printer-line me-1"></i>Print Case Sheet
        </button>
        <a href="/visits" class="btn btn-light btn-wave">
            <i class="ri-arrow-left-line me-1"></i>Back to Visits
        </a>
    </div>
</div>

<?php if (!$visitId): ?>
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body text-center py-5">
                <div class="avatar avatar-xxl bg-danger-transparent text-danger mb-3">
                    <i class="ri-error-warning-line fs-48"></i>
                </div>
                <h4>No Visit Selected</h4>
                <p class="text-muted">Please select a visit from the <a href="/visits">visits page</a> to view the case sheet.</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body" id="caseSheetContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3 text-muted">Loading case sheet...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    const visitId = <?= $visitId ? (int)$visitId : 'null' ?>;

    document.addEventListener('DOMContentLoaded', () => {
        if (visitId) {
            loadCaseSheet();
        }
    });

    async function loadCaseSheet() {
        const container = document.getElementById('caseSheetContent');
        
        try {
            // Load visit details
            const visitRes = await fetch(`/api/v1/visits?search=&status=`);
            const visitData = await visitRes.json();
            
            if (!visitData.success) {
                throw new Error('Failed to load visit data');
            }
            
            const visit = visitData.data.visits.find(v => v.visit_id == visitId);
            if (!visit) {
                throw new Error('Visit not found');
            }

            // Load vitals
            const vitalsRes = await fetch(`/api/v1/visits/${visitId}/vitals`);
            const vitalsData = await vitalsRes.json();
            const vitals = vitalsData.success && vitalsData.data.vitals.length > 0 ? vitalsData.data.vitals[0] : null;

            // Load clinical notes
            const notesRes = await fetch(`/api/v1/visits/${visitId}/clinical-notes`);
            const notesData = await notesRes.json();
            const notes = notesData.success ? notesData.data.notes : [];

            // Load prescriptions
            const rxRes = await fetch(`/api/v1/visits/${visitId}/prescriptions`);
            const rxData = await rxRes.json();
            const prescriptions = rxData.success ? rxData.data.prescriptions : [];

            // Load siddha notes
            const siddhaRes = await fetch(`/api/v1/visits/${visitId}/siddha-notes`);
            const siddhaData = await siddhaRes.json();
            const siddha = siddhaData.success ? siddhaData.data.notes : null;

            // Render case sheet
            const age = calculateAge(visit.dob);
            const visitDate = new Date(visit.visit_start).toLocaleString();
            
            container.innerHTML = `
                <!-- Header -->
                <div class="border-bottom pb-3 mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="mb-2">${visit.first_name} ${visit.last_name || ''}</h3>
                            <div class="row g-2">
                                <div class="col-auto">
                                    <strong>MRN:</strong> ${visit.mrn}
                                </div>
                                <div class="col-auto">
                                    <strong>Age:</strong> ${age}
                                </div>
                                <div class="col-auto">
                                    <strong>Gender:</strong> ${visit.gender || '-'}
                                </div>
                                <div class="col-auto">
                                    <strong>Visit Type:</strong> ${visit.visit_type}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-muted">Visit Date</div>
                            <div class="fw-bold">${visitDate}</div>
                            <div class="text-muted mt-2">Visit ID: #${visit.visit_id}</div>
                        </div>
                    </div>
                </div>

                <!-- Vitals Section -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3"><i class="ri-heart-pulse-line me-2"></i>Vital Signs</h5>
                    ${vitals ? `
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted fs-12">Blood Pressure</div>
                                    <div class="fw-bold fs-18">${vitals.bp_systolic || '-'}/${vitals.bp_diastolic || '-'} mmHg</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted fs-12">Pulse Rate</div>
                                    <div class="fw-bold fs-18">${vitals.pulse_per_min || '-'} bpm</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted fs-12">Temperature</div>
                                    <div class="fw-bold fs-18">${vitals.temperature_c || '-'} °C</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted fs-12">SPO2</div>
                                    <div class="fw-bold fs-18">${vitals.spo2 || '-'}%</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted fs-12">Weight</div>
                                    <div class="fw-bold fs-18">${vitals.weight_kg || '-'} kg</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted fs-12">Height</div>
                                    <div class="fw-bold fs-18">${vitals.height_cm || '-'} cm</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted fs-12">Respiratory Rate</div>
                                    <div class="fw-bold fs-18">${vitals.respiratory_rate || '-'} /min</div>
                                </div>
                            </div>
                        </div>
                    ` : '<p class="text-muted">No vitals recorded</p>'}
                </div>

                <!-- Clinical Notes Section -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3"><i class="ri-file-text-line me-2"></i>Clinical Notes</h5>
                    ${notes.length > 0 ? notes.map(note => `
                        <div class="border rounded p-3 mb-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary-transparent">${note.note_type.toUpperCase()}</span>
                                <span class="text-muted fs-12">${new Date(note.created_at).toLocaleString()}</span>
                            </div>
                            <p class="mb-0">${note.note_text}</p>
                        </div>
                    `).join('') : '<p class="text-muted">No clinical notes recorded</p>'}
                </div>

                <!-- Siddha Notes Section -->
                ${siddha ? `
                    <div class="mb-4">
                        <h5 class="text-primary mb-3"><i class="ri-ancient-pavilion-line me-2"></i>Siddha Assessment</h5>
                        <div class="border rounded p-3">
                            ${siddha.note_text ? `<p>${siddha.note_text}</p>` : '<p class="text-muted">No Siddha notes recorded</p>'}
                        </div>
                    </div>
                ` : ''}

                <!-- Prescriptions Section -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3"><i class="ri-medicine-bottle-line me-2"></i>Prescriptions</h5>
                    ${prescriptions.length > 0 ? `
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${prescriptions.map(rx => rx.items.map(item => `
                                        <tr>
                                            <td>${item.product_name || 'Medicine'}</td>
                                            <td>${item.dosage || '-'}</td>
                                            <td>${item.frequency || '-'}</td>
                                            <td>${item.duration || '-'} days</td>
                                            <td>${item.qty || '-'} ${item.unit || ''}</td>
                                        </tr>
                                    `).join('')).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : '<p class="text-muted">No prescriptions recorded</p>'}
                </div>

                <!-- Footer -->
                <div class="border-top pt-3 mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-0">Provider: ${visit.provider_name || 'Not assigned'}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="text-muted mb-0">Generated: ${new Date().toLocaleString()}</p>
                        </div>
                    </div>
                </div>
            `;

        } catch (e) {
            console.error(e);
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="avatar avatar-xl bg-danger-transparent text-danger mb-3">
                        <i class="ri-error-warning-line fs-32"></i>
                    </div>
                    <h5>Error Loading Case Sheet</h5>
                    <p class="text-muted">${e.message}</p>
                </div>
            `;
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
</script>

<style>
    @media print {
        .page-header-breadcrumb,
        .btn-list,
        .sidebar,
        .header {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>
