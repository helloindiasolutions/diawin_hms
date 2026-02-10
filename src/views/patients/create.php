<?php
/**
 * Patient Registration - Zoho Style
 * Step 1: Mobile lookup -> Family tree check
 * Step 2: Patient details form
 */
$pageTitle = 'New Patient Registration';
ob_start();
?>

<style>
    /* Clean Page Layout - No heavy backgrounds */
    .registration-page-wrapper {
        min-height: calc(100vh - 120px);
        background: transparent;
        padding: 1rem 1rem;
    }

    /* Registration Container - WIDER for Step 3 */
    .registration-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .registration-container.wide-form {
        max-width: 1400px;
    }

    /* Page Header - Simple with border */
    .registration-page-header {
        padding: 0.75rem 0;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--default-border, #e2e8ee);
    }

    /* Step Indicator - Compact */
    .step-indicator {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 0;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        min-width: 80px;
    }

    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s;
        border: 2px solid #dee2e6;
        background: #fff;
        color: #6c757d;
    }

    .step-circle.active {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }

    .step-circle.completed {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }

    .step-circle.completed i {
        font-size: 16px;
    }

    .step-circle.pending {
        background: #fff;
        color: #adb5bd;
        border-color: #dee2e6;
    }

    .step-line {
        width: 60px;
        height: 2px;
        background: #dee2e6;
        margin-top: 17px;
        flex-shrink: 0;
    }

    .step-line.completed {
        background: #28a745;
    }

    .step-label {
        font-size: 11px;
        margin-top: 6px;
        text-align: center;
        color: #6c757d;
        font-weight: 500;
    }

    .step-circle.active~.step-label,
    .step-circle.completed~.step-label {
        color: var(--default-text-color);
        font-weight: 600;
    }

    /* Back Button */
    .btn-back-lg {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 16px;
        transition: all 0.2s;
        padding: 0;
        background: var(--default-background);
        border: 1px solid var(--default-border);
        color: var(--default-text-color);
    }

    .btn-back-lg:hover {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }

    /* Mobile Search Box */
    .mobile-search-box {
        max-width: 450px;
        margin: 0 auto;
        width: 100%;
    }

    .mobile-search-box .input-group {
        height: 56px;
    }

    .mobile-search-box .form-control {
        font-size: 24px;
        text-align: center;
        letter-spacing: 4px;
        padding: 0.75rem 1rem;
        height: 56px;
        border-radius: 0 8px 8px 0 !important;
        font-weight: 500;
    }

    .mobile-search-box .form-control::placeholder {
        font-size: 24px;
        letter-spacing: 4px;
        color: #adb5bd;
    }

    .mobile-search-box .input-group-text {
        font-size: 18px;
        font-weight: 600;
        background: var(--primary-color);
        color: #fff;
        padding: 0 1.25rem;
        border-radius: 8px 0 0 8px !important;
        border: none;
        min-width: 70px;
        justify-content: center;
    }

    /* Phone Icon */
    .phone-icon-lg {
        width: 64px;
        height: 64px;
    }

    .phone-icon-lg i {
        font-size: 28px;
    }

    /* Cards - Simple borders */
    .registration-page-wrapper .card {
        border: 1px solid var(--default-border);
        box-shadow: none;
    }

    /* Family Cards */
    .family-tree-card {
        border: 1px solid var(--default-border);
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .family-tree-card:hover {
        border-color: var(--primary-color);
    }

    .family-tree-card.selected {
        border-color: var(--primary-color);
        background: rgba(var(--primary-rgb), 0.03);
    }

    .family-member {
        padding: 10px;
        border-bottom: 1px solid var(--default-border);
    }

    .family-member:last-child {
        border-bottom: none;
    }

    /* Form Sections - Compact */
    .form-section {
        background: var(--custom-white);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border: 1px solid var(--default-border);
    }

    .form-section-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-section-title i {
        font-size: 14px;
    }

    /* Compact form controls */
    .form-section .form-label {
        font-size: 12px;
        margin-bottom: 0.25rem;
    }

    .form-section .form-control,
    .form-section .form-select {
        font-size: 13px;
        padding: 0.4rem 0.65rem;
    }

    .form-section .input-group-text {
        font-size: 12px;
        padding: 0.4rem 0.65rem;
    }

    .form-section .btn-group .btn {
        font-size: 12px;
        padding: 0.4rem 0.5rem;
    }

    /* Two-column layout for Step 3 */
    .form-two-column {
        display: flex;
        gap: 1rem;
    }

    .form-column-left {
        flex: 1;
        min-width: 0;
    }

    .form-column-right {
        flex: 1;
        min-width: 0;
    }

    /* New Family Option */
    .new-family-option {
        border: 1px dashed #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 120px;
    }

    .new-family-option:hover {
        border-color: var(--primary-color);
    }

    .new-family-option.selected {
        border-color: var(--primary-color);
        border-style: solid;
        background: rgba(var(--primary-rgb), 0.03);
    }

    /* Search Button */
    .btn-search-lg {
        padding: 10px 28px;
        font-size: 14px;
        font-weight: 600;
    }

    /* Success Icon - Simple */
    .success-icon {
        width: 64px;
        height: 64px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    .success-icon i {
        font-size: 32px;
        color: #fff;
    }

    /* Responsive - Stack columns on smaller screens */
    @media (max-width: 991px) {
        .registration-page-wrapper {
            padding: 0.75rem 0.5rem;
        }

        .form-two-column {
            flex-direction: column;
            gap: 0;
        }

        .registration-container.wide-form {
            max-width: 700px;
        }
    }

    /* Emergency Contact Auto-fill Styles */
    #emergencyMatchInfo {
        font-size: 11px;
        margin-top: 3px;
    }

    #relationSuggestion {
        font-size: 11px;
        margin-top: 3px;
        cursor: pointer;
    }

    #emergencySearchHint {
        font-size: 10px;
    }

    #searchEmergencyBtn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    /* Family Member Relation Styles */
    #familyRelationSuggestion {
        font-size: 11px;
        margin-top: 3px;
    }

    #relatedToSection .alert {
        background: rgba(var(--primary-rgb), 0.05);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
    }

    #relation_to_member {
        text-transform: capitalize;
    }

    @media (max-width: 768px) {
        .registration-page-wrapper {
            padding: 0.75rem 0.5rem;
        }

        .step-indicator {
            padding: 0 10px;
        }

        .step-line {
            width: 40px;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            font-size: 12px;
        }

        .step-line {
            margin-top: 15px;
        }

        .step-label {
            font-size: 9px;
        }

        .step-item {
            min-width: 60px;
        }

        .mobile-search-box .input-group {
            height: 50px;
        }

        .mobile-search-box .form-control {
            font-size: 20px;
            letter-spacing: 3px;
            height: 50px;
            padding: 0.5rem 0.75rem;
        }

        .mobile-search-box .form-control::placeholder {
            font-size: 20px;
            letter-spacing: 3px;
        }

        .mobile-search-box .input-group-text {
            font-size: 18px;
            padding: 0 1rem;
            min-width: 65px;
        }

        .phone-icon-lg {
            width: 56px;
            height: 56px;
        }

        .phone-icon-lg i {
            font-size: 24px;
        }

        .form-section {
            padding: 0.75rem;
        }

        .btn-back-lg {
            width: 32px;
            height: 32px;
            min-width: 32px;
        }

        .btn-back-lg i {
            font-size: 16px;
        }

        .btn-icon {
            width: 30px;
            height: 30px;
        }

        .btn-icon i {
            font-size: 14px;
        }

        .card-body.py-5 {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }
    }

    @media (max-width: 576px) {
        .registration-page-wrapper {
            margin: -0.75rem;
            padding: 1rem 0.25rem;
        }

        .registration-container {
            padding: 0 8px;
        }

        .step-line {
            width: 30px;
        }

        .step-circle {
            width: 42px;
            height: 42px;
            font-size: 14px;
        }

        .step-line {
            margin-top: 19px;
            height: 3px;
        }

        .step-label {
            font-size: 10px;
            margin-top: 8px;
        }

        .step-item {
            min-width: 55px;
        }

        .mobile-search-box {
            padding: 0 5px;
        }

        .mobile-search-box .input-group {
            height: 54px;
        }

        .mobile-search-box .form-control {
            font-size: 18px;
            letter-spacing: 2px;
            height: 54px;
            padding: 0.5rem 0.75rem;
        }

        .mobile-search-box .form-control::placeholder {
            font-size: 18px;
            letter-spacing: 2px;
        }

        .mobile-search-box .input-group-text {
            font-size: 16px;
            padding: 0 0.75rem;
            min-width: 55px;
        }

        .phone-icon-lg {
            width: 56px;
            height: 56px;
        }

        .phone-icon-lg i {
            font-size: 24px;
        }

        .btn-back-lg {
            width: 38px;
            height: 38px;
            min-width: 38px;
        }

        .btn-back-lg i {
            font-size: 18px;
        }

        .btn-search-lg {
            padding: 12px 30px;
            font-size: 14px;
        }

        .page-title {
            font-size: 16px !important;
        }

        h3.fw-semibold {
            font-size: 1.25rem;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
        }

        .btn-icon i {
            font-size: 14px;
        }

        .card-body.py-5 {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }

        .card-body.px-3 {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<div class="registration-page-wrapper">
    <!-- Page Header -->
    <div class="registration-page-header">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= baseUrl('/patients') ?>" class="btn btn-back-lg">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="page-title fw-semibold fs-18 mb-0">New Patient Registration</h1>
                <span class="text-muted fs-12">Register a new patient to the system</span>
            </div>
        </div>
    </div>

    <div class="registration-container">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step-item">
                <div class="step-circle active" id="step1Circle">1</div>
                <div class="step-label">Mobile Lookup</div>
            </div>
            <div class="step-line" id="stepLine1"></div>
            <div class="step-item">
                <div class="step-circle pending" id="step2Circle">2</div>
                <div class="step-label">Family Selection</div>
            </div>
            <div class="step-line" id="stepLine2"></div>
            <div class="step-item">
                <div class="step-circle pending" id="step3Circle">3</div>
                <div class="step-label">Patient Details</div>
            </div>
        </div>

        <!-- Step 1: Mobile Number Lookup -->
        <div id="step1" class="step-content">
            <div class="card custom-card">
                <div class="card-body py-5 px-3 px-md-5">
                    <div class="text-center mb-4">
                        <span class="avatar phone-icon-lg bg-primary-transparent rounded-circle mb-3">
                            <i class="ri-phone-line text-primary"></i>
                        </span>
                        <h3 class="fw-semibold mb-2">Enter Mobile Number</h3>
                        <p class="text-muted fs-15">We'll check if this patient or family already exists</p>
                    </div>

                    <form id="mobileSearchForm" class="needs-validation" novalidate onsubmit="searchMobile(event)">
                        <div class="mobile-search-box">
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control" id="mobileInput" name="mobile"
                                    placeholder="9876543210" maxlength="10" pattern="[0-9]{10}" autocomplete="off"
                                    required>
                            </div>
                            <div class="invalid-feedback" id="mobileError">Please enter a valid 10-digit mobile number.
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-search-lg" id="searchMobileBtn">
                                <span class="spinner-border spinner-border-sm d-none me-2" id="searchSpinner"></span>
                                <i class="ri-search-line me-2" id="searchIcon"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Step 2: Family Selection -->
        <div id="step2" class="step-content d-none">
            <div class="card custom-card">
                <div class="card-header py-3">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-light btn-back-lg" onclick="goToStep(1)">
                            <i class="ri-arrow-left-line"></i>
                        </button>
                        <div>
                            <h5 class="card-title mb-0 fs-18">Select Family</h5>
                            <span class="text-muted fs-13">Mobile: <strong id="displayMobile"></strong></span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div id="familyResults">
                        <!-- Family cards will be inserted here -->
                    </div>

                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-search-lg" id="continueToStep3" onclick="goToStep(3)"
                            disabled>
                            Continue <i class="ri-arrow-right-line ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Patient Details Form -->
        <div id="step3" class="step-content d-none">
            <div class="d-flex align-items-center gap-2 mb-3">
                <button class="btn btn-light btn-back-lg" onclick="goToStep(2)">
                    <i class="ri-arrow-left-line"></i>
                </button>
                <div>
                    <h5 class="mb-0 fs-16">Patient Details</h5>
                    <span class="text-muted fs-12" id="familyContext"></span>
                </div>
            </div>

            <form id="patientForm" class="needs-validation" novalidate>
                <input type="hidden" id="familyId" name="family_id">
                <input type="hidden" id="patientMobile" name="mobile">
                <input type="hidden" name="relation" id="relation" value="self">
                <input type="hidden" name="related_to_patient_id" id="related_to_patient_id" value="">
                <input type="hidden" name="relation_to_member" id="relation_to_member" value="">

                <!-- Two Column Layout -->
                <div class="form-two-column">
                    <!-- LEFT COLUMN: Basic Info + Family Relation -->
                    <div class="form-column-left">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="ri-user-line"></i> Basic Information
                            </div>
                            <div class="row g-2">
                                <div class="col-3">
                                    <label class="form-label">Title</label>
                                    <select class="form-select" name="title" id="title">
                                        <option value="">--</option>
                                        <option value="Mr.">Mr.</option>
                                        <option value="Mrs.">Mrs.</option>
                                        <option value="Ms.">Ms.</option>
                                        <option value="Dr.">Dr.</option>
                                        <option value="Master">Master</option>
                                        <option value="Baby">Baby</option>
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" id="first_name" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="last_name">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="gender" id="genderMale"
                                            value="male">
                                        <label class="btn btn-outline-primary" for="genderMale"><i
                                                class="ri-men-line me-1"></i>Male</label>
                                        <input type="radio" class="btn-check" name="gender" id="genderFemale"
                                            value="female">
                                        <label class="btn btn-outline-primary" for="genderFemale"><i
                                                class="ri-women-line me-1"></i>Female</label>
                                        <input type="radio" class="btn-check" name="gender" id="genderOther"
                                            value="other">
                                        <label class="btn btn-outline-primary" for="genderOther">Other</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" id="dob"
                                        max="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Age</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="age" id="age" min="0" max="150">
                                        <span class="input-group-text">Yrs</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Blood Group</label>
                                    <select class="form-select" name="blood_group" id="blood_group">
                                        <option value="">Unknown</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Mobile</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+91</span>
                                        <input type="tel" class="form-control" id="displayMobileField" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Family Relation (shown only when adding to existing family) -->
                        <div class="form-section d-none" id="relatedToSection">
                            <div class="form-section-title">
                                <i class="ri-git-branch-line"></i> Family Relation
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">Related To <span class="text-danger">*</span></label>
                                    <!-- Read-only input showing Family Head only -->
                                    <input type="text" class="form-control" id="related_to_display" readonly
                                        style="background-color: var(--default-background);">
                                    <input type="hidden" id="related_to_select" value="">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                    <select class="form-select" id="relationship_select">
                                        <option value="">-- Select --</option>
                                        <option value="Self">Self</option>
                                        <option value="Son">Son</option>
                                        <option value="Daughter">Daughter</option>
                                        <option value="Father">Father</option>
                                        <option value="Mother">Mother</option>
                                        <option value="Spouse">Spouse</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <small class="text-success d-none" id="familyRelationSuggestion"></small>
                                    <div class="alert alert-light border py-1 px-2 mb-0 mt-1 d-none"
                                        id="hierarchyPreview">
                                        <small><i class="ri-git-branch-line me-1 text-primary"></i><span
                                                id="hierarchyText"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Contact + Address + Emergency -->
                    <div class="form-column-right">
                        <!-- Contact & Address Combined -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="ri-map-pin-line"></i> Contact & Address
                            </div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email"
                                        placeholder="email@example.com">
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" class="form-control" name="address" id="address"
                                        placeholder="Street address">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" id="city">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state" id="state" value="Tamil Nadu">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" class="form-control" name="pincode" id="pincode" maxlength="6">
                                </div>
                                <input type="hidden" name="country" id="country" value="India">
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="ri-heart-pulse-line"></i> Emergency Contact
                            </div>
                            <div class="row g-2">
                                <div class="col-5">
                                    <label class="form-label">Mobile</label>
                                    <div class="input-group">
                                        <input type="tel" class="form-control" name="emergency_contact_mobile"
                                            id="emergency_contact_mobile" maxlength="10" pattern="[0-9]{10}"
                                            placeholder="10 digits">
                                        <button type="button" class="btn btn-outline-primary btn-sm px-2"
                                            id="searchEmergencyBtn" onclick="searchEmergencyContact()">
                                            <i class="ri-search-line"></i>
                                        </button>
                                        <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="emergency_contact_name"
                                        id="emergency_contact_name">
                                    <small class="text-success d-none" id="emergencyMatchInfo"></small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Relation</label>
                                    <select class="form-select" name="emergency_contact_relation"
                                        id="emergency_contact_relation">
                                        <option value="">Select Relation</option>
                                        <option value="Father">Father</option>
                                        <option value="Mother">Mother</option>
                                        <option value="Spouse">Spouse</option>
                                        <option value="Son">Son</option>
                                        <option value="Daughter">Daughter</option>
                                        <option value="Brother">Brother</option>
                                        <option value="Sister">Sister</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <small class="text-primary d-none" id="relationSuggestion"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit - Full Width -->
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <button type="button" class="btn btn-light" onclick="goToStep(2)">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </button>
                    <button type="submit" class="btn btn-primary btn-search-lg" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner"></span>
                        <i class="ri-check-line me-1" id="submitIcon"></i>Register Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div><!-- End registration-page-wrapper -->

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="success-check-icon mb-4">
                    <div class="success-icon mx-auto">
                        <i class="ri-check-line"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-success mb-2">Patient Registered!</h4>
                <p class="text-muted mb-4">Patient has been successfully registered.</p>
                <div class="bg-light rounded p-3 mb-4">
                    <span class="text-muted fs-13 d-block">Medical Record Number</span>
                    <h3 class="mb-0 text-primary fw-bold" id="successMRN"></h3>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-success px-4" onclick="registerAnother()">
                        <i class="ri-add-line me-1"></i>Register Another
                    </button>
                    <a href="<?= baseUrl('/patients') ?>" class="btn btn-primary px-4">
                        <i class="ri-eye-line me-1"></i>View Patients
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="ri-error-warning-line fs-5 me-2"></i>
                <span id="errorToastMsg">An error occurred</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <div id="warningToast" class="toast align-items-center text-dark bg-warning border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="ri-alert-line fs-5 me-2"></i>
                <span id="warningToastMsg">Warning</span>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    // State
    let currentStep = 1;
    let searchedMobile = '';
    let selectedFamilyId = null;
    let isNewFamily = false;
    let familyData = null;
    let selectedFamilyMembers = []; // Store family members for relation suggestion

    // Toast helpers
    function showError(msg) {
        document.getElementById('errorToastMsg').textContent = msg;
        const toast = new bootstrap.Toast(document.getElementById('errorToast'));
        toast.show();
    }

    function showWarning(msg) {
        document.getElementById('warningToastMsg').textContent = msg;
        const toast = new bootstrap.Toast(document.getElementById('warningToast'));
        toast.show();
    }

    function showSuccess(mrn) {
        document.getElementById('successMRN').textContent = mrn;
        const modalEl = document.getElementById('successModal');
        // Dispose any existing instance first
        const existingModal = bootstrap.Modal.getInstance(modalEl);
        if (existingModal) {
            existingModal.dispose();
        }
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function registerAnother() {
        const modalEl = document.getElementById('successModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
        // Ensure backdrop is removed
        modalEl.addEventListener('hidden.bs.modal', function handler() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
            // Force remove any leftover backdrops
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }, { once: true });

        document.getElementById('patientForm').reset();
        goToStep(1);
        document.getElementById('mobileInput').value = '';
        document.getElementById('mobileInput').focus();
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('mobileInput').focus();

        // Form submit
        document.getElementById('mobileSearchForm').addEventListener('submit', function (e) {
            e.preventDefault();
            searchMobile(e);
        });

        // Form submit
        document.getElementById('patientForm').addEventListener('submit', function (e) {
            e.preventDefault();
            submitPatient();
        });

        // DOB change - calculate age
        document.getElementById('dob').addEventListener('change', function () {
            if (this.value) {
                const dob = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
                document.getElementById('age').value = age;
            }
        });
    });

    function goToStep(step) {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));

        // Show target step
        document.getElementById('step' + step).classList.remove('d-none');

        // Toggle wide form class for Step 3
        const container = document.querySelector('.registration-container');
        if (step === 3) {
            container.classList.add('wide-form');
        } else {
            container.classList.remove('wide-form');
        }

        // Update step indicators
        for (let i = 1; i <= 3; i++) {
            const circle = document.getElementById('step' + i + 'Circle');
            circle.classList.remove('active', 'completed', 'pending');

            if (i < step) {
                circle.classList.add('completed');
                circle.innerHTML = '<i class="ri-check-line"></i>';
            } else if (i === step) {
                circle.classList.add('active');
                circle.textContent = i;
            } else {
                circle.classList.add('pending');
                circle.textContent = i;
            }
        }

        // Update step lines
        document.getElementById('stepLine1').classList.toggle('completed', step > 1);
        document.getElementById('stepLine2').classList.toggle('completed', step > 2);

        currentStep = step;
    }

    async function searchMobile(e) {
        if (e) e.preventDefault();
        const mobile = document.getElementById('mobileInput').value.trim();

        if (mobile.length !== 10) {
            document.getElementById('mobileInput').classList.add('is-invalid');
            document.getElementById('mobileError').textContent = 'Please enter a valid 10-digit mobile number';
            return;
        }

        document.getElementById('mobileInput').classList.remove('is-invalid');
        searchedMobile = mobile;

        // Show loading
        document.getElementById('searchSpinner').classList.remove('d-none');
        document.getElementById('searchIcon').classList.add('d-none');
        document.getElementById('searchMobileBtn').disabled = true;

        try {
            const res = await fetch('/api/v1/patients/family-lookup?mobile=' + mobile);
            const data = await res.json();

            if (data.success) {
                renderFamilyResults(data.data);
                document.getElementById('displayMobile').textContent = '+91 ' + mobile;
                goToStep(2);
            } else {
                showError(data.message || 'Failed to search');
            }
        } catch (e) {
            showError('Network error. Please try again.');
        } finally {
            document.getElementById('searchSpinner').classList.add('d-none');
            document.getElementById('searchIcon').classList.remove('d-none');
            document.getElementById('searchMobileBtn').disabled = false;
        }
    }

    function renderFamilyResults(data) {
        const container = document.getElementById('familyResults');
        let html = '';

        // Store all families data for later use
        familyData = data;

        if (data.families && data.families.length > 0) {
            html += '<div class="alert alert-info mb-4"><i class="ri-information-line me-2"></i>We found existing patients with this mobile number. Select a family to add this patient, or create a new family.</div>';

            // Use full width (col-12) for single family, half width (col-md-6) for multiple
            const colClass = data.families.length === 1 ? 'col-12' : 'col-md-6';

            html += '<div class="row g-3 mb-4">';
            data.families.forEach((family, idx) => {
                html += `
                <div class="${colClass}">
                    <div class="family-tree-card p-3" data-family-id="${family.family_id}" data-family-idx="${idx}" onclick="selectFamily('${family.family_id}', this, ${idx})">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0"><i class="ri-home-heart-line me-2 text-primary"></i>Family #${family.family_id}</h6>
                            <span class="badge bg-primary-transparent">${family.members.length} member${family.members.length > 1 ? 's' : ''}</span>
                        </div>
                        <div class="family-members">
                            ${family.members.map(m => `
                                <div class="family-member d-flex align-items-center gap-2" 
                                     data-patient-id="${m.patient_id}" 
                                     data-name="${escapeHtml(m.full_name)}" 
                                     data-age="${m.age || ''}" 
                                     data-gender="${m.gender || ''}">
                                    <span class="avatar avatar-sm ${m.gender === 'male' ? 'bg-primary-transparent' : 'bg-pink-transparent'} rounded-circle">
                                        ${getInitials(m.full_name)}
                                    </span>
                                    <div class="flex-fill">
                                        <span class="d-block fw-medium fs-13">${escapeHtml(m.full_name)}</span>
                                        <span class="text-muted fs-11">${m.relation || 'Member'} • ${m.age ? m.age + ' yrs' : ''} ${m.gender ? '• ' + capitalize(m.gender) : ''}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
            });

            // Add "Create New Family" option in the same row
            html += `
            <div class="${colClass}">
                <div class="new-family-option h-100" onclick="selectNewFamily(this)">
                    <i class="ri-add-circle-line fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-1">Create New Family</h6>
                    <p class="text-muted mb-0 fs-13">Start a new family tree with this patient as the head</p>
                </div>
            </div>
        `;
            html += '</div>';
        } else {
            html += '<div class="alert alert-success mb-4"><i class="ri-checkbox-circle-line me-2"></i>No existing patients found with this mobile number. A new family will be created.</div>';

            // New family option - full width when no existing families
            html += `
            <div class="new-family-option selected" onclick="selectNewFamily(this)">
                <i class="ri-add-circle-line fs-1 text-primary mb-2 d-block"></i>
                <h6 class="mb-1">Create New Family</h6>
                <p class="text-muted mb-0 fs-13">Start a new family tree with this patient as the head</p>
            </div>
        `;
        }

        container.innerHTML = html;

        // Auto-select new family if no existing families
        if (!data.families || data.families.length === 0) {
            isNewFamily = true;
            selectedFamilyId = null;
            selectedFamilyMembers = [];
            document.getElementById('continueToStep3').disabled = false;
        }
    }

    function selectFamily(familyId, element, familyIdx) {
        // Deselect all
        document.querySelectorAll('.family-tree-card').forEach(el => el.classList.remove('selected'));
        document.querySelector('.new-family-option')?.classList.remove('selected');

        // Select this one
        element.classList.add('selected');
        selectedFamilyId = familyId;
        isNewFamily = false;

        // Store family members from the API data
        if (familyData && familyData.families && familyData.families[familyIdx]) {
            selectedFamilyMembers = familyData.families[familyIdx].members.map(m => ({
                patient_id: m.patient_id,
                name: m.full_name,
                age: m.age,
                gender: m.gender,
                relation: m.relation
            }));
        } else {
            // Fallback: extract from DOM
            const familyMembers = element.querySelectorAll('.family-member');
            selectedFamilyMembers = [];
            familyMembers.forEach(memberEl => {
                selectedFamilyMembers.push({
                    patient_id: memberEl.dataset.patientId,
                    name: memberEl.dataset.name,
                    age: memberEl.dataset.age ? parseInt(memberEl.dataset.age) : null,
                    gender: memberEl.dataset.gender || null
                });
            });
        }

        document.getElementById('continueToStep3').disabled = false;
    }

    function selectNewFamily(element) {
        // Deselect all
        document.querySelectorAll('.family-tree-card').forEach(el => el.classList.remove('selected'));
        document.querySelector('.new-family-option')?.classList.remove('selected');

        // Select new family
        element.classList.add('selected');
        selectedFamilyId = null;
        isNewFamily = true;
        selectedFamilyMembers = []; // Clear family members
        document.getElementById('continueToStep3').disabled = false;
    }

    // Override goToStep for step 3 to set context
    const originalGoToStep = goToStep;
    goToStep = function (step) {
        originalGoToStep(step);

        if (step === 3) {
            document.getElementById('patientMobile').value = searchedMobile;
            document.getElementById('displayMobileField').value = searchedMobile;
            document.getElementById('familyId').value = selectedFamilyId || '';

            const relatedToSection = document.getElementById('relatedToSection');
            const relatedToDisplay = document.getElementById('related_to_display');
            const relatedToSelect = document.getElementById('related_to_select');
            const relationshipSelect = document.getElementById('relationship_select');
            const familyRelationSuggestion = document.getElementById('familyRelationSuggestion');

            if (isNewFamily) {
                // First patient = Family Head (no relation selection needed)
                document.getElementById('familyContext').textContent = 'Creating new family with mobile +91 ' + searchedMobile;
                document.getElementById('relation').value = 'self';
                document.getElementById('related_to_patient_id').value = '';
                document.getElementById('relation_to_member').value = '';
                relatedToSection.classList.add('d-none');
                relatedToDisplay.value = '';
                relatedToSelect.value = '';
                relationshipSelect.value = '';
                familyRelationSuggestion.classList.add('d-none');
                document.getElementById('hierarchyPreview').classList.add('d-none');
            } else {
                // Adding to existing family - show relation selection
                document.getElementById('familyContext').textContent = 'Adding to Family #' + selectedFamilyId;
                document.getElementById('relation').value = '';

                relatedToSection.classList.remove('d-none');

                // Find the Family Head from selectedFamilyMembers
                const familyHead = selectedFamilyMembers.find(m => m.relation === 'Head' || m.relation === 'Self') || selectedFamilyMembers[0];

                if (familyHead) {
                    // Set read-only display showing Family Head
                    const ageInfo = familyHead.age ? ` (${familyHead.age} yrs)` : '';
                    const genderInfo = familyHead.gender ? ` - ${capitalize(familyHead.gender)}` : '';
                    relatedToDisplay.value = `${familyHead.name}${ageInfo}${genderInfo} [Head]`;

                    // Store head data in hidden field and data attributes
                    relatedToSelect.value = familyHead.patient_id;
                    relatedToSelect.dataset.age = familyHead.age || '';
                    relatedToSelect.dataset.gender = familyHead.gender || '';
                    relatedToSelect.dataset.name = familyHead.name;
                    relatedToSelect.dataset.relation = 'Head';

                    // Trigger relationship suggestion after a short delay to ensure form is ready
                    setTimeout(() => {
                        suggestFamilyMemberRelation();
                    }, 100);
                }

                // Clear previous selections
                relationshipSelect.value = '';
                familyRelationSuggestion.classList.add('d-none');
                document.getElementById('hierarchyPreview').classList.add('d-none');
            }
        }
    };

    async function submitPatient() {
        const form = document.getElementById('patientForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((v, k) => data[k] = v);

        // Clear previous validation states
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        // Validation
        let hasError = false;

        // First name validation
        const firstName = data.first_name?.trim();
        if (!firstName) {
            showFieldError('first_name', 'First name is required');
            hasError = true;
        } else if (firstName.length < 2) {
            showFieldError('first_name', 'First name must be at least 2 characters');
            hasError = true;
        } else if (!/^[a-zA-Z\s]+$/.test(firstName)) {
            showFieldError('first_name', 'First name should only contain letters');
            hasError = true;
        }

        // Last name validation (optional but if provided, validate)
        const lastName = data.last_name?.trim();
        if (lastName && !/^[a-zA-Z\s]*$/.test(lastName)) {
            showFieldError('last_name', 'Last name should only contain letters');
            hasError = true;
        }

        // Gender validation
        if (!document.querySelector('input[name="gender"]:checked')) {
            showWarning('Please select gender');
            document.querySelector('.btn-group[role="group"]').classList.add('border', 'border-danger', 'rounded');
            hasError = true;
        } else {
            data.gender = document.querySelector('input[name="gender"]:checked').value;
            document.querySelector('.btn-group[role="group"]').classList.remove('border', 'border-danger', 'rounded');
        }

        // DOB validation - should not be future date
        if (data.dob) {
            const dobDate = new Date(data.dob);
            const today = new Date();
            if (dobDate > today) {
                showFieldError('dob', 'Date of birth cannot be in the future');
                hasError = true;
            }
        }

        // Age validation
        if (data.age && (parseInt(data.age) < 0 || parseInt(data.age) > 150)) {
            showFieldError('age', 'Please enter a valid age (0-150)');
            hasError = true;
        }

        // Relationship validation (required when adding to existing family)
        if (!isNewFamily) {
            const relatedTo = document.getElementById('related_to_select').value;
            const relationship = document.getElementById('relationship_select').value;

            if (!relatedTo) {
                showWarning('Family head not found');
                hasError = true;
            }
            if (!relationship) {
                showFieldError('relationship_select', 'Please select the relationship');
                hasError = true;
            }

            // Update hidden fields
            data.related_to_patient_id = relatedTo;
            data.relation_to_member = relationship;
        }

        // Email validation
        const email = data.email?.trim();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showFieldError('email', 'Please enter a valid email address');
            hasError = true;
        }

        // Pincode validation
        const pincode = data.pincode?.trim();
        if (pincode && !/^\d{6}$/.test(pincode)) {
            showFieldError('pincode', 'Pincode must be 6 digits');
            hasError = true;
        }

        // Emergency contact mobile validation
        const emergencyMobile = data.emergency_contact_mobile?.trim();
        if (emergencyMobile && !/^[0-9]{10}$/.test(emergencyMobile.replace(/\D/g, ''))) {
            showFieldError('emergency_contact_mobile', 'Please enter a valid 10-digit mobile number');
            hasError = true;
        }

        if (hasError) {
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return;
        }

        // Show loading
        document.getElementById('submitSpinner').classList.remove('d-none');
        document.getElementById('submitIcon').classList.add('d-none');
        document.getElementById('submitBtn').disabled = true;

        try {
            const res = await fetch('/api/v1/patients', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(data)
            });
            const result = await res.json();

            if (result.success) {
                showSuccess(result.data.mrn);
            } else {
                showError(result.message || 'Failed to register patient');
            }
        } catch (e) {
            showError('Network error. Please try again.');
        } finally {
            document.getElementById('submitSpinner').classList.add('d-none');
            document.getElementById('submitIcon').classList.remove('d-none');
            document.getElementById('submitBtn').disabled = false;
        }
    }

    // Show field validation error
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = message;
            field.parentNode.appendChild(feedback);
        }
    }

    // Clear field error on input
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#patientForm input, #patientForm select, #patientForm textarea').forEach(field => {
            field.addEventListener('input', function () {
                this.classList.remove('is-invalid');
                const feedback = this.parentNode.querySelector('.invalid-feedback');
                if (feedback) feedback.remove();
            });
        });
    });

    // Helpers
    function escapeHtml(str) { return str ? String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]) : ''; }
    function capitalize(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }
    function getInitials(name) { return name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : '?'; }

    // ============================================
    // BLOOD RELATIONSHIP AUTO-FILL LOGIC
    // ============================================
    let emergencyPatientData = null;
    let searchTimeout = null;

    // Initialize emergency contact mobile input
    document.addEventListener('DOMContentLoaded', function () {
        const emergencyMobileInput = document.getElementById('emergency_contact_mobile');
        if (emergencyMobileInput) {
            // Only allow numbers
            emergencyMobileInput.addEventListener('input', function (e) {
                this.value = this.value.replace(/\D/g, '').slice(0, 10);

                // Auto-search when 10 digits entered
                if (this.value.length === 10) {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => searchEmergencyContact(), 500);
                } else {
                    // Clear previous match info
                    document.getElementById('emergencyMatchInfo').classList.add('d-none');
                    document.getElementById('relationSuggestion').classList.add('d-none');
                    emergencyPatientData = null;
                }
            });
        }
    });

    // Search for existing patient by emergency contact mobile
    async function searchEmergencyContact() {
        const mobile = document.getElementById('emergency_contact_mobile').value.trim();

        if (mobile.length !== 10) {
            showWarning('Please enter a valid 10-digit mobile number');
            return;
        }

        const searchBtn = document.getElementById('searchEmergencyBtn');
        const originalHtml = searchBtn.innerHTML;
        searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        searchBtn.disabled = true;

        try {
            const res = await fetch('/api/v1/patients/search-by-emergency-mobile?mobile=' + mobile);
            const data = await res.json();

            if (data.success && data.data.patient) {
                emergencyPatientData = data.data.patient;
                autoFillEmergencyContact(data.data.patient);
            } else {
                // No match found - clear suggestions
                document.getElementById('emergencyMatchInfo').classList.add('d-none');
                document.getElementById('relationSuggestion').classList.add('d-none');
                emergencyPatientData = null;
            }
        } catch (e) {
            console.error('Error searching emergency contact:', e);
        } finally {
            searchBtn.innerHTML = originalHtml;
            searchBtn.disabled = false;
        }
    }

    // Auto-fill emergency contact details from existing patient
    function autoFillEmergencyContact(patient) {
        // Auto-fill name (editable)
        const nameField = document.getElementById('emergency_contact_name');
        nameField.value = patient.full_name;

        // Show match info
        const matchInfo = document.getElementById('emergencyMatchInfo');
        matchInfo.innerHTML = `<i class="ri-checkbox-circle-line me-1"></i>Found: ${patient.full_name} (${patient.mrn})`;
        matchInfo.classList.remove('d-none');

        // Suggest blood relationship based on age
        suggestBloodRelation(patient);
    }

    // Suggest blood relationship based on age comparison
    function suggestBloodRelation(emergencyPatient) {
        const newPatientAge = getNewPatientAge();
        const emergencyAge = emergencyPatient.age;
        const emergencyGender = emergencyPatient.gender;

        if (!emergencyAge || !newPatientAge) {
            return; // Can't suggest without ages
        }

        const ageDiff = emergencyAge - newPatientAge; // Positive = emergency contact is older
        let suggestedRelation = '';
        let suggestionText = '';

        // Blood relationship logic based on age difference
        if (ageDiff >= 15 && ageDiff <= 50) {
            // Emergency contact is 15-50 years older - likely parent
            if (emergencyGender === 'male') {
                suggestedRelation = 'Father';
                suggestionText = `Suggested: Father (${emergencyAge} yrs is ${ageDiff} yrs older)`;
            } else if (emergencyGender === 'female') {
                suggestedRelation = 'Mother';
                suggestionText = `Suggested: Mother (${emergencyAge} yrs is ${ageDiff} yrs older)`;
            } else {
                suggestedRelation = 'Other';
                suggestionText = `Suggested: Parent (${emergencyAge} yrs is ${ageDiff} yrs older)`;
            }
        } else if (ageDiff >= -50 && ageDiff <= -15) {
            // Emergency contact is 15-50 years younger - likely child
            if (emergencyGender === 'male') {
                suggestedRelation = 'Son';
                suggestionText = `Suggested: Son (${emergencyAge} yrs is ${Math.abs(ageDiff)} yrs younger)`;
            } else if (emergencyGender === 'female') {
                suggestedRelation = 'Daughter';
                suggestionText = `Suggested: Daughter (${emergencyAge} yrs is ${Math.abs(ageDiff)} yrs younger)`;
            } else {
                suggestedRelation = 'Other';
                suggestionText = `Suggested: Child (${emergencyAge} yrs is ${Math.abs(ageDiff)} yrs younger)`;
            }
        } else if (ageDiff >= 40) {
            // Emergency contact is 40+ years older - likely grandparent
            if (emergencyGender === 'male') {
                suggestedRelation = 'Grandfather';
                suggestionText = `Suggested: Grandfather (${emergencyAge} yrs is ${ageDiff} yrs older)`;
            } else if (emergencyGender === 'female') {
                suggestedRelation = 'Grandmother';
                suggestionText = `Suggested: Grandmother (${emergencyAge} yrs is ${ageDiff} yrs older)`;
            } else {
                suggestedRelation = 'Other';
                suggestionText = `Suggested: Grandparent (${emergencyAge} yrs is ${ageDiff} yrs older)`;
            }
        } else {
            // Other cases - set to Other
            suggestedRelation = 'Other';
            suggestionText = `Age difference: ${Math.abs(ageDiff)} yrs`;
        }

        // Show suggestion and auto-select
        const relationSelect = document.getElementById('emergency_contact_relation');
        const suggestionEl = document.getElementById('relationSuggestion');

        if (suggestedRelation) {
            // Auto-select the suggested relation
            relationSelect.value = suggestedRelation;

            // Show suggestion text
            suggestionEl.innerHTML = `<i class="ri-lightbulb-line me-1"></i>${suggestionText}`;
            suggestionEl.classList.remove('d-none');
        } else {
            suggestionEl.classList.add('d-none');
        }
    }

    // Get new patient's age from form
    function getNewPatientAge() {
        const ageField = document.getElementById('age');
        const dobField = document.getElementById('dob');

        // Check age field first - allow 0 as valid age
        if (ageField.value !== '' && ageField.value !== null) {
            const age = parseInt(ageField.value);
            if (!isNaN(age) && age >= 0) {
                return age;
            }
        }

        if (dobField.value) {
            const dob = new Date(dobField.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
            return Math.max(0, age); // Ensure non-negative
        }

        return null;
    }

    // Re-calculate suggestion when patient age or gender changes
    document.addEventListener('DOMContentLoaded', function () {
        // When DOB changes, recalculate suggestion
        document.getElementById('dob')?.addEventListener('change', function () {
            if (emergencyPatientData) {
                suggestBloodRelation(emergencyPatientData);
            }
            // Also recalculate family member relation
            recalculateFamilyMemberRelation();
        });

        // When age changes, recalculate suggestion
        document.getElementById('age')?.addEventListener('input', function () {
            if (emergencyPatientData) {
                setTimeout(() => suggestBloodRelation(emergencyPatientData), 300);
            }
            // Also recalculate family member relation
            setTimeout(() => recalculateFamilyMemberRelation(), 300);
        });

        // When gender changes, recalculate suggestion
        document.querySelectorAll('input[name="gender"]').forEach(radio => {
            radio.addEventListener('change', function () {
                if (emergencyPatientData) {
                    suggestBloodRelation(emergencyPatientData);
                }
                // Also recalculate family member relation
                recalculateFamilyMemberRelation();
            });
        });

        // When "Related To" family member changes - no longer needed as it's read-only
        // document.getElementById('related_to_select')?.addEventListener('change', function() {
        //     suggestFamilyMemberRelation();
        // });

        // Update first name field to trigger hierarchy preview update
        document.getElementById('first_name')?.addEventListener('input', function () {
            updateHierarchyPreview();
        });
    });

    // ============================================
    // FAMILY MEMBER BLOOD RELATION AUTO-SUGGEST
    // ============================================

    // Initialize family relation event listeners
    document.addEventListener('DOMContentLoaded', function () {
        // When relationship changes
        document.getElementById('relationship_select')?.addEventListener('change', function () {
            updateHierarchyPreview();
            updateHiddenFields();
        });
    });

    // Update hidden form fields based on selections
    function updateHiddenFields() {
        const relatedToSelect = document.getElementById('related_to_select');
        const relationshipSelect = document.getElementById('relationship_select');

        document.getElementById('related_to_patient_id').value = relatedToSelect.value || '';
        document.getElementById('relation_to_member').value = relationshipSelect.value || '';

        // Set relation to family head
        document.getElementById('relation').value = relationshipSelect.value ? relationshipSelect.value.toLowerCase() : '';
    }

    // Suggest blood relation based on Family Head
    function suggestFamilyMemberRelation() {
        const relatedToSelect = document.getElementById('related_to_select');
        const relationshipSelect = document.getElementById('relationship_select');
        const suggestionEl = document.getElementById('familyRelationSuggestion');

        if (!relatedToSelect.value) {
            relationshipSelect.value = '';
            suggestionEl.classList.add('d-none');
            return;
        }

        const memberAge = parseInt(relatedToSelect.dataset.age) || null;
        const memberGender = relatedToSelect.dataset.gender || null;
        const newPatientAge = getNewPatientAge();
        const newPatientGender = document.querySelector('input[name="gender"]:checked')?.value || null;

        // Allow age 0 (infant) - check for null/undefined specifically
        if (memberAge === null || newPatientAge === null) {
            suggestionEl.innerHTML = '<i class="ri-information-line me-1"></i>Enter patient age/DOB to get relation suggestion';
            suggestionEl.classList.remove('d-none');
            return;
        }

        // If gender not selected yet, show hint
        if (!newPatientGender) {
            suggestionEl.innerHTML = '<i class="ri-information-line me-1"></i>Select gender to auto-suggest relationship';
            suggestionEl.classList.remove('d-none');
            return;
        }

        const ageDiff = memberAge - newPatientAge; // Positive = family head is older
        let suggestedRelation = '';
        let suggestionText = '';

        // Simplified relationship logic: Self, Son, Daughter, Father, Mother, Spouse, Other
        if (ageDiff >= 15) {
            // Family Head is 15+ years older - new patient is likely their child
            if (newPatientGender === 'male') {
                suggestedRelation = 'Son';
                suggestionText = `Suggested: Son (you are ${Math.abs(ageDiff)} yrs younger than Head)`;
            } else if (newPatientGender === 'female') {
                suggestedRelation = 'Daughter';
                suggestionText = `Suggested: Daughter (you are ${Math.abs(ageDiff)} yrs younger than Head)`;
            }
        } else if (ageDiff <= -15) {
            // Family Head is 15+ years younger - new patient is likely their parent
            if (newPatientGender === 'male') {
                suggestedRelation = 'Father';
                suggestionText = `Suggested: Father (you are ${Math.abs(ageDiff)} yrs older than Head)`;
            } else if (newPatientGender === 'female') {
                suggestedRelation = 'Mother';
                suggestionText = `Suggested: Mother (you are ${Math.abs(ageDiff)} yrs older than Head)`;
            }
        } else if (Math.abs(ageDiff) < 15) {
            // Similar age (within 15 years) - likely spouse
            suggestedRelation = 'Spouse';
            suggestionText = `Suggested: Spouse (similar age, ${Math.abs(ageDiff)} yrs diff)`;
        }

        if (suggestedRelation) {
            relationshipSelect.value = suggestedRelation;
            suggestionEl.innerHTML = `<i class="ri-lightbulb-line me-1"></i>${suggestionText}`;
            suggestionEl.classList.remove('d-none');
            updateHierarchyPreview();
            updateHiddenFields();
        } else {
            suggestedRelation = 'Other';
            relationshipSelect.value = 'Other';
            suggestionEl.innerHTML = `<i class="ri-information-line me-1"></i>Age difference: ${Math.abs(ageDiff)} yrs - Set as Other`;
            suggestionEl.classList.remove('d-none');
            updateHierarchyPreview();
            updateHiddenFields();
        }
    }

    // Update hierarchy preview showing how family tree will look
    function updateHierarchyPreview() {
        const relatedToSelect = document.getElementById('related_to_select');
        const relationshipSelect = document.getElementById('relationship_select');
        const previewEl = document.getElementById('hierarchyPreview');
        const hierarchyText = document.getElementById('hierarchyText');

        if (!relatedToSelect.value || !relationshipSelect.value) {
            previewEl.classList.add('d-none');
            return;
        }

        const memberName = relatedToSelect.dataset.name || 'Family Head';
        const relationship = relationshipSelect.value;
        const newPatientName = document.getElementById('first_name').value || 'New Patient';

        let preview = '';

        if (relationship === 'Self') {
            preview = `<span class="text-primary fw-bold">${newPatientName}</span> is the same person as ${memberName}`;
        } else if (relationship === 'Father' || relationship === 'Mother') {
            // New patient is parent of head - they become new head
            preview = `<span class="text-success fw-bold">${newPatientName}</span> (New Head) → ${memberName} (Child)`;
            hierarchyText.innerHTML = preview + '<br><small class="text-muted">Family head will be updated automatically</small>';
            previewEl.classList.remove('d-none');
            return;
        } else if (relationship === 'Son' || relationship === 'Daughter') {
            preview = `${memberName} → <span class="text-primary fw-bold">${newPatientName}</span> (${relationship})`;
        } else if (relationship === 'Spouse') {
            preview = `${memberName} ↔ <span class="text-primary fw-bold">${newPatientName}</span> (${relationship})`;
        } else {
            preview = `${memberName} → <span class="text-primary fw-bold">${newPatientName}</span> (${relationship})`;
        }

        hierarchyText.innerHTML = preview;
        previewEl.classList.remove('d-none');
    }

    // Recalculate family member relation when patient details change
    function recalculateFamilyMemberRelation() {
        const select = document.getElementById('related_to_select');
        if (select && select.value) {
            suggestFamilyMemberRelation();
        }
    }
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>