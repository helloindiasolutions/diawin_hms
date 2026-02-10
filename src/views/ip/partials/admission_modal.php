<!-- IP Admission Modal -->
<div class="modal fade" id="ipAdmissionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-bottom bg-white py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-primary-transparent text-primary rounded-circle">
                        <i class="ri-hospital-line fs-20"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">IPD ADMISSION</h5>
                        <p class="mb-0 text-muted fs-12 fw-medium">IN-PATIENT ENTRY FLOW</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light-alt">


                <div class="tab-content px-4 py-4 bg-white">
                    <!-- Section 1: Existing Patient -->
                    <div id="section_existing">
                        <form id="ipAdmissionForm" class="needs-validation" novalidate>
                            <input type="hidden" name="patient_id" id="ip_adm_patient_id">
                            <input type="hidden" name="visit_id" id="ip_adm_visit_id">

                            <div class="mb-4">
                                <label
                                    class="form-label fw-bold text-muted fs-11 text-uppercase mb-3 letter-spacing-1">1.
                                    Patient Selection</label>
                                <div class="patient-search-box">
                                    <div id="ip_adm_patient_display"
                                        class="d-none animate__animated animate__fadeIn p-3 bg-soft-primary rounded-3 border border-primary border-dashed mb-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar avatar-md bg-white text-primary rounded-circle fw-bold shadow-sm border border-primary-light"
                                                    id="ip_adm_p_initials">P</div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold fs-15 text-dark" id="ip_adm_p_name">---</h6>
                                                    <span class="fs-12 text-muted fw-medium d-block"
                                                        id="ip_adm_p_meta">---</span>
                                                </div>
                                            </div>
                                            <button type="button"
                                                class="btn btn-sm btn-white text-danger px-3 py-1 rounded-pill fw-bold fs-11 shadow-sm border hover-lift"
                                                onclick="IPAdmission.changePatient()">CHANGE</button>
                                        </div>
                                    </div>
                                    <div id="ip_adm_search_box" class="position-relative">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i
                                                    class="ri-search-2-line fs-18"></i></span>
                                            <input type="text"
                                                class="form-control bg-white border-start-0 ps-2 fs-15 py-3"
                                                id="ip_adm_patient_search"
                                                placeholder="Type Name, MRN or Phone number..." autocomplete="off">
                                        </div>
                                        <div id="ip_adm_search_results" class="search-results-overlay d-none"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-2">
                                <div class="col-md-12">
                                    <label
                                        class="form-label fw-bold text-muted fs-11 text-uppercase mb-3 mt-2 letter-spacing-1">2.
                                        Logistics & Allocation</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select bg-light-input" name="admission_type"
                                            id="ip_adm_type" required>
                                            <option value="Planned">Planned Admission</option>
                                            <option value="Emergency">Emergency Entry</option>
                                            <option value="Transfer">External Transfer</option>
                                            <option value="Referral">OPD Referral</option>
                                        </select>
                                        <label class="text-muted fs-12">Admission Type</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="datetime-local" class="form-control bg-light-input"
                                            name="admission_date" id="ip_adm_date" required>
                                        <label class="text-muted fs-12">Date & Time</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select bg-light-input" name="primary_doctor_id"
                                            id="ip_adm_doctor" required>
                                            <option value="">Select Doctor</option>
                                        </select>
                                        <label class="text-muted fs-12">Attending Doctor</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select bg-light-input" name="ward_id" id="ip_adm_ward"
                                            required>
                                            <option value="">Select Ward</option>
                                        </select>
                                        <label class="text-muted fs-12">Ward / Unit</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select bg-light-input" name="bed_id" id="ip_adm_bed"
                                            required disabled>
                                            <option value="">Select Bed</option>
                                        </select>
                                        <label class="text-muted fs-12">Bed Number</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control bg-light-input" name="department"
                                            value="General Medicine" placeholder="Dept">
                                        <label class="text-muted fs-12">Department</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <textarea class="form-control bg-light-input" name="admission_reason"
                                            style="height: 100px" placeholder="Reason"></textarea>
                                        <label class="text-muted fs-12">Provisional Diagnosis / Reason</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>


                </div>
            </div>
            <div class="modal-footer bg-white border-top p-3 justify-content-between">
                <button type="button" class="btn btn-light fw-medium text-muted fs-13"
                    data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4 fw-semibold shadow-sm" id="btn_confirm_admission"
                    onclick="IPAdmission.submit()">
                    <i class="ri-check-line me-1"></i> Confirm Admission
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Clean Modal Styles */
    #ipAdmissionModal .modal-content {
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
    }

    #ipAdmissionModal .form-control,
    #ipAdmissionModal .form-select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        height: 52px;
        transition: all 0.2s;
    }

    #ipAdmissionModal .bg-light-input {
        background-color: #f8fafc;
    }

    #ipAdmissionModal .form-control:focus,
    #ipAdmissionModal .form-select:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.08) !important;
    }

    #ipAdmissionModal .form-floating>label {
        padding-top: 0.9rem;
    }

    #ipAdmissionModal textarea.form-control {
        height: auto !important;
        min-height: 80px;
    }

    /* Tab Styling */
    .nav-custom-light .nav-link {
        color: #64748b;
        transition: all 0.2s;
        font-size: 14px;
        letter-spacing: 0.3px;
    }

    .nav-custom-light .nav-link:hover {
        color: var(--primary-color);
    }

    .nav-custom-light .nav-link.active {
        color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    .border-transparent {
        border-color: transparent !important;
    }

    /* Search Results */
    .search-results-overlay {
        position: absolute;
        top: 105%;
        left: 0;
        right: 0;
        z-index: 1060;
        background: white;
        max-height: 280px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        text-align: left;
    }

    .search-result-item {
        padding: 12px 18px;
        border-bottom: 1px solid #f8fafc;
        cursor: pointer;
        transition: background 0.15s;
        display: flex;
        flex-direction: column;
        text-align: left;
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .search-result-item:hover,
    .search-result-item.selected {
        background-color: #f1f5f9;
        padding-left: 22px;
    }

    .search-result-item .p-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .search-result-item .p-meta {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
    }

    .bg-light-alt {
        background-color: #ffffff;
    }

    .letter-spacing-1 {
        letter-spacing: 0.5px;
    }

    .bg-soft-primary {
        background-color: rgba(var(--primary-rgb), 0.04) !important;
    }

    .border-primary-light {
        border-color: rgba(var(--primary-rgb), 0.2) !important;
    }

    .bg-soft-info {
        background-color: rgba(var(--info-rgb), 0.08) !important;
    }

    .hover-lift:hover {
        transform: translateY(-1px);
    }

    /* Scrollbar for search results */
    .search-results-overlay::-webkit-scrollbar {
        width: 6px;
    }

    .search-results-overlay::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .search-results-overlay::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
</style>

<script>
    window.IPAdmission = {
        modal: null,
        searchTimeout: null,
        selectedIndex: -1,
        currentResults: [],

        init() {
            const modalEl = document.getElementById('ipAdmissionModal');
            if (!modalEl) return;
            this.modal = new bootstrap.Modal(modalEl);

            this.setupListeners();
            this.loadDoctors();
            this.loadWards();

            // Set default date to now
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('ip_adm_date').value = now.toISOString().slice(0, 16);
        },



        open(config = {}) {
            if (!this.modal) this.init();

            if (!this.modal) {
                console.error('Modal element not found');
                return;
            }

            this.reset();
            this.modal.show();

            if (config.patient) {
                this.selectPatient({
                    patient_id: config.patient.patient_id,
                    full_name: config.patient.full_name,
                    mrn: config.patient.mrn,
                    gender: config.patient.gender,
                    age: config.patient.age
                });
            }

            if (config.type) {
                document.getElementById('ip_adm_type').value = config.type;
            }

            if (config.visit_id) {
                document.getElementById('ip_adm_visit_id').value = config.visit_id;
            }
        },

        reset() {
            const form = document.getElementById('ipAdmissionForm');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
            }

            this.changePatient();

            const bedSelect = document.getElementById('ip_adm_bed');
            if (bedSelect) {
                bedSelect.disabled = true;
                bedSelect.innerHTML = '<option value="">Select Bed</option>';
            }

            const dateInput = document.getElementById('ip_adm_date');
            if (dateInput) {
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                dateInput.value = now.toISOString().slice(0, 16);
            }
        },

        setupListeners() {
            const searchInput = document.getElementById('ip_adm_patient_search');
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                const query = e.target.value.trim();
                if (query.length < 2) {
                    document.getElementById('ip_adm_search_results').classList.add('d-none');
                    return;
                }
                this.searchTimeout = setTimeout(() => this.searchPatients(query), 300);
            });

            searchInput.addEventListener('keydown', (e) => this.handleSearchKeydown(e));

            document.getElementById('ip_adm_ward').addEventListener('change', (e) => {
                this.loadBeds(e.target.value);
            });
        },

        async searchPatients(query) {
            try {
                const res = await fetch(`/api/v1/patients/search?q=${encodeURIComponent(query)}`);
                const data = await res.json();
                const resultsBox = document.getElementById('ip_adm_search_results');
                resultsBox.innerHTML = '';

                // Handle both array and object wrapper formats safely
                const patients = Array.isArray(data.data) ? data.data : (data.data?.patients || []);
                this.currentResults = patients;
                this.selectedIndex = -1;

                if (data.success && patients.length > 0) {
                    patients.forEach((p, index) => {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.dataset.index = index;
                        item.id = `search_result_${index}`;
                        item.innerHTML = `
                        <div class="p-name">${p.full_name || p.name}</div>
                        <div class="p-meta">MRN: ${p.mrn || 'N/A'} | Mob: ${p.primary_mobile || p.mobile || 'N/A'} | ${p.gender}, ${p.age}Y</div>
                    `;
                        item.onclick = () => this.selectPatient(p);
                        resultsBox.appendChild(item);
                    });
                    resultsBox.classList.remove('d-none');
                } else {
                    this.currentResults = [];
                    resultsBox.innerHTML = '<div class="p-5 text-muted text-center fs-12"><i class="ri-user-unfollow-line fs-32 mb-2 d-block opacity-25"></i>No patient records match</div>';
                    resultsBox.classList.remove('d-none');
                }
            } catch (e) {
                console.error('Search failed', e);
            }
        },

        handleSearchKeydown(e) {
            if (this.currentResults.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.selectedIndex++;
                if (this.selectedIndex >= this.currentResults.length) {
                    this.selectedIndex = this.currentResults.length - 1;
                }
                this.updateSelection();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.selectedIndex--;
                if (this.selectedIndex < 0) {
                    this.selectedIndex = 0;
                }
                this.updateSelection();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.selectedIndex > -1 && this.currentResults[this.selectedIndex]) {
                    this.selectPatient(this.currentResults[this.selectedIndex]);
                }
            }
        },

        updateSelection() {
            const items = document.querySelectorAll('.search-result-item');
            items.forEach((item, index) => {
                if (index === this.selectedIndex) {
                    item.classList.add('selected');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('selected');
                }
            });
        },

        selectPatient(p) {
            document.getElementById('ip_adm_patient_id').value = p.patient_id || p.id;
            document.getElementById('ip_adm_p_name').innerText = p.full_name || p.name;
            document.getElementById('ip_adm_p_meta').innerText = `MRN: ${p.mrn || 'N/A'} | ${p.gender} | ${p.age} Years`;
            const name = p.full_name || p.name || 'P';
            document.getElementById('ip_adm_p_initials').innerText = name[0].toUpperCase();

            document.getElementById('ip_adm_search_box').classList.add('d-none');
            document.getElementById('ip_adm_patient_display').classList.remove('d-none');
            document.getElementById('ip_adm_search_results').classList.add('d-none');
        },

        changePatient() {
            document.getElementById('ip_adm_patient_id').value = '';
            document.getElementById('ip_adm_search_box').classList.remove('d-none');
            document.getElementById('ip_adm_patient_display').classList.add('d-none');
            document.getElementById('ip_adm_patient_search').value = '';
            document.getElementById('ip_adm_patient_search').focus();
        },

        async loadDoctors() {
            try {
                const res = await fetch('/api/v1/appointments/providers');
                const data = await res.json();
                const selectExisting = document.getElementById('ip_adm_doctor');

                const providers = Array.isArray(data.data) ? data.data : (data.data?.providers || []);

                if (data.success && providers.length > 0) {
                    providers.forEach(d => {
                        const opt = new Option(`Dr. ${d.full_name} (${d.specialization || 'Clinical'})`, d.provider_id);
                        selectExisting.add(opt);
                    });
                }
            } catch (e) {
                console.error('Failed to load doctors', e);
            }
        },

        async loadWards() {
            try {
                const res = await fetch('/api/v1/ipd/wards');
                const data = await res.json();
                const selectExisting = document.getElementById('ip_adm_ward');

                const wards = Array.isArray(data.data) ? data.data : (data.data?.wards || []);

                if (data.success) {
                    wards.forEach(w => {
                        const opt = new Option(`${w.ward_name} (${w.available_beds}/${w.total_beds} Avail)`, w.ward_id);
                        opt.disabled = w.available_beds === 0;
                        selectExisting.add(opt);
                    });
                }
            } catch (e) {
                console.error('Failed to load wards', e);
            }
        },

        async loadBeds(wardId) {
            const select = document.getElementById('ip_adm_bed');
            if (!wardId) {
                select.disabled = true;
                select.innerHTML = '<option value="">Select Bed</option>';
                return;
            }

            try {
                const res = await fetch(`/api/v1/ipd/beds?ward_id=${wardId}&status=Available`);
                const data = await res.json();
                select.innerHTML = '<option value="">Select Bed</option>';
                if (data.success && data.data.beds.length > 0) {
                    data.data.beds.forEach(b => {
                        const opt = new Option(`Bed: ${b.bed_number} (${b.bed_type})`, b.bed_id);
                        select.add(opt);
                    });
                    select.disabled = false;
                } else {
                    select.innerHTML = '<option value="">No beds available</option>';
                    select.disabled = true;
                }
            } catch (e) {
                console.error('Failed to load beds', e);
            }
        },

        async submit() {
            const form = document.getElementById('ipAdmissionForm');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const btn = document.getElementById('btn_confirm_admission');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>PROCESSING...';

            const formData = new FormData(form);
            const json = Object.fromEntries(formData.entries());

            if (json.visit_id === '') delete json.visit_id;

            try {
                const res = await fetch('/api/v1/ipd/admissions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(json)
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ADMITTED SUCCESSFULLY',
                        html: `Admission ID: <b>${data.data.admission_number}</b><br>MRN: <b>${data.data.mrn || ''}</b>`,
                        timer: 2500,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });
                    this.modal.hide();
                    setTimeout(() => window.location.href = `/ip/admission-details?admission_id=${data.data.admission_id}`, 800);
                } else {
                    Swal.fire('Error', data.message || 'Admission failed', 'error');
                }
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'Communication failure', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    };

    // Initialize modal on load or immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => IPAdmission.init());
    } else {
        IPAdmission.init();
    }
</script>