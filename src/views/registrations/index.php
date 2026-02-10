<?php
$pageTitle = "Registrations";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Visit Registrations</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Registrations</a></li>
            <li class="breadcrumb-item active" aria-current="page">All Registrations</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary d-inline-flex align-items-center" data-bs-toggle="modal"
            data-bs-target="#regModal">
            <i class="ri-add-line me-1"></i>New Registration
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Daily Intake Log</div>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" id="regSearch"
                        placeholder="Search Patient..." onkeyup="fetchRegistrations()">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Visit ID</th>
                                <th>Patient Details</th>
                                <th>Category</th>
                                <th>Provider</th>
                                <th>Time</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="regList">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Registration Modal -->
<div class="modal fade" id="regModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="regForm" class="needs-validation" novalidate onsubmit="saveRegistration(event)">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">New Visit Registration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Search Patient</label>
                            <input type="text" class="form-control" id="patientSearch"
                                placeholder="Type Name or MRN to find patient...">
                            <input type="hidden" id="reg_patient_id">
                            <div id="patientInfoBrief" class="mt-2 p-2 bg-light rounded d-none">
                                <span class="fw-bold" id="b_name"></span> | <small class="text-muted"
                                    id="b_mrn"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visit Type</label>
                            <select class="form-select" id="reg_type" onchange="toggleIPFields()">
                                <option value="OP">Out-Patient (OP)</option>
                                <option value="IP">In-Patient (IP)</option>
                                <option value="ER">Emergency (ER)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Consulting Doctor</label>
                            <select class="form-select" id="reg_provider" required>
                                <option value="">Select Doctor</option>
                            </select>
                        </div>
                        <div class="row g-3 mt-1 d-none" id="ipFields">
                            <div class="col-md-6">
                                <label class="form-label">Assign Ward</label>
                                <select class="form-select" id="reg_ward">
                                    <option value="General">General Ward</option>
                                    <option value="Semi-Special">Semi-Special</option>
                                    <option value="Special">Special Room</option>
                                    <option value="ICU">ICU</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room / Bed No</label>
                                <input type="text" class="form-control" id="reg_room" placeholder="e.g., 201-A">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Complete Registration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    // Use Melina.onPageLoad for SPA compatibility
    const initRegistrations = () => {
        fetchRegistrations();
        loadProviders();
        setupPatientSearch();
    };

    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initRegistrations);
    } else {
        document.addEventListener('DOMContentLoaded', initRegistrations);
    }

    async function loadProviders() {
        try {
            const select = document.getElementById('reg_provider');
            
            // Exit early if element doesn't exist (wrong page)
            if (!select) {
                return;
            }

            const res = await fetch('/api/v1/appointments/providers');
            const data = await res.json();

            if (data.success && data.data.providers) {
                // Clear existing options except first
                select.innerHTML = '<option value="">Select Doctor</option>';

                data.data.providers.forEach(p => {
                    const specialization = p.specialization ? ` (${p.specialization})` : '';
                    const opt = new Option(`${p.full_name}${specialization}`, p.provider_id);
                    select.add(opt);
                });
            }
        } catch (e) {
            console.error('Failed to load specialists registry');
        }
    }

    async function fetchRegistrations() {
        const list = document.getElementById('regList');
        const searchInput = document.getElementById('regSearch');
        if (!list || !searchInput) return;

        const search = searchInput.value;
        list.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const res = await fetch(`/api/v1/registrations?search=${search}`);
            const data = await res.json();
            if (!document.getElementById('regList')) return; // Check again after await
            list.innerHTML = '';

            if (data.success && data.data.registrations.length > 0) {
                data.data.registrations.forEach(r => {
                    const time = new Date(r.visit_start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const date = new Date(r.visit_start).toLocaleDateString();

                    let typeBadge = '';
                    if (r.visit_type === 'OP') typeBadge = '<span class="badge bg-primary-transparent">OP</span>';
                    else if (r.visit_type === 'IP') typeBadge = '<span class="badge bg-secondary-transparent">IP</span>';
                    else typeBadge = '<span class="badge bg-danger-transparent">ER</span>';

                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td class="ps-4"><span class="fw-bold">RE-${String(r.visit_id).padStart(5, '0')}</span></td>
                    <td>
                        <div class="fw-medium">${r.first_name} ${r.last_name || ''}</div>
                        <div class="text-muted fs-11">${r.mrn}</div>
                    </td>
                    <td>${typeBadge}</td>
                    <td>${r.provider_name || 'General Desk'}</td>
                    <td>
                        <div class="fs-12">${date}</div>
                        <div class="text-muted fs-11">${time}</div>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-icon btn-light rounded-pill"><i class="ri-printer-line"></i></button>
                    </td>
                `;
                    list.appendChild(row);
                });
            }
        } catch (e) { }
    }

    function toggleIPFields() {
        const type = document.getElementById('reg_type').value;
        document.getElementById('ipFields').classList.toggle('d-none', type !== 'IP');
    }

    function setupPatientSearch() {
        // Basic search simulation - in real app use autoComplete.js like Appointments
        const input = document.getElementById('patientSearch');
        input.addEventListener('keyup', async (e) => {
            if (input.value.length < 3) return;
            try {
                const res = await fetch(`/api/v1/patients/search?q=${input.value}`);
                const data = await res.json();
                if (data.success && data.data.patients.length > 0) {
                    const p = data.data.patients[0]; // Take first match for quick demo
                    document.getElementById('reg_patient_id').value = p.patient_id;
                    document.getElementById('b_name').innerText = p.full_name;
                    document.getElementById('b_mrn').innerText = p.mrn;
                    document.getElementById('patientInfoBrief').classList.remove('d-none');
                }
            } catch (e) { }
        });
    }

    async function saveRegistration(e) {
        e.preventDefault();
        const pid = document.getElementById('reg_patient_id').value;
        if (!pid) { alert('Select a patient first'); return; }

        const payload = {
            patient_id: pid,
            visit_type: document.getElementById('reg_type').value,
            provider_id: document.getElementById('reg_provider').value,
            ward: document.getElementById('reg_ward').value,
            room: document.getElementById('reg_room').value
        };

        try {
            const res = await fetch('/api/v1/registrations', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('regModal')).hide();
                fetchRegistrations();
                document.getElementById('regForm').reset();
                document.getElementById('patientInfoBrief').classList.add('d-none');
            }
        } catch (e) { }
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>