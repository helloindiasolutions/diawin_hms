<?php
/**
 * OPD Treatment Plans
 * Database-Integrated Clinical Interface
 */
$pageTitle = "Treatment Plans";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Clinical Treatment Plans</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/visits">OPD</a></li>
            <li class="breadcrumb-item active" aria-current="page">Treatment Plans</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#newPlanModal">
            <i class="ri-add-line align-middle me-1"></i>New Protocol
        </button>
    </div>
</div>

<div class="row">
    <!-- Protocol Library -->
    <div class="col-xl-4 col-md-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Protocol Library</div>
            </div>
            <div class="card-body p-0">
                <div class="input-group p-3">
                    <input type="text" class="form-control" id="protocolSearch" placeholder="Search protocols..."
                        onkeyup="filterProtocols()">
                    <span class="input-group-text"><i class="ri-search-line"></i></span>
                </div>
                <div class="list-group list-group-flush" id="protocolList" style="max-height: 500px; overflow-y: auto;">
                    <!-- Loading State -->
                    <div class="text-center py-4" id="protocolLoading">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <p class="text-muted mt-2 mb-0">Loading protocols...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Add Protocol -->
        <div class="card custom-card mt-3">
            <div class="card-header bg-primary-transparent">
                <div class="card-title"><i class="ri-lightbulb-line me-1"></i>Siddha Protocols</div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="siddhaProtocols">
                    <div class="list-group-item p-3 border-0" onclick="selectProtocol('vatha')">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-danger-transparent text-danger me-2">V</div>
                            <div>
                                <h6 class="mb-0 fw-semibold fs-13">Vatha Disorders</h6>
                                <small class="text-muted">Pain, joint issues, neurological</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item p-3 border-0" onclick="selectProtocol('pitha')">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-warning-transparent text-warning me-2">P</div>
                            <div>
                                <h6 class="mb-0 fw-semibold fs-13">Pitha Disorders</h6>
                                <small class="text-muted">Skin, digestive, inflammatory</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item p-3 border-0" onclick="selectProtocol('kapha')">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-info-transparent text-info me-2">K</div>
                            <div>
                                <h6 class="mb-0 fw-semibold fs-13">Kapha Disorders</h6>
                                <small class="text-muted">Respiratory, metabolic, obesity</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Patient Plans -->
    <div class="col-xl-8 col-md-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between border-bottom">
                <div class="card-title">Active Treatment Plans</div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="statusFilter" style="width: 150px;"
                        onchange="fetchPlans()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="on_hold">On Hold</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100 mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Patient Details</th>
                                <th>Plan / Goal</th>
                                <th>Duration</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="planList">
                            <tr id="loadingState">
                                <td colspan="6" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Loading treatment plans...</p>
                                </td>
                            </tr>
                            <tr id="emptyState" class="d-none">
                                <td colspan="6" class="text-center py-5">
                                    <i class="ri-file-list-3-line fs-48 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-2">No treatment plans found</p>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#newPlanModal">
                                        Create First Plan
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div id="planInfo" class="text-muted fs-13">Showing 0 treatment plans</div>
            </div>
        </div>

        <!-- Plan Details Card -->
        <div class="card custom-card mt-3 d-none" id="planDetailsCard">
            <div class="card-header bg-success-transparent">
                <div class="card-title"><i class="ri-health-book-line me-1"></i>Plan Details</div>
            </div>
            <div class="card-body" id="planDetailsBody">
                <!-- Populated dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- New Plan Modal -->
<div class="modal fade" id="newPlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ri-file-add-line me-2"></i>Create Treatment Plan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="planForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Patient <span class="text-danger">*</span></label>
                            <select class="form-select" id="planPatient" required>
                                <option value="">-- Search Patient --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Protocol Type</label>
                            <select class="form-select" id="planProtocol">
                                <option value="">-- Select or Custom --</option>
                                <option value="vatha">Vatha Management</option>
                                <option value="pitha">Pitha Management</option>
                                <option value="kapha">Kapha Management</option>
                                <option value="detox">Panchakarma Detox</option>
                                <option value="diet">Diet & Lifestyle</option>
                                <option value="custom">Custom Plan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Plan Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="planTitle"
                                placeholder="e.g., Weight Management Plan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration (Weeks)</label>
                            <input type="number" class="form-control" id="planDuration" value="4" min="1" max="52">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Goal/Target</label>
                            <input type="text" class="form-control" id="planGoal"
                                placeholder="e.g., Reduce pain by 50%">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Treatment Description</label>
                            <textarea class="form-control" id="planDescription" rows="4"
                                placeholder="Detailed treatment plan, medications, therapies..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Dietary Recommendations</label>
                            <textarea class="form-control" id="planDiet" rows="2"
                                placeholder="Pathya-Apathya, dietary guidelines..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePlan()">
                    <i class="ri-save-line me-1"></i>Create Plan
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .list-group-item {
        cursor: pointer;
        transition: all 0.2s;
    }

    .list-group-item:hover {
        background-color: rgba(var(--primary-rgb), 0.05);
    }

    #siddhaProtocols .list-group-item:hover {
        transform: translateX(5px);
    }
</style>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    // Use Melina.onPageLoad for SPA-compatible initialization
    // This runs on both initial page load AND SPA navigation (like React's useEffect)
    Melina.onPageLoad(() => {
        fetchProtocols();
        fetchPlans();
        fetchPatientsForDropdown();
    });

    async function fetchPatientsForDropdown() {
        try {
            const res = await fetch('/api/v1/patients?limit=100');
            const data = await res.json();
            if (data.success && data.data.patients) {
                const select = document.getElementById('planPatient');
                data.data.patients.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.patient_id;
                    opt.textContent = `${p.first_name} ${p.last_name || ''} - ${p.mrn}`;
                    select.appendChild(opt);
                });
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function fetchProtocols() {
        const list = document.getElementById('protocolList');
        const loading = document.getElementById('protocolLoading');

        // Predefined Siddha protocols (can be expanded to database later)
        const protocols = [
            { name: 'Vatha Vyadhi Protocol', category: 'Neurology', description: 'For joint pain, paralysis, tremors', color: 'danger' },
            { name: 'Pitha Samana Protocol', category: 'Dermatology', description: 'For skin disorders, acidity, inflammation', color: 'warning' },
            { name: 'Kapha Nashanam Protocol', category: 'Respiratory', description: 'For asthma, obesity, congestion', color: 'info' },
            { name: 'Panchakarma Detox', category: 'General', description: 'Complete body purification', color: 'success' },
            { name: 'Agni Deepana Protocol', category: 'Gastroenterology', description: 'Digestive enhancement', color: 'primary' },
            { name: 'Rasayana Chikitsa', category: 'Anti-aging', description: 'Rejuvenation and immunity', color: 'secondary' }
        ];

        loading.classList.add('d-none');

        protocols.forEach(p => {
            const item = document.createElement('div');
            item.className = 'list-group-item p-3 border-bottom border-light';
            item.innerHTML = `
                <div class="d-flex align-items-center mb-1">
                    <span class="avatar avatar-sm bg-${p.color}-transparent text-${p.color} me-2">
                        <i class="ri-microscope-line"></i>
                    </span>
                    <h6 class="fw-bold mb-0 fs-14">${p.name}</h6>
                </div>
                <p class="fs-11 text-muted mb-2">${p.description}</p>
                <div class="d-flex justify-content-between">
                    <span class="badge bg-light text-muted fs-10">${p.category}</span>
                    <button class="btn btn-xs btn-primary-light" onclick="applyProtocol('${p.name}')">Apply</button>
                </div>
            `;
            list.appendChild(item);
        });
    }

    async function fetchPlans() {
        const list = document.getElementById('planList');
        const loading = document.getElementById('loadingState');
        const empty = document.getElementById('emptyState');

        // Clear previous rows except loading/empty
        const rows = list.querySelectorAll('tr:not(#loadingState):not(#emptyState)');
        rows.forEach(r => r.remove());

        loading.classList.remove('d-none');
        empty.classList.add('d-none');

        const status = document.getElementById('statusFilter').value;

        try {
            // Try to fetch treatment plans from API
            const res = await fetch(`/api/v1/treatment-plans?status=${status}`);
            const data = await res.json();
            loading.classList.add('d-none');

            if (data.success && data.data.plans && data.data.plans.length > 0) {
                renderPlans(data.data.plans);
                document.getElementById('planInfo').innerText = `Showing ${data.data.plans.length} treatment plans`;
            } else {
                // Show sample data if API not available yet or no plans
                showSamplePlans();
            }
        } catch (e) {
            loading.classList.add('d-none');
            console.log('Treatment plans API not available, showing sample data');
            showSamplePlans();
        }
    }

    function showSamplePlans() {
        const list = document.getElementById('planList');
        const empty = document.getElementById('emptyState');
        empty.classList.remove('d-none');
        document.getElementById('planInfo').innerText = 'No treatment plans created yet';
    }

    function renderPlans(plans) {
        const list = document.getElementById('planList');
        plans.forEach(p => {
            const progress = Math.round((p.current_week / p.total_weeks) * 100) || 0;
            const statusBadge = getStatusBadge(p.status);

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="ps-4">
                    <h6 class="mb-0 fw-bold">${p.patient_name}</h6>
                    <small class="text-muted">#${p.mrn} | ${p.gender}/${p.age}</small>
                </td>
                <td>
                    <span class="fw-semibold">${p.plan_title}</span>
                    <small class="d-block text-muted">Target: ${p.goal || 'N/A'}</small>
                </td>
                <td>${p.total_weeks} weeks</td>
                <td>
                    <div class="progress progress-xs mb-1" style="height: 5px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${progress}%"></div>
                    </div>
                    <small class="text-muted">Week ${p.current_week || 0} of ${p.total_weeks}</small>
                </td>
                <td>${statusBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-icon btn-primary-light" onclick="viewPlanDetails(${p.plan_id})">
                        <i class="ri-eye-line"></i>
                    </button>
                </td>
            `;
            list.appendChild(row);
        });
    }

    function getStatusBadge(status) {
        const badges = {
            'active': '<span class="badge bg-success-transparent text-success">Active</span>',
            'completed': '<span class="badge bg-primary-transparent text-primary">Completed</span>',
            'on_hold': '<span class="badge bg-warning-transparent text-warning">On Hold</span>',
            'cancelled': '<span class="badge bg-danger-transparent text-danger">Cancelled</span>'
        };
        return badges[status] || '<span class="badge bg-light text-muted">Unknown</span>';
    }

    function filterProtocols() {
        const search = document.getElementById('protocolSearch').value.toLowerCase();
        const items = document.querySelectorAll('#protocolList .list-group-item');
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    }

    function selectProtocol(type) {
        document.getElementById('planProtocol').value = type;
        const modal = new bootstrap.Modal(document.getElementById('newPlanModal'));
        modal.show();
    }

    function applyProtocol(name) {
        document.getElementById('planTitle').value = name;
        const modal = new bootstrap.Modal(document.getElementById('newPlanModal'));
        modal.show();
    }

    async function savePlan() {
        const patientId = document.getElementById('planPatient').value;
        const title = document.getElementById('planTitle').value;

        if (!patientId || !title) {
            alert('Please select a patient and provide a plan title');
            return;
        }

        const payload = {
            patient_id: patientId,
            plan_title: title,
            protocol_type: document.getElementById('planProtocol').value,
            total_weeks: document.getElementById('planDuration').value,
            goal: document.getElementById('planGoal').value,
            description: document.getElementById('planDescription').value,
            dietary_notes: document.getElementById('planDiet').value,
            status: 'active'
        };

        try {
            const res = await fetch('/api/v1/treatment-plans', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('newPlanModal')).hide();
                document.getElementById('planForm').reset();
                fetchPlans();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Success', text: 'Treatment plan created!', timer: 2000 });
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to create plan'));
            }
        } catch (e) {
            console.error(e);
            alert('Network error. The treatment plans API may not be implemented yet.');
        }
    }

    function viewPlanDetails(planId) {
        document.getElementById('planDetailsCard').classList.remove('d-none');
        document.getElementById('planDetailsBody').innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>';
        // Can be expanded to fetch details from API
    }
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>