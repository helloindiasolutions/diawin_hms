<?php
/**
 * Family Management
 * Clean, professional Zoho-inspired design
 */
$pageTitle = 'Family Management';
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Family Management</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/patients">Patients</a></li>
            <li class="breadcrumb-item active">Family Groups</li>
        </ol>
    </div>
</div>

<!-- Stats Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-primary-transparent rounded-circle">
                        <i class="ri-home-heart-line fs-22 text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="fw-bold mb-0" id="statFamilies">-</h4>
                        <span class="text-muted fs-12">Family Groups</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-success-transparent rounded-circle">
                        <i class="ri-user-heart-line fs-22 text-success"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="fw-bold mb-0" id="statTotal">-</h4>
                        <span class="text-muted fs-12">Total Patients</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-info-transparent rounded-circle">
                        <i class="ri-parent-line fs-22 text-info"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="fw-bold mb-0" id="statLinked">-</h4>
                        <span class="text-muted fs-12">Family Members</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-warning-transparent rounded-circle">
                        <i class="ri-user-line fs-22 text-warning"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="fw-bold mb-0" id="statIndividual">-</h4>
                        <span class="text-muted fs-12">Individual Patients</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Family Groups List -->
    <div class="col-xl-5">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <i class="ri-group-2-line me-1 text-primary"></i>Family Groups
                </div>
            </div>
            <div class="card-body border-bottom py-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="ri-phone-line text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" id="mobileSearch" 
                        placeholder="Enter mobile number to find family...">
                    <button class="btn btn-primary" type="button" onclick="searchFamily()">
                        <i class="ri-search-line me-1"></i>Search
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush family-list" id="familyList" style="max-height: 420px; overflow-y: auto;">
                    <!-- Initial State -->
                    <div class="text-center py-5" id="familyInitial">
                        <div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">
                            <i class="ri-search-line fs-28 text-muted"></i>
                        </div>
                        <p class="text-muted mb-1">Enter a mobile number</p>
                        <span class="text-muted fs-12">to find existing family groups</span>
                    </div>
                    <!-- Loading State -->
                    <div class="text-center py-5 d-none" id="familyLoading">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <p class="text-muted mt-2 mb-0 fs-13">Searching...</p>
                    </div>
                    <!-- Empty State -->
                    <div class="text-center py-5 d-none" id="familyEmpty">
                        <i class="ri-user-add-line fs-48 text-muted opacity-50 d-block mb-2"></i>
                        <p class="text-muted mb-2">No patients found with this mobile</p>
                        <a href="/patients/create" class="btn btn-sm btn-primary">
                            <i class="ri-add-line me-1"></i>Register New Patient
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Family Details Panel -->
    <div class="col-xl-7">
        <!-- Placeholder -->
        <div id="familyPlaceholder" class="card custom-card">
            <div class="card-body text-center py-5">
                <div class="avatar avatar-xxl bg-light rounded-circle mx-auto mb-3">
                    <i class="ri-family-line fs-48 text-muted"></i>
                </div>
                <h5 class="text-muted">Family Details</h5>
                <p class="text-muted fs-13 mb-0">Search by mobile number to view family members and relationships</p>
            </div>
        </div>

        <!-- Family Details (hidden initially) -->
        <div id="familyDetails" class="d-none">
            <!-- Family Header Card -->
            <div class="card custom-card">
                <div class="card-header bg-primary-transparent">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-primary text-white rounded-circle me-3">
                                <i class="ri-home-heart-line fs-22"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" id="detailFamilyMobile">-</h5>
                                <span class="badge bg-primary-transparent text-primary fs-11">
                                    <i class="ri-user-line me-1"></i><span id="detailMemberCount">0</span> Members
                                </span>
                            </div>
                        </div>
                        <a href="/patients/create" class="btn btn-sm btn-outline-primary">
                            <i class="ri-user-add-line me-1"></i>Add Member
                        </a>
                    </div>
                </div>
            </div>

            <!-- Family Members -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title mb-0">
                        <i class="ri-team-line me-1 text-primary"></i>Family Members
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Member</th>
                                    <th>Relationship</th>
                                    <th>Age / Gender</th>
                                    <th>Contact</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="membersTableBody">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="ri-user-search-line fs-24 d-block mb-1"></i>
                                        No members found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Info -->
            <div class="card custom-card bg-info-transparent border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <i class="ri-lightbulb-line fs-18 text-info me-2"></i>
                        <span class="fs-13">Family members are linked by shared mobile numbers. Add new family members during patient registration.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .family-list .list-group-item {
        cursor: pointer;
        border: none;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        transition: all 0.15s ease;
        padding: 1rem 1.25rem;
    }
    
    .family-list .list-group-item:hover {
        background-color: rgba(var(--primary-rgb), 0.04);
    }
    
    .family-list .list-group-item.active {
        background-color: rgba(var(--primary-rgb), 0.08);
        border-left: 3px solid var(--primary-color);
    }
    
    .family-list .list-group-item.active .member-name {
        color: var(--primary-color);
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #666;
    }
    
    .badge-head {
        background-color: rgba(var(--success-rgb), 0.15);
        color: var(--success-color);
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    let currentFamily = null;

    document.addEventListener('DOMContentLoaded', () => {
        fetchStats();
        
        // Enter key to search
        document.getElementById('mobileSearch').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') searchFamily();
        });
    });

    async function fetchStats() {
        try {
            const res = await fetch('/api/v1/patients/stats');
            const data = await res.json();
            if (data.success) {
                document.getElementById('statTotal').innerText = data.data.total_patients || 0;
            }
        } catch (e) {
            console.error(e);
        }
        
        // These would need specific endpoints - setting reasonable defaults
        document.getElementById('statFamilies').innerText = '-';
        document.getElementById('statLinked').innerText = '-';
        document.getElementById('statIndividual').innerText = '-';
    }

    async function searchFamily() {
        const mobile = document.getElementById('mobileSearch').value.trim();
        
        if (!mobile || mobile.length < 10) {
            alert('Please enter a valid 10-digit mobile number');
            return;
        }

        const list = document.getElementById('familyList');
        const initial = document.getElementById('familyInitial');
        const loading = document.getElementById('familyLoading');
        const empty = document.getElementById('familyEmpty');

        // Show loading
        initial.classList.add('d-none');
        empty.classList.add('d-none');
        loading.classList.remove('d-none');
        list.querySelectorAll('.family-item').forEach(i => i.remove());

        try {
            const res = await fetch(`/api/v1/patients/family-lookup?mobile=${encodeURIComponent(mobile)}`);
            const data = await res.json();
            loading.classList.add('d-none');

            if (data.success && data.data.families && data.data.families.length > 0) {
                currentFamily = data.data.families[0];
                showFamilyDetails(currentFamily);
                
                // Update stats
                document.getElementById('statFamilies').innerText = data.data.families.length;
                document.getElementById('statLinked').innerText = data.data.total_patients || 0;
            } else {
                empty.classList.remove('d-none');
                document.getElementById('familyPlaceholder').classList.remove('d-none');
                document.getElementById('familyDetails').classList.add('d-none');
            }
        } catch (e) {
            loading.classList.add('d-none');
            initial.classList.remove('d-none');
            console.error('Error searching family:', e);
        }
    }

    function showFamilyDetails(family) {
        // Show details panel
        document.getElementById('familyPlaceholder').classList.add('d-none');
        document.getElementById('familyDetails').classList.remove('d-none');

        // Update header
        document.getElementById('detailFamilyMobile').innerText = formatMobile(family.mobile);
        document.getElementById('detailMemberCount').innerText = family.members.length;

        // Render members table
        const tbody = document.getElementById('membersTableBody');
        tbody.innerHTML = '';

        if (family.members.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No members found</td></tr>';
            return;
        }

        family.members.forEach((m, idx) => {
            const isHead = m.relation === 'Head' || m.relation === 'self';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm ${isHead ? 'bg-success text-white' : 'bg-primary-transparent text-primary'} rounded-circle me-2">
                            ${getInitials(m.full_name)}
                        </div>
                        <div>
                            <span class="fw-medium">${m.full_name}</span>
                            ${isHead ? '<span class="badge badge-head ms-2 fs-9"><i class="ri-vip-crown-line me-1"></i>HEAD</span>' : ''}
                            <small class="d-block text-muted">${m.mrn}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-dark">${m.relation || 'Member'}</span>
                    ${m.relation_to_member ? `<small class="d-block text-muted fs-10">(${m.relation_to_member})</small>` : ''}
                </td>
                <td>${m.age || '-'} / ${m.gender ? m.gender.charAt(0).toUpperCase() : '-'}</td>
                <td>${formatMobile(m.mobile) || '-'}</td>
                <td class="text-center">
                    <div class="btn-list">
                        <a href="/patients/${m.patient_id}" class="btn btn-sm btn-icon btn-primary-light" title="View Profile">
                            <i class="ri-eye-line"></i>
                        </a>
                        <a href="/patients/${m.patient_id}/edit" class="btn btn-sm btn-icon btn-light" title="Edit">
                            <i class="ri-edit-line"></i>
                        </a>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function getInitials(name) {
        if (!name) return 'P';
        const parts = name.split(' ');
        return (parts[0]?.charAt(0) || '') + (parts[1]?.charAt(0) || '');
    }

    function formatMobile(mobile) {
        if (!mobile) return '-';
        // Format as XXX-XXX-XXXX
        const clean = mobile.replace(/\D/g, '');
        if (clean.length === 10) {
            return clean.slice(0, 3) + '-' + clean.slice(3, 6) + '-' + clean.slice(6);
        }
        return mobile;
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>