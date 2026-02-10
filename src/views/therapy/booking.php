<?php
/**
 * Therapy Session Booking
 * Specialized booking for Siddha/Ayurvedic Therapies
 */
$pageTitle = "Book Therapy Session";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Book Therapy Session</h2>
        <span class="text-muted fs-12">Schedule a new treatment session for a patient.</span>
    </div>
    <div class="btn-list">
        <a href="<?= baseUrl('/therapy/sessions') ?>" class="btn btn-light btn-wave">
            <i class="ri-arrow-left-line align-middle me-1"></i> Back to Sessions
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="card custom-card">
            <div class="card-body p-4">
                <form id="therapyBookingForm">
                    <div class="row g-3">
                        <!-- Patient Search -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Patient Search <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="ri-user-search-line"></i></span>
                                <input type="text" id="patientSearch" class="form-control"
                                    placeholder="Search by MRN, Name or Mobile..." autocomplete="off">
                            </div>
                            <div id="patientResults" class="search-dropdown d-none"></div>
                            <input type="hidden" name="patient_id" id="selected_patient_id">
                            <div id="selected_patient_box" class="mt-2 d-none">
                                <div
                                    class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold" id="sp_name"></span> - <span id="sp_mrn"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-icon btn-danger-light"
                                        onclick="clearPatient()"><i class="ri-close-line"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Protocol Selection -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Therapy Protocol <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="protocol_id" id="protocolSelect" required>
                                <option value="">Select Protocol...</option>
                                <option value="1">Elakizhi (Leaf Bolus)</option>
                                <option value="2">Varmam Point Stimulation</option>
                                <option value="3">Abhyanga (General)</option>
                                <option value="4">Podikizhi (Powder Bolus)</option>
                                <option value="5">Nasyam (Nasal Therapy)</option>
                            </select>
                        </div>

                        <!-- Practitioner -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lead Practitioner <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="practitioner_id" required>
                                <option value="">Select Staff...</option>
                                <option value="1">Dr. Rajesh (Siddha Varmam Expert)</option>
                                <option value="2">Anand (Senior Therapist)</option>
                                <option value="3">Mala (Ayurveda Specialist)</option>
                            </select>
                        </div>

                        <!-- Date and Time -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Session Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="scheduled_on" value="<?= date('Y-m-d') ?>"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Preferred Time</label>
                            <input type="time" class="form-control" name="scheduled_time">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Special Instructions / Observations</label>
                            <textarea class="form-control" name="notes" rows="3"
                                placeholder="Any specific requirements for this session (e.g., patient allergies, high blood pressure)"></textarea>
                        </div>

                        <div class="col-12 pt-3">
                            <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm">
                                <i class="ri-calendar-check-line me-2"></i> Confirm Booking
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Sidebar -->
    <div class="col-xl-4 col-lg-2 d-none d-xl-block">
        <div class="card custom-card bg-primary-transparent border-primary border-opacity-10">
            <div class="card-body">
                <h6 class="fw-semibold text-primary mb-3">Therapy Guidelines</h6>
                <ul class="list-unstyled mb-0 fs-12 text-muted lh-lg">
                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i> Ensure patient consent is signed.</li>
                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i> Double check Thailam (Oil)
                        availability.</li>
                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i> Monitor Vitals before Varmam
                        procedures.</li>
                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i> Post-therapy rest instructions must be
                        given.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Simplified Patient Search
    const pSearch = document.getElementById('patientSearch');
    const pResults = document.getElementById('patientResults');

    pSearch.addEventListener('input', async (e) => {
        const query = e.target.value.trim();
        if (query.length < 2) {
            pResults.classList.add('d-none');
            return;
        }

        try {
            const res = await fetch(`/api/v1/patients/search?q=${query}`);
            const data = await res.json();
            if (data.success && data.data.patients.length > 0) {
                pResults.innerHTML = '';
                data.data.patients.forEach(p => {
                    const item = document.createElement('div');
                    item.className = 'search-item';
                    item.innerHTML = `
                        <div class="fw-semibold">${p.full_name}</div>
                        <div class="text-muted fs-11">${p.mrn} | ${p.mobile || 'No Mobile'}</div>
                    `;
                    item.onclick = () => selectPatient(p);
                    pResults.appendChild(item);
                });
                pResults.classList.remove('d-none');
            } else {
                pResults.innerHTML = '<div class="p-3 text-center text-muted">No patients found.</div>';
                pResults.classList.remove('d-none');
            }
        } catch (e) {
            console.error('Search error:', e);
        }
    });

    function selectPatient(p) {
        document.getElementById('selected_patient_id').value = p.patient_id;
        document.getElementById('sp_name').innerText = p.full_name;
        document.getElementById('sp_mrn').innerText = p.mrn;
        document.getElementById('selected_patient_box').classList.remove('d-none');
        pResults.classList.add('d-none');
        pSearch.value = '';
        pSearch.disabled = true;
    }

    function clearPatient() {
        document.getElementById('selected_patient_id').value = '';
        document.getElementById('selected_patient_box').classList.add('d-none');
        pSearch.disabled = false;
        pSearch.focus();
    }

    // Form Submission
    document.getElementById('therapyBookingForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!document.getElementById('selected_patient_id').value) {
            showToast('Please select a patient first', 'error');
            return;
        }

        const formData = new FormData(e.target);
        Swal.fire({
            title: 'Booking...',
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch('/api/v1/therapy/sessions', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Success!', 'Therapy session booked successfully.', 'success').then(() => {
                    window.location.href = '<?= baseUrl('/therapy/sessions') ?>';
                });
            } else {
                throw new Error(data.message || 'Failed to book session');
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    });

    async function loadProtocols() {
        try {
            const res = await fetch('/api/v1/therapy/protocols');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('protocolSelect');
                select.innerHTML = '<option value="">Select Protocol...</option>';
                data.data.protocols.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.protocol_id;
                    opt.innerText = p.name;
                    select.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Error loading protocols:', e);
        }
    }

    async function loadPractitioners() {
        try {
            const res = await fetch('/api/v1/staff?role=Therapist');
            const data = await res.json();
            const select = document.querySelector('select[name="practitioner_id"]');
            select.innerHTML = '<option value="">Select Staff...</option>';
            if (data.success) {
                data.data.staff.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.staff_id;
                    opt.innerText = s.full_name;
                    select.appendChild(opt);
                });
            }
        } catch (e) { console.error('Error loading staff:', e); }
    }

    // Initialize
    loadProtocols();
    loadPractitioners();

    // Close results when clicking outside
    document.addEventListener('click', (e) => {
        if (!pSearch.contains(e.target) && !pResults.contains(e.target)) {
            pResults.classList.add('d-none');
        }
    });
</script>

<style>
    .search-dropdown {
        position: absolute;
        z-index: 1000;
        background: #fff;
        border: 1px solid #eef1f6;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
    }

    .search-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
    }

    .search-item:hover {
        background: #f1f6ff;
    }
</style>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>