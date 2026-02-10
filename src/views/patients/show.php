<?php
/**
 * Patient Detail View - Clean Zoho Style
 */
$pageTitle = 'Patient Details';
$patientId = $patient_id ?? $patientId ?? 0;
ob_start();
?>

<link rel="stylesheet" href="<?= asset('libs/sweetalert2/sweetalert2.min.css') ?>">
<style>
    /* Clean Zoho-style Patient View */
    .patient-header {
        background: var(--custom-white);
        border: 1px solid var(--default-border);
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .patient-header-main {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .patient-avatar {
        width: 56px;
        height: 56px;
        font-size: 20px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .patient-info h2 {
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0 0 0.25rem 0;
        color: var(--default-text-color);
    }

    .patient-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1.25rem;
        font-size: 0.8125rem;
        color: #6c757d;
    }

    .patient-meta-item {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .patient-meta-item i {
        font-size: 14px;
        color: #9ca3af;
    }

    .patient-stats {
        display: flex;
        gap: 2rem;
        margin-left: auto;
    }

    .patient-stat {
        text-align: center;
        min-width: 80px;
    }

    .patient-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--default-text-color);
    }

    .patient-stat-label {
        font-size: 0.6875rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Tabs */
    .patient-tabs {
        background: var(--custom-white);
        border: 1px solid var(--default-border);
        border-radius: 8px;
        padding: 0 1rem;
        margin-bottom: 1rem;
    }

    .patient-tabs .nav-link {
        border: none;
        color: #6c757d;
        padding: 0.875rem 1rem;
        font-weight: 500;
        font-size: 0.8125rem;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        border-radius: 0;
    }

    .patient-tabs .nav-link:hover {
        color: var(--primary-color);
    }

    .patient-tabs .nav-link.active {
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
        background: transparent;
        border-radius: 0;
    }

    .patient-tabs .nav-link i {
        margin-right: 0.4rem;
        font-size: 15px;
    }

    /* Info Cards */
    .info-card {
        background: var(--custom-white);
        border: 1px solid var(--default-border);
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .info-card-header {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--default-border);
        font-weight: 600;
        font-size: 0.8125rem;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-card-header i {
        color: var(--primary-color);
        font-size: 15px;
    }

    .info-card-body {
        padding: 1rem;
    }

    .info-row {
        display: flex;
        padding: 0.4rem 0;
    }

    .info-row:not(:last-child) {
        border-bottom: 1px solid #f3f4f6;
    }

    .info-label {
        width: 130px;
        color: #6b7280;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .info-value {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--default-text-color);
    }

    /* Visit/Prescription Cards */
    .record-card {
        border: 1px solid var(--default-border);
        border-radius: 6px;
        padding: 0.875rem;
        margin-bottom: 0.75rem;
        background: var(--custom-white);
    }

    .record-card:hover {
        border-color: #d1d5db;
    }
</style>
<style>
    /* Summary Cards */
    .summary-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 1rem;
        text-align: center;
    }

    .summary-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--default-text-color);
    }

    .summary-value.text-success {
        color: #10b981 !important;
    }

    .summary-value.text-danger {
        color: #ef4444 !important;
    }

    .summary-label {
        font-size: 0.6875rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 0.25rem;
    }

    /* Timeline */
    .timeline-item {
        position: relative;
        padding-left: 1.5rem;
        padding-bottom: 1.25rem;
        border-left: 2px solid #e5e7eb;
        margin-left: 0.5rem;
    }

    .timeline-item:last-child {
        border-left-color: transparent;
        padding-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: -5px;
        top: 2px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary-color);
    }

    .timeline-dot.success {
        background: #10b981;
    }

    .timeline-dot.warning {
        background: #f59e0b;
    }

    .timeline-date {
        font-size: 0.6875rem;
        color: #9ca3af;
        margin-bottom: 0.125rem;
    }

    .timeline-title {
        font-weight: 500;
        font-size: 0.8125rem;
        color: var(--default-text-color);
    }

    .timeline-desc {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Family Card */
    .family-card {
        border: 1px solid var(--default-border);
        border-radius: 6px;
        padding: 0.875rem;
        text-align: center;
        cursor: pointer;
        background: var(--custom-white);
    }

    .family-card:hover {
        border-color: var(--primary-color);
        background: rgba(var(--primary-rgb), 0.02);
    }

    .family-avatar {
        width: 40px;
        height: 40px;
        font-size: 14px;
        margin: 0 auto 0.5rem;
    }

    /* Family Badges Quick Access */
    .family-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.15s ease;
        border: 1px solid var(--default-border);
        background: var(--custom-white);
    }

    .family-badge:hover {
        border-color: var(--primary-color);
        background: rgba(var(--primary-rgb), 0.05);
        transform: translateY(-1px);
    }

    .family-badge.current {
        background: rgba(var(--primary-rgb), 0.1);
        border-color: var(--primary-color);
        pointer-events: none;
    }

    .family-badge .badge-avatar {
        width: 22px;
        height: 22px;
        font-size: 9px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .family-badge .badge-name {
        font-weight: 500;
        color: var(--default-text-color);
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .family-badge .badge-relation {
        font-size: 0.65rem;
        color: #9ca3af;
        background: #f3f4f6;
        padding: 0.1rem 0.35rem;
        border-radius: 8px;
    }

    /* Loading */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    @media (max-width: 768px) {
        .patient-header-main {
            flex-wrap: wrap;
        }

        .patient-stats {
            margin-left: 0;
            margin-top: 1rem;
            width: 100%;
            justify-content: space-around;
        }

        .patient-tabs .nav-link {
            padding: 0.75rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<!-- Loading -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="text-center">
        <div class="spinner-border text-primary mb-2"></div>
        <div class="text-muted fs-13">Loading patient details...</div>
    </div>
</div>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= baseUrl('/patients') ?>" class="btn btn-light btn-sm btn-icon">
            <i class="ri-arrow-left-line"></i>
        </a>
        <div>
            <h1 class="page-title fw-semibold fs-16 mb-0">Patient Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-12">
                    <li class="breadcrumb-item"><a href="<?= baseUrl('/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= baseUrl('/patients') ?>">Patients</a></li>
                    <li class="breadcrumb-item active" id="breadcrumbPatient">...</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-sm" onclick="bookAppointment()"><i
                class="ri-calendar-check-line me-1"></i>Book Appointment</button>
        <button class="btn btn-success btn-sm" onclick="newVisit()"><i class="ri-stethoscope-line me-1"></i>New
            Visit</button>
        <div class="dropdown d-inline-block">
            <button class="btn btn-light btn-sm btn-icon" data-bs-toggle="dropdown"><i
                    class="ri-more-2-fill"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" onclick="editPatient()"><i
                            class="ri-pencil-line me-2"></i>Edit</a></li>
                <li><a class="dropdown-item" href="#" onclick="printCard()"><i class="ri-printer-line me-2"></i>Print
                        Card</a></li>
                <li><a class="dropdown-item" href="#" onclick="sendWhatsApp()"><i
                            class="ri-whatsapp-line me-2"></i>WhatsApp</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="#" onclick="deactivatePatient()"><i
                            class="ri-user-unfollow-line me-2"></i>Deactivate</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Patient Header Card -->
<div class="patient-header">
    <div class="patient-header-main">
        <span class="avatar patient-avatar avatar-rounded bg-primary-transparent text-primary"
            id="patientAvatar">--</span>
        <div class="patient-info">
            <h2 id="patientName">Loading...</h2>
            <div class="patient-meta">
                <span class="patient-meta-item" id="metaMRN"><i class="ri-hashtag"></i><span>--</span></span>
                <span class="patient-meta-item" id="metaMobile"><i class="ri-phone-line"></i><span>--</span></span>
                <span class="patient-meta-item" id="metaEmail"><i class="ri-mail-line"></i><span>--</span></span>
                <span class="patient-meta-item" id="metaAge"><i class="ri-user-line"></i><span>--</span></span>
                <span class="badge bg-success-transparent text-success" id="patientStatus">Active</span>
            </div>
        </div>
        <div class="patient-stats">
            <div class="patient-stat">
                <div class="patient-stat-value" id="statVisits">0</div>
                <div class="patient-stat-label">Visits</div>
            </div>
            <div class="patient-stat">
                <div class="patient-stat-value" id="statBilled">₹0</div>
                <div class="patient-stat-label">Billed</div>
            </div>
            <div class="patient-stat">
                <div class="patient-stat-value text-danger" id="statDue">₹0</div>
                <div class="patient-stat-label">Due</div>
            </div>
        </div>
    </div>
    <!-- Family Members Quick Access -->
    <div class="family-badges-section mt-3 pt-3 border-top" id="familyBadgesSection" style="display: none;">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted fs-12 me-1"><i class="ri-group-line me-1"></i>Family:</span>
            <div class="d-flex flex-wrap gap-2" id="familyBadges"></div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="patient-tabs">
    <ul class="nav" id="patientTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabOverview"><i
                    class="ri-dashboard-line"></i>Overview</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabVisits"><i
                    class="ri-stethoscope-line"></i>Visits</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPrescriptions"><i
                    class="ri-capsule-line"></i>Prescriptions</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBilling"><i
                    class="ri-money-rupee-circle-line"></i>Billing</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabFamily"><i
                    class="ri-group-line"></i>Family</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabTimeline"><i
                    class="ri-time-line"></i>Timeline</a></li>
    </ul>
</div>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Overview -->
    <div class="tab-pane fade show active" id="tabOverview">
        <div class="row">
            <div class="col-lg-4">
                <div class="info-card">
                    <div class="info-card-header"><i class="ri-user-line"></i>Basic Information</div>
                    <div class="info-card-body">
                        <div class="info-row"><span class="info-label">Full Name</span><span class="info-value"
                                id="infoName">--</span></div>
                        <div class="info-row"><span class="info-label">Gender</span><span class="info-value"
                                id="infoGender">--</span></div>
                        <div class="info-row"><span class="info-label">Date of Birth</span><span class="info-value"
                                id="infoDOB">--</span></div>
                        <div class="info-row"><span class="info-label">Age</span><span class="info-value"
                                id="infoAge">--</span></div>
                        <div class="info-row"><span class="info-label">Blood Group</span><span class="info-value"
                                id="infoBlood">--</span></div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-header"><i class="ri-phone-line"></i>Contact</div>
                    <div class="info-card-body">
                        <div class="info-row"><span class="info-label">Mobile</span><span class="info-value"
                                id="infoMobile">--</span></div>
                        <div class="info-row"><span class="info-label">Email</span><span class="info-value"
                                id="infoEmail">--</span></div>
                        <div class="info-row"><span class="info-label">Address</span><span class="info-value"
                                id="infoAddress">--</span></div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-header"><i class="ri-heart-pulse-line"></i>Emergency Contact</div>
                    <div class="info-card-body">
                        <div class="info-row"><span class="info-label">Name</span><span class="info-value"
                                id="infoEmName">--</span></div>
                        <div class="info-row"><span class="info-label">Relation</span><span class="info-value"
                                id="infoEmRelation">--</span></div>
                        <div class="info-row"><span class="info-label">Mobile</span><span class="info-value"
                                id="infoEmMobile">--</span></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="info-card">
                    <div class="info-card-header d-flex justify-content-between"><span><i
                                class="ri-calendar-check-line"></i>Recent Visits</span><a href="#"
                            class="text-primary fs-12"
                            onclick="document.querySelector('[href=\'#tabVisits\']').click();return false;">View All</a>
                    </div>
                    <div class="info-card-body" id="recentVisits">
                        <div class="text-center py-3 text-muted">Loading...</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-value" id="sumBilled">₹0</div>
                            <div class="summary-label">Total Billed</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-value text-success" id="sumPaid">₹0</div>
                            <div class="summary-label">Total Paid</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-value text-danger" id="sumDue">₹0</div>
                            <div class="summary-label">Balance Due</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visits Tab -->
    <div class="tab-pane fade" id="tabVisits">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active" data-filter="all">All</button>
                <button class="btn btn-outline-secondary" data-filter="OP">OP</button>
                <button class="btn btn-outline-secondary" data-filter="IP">IP</button>
            </div>
            <input type="text" class="form-control form-control-sm" style="width:200px" placeholder="Search visits..."
                id="visitSearch">
        </div>
        <div id="visitsContent">
            <div class="text-center py-4 text-muted">Loading...</div>
        </div>
    </div>

    <!-- Prescriptions Tab -->
    <div class="tab-pane fade" id="tabPrescriptions">
        <div id="prescriptionsContent">
            <div class="text-center py-4 text-muted">Loading...</div>
        </div>
    </div>

    <!-- Billing Tab -->
    <div class="tab-pane fade" id="tabBilling">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="summary-value" id="billTotal">₹0</div>
                    <div class="summary-label">Total Invoiced</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="summary-value text-success" id="billPaid">₹0</div>
                    <div class="summary-label">Total Paid</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="summary-value text-danger" id="billDue">₹0</div>
                    <div class="summary-label">Balance Due</div>
                </div>
            </div>
        </div>
        <div class="info-card">
            <div class="info-card-header"><i class="ri-file-list-3-line"></i>Invoices</div>
            <div class="info-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 fs-13">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="invoicesBody">
                            <tr>
                                <td colspan="7" class="text-center py-3">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Family Tab -->
    <div class="tab-pane fade" id="tabFamily">
        <div class="row" id="familyContent">
            <div class="col-12 text-center py-4 text-muted">Loading...</div>
        </div>
    </div>

    <!-- Timeline Tab -->
    <div class="tab-pane fade" id="tabTimeline">
        <div id="timelineContent">
            <div class="text-center py-4 text-muted">Loading...</div>
        </div>
    </div>
</div>

<!-- Visit Details Modal -->
<div class="modal fade" id="visitDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom bg-light py-3">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-primary-transparent text-primary rounded-circle me-3">
                        <i class="ri-calendar-event-line fs-20"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Visit Details</h5>
                        <p class="mb-0 text-muted fs-12 fw-medium" id="vd_visit_id">VISIT #---</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="visitDetailsBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading details...</p>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-light" id="vd_print_btn" style="display:none"
                    onclick="printPrescription(currentVisitId)">
                    <i class="ri-printer-line me-1"></i> Print Prescription
                </button>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script src="<?= asset('libs/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>
    const patientId = <?= $patientId ?>;
    let patientData = null;
    let familyData = null;

    const initPatientView = () => {
        loadPatientDetails();

        // Named handler for cleanup
        const hashHandler = () => activateTabFromHash();

        // Tab event listeners for lazy loading content
        document.querySelectorAll('#patientTabs .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (e) {
                const t = e.target.getAttribute('href');
                // Update URL hash without triggering page reload
                if (window.location.hash !== t) {
                    history.replaceState(null, null, t);
                }
                // Always load tab content when tab is shown
                loadTabContent(t);
            });
        });

        // Check for hash on page load and activate the correct tab
        activateTabFromHash();

        // Listen for browser back/forward navigation
        window.addEventListener('hashchange', hashHandler);

        // Cleanup on navigate away
        if (typeof Melina !== 'undefined') {
            Melina.onPageUnload(() => {
                window.removeEventListener('hashchange', hashHandler);
                console.log('Patient view cleaned up');
            });
        }
    };

    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initPatientView);
    } else {
        document.addEventListener('DOMContentLoaded', initPatientView);
    }

    // Function to load content based on tab hash
    function loadTabContent(hash) {
        // Prevent calls if we are not on the patient page anymore or hash is empty
        if (!window.location.pathname.includes('/patients/')) return;

        if (hash === '#tabVisits') loadVisits();
        else if (hash === '#tabPrescriptions') loadPrescriptions();
        else if (hash === '#tabBilling') loadBilling();
        else if (hash === '#tabFamily') loadFamily();
        else if (hash === '#tabTimeline') loadTimeline();
    }

    // Function to activate tab from URL hash
    function activateTabFromHash() {
        const hash = window.location.hash;
        if (hash && hash !== '#tabOverview') {
            const tabTrigger = document.querySelector(`#patientTabs .nav-link[href="${hash}"]`);
            if (tabTrigger) {
                // Use Bootstrap's Tab API to show the tab
                const tab = new bootstrap.Tab(tabTrigger);
                tab.show();
                // Also load the tab content immediately
                loadTabContent(hash);
            }
        }
    }

    async function loadPatientDetails() {
        try {
            const res = await fetch('/api/v1/patients/' + patientId + '/full');
            const data = await res.json();
            if (data.success) {
                patientData = data.data;
                renderProfile(data.data);
                renderOverview(data.data);
                loadFamilyBadges(); // Load family badges for quick access
            } else { showError('Failed to load patient'); }
        } catch (e) { showError('Network error'); }
        finally {
            const loader = document.getElementById('loadingOverlay');
            if (loader) loader.style.display = 'none';
        }
    }

    // Load family badges for quick access in header
    async function loadFamilyBadges() {
        if (familyData) {
            renderFamilyBadges(familyData);
            return;
        }
        try {
            const res = await fetch('/api/v1/patients/' + patientId + '/family');
            const data = await res.json();
            if (data.success && data.data.family_members.length > 1) {
                familyData = data.data.family_members;
                renderFamilyBadges(familyData);
            }
        } catch (e) { console.error('Failed to load family badges:', e); }
    }

    function renderFamilyBadges(members) {
        const section = document.getElementById('familyBadgesSection');
        const container = document.getElementById('familyBadges');

        // Filter out current patient and show only other family members
        const otherMembers = members.filter(m => m.patient_id !== patientId);

        if (otherMembers.length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';
        container.innerHTML = otherMembers.map(m => `
        <a href="<?= baseUrl('/patients') ?>/${m.patient_id}" class="family-badge" title="View ${esc(m.full_name)}'s details">
            <span class="avatar badge-avatar avatar-rounded ${m.gender === 'male' ? 'bg-primary-transparent text-primary' : 'bg-pink-transparent text-pink'}">${getInitials(m.full_name)}</span>
            <span class="badge-name">${esc(m.full_name)}</span>
            <span class="badge-relation">${m.relation || 'Family'}</span>
        </a>
    `).join('');
    }

    function renderProfile(data) {
        const p = data.patient;
        const elId = (id) => document.getElementById(id);

        if (elId('breadcrumbPatient')) elId('breadcrumbPatient').textContent = p.full_name;

        const avatar = elId('patientAvatar');
        if (avatar) {
            avatar.textContent = getInitials(p.full_name);
            avatar.className = 'avatar patient-avatar avatar-rounded ' + (p.gender === 'male' ? 'bg-primary-transparent text-primary' : p.gender === 'female' ? 'bg-pink-transparent text-pink' : 'bg-secondary-transparent');
        }

        if (elId('patientName')) elId('patientName').textContent = p.full_name;
        if (elId('metaMRN')) elId('metaMRN').querySelector('span').textContent = p.mrn;
        if (elId('metaMobile')) elId('metaMobile').querySelector('span').textContent = p.mobile || 'N/A';
        if (elId('metaEmail')) elId('metaEmail').querySelector('span').textContent = p.email || 'N/A';
        if (elId('metaAge')) elId('metaAge').querySelector('span').textContent = p.age ? p.age + ' yrs, ' + capitalize(p.gender) : capitalize(p.gender) || 'N/A';

        const status = elId('patientStatus');
        if (status) {
            status.textContent = p.is_active ? 'Active' : 'Inactive';
            status.className = 'badge ' + (p.is_active ? 'bg-success-transparent text-success' : 'bg-secondary-transparent');
        }

        if (elId('statVisits')) elId('statVisits').textContent = data.stats.total_visits || 0;
        if (elId('statBilled')) elId('statBilled').textContent = '₹' + formatNum(data.stats.total_billed || 0);
        if (elId('statDue')) elId('statDue').textContent = '₹' + formatNum(data.stats.balance_due || 0);
    }

    function renderOverview(data) {
        const p = data.patient;
        document.getElementById('infoName').textContent = p.full_name;
        document.getElementById('infoGender').innerHTML = '<span class="badge ' + (p.gender === 'male' ? 'bg-primary-transparent' : 'bg-pink-transparent') + '">' + capitalize(p.gender) + '</span>';
        document.getElementById('infoDOB').textContent = p.dob ? formatDate(p.dob) : '--';
        document.getElementById('infoAge').textContent = p.age ? p.age + ' years' : '--';
        document.getElementById('infoBlood').innerHTML = p.blood_group ? '<span class="badge bg-danger-transparent">' + p.blood_group + '</span>' : '--';
        document.getElementById('infoMobile').innerHTML = p.mobile ? '<a href="tel:' + p.mobile + '">' + p.mobile + '</a>' : '--';
        document.getElementById('infoEmail').innerHTML = p.email ? '<a href="mailto:' + p.email + '">' + p.email + '</a>' : '--';
        document.getElementById('infoAddress').textContent = [p.address, p.city, p.state, p.pincode].filter(Boolean).join(', ') || '--';
        document.getElementById('infoEmName').textContent = p.emergency_contact_name || '--';
        document.getElementById('infoEmRelation').textContent = p.emergency_contact_relation || '--';
        document.getElementById('infoEmMobile').innerHTML = p.emergency_contact_mobile ? '<a href="tel:' + p.emergency_contact_mobile + '">' + p.emergency_contact_mobile + '</a>' : '--';

        // Recent visits
        const rv = data.recent_visits || [];
        document.getElementById('recentVisits').innerHTML = rv.length ? rv.slice(0, 5).map(v => `
        <div class="record-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><span class="badge ${v.visit_type === 'OP' ? 'bg-primary-transparent' : 'bg-success-transparent'} me-2">${v.visit_type}</span><span class="fw-medium">${formatDate(v.visit_start)}</span></div>
                <span class="badge ${v.visit_status === 'closed' ? 'bg-success' : 'bg-warning'}">${capitalize(v.visit_status)}</span>
            </div>
            ${v.provider_name ? '<div class="text-muted fs-12 mt-1"><i class="ri-user-star-line me-1"></i>' + v.provider_name + '</div>' : ''}
        </div>
    `).join('') : '<div class="text-center py-3 text-muted"><i class="ri-calendar-line fs-24 d-block mb-1"></i>No visits yet</div>';

        // Summary
        document.getElementById('sumBilled').textContent = '₹' + formatNum(data.stats.total_billed || 0);
        document.getElementById('sumPaid').textContent = '₹' + formatNum(data.stats.total_paid || 0);
        document.getElementById('sumDue').textContent = '₹' + formatNum(data.stats.balance_due || 0);
    }

    async function loadVisits() {
        const c = document.getElementById('visitsContent');
        // Skip if already loaded (not showing loading text or spinner)
        if (c && !c.textContent.includes('Loading') && !c.querySelector('.spinner-border')) return;

        if (c) c.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
        try {
            const res = await fetch('/api/v1/patients/' + patientId + '/visits');
            const data = await res.json();
            if (data.success) renderVisits(data.data.visits);
        } catch (e) { if (c) c.innerHTML = '<div class="text-center py-4 text-danger">Failed to load</div>'; }
    }

    function renderVisits(visits) {
        const c = document.getElementById('visitsContent');
        if (!visits.length) { c.innerHTML = '<div class="text-center py-4 text-muted"><i class="ri-calendar-line fs-32 d-block mb-1"></i>No visits</div>'; return; }
        c.innerHTML = visits.map(v => `
        <div class="record-card pointer-cursor hover-shadow-sm" onclick="viewVisitDetails(${v.visit_id})" style="cursor: pointer;">
            <div class="row align-items-center">
                <div class="col-md-3"><span class="badge ${v.visit_type === 'OP' ? 'bg-primary' : 'bg-success'}">${v.visit_type}</span><div class="fw-medium mt-1">${formatDateTime(v.visit_start)}</div></div>
                <div class="col-md-3"><div class="text-muted fs-11">Provider</div><div class="fw-medium">${v.provider_name || '--'}</div></div>
                <div class="col-md-2"><div class="text-muted fs-11">Branch</div><div class="fw-medium">${v.branch_name || '--'}</div></div>
                <div class="col-md-2"><div class="text-muted fs-11">Token</div><div class="fw-medium">${v.token_no || '--'}</div></div>
                <div class="col-md-2 text-end"><span class="badge ${v.visit_status === 'closed' ? 'bg-success' : 'bg-warning'}">${capitalize(v.visit_status)}</span></div>
            </div>
        </div>
    `).join('');
    }

    let currentVisitId = null;
    async function viewVisitDetails(visitId) {
        currentVisitId = visitId;
        const modal = new bootstrap.Modal(document.getElementById('visitDetailsModal'));
        const body = document.getElementById('visitDetailsBody');
        const visitIdEl = document.getElementById('vd_visit_id');
        const printBtn = document.getElementById('vd_print_btn');

        visitIdEl.innerText = `VISIT #${visitId}`;
        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Fetching encounter data...</p></div>';
        printBtn.style.display = 'none';

        modal.show();

        try {
            const res = await fetch(`/api/v1/visits/${visitId}`);
            const data = await res.json();

            if (data.success) {
                const v = data.data.visit;
                const vitals = data.data.vitals;
                const rx = data.data.prescriptions;
                const notes = data.data.clinical_notes;

                const siddha = data.data.siddha_notes;

                if (rx && rx.length > 0) printBtn.style.display = 'block';

                let html = `
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Encounter Info</h6>
                            <div class="mb-2"><span class="text-muted fs-12 d-block">Provider / Attending</span> <strong>Dr. ${v.provider_name || '--'}</strong></div>
                            <div class="mb-2"><span class="text-muted fs-12 d-block">Facility / Branch</span> <strong>${v.branch_name || '--'}</strong></div>
                            <div class="mb-2"><span class="text-muted fs-12 d-block">Status</span> <span class="badge ${v.visit_status === 'closed' ? 'bg-success-transparent text-success' : 'bg-warning-transparent text-warning'}">${capitalize(v.visit_status)}</span></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Vitals & Obs</h6>
                            ${vitals ? `
                                <div class="row g-2">
                                    <div class="col-4"><span class="text-muted fs-11 d-block text-uppercase">BP</span> <strong>${vitals.bp_systolic || '--'}/${vitals.bp_diastolic || '--'}</strong></div>
                                    <div class="col-4"><span class="text-muted fs-11 d-block text-uppercase">Pulse</span> <strong>${vitals.pulse_per_min || '--'} bpm</strong></div>
                                    <div class="col-4"><span class="text-muted fs-11 d-block text-uppercase">SpO2</span> <strong>${vitals.spo2 || '--'}%</strong></div>
                                    <div class="col-4"><span class="text-muted fs-11 d-block text-uppercase">Weight</span> <strong>${vitals.weight_kg || '--'} kg</strong></div>
                                    <div class="col-4"><span class="text-muted fs-11 d-block text-uppercase">Temp</span> <strong>${vitals.temperature_c || '--'} °C</strong></div>
                                </div>
                            ` : '<div class="text-muted fs-12 py-2">No vitals captured for this visit</div>'}
                        </div>
                        <div class="col-12">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Diagnosis & Clinical Notes</h6>
                            
                            ${siddha ? `
                                <div class="card mb-3 border border-primary shadow-none bg-primary-transparent">
                                    <div class="card-header py-2">
                                        <span class="fw-bold text-primary"><i class="ri-pulse-line me-1"></i> Siddha Clinical Assessment</span>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="row g-3">
                                            ${siddha.pulse_diagnosis ? `<div class="col-md-6"><small class="text-muted d-block text-uppercase" style="font-size: 10px;">Nadi / Pulse</small><strong>${siddha.pulse_diagnosis}</strong></div>` : ''}
                                            ${siddha.tongue ? `<div class="col-md-6"><small class="text-muted d-block text-uppercase" style="font-size: 10px;">Tongue</small><strong>${siddha.tongue}</strong></div>` : ''}
                                            ${siddha.prakriti ? `<div class="col-md-6"><small class="text-muted d-block text-uppercase" style="font-size: 10px;">Prakriti</small><strong>${siddha.prakriti}</strong></div>` : ''}
                                            ${siddha.anupanam ? `<div class="col-md-6"><small class="text-muted d-block text-uppercase" style="font-size: 10px;">Anupanam</small><strong>${siddha.anupanam}</strong></div>` : ''}
                                            ${siddha.note_text ? `<div class="col-12 mt-2"><small class="text-muted d-block border-top pt-2 text-uppercase" style="font-size: 10px;">Clinical Observation</small><p class="mb-0 fs-13">${siddha.note_text}</p></div>` : ''}
                                        </div>
                                    </div>
                                </div>
                            ` : ''}

                            ${notes && notes.length > 0 ? notes.map(n => `
                                <div class="bg-light p-3 rounded mb-2">
                                    <span class="badge bg-white text-primary border mb-2">${capitalize(n.note_type)} Note</span>
                                    <div class="fs-13 text-dark">${esc(n.note_text)}</div>
                                </div>
                            `).join('') : (!siddha ? '<div class="text-muted fs-12 py-2 italic text-center">No clinical notes recorded</div>' : '')}
                        </div>
                        <div class="col-12">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Medications / Prescriptions</h6>
                            ${rx && rx.length > 0 ? `
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="bg-light">
                                            <tr class="fs-12">
                                                <th>Medicine Name</th>
                                                <th>Dosage</th>
                                                <th>Freq</th>
                                                <th>Dur</th>
                                                <th>Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fs-13">
                                            ${rx.map(p => `
                                                <tr>
                                                    <td class="fw-bold">${p.product_name}</td>
                                                    <td>${p.dosage || '--'}</td>
                                                    <td>${p.frequency || '--'}</td>
                                                    <td>${p.duration_days ? p.duration_days + ' days' : '--'}</td>
                                                    <td>${p.quantity}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            ` : '<div class="text-muted fs-12 py-2 italic text-center">No medications prescribed</div>'}
                        </div>
                        ${v.notes ? `
                        <div class="col-12">
                            <h6 class="fw-bold mb-2 border-bottom pb-2">Overall Visit Notes</h6>
                            <div class="bg-soft-warning p-3 rounded text-dark fs-13 italic">${esc(v.notes)}</div>
                        </div>
                        ` : ''}
                    </div>
                `;
                body.innerHTML = html;
            } else {
                body.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load visit data'}</div>`;
            }
        } catch (e) {
            console.error(e);
            body.innerHTML = '<div class="alert alert-danger">Network Error: Failed to fetch data</div>';
        }
    }

    async function loadPrescriptions() {
        const c = document.getElementById('prescriptionsContent');
        // Skip if already loaded (not showing loading text or spinner)
        if (c && !c.textContent.includes('Loading') && !c.querySelector('.spinner-border')) return;

        if (c) c.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
        try {
            const res = await fetch('/api/v1/patients/' + patientId + '/prescriptions');
            const data = await res.json();
            if (data.success) renderPrescriptions(data.data.prescriptions);
        } catch (e) { if (c) c.innerHTML = '<div class="text-center py-4 text-danger">Failed to load</div>'; }
    }

    function renderPrescriptions(rx) {
        const c = document.getElementById('prescriptionsContent');
        if (!rx.length) { c.innerHTML = '<div class="text-center py-4 text-muted"><i class="ri-capsule-line fs-32 d-block mb-1"></i>No prescriptions</div>'; return; }
        c.innerHTML = rx.map(p => `
        <div class="info-card">
            <div class="info-card-header d-flex justify-content-between">
                <span><i class="ri-file-list-line"></i>${formatDate(p.prescribed_at)} - ${p.provider_name || 'Unknown'}</span>
                <button class="btn btn-sm btn-light" onclick="printPrescription(${p.prescription_id})"><i class="ri-printer-line"></i></button>
            </div>
            <div class="info-card-body">
                ${p.items.map(i => `
                    <div class="d-flex align-items-center py-2 border-bottom">
                        <span class="avatar avatar-xs bg-primary-transparent text-primary me-2"><i class="ri-capsule-line fs-12"></i></span>
                        <div class="flex-fill"><div class="fw-medium">${esc(i.product_name)}</div><div class="text-muted fs-11">${i.dosage || ''} ${i.frequency || ''} ${i.duration_days ? 'for ' + i.duration_days + ' days' : ''}</div></div>
                        <span class="badge bg-light text-dark">Qty: ${i.quantity}</span>
                    </div>
                `).join('')}
                ${p.notes ? '<div class="mt-2 p-2 bg-light rounded fs-12"><i class="ri-sticky-note-line me-1"></i>' + esc(p.notes) + '</div>' : ''}
            </div>
        </div>
    `).join('');
    }

    async function loadBilling() {
        const c = document.getElementById('invoicesBody');
        // Skip if already loaded (not showing loading text or spinner)
        if (c && !c.textContent.includes('Loading') && !c.querySelector('.spinner-border')) return;

        try {
            const res = await fetch('/api/v1/patients/' + patientId + '/billing');
            const data = await res.json();
            if (data.success) {
                document.getElementById('billTotal').textContent = '₹' + formatNum(data.data.summary.total_invoiced || 0);
                document.getElementById('billPaid').textContent = '₹' + formatNum(data.data.summary.total_paid || 0);
                document.getElementById('billDue').textContent = '₹' + formatNum(data.data.summary.balance_due || 0);
                renderInvoices(data.data.invoices);
            }
        } catch (e) { if (c) c.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed</td></tr>'; }
    }

    function renderInvoices(inv) {
        const tb = document.getElementById('invoicesBody');
        if (!inv.length) { tb.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted">No invoices</td></tr>'; return; }
        tb.innerHTML = inv.map(i => `
        <tr>
            <td><a href="#" onclick="viewInvoice(${i.invoice_id})">${i.invoice_no}</a></td>
            <td>${formatDate(i.created_at)}</td>
            <td><span class="badge bg-${i.invoice_type === 'pharmacy' ? 'success' : 'primary'}-transparent">${capitalize(i.invoice_type)}</span></td>
            <td class="fw-medium">₹${formatNum(i.total_amount)}</td>
            <td class="text-success">₹${formatNum(i.paid_amount)}</td>
            <td><span class="badge bg-${i.status === 'paid' ? 'success' : 'warning'}">${capitalize(i.status)}</span></td>
            <td><button class="btn btn-sm btn-light btn-icon" onclick="printInvoice(${i.invoice_id})"><i class="ri-printer-line"></i></button></td>
        </tr>
    `).join('');
    }

    async function loadFamily() {
        const c = document.getElementById('familyContent');
        // Skip if already loaded (not showing loading text or spinner)
        if (c && !c.textContent.includes('Loading') && !c.querySelector('.spinner-border')) return;

        if (familyData) {
            renderFamily(familyData);
            return;
        }

        if (c) c.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
        try {
            const res = await fetch('/api/v1/patients/' + patientId + '/family');
            const data = await res.json();
            if (data.success) {
                familyData = data.data.family_members;
                renderFamily(familyData);
            }
        } catch (e) { if (c) c.innerHTML = '<div class="col-12 text-center py-4 text-danger">Failed</div>'; }
    }

    function renderFamily(members) {
        const c = document.getElementById('familyContent');
        if (!members.length) { c.innerHTML = '<div class="col-12 text-center py-4 text-muted"><i class="ri-group-line fs-32 d-block mb-1"></i>No family members linked</div>'; return; }
        c.innerHTML = members.map(m => `
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="family-card" onclick="window.location.href='<?= baseUrl('/patients') ?>/${m.patient_id}'">
                <span class="avatar family-avatar avatar-rounded ${m.gender === 'male' ? 'bg-primary-transparent' : 'bg-pink-transparent'}">${getInitials(m.full_name)}</span>
                <h6 class="mb-0 fs-13">${esc(m.full_name)}</h6>
                <div class="text-muted fs-11">${m.mrn}</div>
                <div class="mt-1 fs-11"><span class="text-muted">Visits: ${m.total_visits || 0}</span></div>
            </div>
        </div>
    `).join('');
    }

    async function loadTimeline() {
        const c = document.getElementById('timelineContent');
        // Skip if already loaded (not showing loading text or spinner)
        if (c && !c.textContent.includes('Loading') && !c.querySelector('.spinner-border')) return;

        if (c) c.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
        try {
            const res = await fetch('/api/v1/patients/' + patientId + '/timeline');
            const data = await res.json();
            if (data.success) renderTimeline(data.data.events);
        } catch (e) { if (c) c.innerHTML = '<div class="text-center py-4 text-danger">Failed</div>'; }
    }

    function renderTimeline(events) {
        const c = document.getElementById('timelineContent');
        if (!events.length) { c.innerHTML = '<div class="text-center py-4 text-muted"><i class="ri-time-line fs-32 d-block mb-1"></i>No activity</div>'; return; }
        c.innerHTML = '<div class="ps-2">' + events.map(e => `
        <div class="timeline-item">
            <div class="timeline-dot ${e.type === 'payment' ? 'success' : e.type === 'prescription' ? 'warning' : ''}"></div>
            <div class="timeline-date">${formatDateTime(e.date)}</div>
            <div class="timeline-title">${esc(e.title)}</div>
            <div class="timeline-desc">${esc(e.description || '')}</div>
        </div>
    `).join('') + '</div>';
    }

    // Actions
    function editPatient() { window.location.href = '<?= baseUrl('/patients') ?>/' + patientId + '/edit'; }
    function bookAppointment() {
        if (typeof window.openQuickApt === 'function') {
            window.openQuickApt(patientId);
        } else {
            window.location.href = '<?= baseUrl('/appointments/create') ?>?patient_id=' + patientId;
        }
    }
    function newVisit() { window.location.href = '<?= baseUrl('/visits/create') ?>?patient_id=' + patientId; }
    function printCard() { window.open('/api/v1/patients/' + patientId + '/card', '_blank'); }
    function sendWhatsApp() { if (patientData?.patient?.mobile) window.open('https://wa.me/91' + patientData.patient.mobile, '_blank'); else showError('No mobile'); }
    function viewInvoice(id) { window.location.href = '<?= baseUrl('/billing/invoices') ?>/' + id; }
    function printInvoice(id) { window.open('/api/v1/billing/invoices/' + id + '/print', '_blank'); }
    function printPrescription(id) { window.open('/api/v1/prescriptions/' + id + '/print', '_blank'); }

    async function deactivatePatient() {
        const r = await Swal.fire({ title: 'Deactivate Patient?', text: 'This will mark the patient as inactive.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, deactivate' });
        if (r.isConfirmed) {
            try {
                const res = await fetch('/api/v1/patients/' + patientId, { method: 'DELETE', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' } });
                const data = await res.json();
                if (data.success) { Swal.fire({ icon: 'success', title: 'Deactivated!', timer: 1500, showConfirmButton: false }); setTimeout(() => window.location.href = '<?= baseUrl('/patients') ?>', 1500); }
                else showError(data.message);
            } catch (e) { showError('Network error'); }
        }
    }

    // Helpers
    function showError(msg) { Swal.fire({ icon: 'error', title: 'Error', text: msg }); }
    function esc(s) { return s ? String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]) : ''; }
    function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
    function getInitials(n) { return n ? n.split(' ').map(x => x[0]).slice(0, 2).join('').toUpperCase() : '?'; }
    function formatNum(n) { return Number(n || 0).toLocaleString('en-IN'); }
    function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : ''; }
    function formatDateTime(d) { return d ? new Date(d).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''; }
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>