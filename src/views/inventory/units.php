<?php
$pageTitle = "Unit Master";
ob_start();
?>

<!-- Main Content Header -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title fw-semibold fs-20 mb-1">Unit Master</h1>
        <p class="text-muted mb-0 fs-13">Manage units of measurement for products</p>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" onclick="openUnitModal()">
            <i class="ri-add-line me-1"></i> Add Unit
        </button>
    </div>
</div>

<!-- Units Table -->
<div class="card custom-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="unitsTable">
                <thead class="bg-light">
                    <tr>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3 ps-3">Unit Name</th>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3">Short Code</th>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3">Description</th>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3">Status</th>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3 text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="unitsTableBody">
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Unit Modal -->
<div class="modal fade" id="unitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fs-16 fw-semibold" id="unitModalLabel">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="unitForm">
                    <input type="hidden" id="unit_id" name="unit_id">

                    <div class="mb-3">
                        <label for="unit_name" class="form-label fs-13 fw-semibold">Unit Name <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="unit_name" name="unit_name"
                            placeholder="e.g., Box, Strip, Bottle" required>
                    </div>

                    <div class="mb-3">
                        <label for="unit_code" class="form-label fs-13 fw-semibold">Short Code <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="unit_code" name="unit_code"
                            placeholder="e.g., BOX, STR, BTL" required>
                        <small class="text-muted">Used in reports and labels</small>
                    </div>

                    <div class="mb-3">
                        <label for="unit_description" class="form-label fs-13 fw-semibold">Description</label>
                        <textarea class="form-control" id="unit_description" name="unit_description" rows="2"
                            placeholder="Optional description"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="unit_status" name="unit_status" checked>
                        <label class="form-check-label fs-13" for="unit_status">
                            Active
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveUnit()">Save Unit</button>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    let unitModal;

    // Initialize immediately - works for both initial load AND SPA navigation
    (function initUnitsPage() {
        const modalEl = document.getElementById('unitModal');
        if (!modalEl) {
            console.log('Units script skipped - not on units page');
            return;
        }
        console.log('Units page: Initializing...');
        unitModal = new bootstrap.Modal(modalEl);
        loadUnits();
    })();

    async function loadUnits() {
        try {
            const response = await fetch('/api/v1/inventory/units');
            const result = await response.json();

            const tbody = document.getElementById('unitsTableBody');

            if (result.success && result.data.units.length > 0) {
                tbody.innerHTML = result.data.units.map(unit => `
                <tr>
                    <td class="fs-13 fw-medium ps-3">${unit.name}</td>
                    <td class="fs-12"><span class="badge bg-light text-dark">${unit.code}</span></td>
                    <td class="fs-12 text-muted">${unit.description || '-'}</td>
                    <td>
                        <span class="badge bg-${unit.status === 'active' ? 'success' : 'secondary'}-transparent text-${unit.status === 'active' ? 'success' : 'secondary'} fs-10">
                            ${unit.status === 'active' ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-icon btn-light" onclick="editUnit(${unit.id})">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-light" onclick="deleteUnit(${unit.id})">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            } else {
                tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted fs-13">
                        No units found. Click "Add Unit" to create one.
                    </td>
                </tr>
            `;
            }
        } catch (error) {
            console.error('Error loading units:', error);
            document.getElementById('unitsTableBody').innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-danger fs-13">
                    Failed to load units
                </td>
            </tr>
        `;
        }
    }

    function openUnitModal() {
        document.getElementById('unitModalLabel').textContent = 'Add Unit';
        document.getElementById('unitForm').reset();
        document.getElementById('unit_id').value = '';
        unitModal.show();
    }

    async function editUnit(id) {
        try {
            const response = await fetch(`/api/v1/inventory/units/${id}`);
            const result = await response.json();

            if (result.success) {
                const unit = result.data;
                document.getElementById('unitModalLabel').textContent = 'Edit Unit';
                document.getElementById('unit_id').value = unit.id;
                document.getElementById('unit_name').value = unit.name;
                document.getElementById('unit_code').value = unit.code;
                document.getElementById('unit_description').value = unit.description || '';
                document.getElementById('unit_status').checked = unit.status === 'active';
                unitModal.show();
            }
        } catch (error) {
            console.error('Error loading unit:', error);
            Swal.fire('Error', 'Failed to load unit details', 'error');
        }
    }

    async function saveUnit() {
        const form = document.getElementById('unitForm');
        const formData = new FormData(form);
        const id = document.getElementById('unit_id').value;

        const data = {
            name: formData.get('unit_name'),
            code: formData.get('unit_code').toUpperCase(),
            description: formData.get('unit_description'),
            status: formData.has('unit_status') ? 'active' : 'inactive'
        };

        try {
            const url = id ? `/api/v1/inventory/units/${id}` : '/api/v1/inventory/units';
            const method = id ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: id ? 'Unit updated successfully' : 'Unit created successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
                unitModal.hide();
                loadUnits();
            } else {
                Swal.fire('Error', result.message || 'Failed to save unit', 'error');
            }
        } catch (error) {
            console.error('Error saving unit:', error);
            Swal.fire('Error', 'An error occurred while saving the unit', 'error');
        }
    }

    async function deleteUnit(id) {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "This will not affect existing products using this unit",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/api/v1/inventory/units/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Deleted!', 'Unit has been deleted.', 'success');
                    loadUnits();
                } else {
                    Swal.fire('Error', data.message || 'Failed to delete unit', 'error');
                }
            } catch (error) {
                console.error('Error deleting unit:', error);
                Swal.fire('Error', 'An error occurred while deleting the unit', 'error');
            }
        }
    }
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>