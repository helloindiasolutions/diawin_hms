<?php
$pageTitle = "Prescription Management";
$visitId = $_GET['visit_id'] ?? null;
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Prescription Management</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/visits">Clinical Visits</a></li>
            <li class="breadcrumb-item active" aria-current="page">Prescriptions</li>
        </ol>
    </div>
    <div class="btn-list">
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
                <p class="text-muted">Please select a visit from the <a href="/visits">visits page</a> to manage prescriptions.</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <!-- Patient Info Card -->
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header bg-primary-transparent">
                <div class="card-title"><i class="ri-user-heart-line me-1"></i>Patient Information</div>
            </div>
            <div class="card-body" id="patientInfo">
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prescriptions List -->
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Prescription History</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 fs-11 text-muted text-uppercase">Medicine</th>
                                <th class="fs-11 text-muted text-uppercase">Dosage</th>
                                <th class="fs-11 text-muted text-uppercase">Frequency</th>
                                <th class="fs-11 text-muted text-uppercase">Duration</th>
                                <th class="fs-11 text-muted text-uppercase">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="prescriptionTable">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
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
            loadPatientInfo();
            loadPrescriptions();
        }
    });

    async function loadPatientInfo() {
        const container = document.getElementById('patientInfo');
        try {
            const res = await fetch(`/api/v1/visits?search=&status=`);
            const data = await res.json();
            
            if (data.success) {
                const visit = data.data.visits.find(v => v.visit_id == visitId);
                if (visit) {
                    const age = calculateAge(visit.dob);
                    container.innerHTML = `
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar avatar-lg avatar-rounded bg-primary text-white fw-bold">
                                ${visit.first_name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">${visit.first_name} ${visit.last_name || ''}</h5>
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
                }
            }
        } catch (e) {
            console.error(e);
            container.innerHTML = '<div class="text-center text-danger">Error loading patient info</div>';
        }
    }

    async function loadPrescriptions() {
        const list = document.getElementById('prescriptionTable');
        list.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const res = await fetch(`/api/v1/visits/${visitId}/prescriptions`);
            const data = await res.json();
            list.innerHTML = '';

            if (data.success && data.data.prescriptions.length > 0) {
                data.data.prescriptions.forEach(rx => {
                    rx.items.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="ps-4">
                                <div class="fw-semibold"><i class="ri-medicine-bottle-line me-1 text-primary"></i>${item.product_name || 'Medicine'}</div>
                            </td>
                            <td><span class="fw-medium">${item.dosage || '-'}</span></td>
                            <td><span class="badge bg-info-transparent">${item.frequency || '-'}</span></td>
                            <td>${item.duration || '-'} days</td>
                            <td>${item.qty || '-'} ${item.unit || ''}</td>
                        `;
                        list.appendChild(row);
                    });
                });
            } else {
                list.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No prescriptions recorded yet.</td></tr>';
            }
        } catch (e) {
            console.error(e);
            list.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load prescriptions.</td></tr>';
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
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>