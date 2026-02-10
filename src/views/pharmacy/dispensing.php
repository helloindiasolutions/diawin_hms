<?php
/**
 * Siddha pharmacy Dispensing Counter
 * Dynamic API Integrated
 */
$pageTitle = "Siddha Dispensing";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Siddha Dispensing Counter</h2>
        <span class="text-muted fs-12">Process Siddha internal medicines (Kudineer, Thailam) and external
            materials</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave"><i class="ri-barcode-line align-middle me-1"></i> Scan Siddha
            Rx</button>
    </div>
</div>

<div class="row">
    <!-- Active Prescriptions Queue -->
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Pending Siddha Fulfillments <span class="badge bg-danger rounded-pill ms-2"
                        id="pending-count">...</span></div>
                <div class="d-flex align-items-center">
                    <input type="text" class="form-control form-control-sm me-2" id="rx-search"
                        placeholder="Search by MRN or Patient Name...">
                    <button class="btn btn-sm btn-primary" id="refresh-queue">Refresh Queue</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100" id="rx-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Rx Date/Time</th>
                                <th>Patient / MRN</th>
                                <th>Siddha Doctor</th>
                                <th>Medicines</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="rx-queue-body">
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Fetching live prescriptions...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="dispense-workspace" style="display: none;">
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title">Dispense Workflow: <span id="active-patient-name">Selected patient</span></div>
                <button class="btn btn-sm btn-close" onclick="closeWorkspace()"></button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Siddha medicine / Product</th>
                                <th>Dosage Instructions (Anupanam)</th>
                                <th>Available Stock</th>
                                <th style="width: 100px;">Dispense Qty</th>
                                <th>Stock Status</th>
                            </tr>
                        </thead>
                        <tbody id="dispense-items-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted fs-11">
                    <i class="ri-information-line me-1"></i> Stock will be automatically deducted from central Siddha
                    store.
                </div>
                <div>
                    <button class="btn btn-outline-secondary btn-sm me-2" onclick="closeWorkspace()">Cancel</button>
                    <button class="btn btn-success btn-sm"><i class="ri-check-line me-1"></i>Complete Dispense &
                        Bill</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Pharmacist Tools -->
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Siddha Pharmacist Tools</div>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 mb-4">
                    <button class="btn btn-outline-primary text-start px-3 py-2 fs-12"><i
                            class="ri-find-replace-line me-2"></i> Find Siddha Substitutes</button>
                    <button class="btn btn-outline-info text-start px-3 py-2 fs-12"><i class="ri-printer-line me-2"></i>
                        Print Dosage Sticker</button>
                </div>

                <h6 class="fw-bold mb-3 fs-13">Stock Intelligence</h6>
                <div id="stock-alerts">
                    <div class="alert alert-info-transparent p-2 fs-11 mb-2">
                        Scan a prescription to check stock levels.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-pulse {
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadRxQueue();

        document.getElementById('refresh-queue').addEventListener('click', loadRxQueue);

        async function loadRxQueue() {
            const body = document.getElementById('rx-queue-body');
            try {
                const response = await fetch('<?= baseUrl('/api/v1/visits') ?>?type=prescription&status=pending');
                const result = await response.json();

                if (result.success && result.data.visits) {
                    const visits = result.data.visits;
                    document.getElementById('pending-count').textContent = visits.length;
                    renderQueue(visits);
                } else {
                    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No pending Siddha prescriptions found today.</td></tr>';
                    document.getElementById('pending-count').textContent = '0';
                }
            } catch (error) {
                console.error('Error fetching queue:', error);
                body.innerHTML = '<tr><td colspan="7" class="text-center text-danger">API connection failed.</td></tr>';
            }
        }

        function renderQueue(visits) {
            const body = document.getElementById('rx-queue-body');
            body.innerHTML = '';

            visits.forEach(v => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${v.visit_date} <br> <small class="text-muted">${v.visit_time || ''}</small></td>
                <td>
                    <h6 class="mb-0 fw-bold">${v.patient_name}</h6>
                    <small class="text-muted">${v.mrn}</small>
                </td>
                <td>${v.doctor_name || 'Siddha consultant'}</td>
                <td>${v.medicine_count || 0} Siddha medicines</td>
                <td><span class="badge bg-${v.priority === 'urgent' ? 'danger' : 'primary'}-transparent text-${v.priority === 'urgent' ? 'danger' : 'primary'}">${v.priority || 'Normal'}</span></td>
                <td><span class="badge bg-light text-muted">Awaiting</span></td>
                <td>
                    <button class="btn btn-sm btn-primary-light" onclick="openDispense('${v.visit_id}', '${v.patient_name}')">
                        <i class="ri-medicine-bottle-line me-1"></i> Open Rx
                    </button>
                </td>
            `;
                body.appendChild(row);
            });
        }
    });

    function openDispense(id, name) {
        document.getElementById('dispense-workspace').style.display = 'flex';
        document.getElementById('active-patient-name').textContent = name;

        // Smooth scroll to workspace
        document.getElementById('dispense-workspace').scrollIntoView({ behavior: 'smooth' });

        // Fetch individual Rx items
        loadRxItems(id);
    }

    function closeWorkspace() {
        document.getElementById('dispense-workspace').style.display = 'none';
    }

    async function loadRxItems(id) {
        const body = document.getElementById('dispense-items-body');
        body.innerHTML = '<tr><td colspan="5" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const response = await fetch(`<?= baseUrl('/api/v1/visits') ?>/${id}/prescriptions`);
            const result = await response.json();

            if (result.success && result.data.prescriptions) {
                body.innerHTML = '';
                result.data.prescriptions.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td>
                        <div class="fw-bold">${item.medicine_name}</div>
                        <small class="text-muted">Type: ${item.medicine_type || 'Internal'}</small>
                    </td>
                    <td>${item.dosage || 'As prescribed'} <br> <small class="text-info">${item.instruction || ''}</small></td>
                    <td class="fw-bold text-${item.stock > 10 ? 'success' : 'danger'}">${item.stock || 0}</td>
                    <td><input type="number" class="form-control form-control-sm" value="${item.qty || 1}"></td>
                    <td><span class="text-${item.stock > 0 ? 'success' : 'danger'}"><i class="ri-${item.stock > 0 ? 'checkbox-circle' : 'error-warning'}-fill me-1"></i>${item.stock > 0 ? 'In Stock' : 'Out of Stock'}</span></td>
                `;
                    body.appendChild(tr);
                });
            }
        } catch (error) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to retrieve Siddha medicines.</td></tr>';
        }
    }
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>