<!-- Global Quick Appointment Modal -->
<div class="modal fade" id="quickAptModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;">
        <div class="modal-content shadow-lg border-0 overflow-hidden">
            <div class="modal-header bg-success-gradient text-fixed-white p-3 border-bottom-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-container">
                        <i class="ri-add-circle-fill fs-24"></i>
                    </div>
                    <div>
                        <h6 class="modal-title border-0 text-fixed-white fw-bold mb-0">QUICK TOKEN / APPOINTMENT</h6>
                        <span class="fs-10 opacity-75 text-uppercase fw-semibold">Express Registration System</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-50" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                <!-- Step Progress -->
                <div
                    class="step-progress-wrapper bg-white px-4 py-3 d-flex justify-content-center align-items-center gap-5 border-bottom">
                    <div class="step-item active" id="qaStep1">
                        <div class="step-dot" id="qaStep1Dot">1</div>
                        <span class="step-label">Search</span>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step-item" id="qaStep2">
                        <div class="step-dot" id="qaStep2Dot">2</div>
                        <span class="step-label">Select</span>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step-item" id="qaStep3">
                        <div class="step-dot" id="qaStep3Dot">3</div>
                        <span class="step-label">Confirm</span>
                    </div>
                </div>

                <div class="p-3 bg-light-transparent">
                    <!-- Stage 1: Mobile Lookup -->
                    <div id="qaStage1">
                        <div class="text-center mb-5 mt-2">
                            <h4 class="fw-bold mb-2">Find Your Patient</h4>
                            <p class="text-muted fs-14">Enter the 10-digit mobile number to begin fast registration.</p>
                        </div>
                        <div class="mx-auto" style="max-width: 450px;">
                            <div class="mobile-search-wrapper shadow-lg">
                                <span class="search-icon"><i class="ri-phone-fill text-success"></i></span>
                                <input type="text" class="form-control" id="qaMobileInput"
                                    placeholder="Enter Mobile Number..." maxlength="10">
                                <button class="btn btn-success" id="qaMobileSearchBtn">
                                    <span>Search</span>
                                    <i class="ri-search-2-line ms-2"></i>
                                </button>
                            </div>
                            <div id="qaMobileError" class="text-danger fs-12 mt-3 text-center fw-semibold d-none"></div>
                        </div>
                    </div>

                    <!-- Stage 2: Patient Selection -->
                    <div id="qaStage2" class="d-none animate__animated animate__fadeIn">
                        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                            <div>
                                <h6 class="fw-bold mb-1">Select Patient</h6>
                                <p class="text-muted fs-11 mb-0">Found multiple family members under this mobile</p>
                            </div>
                            <button class="btn btn-sm btn-light text-primary fw-bold fs-11"
                                onclick="QuickApt.goToShowStep(1)">
                                <i class="ri-arrow-left-line me-1"></i>BACK
                            </button>
                        </div>
                        <div class="row g-3" id="qaPatientList">
                            <!-- JS rendered list -->
                        </div>
                        <div class="mt-4 text-center">
                            <a href="<?= baseUrl('/registrations/create') ?>" data-bs-dismiss="modal"
                                class="btn btn-outline-primary btn-sm border-dashed px-4 py-2 rounded-3">
                                <i class="ri-user-add-line me-2"></i>New Patient? Register Here
                            </a>
                        </div>
                    </div>

                    <!-- Stage 3: Appointment Details -->
                    <div id="qaStage3" class="d-none animate__animated animate__fadeIn">
                        <form id="qaAptForm" class="needs-validation" novalidate>
                            <input type="hidden" name="patient_id" id="qa_selected_patient_id">

                            <div class="row g-4">
                                <!-- Left Column: Form -->
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                                        <label
                                            class="form-label fw-bold fs-11 text-uppercase text-muted ls-1 mb-0">Patient
                                            Information</label>
                                        <button type="button"
                                            class="btn btn-sm btn-link text-primary p-0 h-auto fw-bold fs-11 text-uppercase ls-1"
                                            onclick="QuickApt.goToShowStep(2)">
                                            <i class="ri-arrow-left-line me-1"></i> Change Patient
                                        </button>
                                    </div>
                                    <div class="picked-patient-premium-card mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-container shadow-sm" id="qa_picked_initials_bg">
                                                <span id="qa_picked_initials">?</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1 fw-bold text-dark" id="qa_picked_name">---</h5>
                                                <div class="fs-12 text-muted fw-medium d-flex align-items-center gap-2">
                                                    <span class="badge bg-success-transparent text-success"
                                                        id="qa_picked_mrn">MRN260001</span>
                                                    <span class="text-separator">•</span>
                                                    <span id="qa_picked_meta">24Y • Male</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label
                                                class="form-label fw-extrabold fs-11 text-uppercase text-muted ls-1">Consulting
                                                Doctor</label>
                                            <select class="form-select custom-select-lg" name="provider_id"
                                                id="qaAptProvider" required>
                                                <option value="">Select Doctor</option>
                                            </select>
                                            <div id="qaDoctorSuggestion"
                                                class="fs-11 text-success mt-2 d-none animate__animated animate__fadeIn">
                                                <i class="ri-magic-line me-1"></i>Suggested based on medical history
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-extrabold fs-11 text-uppercase text-muted ls-1">Appointment
                                                Date</label>
                                            <div class="datepicker-container">
                                                <i class="ri-calendar-event-line field-icon"></i>
                                                <input type="text" class="form-control" id="qaAptDate" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-extrabold fs-11 text-uppercase text-muted ls-1">Arrival
                                                Time</label>
                                            <div class="datepicker-container">
                                                <i class="ri-time-line field-icon"></i>
                                                <input type="text" class="form-control" id="qaAptTime" required>
                                            </div>
                                            <!-- Quick Time Buttons -->
                                            <div class="d-flex gap-1 mt-2">
                                                <button type="button"
                                                    class="btn btn-sm btn-light fs-10 fw-bold py-1 px-2 border text-success"
                                                    onclick="QuickApt.setQuickTime('now')">NOW</button>
                                                <button type="button"
                                                    class="btn btn-sm btn-light fs-10 fw-bold py-1 px-2 border"
                                                    onclick="QuickApt.setQuickTime(15)">+15m</button>
                                                <button type="button"
                                                    class="btn btn-sm btn-light fs-10 fw-bold py-1 px-2 border"
                                                    onclick="QuickApt.setQuickTime(30)">+30m</button>
                                                <button type="button"
                                                    class="btn btn-sm btn-light fs-10 fw-bold py-1 px-2 border"
                                                    onclick="QuickApt.setQuickTime(60)">+1h</button>
                                            </div>
                                            <!-- Combined Date-Time for API -->
                                            <input type="hidden" name="scheduled_at" id="qaAptDateTimeCombined">
                                        </div>
                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-extrabold fs-11 text-uppercase text-muted ls-1">Traffic
                                                Source</label>
                                            <select class="form-select custom-select-lg" name="source">
                                                <option value="in-person">Walk-in Visit</option>
                                                <option value="phone">Telephonic</option>
                                                <option value="whatsapp">WhatsApp Order</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Medical History -->
                                <div class="col-md-5">
                                    <div class="medical-history-premium-panel">
                                        <div class="panel-header">
                                            <i class="ri-history-line text-primary"></i>
                                            <span>RECENT CLINICAL DATA</span>
                                        </div>
                                        <div class="panel-body" id="qaMedicalHistoryContent">
                                            <div class="text-center py-5 opacity-50">
                                                <div class="spinner-grow text-success spinner-grow-sm mb-3"></div>
                                                <p class="fs-11 fw-bold text-uppercase ls-1">Scanning Records...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-top-0 p-3 d-none shadow-premium-up" id="qaModalFooter">
                <div class="d-flex gap-2 w-100 align-items-center justify-content-between">
                    <!-- Cancel Button -->
                    <button type="button" 
                        class="btn btn-light d-flex align-items-center gap-2 px-3 py-2 fw-semibold"
                        data-bs-dismiss="modal"
                        style="white-space: nowrap; min-width: 110px;">
                        <i class="ri-close-line fs-16"></i>
                        <span>Cancel</span>
                        <kbd class="ms-1" style="font-size: 9px; padding: 2px 6px; background: #e9ecef; border: 1px solid #dee2e6; border-radius: 3px;">ESC</kbd>
                    </button>

                    <!-- Action Buttons Group -->
                    <div class="d-flex gap-2 flex-grow-1">
                        <!-- Token Only Button (Appointment Booking) -->
                        <button type="button"
                            class="btn btn-primary d-flex align-items-center justify-content-center gap-2 px-3 py-2 fw-bold flex-fill"
                            id="qaTokenOnlyBtn"
                            onclick="QuickApt.confirmTokenOnly()"
                            style="white-space: nowrap; box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);">
                            <i class="ri-calendar-line fs-16"></i>
                            <span class="text-uppercase fs-11 ls-1">Token Only</span>
                            <kbd class="ms-1" style="font-size: 9px; padding: 2px 6px; background: rgba(255,255,255,0.9); color: #0d6efd; border: 1px solid rgba(255,255,255,0.3); border-radius: 3px;">F2</kbd>
                        </button>

                        <!-- Token + Arrived Button (Book + Mark as Arrived) -->
                        <button type="button"
                            class="btn btn-success d-flex align-items-center justify-content-center gap-2 px-3 py-2 fw-bold flex-fill"
                            id="qaConfirmBtn"
                            style="white-space: nowrap; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                            <i class="ri-user-received-2-line fs-16"></i>
                            <span class="text-uppercase fs-11 ls-1">Token + Arrived</span>
                            <kbd class="ms-1" style="font-size: 9px; padding: 2px 6px; background: rgba(255,255,255,0.9); color: #10b981; border: 1px solid rgba(255,255,255,0.3); border-radius: 3px;">F3</kbd>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-gradient {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }

    .btn-success-gradient {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: white !important;
        border: none !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-success-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    /* Step Progress Styling */
    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        z-index: 2;
    }

    .step-dot {
        width: 32px;
        height: 32px;
        border-radius: 12px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        transition: all 0.4s ease;
    }

    .step-item.active .step-dot {
        background: #10b981;
        border-color: #10b981;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }

    .step-item.completed .step-dot {
        background: #d1fae5;
        border-color: #10b981;
        color: #059669;
    }

    .step-label {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.5px;
    }

    .step-item.active .step-label {
        color: #10b981;
    }

    .step-connector {
        flex-grow: 1;
        height: 2px;
        background: #e2e8f0;
        margin-top: -18px;
        max-width: 60px;
    }

    /* Mobile Search Design */
    .mobile-search-wrapper {
        display: flex;
        background: white;
        border-radius: 16px;
        padding: 8px;
        align-items: center;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .mobile-search-wrapper:focus-within {
        border-color: #10b981;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.1) !important;
    }

    .mobile-search-wrapper .search-icon {
        padding: 0 15px;
        font-size: 30px;
    }

    .mobile-search-wrapper input {
        border: none !important;
        box-shadow: none !important;
        font-size: 25px;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .mobile-search-wrapper button {
        border-radius: 12px;
        padding: 12px 16px;
        min-width: 120px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    /* Patient Card Styling */
    .qa-patient-card {
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e2e8f0;
        background: white;
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        outline: none;
    }

    .qa-patient-card:hover {
        border-color: #10b981;
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
    }

    .qa-patient-card.selected {
        border-color: #10b981;
        background: #f0fdf4;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);
        transform: translateY(-2px);
    }

    .qa-patient-card.selected::before {
        opacity: 1;
    }

    .qa-patient-card:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .qa-patient-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #10b981;
        opacity: 0;
        transition: 0.3s;
    }

    .qa-patient-card:hover::before {
        opacity: 1;
    }

    /* Medical History Panel */
    .medical-history-premium-panel {
        background: #f8fafc;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .medical-history-premium-panel .panel-header {
        padding: 15px;
        background: white;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .medical-history-premium-panel .panel-body {
        padding: 15px;
        flex-grow: 1;
        overflow-y: auto;
        max-height: 280px;
    }

    /* Form Improvements */
    .custom-select-lg {
        height: 48px;
        border-radius: 12px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }

    .datepicker-container {
        position: relative;
    }

    .datepicker-container .field-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
        z-index: 1;
    }

    .datepicker-container input {
        padding-left: 45px;
        height: 48px;
        border-radius: 12px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }

    .ls-1 {
        letter-spacing: 1px;
    }

    .fw-extrabold {
        font-weight: 800;
    }

    .shadow-premium-up {
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.03);
    }

    .picked-patient-premium-card {
        background: white;
        padding: 15px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .avatar-container {
        width: 50px;
        height: 50px;
        background: #d1fae5;
        color: #059669;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 20px;
    }

    .history-item {
        padding: 12px;
        background: white;
        border-radius: 12px;
        margin-bottom: 8px;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .history-item:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .bg-light-success {
        background-color: #f0fdf4 !important;
    }

    /* Commercial-Grade Time Picker UI */
    .flatpickr-time-only.flatpickr-calendar {
        width: 280px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        margin-top: 5px !important;
    }

    /* Stable Positioning fix for Modals */
    .flatpickr-time-only.static {
        position: absolute !important;
        display: block !important;
    }

    .flatpickr-time-only .flatpickr-time {
        padding: 15px 10px !important;
        height: 100px !important;
        /* Fixed height for stability */
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: transparent !important;
    }

    .flatpickr-time-only .numInputWrapper {
        flex: 1 !important;
        height: 70px !important;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        transition: all 0.2s ease !important;
    }

    .flatpickr-time-only .numInputWrapper:hover {
        border-color: #10b981 !important;
        background: #ffffff !important;
    }

    .flatpickr-time-only .numInputWrapper input {
        font-size: 32px !important;
        font-weight: 800 !important;
        color: #1e293b !important;
        height: 70px !important;
        padding: 0 !important;
    }

    /* Separator Alignment */
    .flatpickr-time-only .flatpickr-time-separator {
        font-size: 24px !important;
        font-weight: 800 !important;
        color: #94a3b8 !important;
        padding: 0 8px !important;
        line-height: 70px !important;
        height: 70px !important;
        display: flex !important;
        align-items: center !important;
    }

    .flatpickr-time-only .flatpickr-am-pm {
        flex: 0 0 70px !important;
        font-weight: 800 !important;
        background: #10b981 !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        height: 70px !important;
        margin-left: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        border: none !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }

    .flatpickr-time-only .flatpickr-am-pm:hover {
        background: #059669 !important;
        transform: scale(1.05) !important;
    }

    /* Touch-Friendly Navigation Arrows */
    .flatpickr-time-only .arrowUp,
    .flatpickr-time-only .arrowDown {
        width: 100% !important;
        height: 35% !important;
        opacity: 0.5 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .flatpickr-time-only .arrowUp:hover,
    .flatpickr-time-only .arrowDown:hover {
        opacity: 1 !important;
        background: rgba(16, 185, 129, 0.1) !important;
    }

    .flatpickr-time-only .arrowUp::after {
        border-bottom-color: #10b981 !important;
        border-width: 6px !important;
    }

    .flatpickr-time-only .arrowDown::after {
        border-top-color: #10b981 !important;
        border-width: 6px !important;
    }
</style>

<script>
    const QuickApt = {
        modal: null,
        currentStep: 1,
        selectedPatient: null,

        init() {
            const modalEl = document.getElementById('quickAptModal');
            if (!modalEl) return;
            this.modal = new bootstrap.Modal(modalEl);
            this.setupListeners();
            this.loadProviders();
            this.initFlatpickr();
        },

        open(patientId = null) {
            this.reset();
            this.modal.show();
            if (patientId) {
                this.loadPatientById(patientId);
            } else {
                setTimeout(() => document.getElementById('qaMobileInput').focus(), 500);
            }
        },

        async loadPatientById(id) {
            try {
                const res = await fetch(`/api/v1/patients/${id}/full`);
                const data = await res.json();
                if (data.success) {
                    const p = data.data.patient;
                    this.selectPatient({
                        patient_id: p.patient_id,
                        full_name: p.full_name,
                        mrn: p.mrn,
                        age: p.age,
                        gender: p.gender
                    });
                }
            } catch (e) {
                console.error('Failed to load patient info', e);
            }
        },

        reset() {
            this.currentStep = 1;
            this.selectedPatient = null;
            document.getElementById('qaAptForm').reset();
            document.getElementById('qaMobileInput').value = '';

            // Reset pickers to current time
            if (this.datePicker) this.datePicker.setDate(new Date());
            if (this.timePicker) this.timePicker.setDate(new Date());
            this.updateCombinedDateTime();

            this.goToShowStep(1);
        },

        setupListeners() {
            // Mobile search on click
            document.getElementById('qaMobileSearchBtn').onclick = () => this.searchMobile();

            // Mobile search on Enter
            document.getElementById('qaMobileInput').onkeydown = (e) => {
                if (e.key === 'Enter') this.searchMobile();
            };

            // Confirm booking
            document.getElementById('qaConfirmBtn').onclick = () => this.submit();

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                const modalEl = document.getElementById('quickAptModal');
                if (!modalEl || !modalEl.classList.contains('show')) return;

                // F2: Token Only
                if (e.key === 'F2') {
                    e.preventDefault();
                    const btn = document.getElementById('qaTokenOnlyBtn');
                    if (btn && !btn.disabled) {
                        QuickApt.confirmTokenOnly();
                    }
                }

                // F3: Token + Appointment
                if (e.key === 'F3') {
                    e.preventDefault();
                    const btn = document.getElementById('qaConfirmBtn');
                    if (btn && !btn.disabled) {
                        btn.click();
                    }
                }

                // ESC is already handled by Bootstrap modal
            });
        },

        async searchMobile() {
            const mobile = document.getElementById('qaMobileInput').value.trim();
            if (mobile.length !== 10) {
                this.showError('Please enter a valid 10-digit mobile number');
                return;
            }

            const btn = document.getElementById('qaMobileSearchBtn');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const res = await fetch(`/api/v1/patients/family-lookup?mobile=${mobile}`);
                const data = await res.json();

                if (data.success && data.data.families.length > 0) {
                    this.renderPatients(data.data.families[0].members);
                    this.goToShowStep(2);
                } else if (data.success) {
                    this.showError('No patient found. Redirecting to registration...');
                    setTimeout(() => window.location.href = `/registrations/create?mobile=${mobile}`, 1500);
                } else {
                    this.showError(data.message || 'Error occurred');
                }
            } catch (e) {
                this.showError('Connection error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        },

        renderPatients(members) {
            const container = document.getElementById('qaPatientList');
            container.innerHTML = members.map((m, index) => `
                <div class="col-md-6">
                    <div class="qa-patient-card p-3 rounded-4 bg-white h-100 ${index === 0 ? 'selected' : ''}" 
                         data-patient-index="${index}"
                         data-patient-data='${JSON.stringify(m)}'
                         tabindex="0"
                         onclick="QuickApt.selectPatient(${JSON.stringify(m).replace(/"/g, '&quot;')})">
                        <div class="d-flex align-items-center gap-3">
                            <span class="avatar-container ${m.gender === 'male' ? 'bg-primary-transparent text-primary' : 'bg-pink-transparent text-pink'}">
                                ${this.getInitials(m.full_name)}
                            </span>
                            <div class="flex-grow-1">
                                <div class="fw-bold fs-13 text-uppercase text-dark">${m.full_name}</div>
                                <div class="fs-10 text-muted fw-semibold">${m.mrn} • ${m.age}Y • ${m.gender}</div>
                            </div>
                            <i class="ri-arrow-right-s-line text-muted"></i>
                        </div>
                    </div>
                </div>
            `).join('');

            // Setup keyboard navigation
            this.setupPatientCardNavigation();
        },

        setupPatientCardNavigation() {
            const cards = document.querySelectorAll('.qa-patient-card');
            if (cards.length === 0) return;

            // Focus first card
            cards[0].focus();

            // Add keyboard event listeners
            cards.forEach((card, index) => {
                card.addEventListener('keydown', (e) => {
                    let targetIndex = index;

                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        targetIndex = (index + 1) % cards.length;
                    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        targetIndex = (index - 1 + cards.length) % cards.length;
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const patientData = JSON.parse(card.dataset.patientData);
                        QuickApt.selectPatient(patientData);
                        return;
                    } else {
                        return;
                    }

                    // Remove selection from all cards
                    cards.forEach(c => c.classList.remove('selected'));
                    
                    // Add selection to target card
                    cards[targetIndex].classList.add('selected');
                    cards[targetIndex].focus();
                });
            });
        },

        async selectPatient(patient) {
            this.selectedPatient = patient;
            document.getElementById('qa_selected_patient_id').value = patient.patient_id;
            document.getElementById('qa_picked_name').textContent = patient.full_name;
            document.getElementById('qa_picked_mrn').textContent = patient.mrn;
            document.getElementById('qa_picked_meta').textContent = `${patient.age}Y • ${patient.gender}`;
            document.getElementById('qa_picked_initials').textContent = this.getInitials(patient.full_name);

            this.goToShowStep(3);
            this.fetchMedicalSummary(patient.patient_id);
        },

        async fetchMedicalSummary(id) {
            const container = document.getElementById('qaMedicalHistoryContent');
            container.innerHTML = '<div class="text-center py-5"><div class="spinner-grow text-success spinner-grow-sm"></div><p class="fs-11 fw-bold text-uppercase mt-2 ls-1">Retrieving Records...</p></div>';

            try {
                const res = await fetch(`/api/v1/patients/${id}/medical-summary`);
                const data = await res.json();

                if (data.success) {
                    this.renderMedicalSummary(data.data);
                    // Suggest doctor
                    if (data.data.last_provider) {
                        const providerId = data.data.last_provider.provider_id;
                        document.getElementById('qaAptProvider').value = providerId;
                        document.getElementById('qaDoctorSuggestion').classList.remove('d-none');
                    } else {
                        document.getElementById('qaDoctorSuggestion').classList.add('d-none');
                    }
                }
            } catch (e) {
                container.innerHTML = '<p class="text-danger fs-11 text-center">Failed to load history</p>';
            }
        },

        renderMedicalSummary(data) {
            const container = document.getElementById('qaMedicalHistoryContent');
            let html = '';

            // Show Last Visit Date at the top
            if (data.summary.last_visit_date) {
                html += `
                    <div class="mb-3 p-2 bg-light rounded-2 border">
                        <div class="fs-10 fw-bold text-muted text-uppercase mb-1">Last Visit</div>
                        <div class="fs-12 fw-semibold text-dark">
                            <i class="ri-calendar-line me-1 text-primary"></i>${new Date(data.summary.last_visit_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}
                        </div>
                    </div>
                `;
            }

            // Vitals
            if (data.summary.vitals) {
                const v = data.summary.vitals;
                html += `
                    <div class="mb-2">
                        <label class="fs-10 fw-bold text-muted text-uppercase mb-2 ls-1">Latest Vitals</label>
                        <div class="d-flex flex-wrap gap-2 text-dark fs-11">
                            <span class="badge bg-light text-dark border fw-semibold px-2 py-1 rounded-pill">WT: ${v.weight}kg</span>
                            <span class="badge bg-light text-dark border fw-semibold px-2 py-1 rounded-pill">BP: ${v.bp_systolic}/${v.bp_diastolic}</span>
                            <span class="badge bg-light text-dark border fw-semibold px-2 py-1 rounded-pill">HR: ${v.heart_rate}</span>
                        </div>
                    </div>
                `;
            }

            // Diagnoses
            if (data.summary.diagnoses && data.summary.diagnoses.length > 0) {
                html += `
                    <div class="mb-3">
                        <label class="fs-10 fw-bold text-muted text-uppercase mb-2 ls-1">Past Diagnoses</label>
                        ${data.summary.diagnoses.map(d => `
                            <div class="history-item fs-12 shadow-sm">
                                <div class="fw-bold">${d.diagnosis_name}</div>
                                <div class="fs-10 text-muted mt-1 fw-medium"><i class="ri-calendar-line me-1"></i>${new Date(d.date).toLocaleDateString()}</div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            // Prescriptions
            if (data.summary.prescriptions && data.summary.prescriptions.length > 0) {
                html += `
                    <div class="mb-2">
                        <label class="fs-10 fw-bold text-muted text-uppercase mb-2 ls-1">Recent Medications</label>
                        <div class="d-flex flex-column gap-2">
                            ${data.summary.prescriptions.map(p => `
                                <div class="d-flex align-items-center p-2 bg-light-success rounded-2 border border-success border-opacity-25">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-success fs-13">${p.meds}</div>
                                        <div class="fs-10 text-muted mt-1"><i class="ri-calendar-line me-1"></i>${new Date(p.date).toLocaleDateString()}</div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            // Allergies
            if (data.summary.allergies && data.summary.allergies.length > 0) {
                html += `
                    <div class="mb-0">
                        <label class="fs-10 fw-bold text-danger text-uppercase mb-2 ls-1">Critical Allergies</label>
                        <div class="d-flex flex-wrap gap-1">
                            ${data.summary.allergies.map(a => `<span class="badge bg-danger text-white rounded-3 px-3 py-2 shadow-sm">${a.allergen}</span>`).join('')}
                        </div>
                    </div>
                `;
            }

            if (!html) html = '<div class="text-center py-5 text-muted opacity-50"><i class="ri-information-line fs-24 d-block mb-2"></i><p class="fs-11 fw-bold text-uppercase ls-1">No Clinical Records Found</p></div>';
            container.innerHTML = html;
        },

        goToShowStep(step) {
            this.currentStep = step;
            // Hide all stages
            document.getElementById('qaStage1').classList.add('d-none');
            document.getElementById('qaStage2').classList.add('d-none');
            document.getElementById('qaStage3').classList.add('d-none');
            document.getElementById('qaModalFooter').classList.add('d-none');

            // Show target stage
            document.getElementById('qaStage' + step).classList.remove('d-none');

            // Update dots
            for (let i = 1; i <= 3; i++) {
                const dot = document.getElementById(`qaStep${i}Dot`);
                const item = document.getElementById(`qaStep${i}`);
                if (dot && item) {
                    item.classList.remove('active', 'completed');
                    dot.classList.remove('active', 'completed');
                    if (i < step) {
                        item.classList.add('completed');
                        dot.classList.add('completed');
                        dot.innerHTML = '<i class="ri-check-line fw-bold"></i>';
                    } else if (i === step) {
                        item.classList.add('active');
                        dot.classList.add('active');
                        dot.innerHTML = i;
                    } else {
                        dot.innerHTML = i;
                    }
                }
            }

            if (step === 3) {
                document.getElementById('qaModalFooter').classList.remove('d-none');
            }
        },

        async loadProviders() {
            try {
                const res = await fetch('/api/v1/appointments/providers');
                const data = await res.json();
                if (data.success) {
                    const select = document.getElementById('qaAptProvider');
                    // Prevent duplicate appending - Clear options except the placeholder
                    select.innerHTML = '<option value="">Select Doctor</option>';
                    data.data.providers.forEach(p => {
                        select.add(new Option(`${p.full_name} (${p.specialization || 'Clinical'})`, p.provider_id));
                    });
                }
            } catch (e) { }
        },

        initFlatpickr() {
            const self = this;

            // Appointment Date Picker (No Time)
            this.datePicker = flatpickr("#qaAptDate", {
                dateFormat: "Y-m-d",
                defaultDate: new Date(),
                minDate: "today",
                disableMobile: "true",
                onChange: () => self.updateCombinedDateTime(),
                onReady: (sd, ds, inst) => {
                    inst.calendarContainer.style.zIndex = "9999";
                    inst.calendarContainer.classList.add('shadow-lg', 'border-0', 'rounded-4');
                }
            });

            // Arrival Time Picker (Pro Commercial Grade UI)
            this.timePicker = flatpickr("#qaAptTime", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K",
                defaultDate: new Date(),
                time_24hr: false,
                minuteIncrement: 5,
                disableMobile: "true",
                static: true, // Crucial for modal stability
                allowInput: true,
                onChange: () => self.updateCombinedDateTime(),
                onOpen: (sd, ds, inst) => {
                    inst.calendarContainer.classList.add('flatpickr-time-only');
                }
            });

            this.updateCombinedDateTime();
        },

        updateCombinedDateTime() {
            const date = document.getElementById('qaAptDate').value;
            const timeRaw = document.getElementById('qaAptTime').value;

            if (date && timeRaw) {
                const parts = timeRaw.split(' ');
                const time = parts[0];
                const modifier = parts[1];
                let [hours, minutes] = time.split(':');
                if (modifier === 'PM' && hours !== '12') hours = parseInt(hours, 10) + 12;
                if (modifier === 'AM' && hours === '12') hours = '00';

                const time24 = `${hours.toString().padStart(2, '0')}:${minutes}`;
                document.getElementById('qaAptDateTimeCombined').value = `${date} ${time24}`;
            }
        },

        setQuickTime(minutesToAdd) {
            let newTime = new Date();
            if (minutesToAdd !== 'now') {
                newTime.setMinutes(newTime.getMinutes() + minutesToAdd);
            }

            if (this.timePicker) {
                this.timePicker.setDate(newTime);
                this.updateCombinedDateTime();

                // Visual feedback
                const input = document.getElementById('qaAptTime');
                input.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => input.classList.remove('animate__animated', 'animate__pulse'), 500);
            }
        },

        showError(msg) {
            const el = document.getElementById('qaMobileError');
            el.textContent = msg;
            el.classList.remove('d-none');
            setTimeout(() => el.classList.add('d-none'), 3000);
        },

        getInitials(name) {
            if (!name) return '?';
            return name.split(' ').filter(n => n).map(n => n[0]).join('').toUpperCase().substring(0, 2);
        },

        async confirmTokenOnly() {
            const formData = new FormData(document.getElementById('qaForm'));
            const payload = {};
            formData.forEach((v, k) => payload[k] = v);

            if (!payload.provider_id) {
                window.showToast('Please select a doctor', 'warning');
                return;
            }

            // Show loading state
            const btn = event.target;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating Token...';

            try {
                const res = await fetch('/api/v1/visits', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        patient_id: payload.patient_id,
                        provider_id: payload.provider_id,
                        visit_type: 'walk-in',
                        status: 'waiting',
                        notes: 'Quick Token - Walk-in'
                    })
                });

                const data = await res.json();
                if (data.success) {
                    window.showToast('Token Generated Successfully!', 'success');
                    this.modal.hide();
                    if (window.location.pathname.includes('/visits') || window.location.pathname.includes('/front-office')) {
                        if (typeof reloadData === 'function') reloadData();
                    }
                } else {
                    window.showToast(data.message || 'Token generation failed', 'error');
                }
            } catch (e) {
                window.showToast('Network error', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        },

        async submit() {
            const form = document.getElementById('qaAptForm');
            const formData = new FormData(form);
            const payload = {};
            formData.forEach((v, k) => payload[k] = v);

            if (!payload.provider_id) {
                window.showToast('Please select a doctor', 'warning');
                return;
            }

            const btn = document.getElementById('qaConfirmBtn');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

            try {
                // Step 1: Create appointment
                const res = await fetch('/api/v1/appointments', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ...payload,
                        duration: 15,
                        notes: 'Quick Token Entry - Patient Arrived'
                    })
                });

                const data = await res.json();
                if (data.success) {
                    const appointmentId = data.data.appointment_id;
                    
                    // Step 2: Mark patient as arrived (checked-in)
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Marking Arrival...';
                    
                    const arrivalRes = await fetch(`/api/v1/appointments/${appointmentId}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: 'checked-in' })
                    });

                    const arrivalData = await arrivalRes.json();
                    
                    if (arrivalData.success) {
                        window.showToast('Token Generated & Patient Marked as Arrived!', 'success');
                    } else {
                        window.showToast('Token Generated but arrival marking failed', 'warning');
                    }
                    
                    this.modal.hide();
                    
                    // Reload data if on relevant pages
                    if (window.location.pathname.includes('/appointments') || 
                        window.location.pathname.includes('/visits') || 
                        window.location.pathname.includes('/front-office')) {
                        if (typeof reloadData === 'function') reloadData();
                    }
                } else {
                    window.showToast(data.message || 'Booking failed', 'error');
                }
            } catch (e) {
                console.error('Error:', e);
                window.showToast('Network error', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    };

    // Global helper
    window.openQuickApt = (id = null) => QuickApt.open(id);

    // Auto init
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => QuickApt.init(), 100);
    });
</script>