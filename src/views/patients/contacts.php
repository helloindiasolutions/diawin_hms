<?php
/**
 * Patient Contacts - Emergency contacts list
 */
$pageTitle = 'Patient Contacts';
ob_start();
?>

<link rel="stylesheet" href="<?= asset('libs/sweetalert2/sweetalert2.min.css') ?>">
<style>
    /* Compact table styling */
    #contactsTable {
        font-size: 0.8125rem;
    }

    #contactsTable th {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6c757d;
    }

    #contactsTable td {
        font-size: 0.8125rem;
        vertical-align: middle;
        padding: 0.625rem 0.75rem;
    }

    #contactsTable .fw-medium {
        font-size: 0.8125rem;
    }

    #contactsTable .fs-11 {
        font-size: 0.6875rem !important;
    }

    #contactsTable .fs-12 {
        font-size: 0.75rem !important;
    }

    /* Clickable rows */
    #contactsTable tbody tr.contact-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    #contactsTable tbody tr.contact-row:hover {
        background-color: rgba(var(--primary-rgb), 0.04);
    }

    .badge {
        font-size: 0.6875rem;
        padding: 0.25em 0.5em;
    }

    @media (max-width: 768px) {
        #contactsTable {
            font-size: 0.75rem;
        }

        #contactsTable td {
            padding: 0.5rem 0.625rem;
        }
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<!-- Page Header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-0">Patient Contacts</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= baseUrl('/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= baseUrl('/patients') ?>">Patients</a></li>
                    <li class="breadcrumb-item active">Contacts</li>
                </ol>
            </nav>
        </div>
        <div class="btn-list">
            <button class="btn btn-success-light" onclick="exportContacts()">
                <i class="ri-download-2-line me-1"></i>Export
            </button>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group" style="width: 280px;">
                        <span class="input-group-text bg-transparent border-end-0"><i class="ri-search-line"></i></span>
                        <input type="text" class="form-control border-start-0" id="searchInput"
                            placeholder="Search patient or contact...">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select class="form-select" id="filterRelation" style="width: 150px;">
                        <option value="">All Relations</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Parent">Parent</option>
                        <option value="Child">Child</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Friend">Friend</option>
                        <option value="Other">Other</option>
                    </select>
                    <button class="btn btn-outline-secondary" onclick="resetFilters()" title="Reset Filters">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0" id="contactsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>MRN</th>
                                <th>Emergency Contact</th>
                                <th>Contact Mobile</th>
                                <th>Relation</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="contactsTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="ms-2">Loading contacts...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="text-muted fs-13" id="paginationInfo">Showing 0 of 0 contacts</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Contact Detail Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="contactDetailOffcanvas" style="width: 420px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Contact Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0" id="contactDetailContent">
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>
    </div>
</div>

<!-- Edit Contact Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Emergency Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editContactForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="editPatientId" name="patient_id">
                    <div class="mb-3">
                        <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editContactName" name="emergency_contact_name"
                            required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Relation <span class="text-danger">*</span></label>
                        <select class="form-select" id="editContactRelation" name="emergency_contact_relation" required>
                            <option value="">Select Relation</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Parent">Parent</option>
                            <option value="Child">Child</option>
                            <option value="Sibling">Sibling</option>
                            <option value="Friend">Friend</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Mobile <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+91</span>
                            <input type="tel" class="form-control" id="editContactMobile"
                                name="emergency_contact_mobile" maxlength="10" pattern="[0-9]{10}" required>
                        </div>
                        <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveContactBtn">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="saveSpinner"></span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script src="<?= asset('libs/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>
    let currentPage = 1;
    let searchTimeout = null;

    document.addEventListener('DOMContentLoaded', function () {
        loadContacts();
        setupEventListeners();
    });

    function setupEventListeners() {
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => { currentPage = 1; loadContacts(); }, 400);
        });

        document.getElementById('filterRelation').addEventListener('change', () => { currentPage = 1; loadContacts(); });

        document.getElementById('editContactForm').addEventListener('submit', function (e) {
            e.preventDefault();
            saveContact();
        });

    }

    async function loadContacts() {
        const tbody = document.getElementById('contactsTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div><span class="ms-2">Loading...</span></td></tr>';

        const params = new URLSearchParams({
            page: currentPage,
            limit: 20,
            search: document.getElementById('searchInput').value,
            relation: document.getElementById('filterRelation').value
        });

        try {
            const res = await fetch('/api/v1/patients/contacts?' + params);
            const data = await res.json();

            if (data.success) {
                renderContacts(data.data.contacts);
                renderPagination(data.data.pagination);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load contacts</td></tr>';
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error loading contacts</td></tr>';
        }
    }

    function renderContacts(contacts) {
        const tbody = document.getElementById('contactsTableBody');

        if (!contacts.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><i class="ri-contacts-book-line fs-48 text-muted d-block mb-2"></i><span class="text-muted">No emergency contacts found</span></td></tr>';
            return;
        }

        tbody.innerHTML = contacts.map(c => `
        <tr class="contact-row" data-patient-id="${c.patient_id}" onclick="viewContact(${c.patient_id})">
            <td>
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm avatar-rounded ${c.gender === 'male' ? 'bg-primary-transparent' : 'bg-pink-transparent'}">
                        ${getInitials(c.patient_name)}
                    </span>
                    <div>
                        <span class="fw-medium text-dark">${escapeHtml(c.patient_name)}</span>
                        <span class="d-block text-muted fs-11">${c.city || ''}</span>
                    </div>
                </div>
            </td>
            <td><span class="badge bg-light text-dark">${c.mrn}</span></td>
            <td>
                <span class="fw-medium">${escapeHtml(c.emergency_contact_name || '--')}</span>
            </td>
            <td>
                ${c.emergency_contact_mobile ? '<i class="ri-phone-line me-1 text-muted"></i>' + escapeHtml(c.emergency_contact_mobile) : '<span class="text-muted">--</span>'}
            </td>
            <td>
                ${c.emergency_contact_relation ? '<span class="badge bg-info-transparent">' + escapeHtml(c.emergency_contact_relation) + '</span>' : '<span class="text-muted">--</span>'}
            </td>
            <td onclick="event.stopPropagation()">
                <div class="btn-list">
                    <button class="btn btn-sm btn-info-light btn-icon" onclick="editContact(${c.patient_id})" title="Edit"><i class="ri-pencil-line"></i></button>
                    <a href="<?= baseUrl('/patients') ?>/${c.patient_id}/edit" class="btn btn-sm btn-primary-light btn-icon" title="View Patient"><i class="ri-user-line"></i></a>
                </div>
            </td>
        </tr>
    `).join('');
    }

    function renderPagination(pagination) {
        const { total, page, limit, total_pages } = pagination;
        const start = (page - 1) * limit + 1;
        const end = Math.min(page * limit, total);

        document.getElementById('paginationInfo').textContent = `Showing ${total ? start : 0}-${end} of ${total} contacts`;

        const paginationEl = document.getElementById('pagination');
        if (total_pages <= 1) { paginationEl.innerHTML = ''; return; }

        let html = '';
        html += `<li class="page-item ${page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="goToPage(${page - 1})">«</a></li>`;

        for (let i = Math.max(1, page - 2); i <= Math.min(total_pages, page + 2); i++) {
            html += `<li class="page-item ${i === page ? 'active' : ''}"><a class="page-link" href="#" onclick="goToPage(${i})">${i}</a></li>`;
        }

        html += `<li class="page-item ${page === total_pages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="goToPage(${page + 1})">»</a></li>`;
        paginationEl.innerHTML = html;
    }

    function goToPage(page) { currentPage = page; loadContacts(); }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterRelation').value = '';
        currentPage = 1;
        loadContacts();
    }

    async function viewContact(patientId) {
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('contactDetailOffcanvas'));
        offcanvas.show();

        document.getElementById('contactDetailContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        try {
            const res = await fetch('/api/v1/patients/' + patientId);
            const data = await res.json();
            if (data.success) renderContactDetail(data.data.patient);
        } catch (e) {
            document.getElementById('contactDetailContent').innerHTML = '<div class="text-center py-5 text-danger">Failed to load details</div>';
        }
    }

    function renderContactDetail(p) {
        document.getElementById('contactDetailContent').innerHTML = `
        <div class="p-3 bg-light border-bottom">
            <h6 class="mb-1"><i class="ri-user-heart-line me-2 text-primary"></i>Patient Information</h6>
        </div>
        <div class="p-3 border-bottom">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="avatar avatar-lg avatar-rounded ${p.gender === 'male' ? 'bg-primary-transparent' : 'bg-pink-transparent'}">
                    ${getInitials(p.full_name)}
                </span>
                <div>
                    <h5 class="mb-1">${escapeHtml(p.full_name)}</h5>
                    <span class="badge bg-light text-dark">${p.mrn}</span>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-6"><span class="text-muted fs-12 d-block">Mobile</span><span class="fw-medium">${p.mobile || '--'}</span></div>
                <div class="col-6"><span class="text-muted fs-12 d-block">Email</span><span class="fw-medium">${p.email || '--'}</span></div>
            </div>
        </div>
        <div class="p-3 bg-light border-bottom">
            <h6 class="mb-0"><i class="ri-heart-pulse-line me-2 text-danger"></i>Emergency Contact</h6>
        </div>
        <div class="p-3">
            ${p.emergency_contact_name ? `
                <div class="row g-3">
                    <div class="col-12">
                        <span class="text-muted fs-12 d-block">Contact Name</span>
                        <span class="fw-semibold fs-15">${escapeHtml(p.emergency_contact_name)}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted fs-12 d-block">Relation</span>
                        <span class="badge bg-info-transparent">${escapeHtml(p.emergency_contact_relation || '--')}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted fs-12 d-block">Mobile</span>
                        <span class="fw-medium">${p.emergency_contact_mobile ? '<i class="ri-phone-line me-1 text-success"></i>' + p.emergency_contact_mobile : '--'}</span>
                    </div>
                </div>
            ` : '<div class="text-center py-4 text-muted"><i class="ri-user-unfollow-line fs-32 d-block mb-2"></i>No emergency contact added</div>'}
        </div>
        <div class="p-3 border-top">
            <button class="btn btn-primary btn-sm w-100" onclick="editContact(${p.patient_id})">
                <i class="ri-pencil-line me-1"></i>${p.emergency_contact_name ? 'Edit' : 'Add'} Emergency Contact
            </button>
        </div>
    `;
    }

    async function editContact(patientId) {
        // Close offcanvas if open
        const offcanvasEl = document.getElementById('contactDetailOffcanvas');
        const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
        if (offcanvas) offcanvas.hide();

        // Fetch patient data
        try {
            const res = await fetch('/api/v1/patients/' + patientId);
            const data = await res.json();
            if (data.success) {
                const p = data.data.patient;
                document.getElementById('editPatientId').value = p.patient_id;
                document.getElementById('editContactName').value = p.emergency_contact_name || '';
                document.getElementById('editContactRelation').value = p.emergency_contact_relation || '';
                document.getElementById('editContactMobile').value = p.emergency_contact_mobile || '';

                const modal = new bootstrap.Modal(document.getElementById('editContactModal'));
                modal.show();
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load patient data' });
        }
    }

    async function saveContact() {
        const form = document.getElementById('editContactForm');
        const patientId = document.getElementById('editPatientId').value;

        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Validation
        let hasError = false;

        const name = document.getElementById('editContactName').value.trim();
        if (!name) {
            document.getElementById('editContactName').classList.add('is-invalid');
            document.getElementById('editContactName').nextElementSibling.textContent = 'Contact name is required';
            hasError = true;
        } else if (!/^[a-zA-Z\s]+$/.test(name)) {
            document.getElementById('editContactName').classList.add('is-invalid');
            document.getElementById('editContactName').nextElementSibling.textContent = 'Name should only contain letters';
            hasError = true;
        }

        const relation = document.getElementById('editContactRelation').value;
        if (!relation) {
            document.getElementById('editContactRelation').classList.add('is-invalid');
            document.getElementById('editContactRelation').nextElementSibling.textContent = 'Please select a relation';
            hasError = true;
        }

        const mobile = document.getElementById('editContactMobile').value.trim();
        if (!mobile) {
            document.getElementById('editContactMobile').classList.add('is-invalid');
            hasError = true;
        } else if (!/^\d{10}$/.test(mobile)) {
            document.getElementById('editContactMobile').classList.add('is-invalid');
            hasError = true;
        }

        if (hasError) return;

        // Show loading
        document.getElementById('saveSpinner').classList.remove('d-none');
        document.getElementById('saveContactBtn').disabled = true;

        try {
            const res = await fetch('/api/v1/patients/' + patientId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    emergency_contact_name: name,
                    emergency_contact_relation: relation,
                    emergency_contact_mobile: mobile
                })
            });
            const result = await res.json();

            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('editContactModal')).hide();
                Swal.fire({ icon: 'success', title: 'Saved!', timer: 1500, showConfirmButton: false });
                loadContacts();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Failed to save' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Network error' });
        } finally {
            document.getElementById('saveSpinner').classList.add('d-none');
            document.getElementById('saveContactBtn').disabled = false;
        }
    }

    function exportContacts() {
        const params = new URLSearchParams({
            search: document.getElementById('searchInput').value,
            relation: document.getElementById('filterRelation').value,
            export: 'csv'
        });
        window.open('/api/v1/patients/contacts/export?' + params, '_blank');
    }

    // Helpers
    function escapeHtml(str) { return str ? String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]) : ''; }
    function getInitials(name) { return name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : '?'; }
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>