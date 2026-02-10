<?php
$pageTitle = "Warehouse/Store Locations";
ob_start();
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title fw-semibold fs-20 mb-1">Warehouse/Store Locations</h1>
        <p class="text-muted mb-0 fs-13">Manage warehouse and store location masters for inventory management</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openAddLocationModal()">
            <i class="ri-add-line me-1"></i>Add Location
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" id="searchLocation" placeholder="Search locations...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterType">
                    <option value="">All Types</option>
                    <option value="warehouse">Warehouse</option>
                    <option value="pharmacy">Pharmacy</option>
                    <option value="store">Store Room</option>
                    <option value="cold_storage">Cold Storage</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="applyFilters()">
                    <i class="ri-search-line me-1"></i>Search
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Locations List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Location Code</th>
                        <th>Location Name</th>
                        <th>Type</th>
                        <th>In-charge</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="locationsTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2">Loading locations...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalTitle">Add Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="locationForm">
                    <input type="hidden" id="location_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Location Code *</label>
                            <input type="text" class="form-control" id="location_code" placeholder="e.g., WH-001"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location Name *</label>
                            <input type="text" class="form-control" id="location_name"
                                placeholder="e.g., Main Pharmacy Rack A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type *</label>
                            <select class="form-select" id="location_type" required>
                                <option value="">Select Type</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="store">Store Room</option>
                                <option value="cold_storage">Cold Storage</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">In-charge Person</label>
                            <input type="text" class="form-control" id="incharge" placeholder="Person name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact" placeholder="Phone number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="address" rows="2" placeholder="Full address"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="2"
                                placeholder="Additional details"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveLocation()">
                    <i class="ri-save-line me-1"></i>Save Location
                </button>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    // CRITICAL: Only execute if we're on the warehouse page
    if (!document.getElementById('locationsTableBody')) {
        console.log('Warehouse script skipped - not on warehouse page');
    } else {
        let locations = [];
        let editingLocationId = null;

        // Initialize immediately - works for both initial load AND SPA navigation
        (function initWarehousePage() {
            console.log('Warehouse page: Initializing...');
            loadLocations();
        })();

        async function loadLocations() {
            try {
                const res = await fetch('/api/v1/inventory/warehouses');
                const data = await res.json();

                if (data.success && data.data.warehouses) {
                    locations = data.data.warehouses;
                    renderLocations(data.data.warehouses);
                } else {
                    throw new Error(data.message || 'Failed to load locations');
                }
            } catch (e) {
                console.error('Failed to load locations:', e);
                const tbody = document.getElementById('locationsTableBody');
                if (tbody) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4 text-danger">
                            Failed to load locations: ${e.message}
                        </td>
                    </tr>
                `;
                }
            }
        }

        function renderLocations(locationsList) {
            const tbody = document.getElementById('locationsTableBody');
            if (!tbody) return; // Exit if element doesn't exist

            if (locationsList.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="ri-inbox-line fs-48 d-block mb-2"></i>
                        No locations found
                    </td>
                </tr>
            `;
                return;
            }

            tbody.innerHTML = locationsList.map((loc, index) => {
                const typeColors = {
                    warehouse: 'primary',
                    pharmacy: 'success',
                    store: 'info',
                    cold_storage: 'warning'
                };

                return `
                <tr>
                    <td>${index + 1}</td>
                    <td><code class="text-primary">${loc.code}</code></td>
                    <td class="fw-semibold">${loc.name}</td>
                    <td>
                        <span class="badge bg-${typeColors[loc.type] || 'secondary'}-transparent">
                            ${(loc.type || '').replace('_', ' ')}
                        </span>
                    </td>
                    <td>${loc.incharge || '-'}</td>
                    <td>
                        <span class="badge bg-${loc.status === 'active' ? 'success' : 'secondary'}">
                            ${loc.status}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editLocation(${loc.id})">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteLocation(${loc.id}, '${loc.name.replace(/'/g, "\\'")}')">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            `;
            }).join('');
        }

        function openAddLocationModal() {
            editingLocationId = null;
            document.getElementById('locationModalTitle').textContent = 'Add Location';
            document.getElementById('locationForm').reset();
            document.getElementById('location_id').value = '';

            const modal = new bootstrap.Modal(document.getElementById('locationModal'));
            modal.show();
        }

        function editLocation(id) {
            const location = locations.find(l => l.id === id);
            if (!location) return;

            editingLocationId = id;
            document.getElementById('locationModalTitle').textContent = 'Edit Location';
            document.getElementById('location_id').value = location.id;
            document.getElementById('location_code').value = location.code;
            document.getElementById('location_name').value = location.name;
            document.getElementById('location_type').value = location.type;
            document.getElementById('incharge').value = location.incharge || '';
            document.getElementById('contact').value = location.contact || '';
            document.getElementById('address').value = location.address || '';
            document.getElementById('description').value = location.description || '';
            document.getElementById('status').value = location.status;

            const modal = new bootstrap.Modal(document.getElementById('locationModal'));
            modal.show();
        }

        async function saveLocation() {
            const form = document.getElementById('locationForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const locationId = document.getElementById('location_id').value;
            const data = {
                code: document.getElementById('location_code').value,
                name: document.getElementById('location_name').value,
                type: document.getElementById('location_type').value,
                incharge: document.getElementById('incharge').value || null,
                contact: document.getElementById('contact').value || null,
                address: document.getElementById('address').value || null,
                description: document.getElementById('description').value || null,
                status: document.getElementById('status').value
            };

            try {
                const url = locationId
                    ? `/api/v1/inventory/warehouses/${locationId}`
                    : '/api/v1/inventory/warehouses';
                const method = locationId ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                if (result.success) {
                    modalNotify.success(result.message || 'Location saved successfully');
                    bootstrap.Modal.getInstance(document.getElementById('locationModal')).hide();
                    loadLocations();
                } else {
                    modalNotify.error(result.message || 'Failed to save location');
                }
            } catch (e) {
                console.error('Failed to save location:', e);
                modalNotify.error('Failed to save location');
            }
        }

        function deleteLocation(id, name) {
            if (!confirm(`Are you sure you want to delete "${name}"?`)) {
                return;
            }

            fetch(`/api/v1/inventory/warehouses/${id}`, {
                method: 'DELETE'
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        modalNotify.success('Location deleted successfully');
                        loadLocations();
                    } else {
                        modalNotify.error(data.message || 'Failed to delete location');
                    }
                })
                .catch(e => {
                    console.error('Failed to delete location:', e);
                    modalNotify.error('Failed to delete location');
                });
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchLocation').value.toLowerCase();
            const typeFilter = document.getElementById('filterType').value;
            const statusFilter = document.getElementById('filterStatus').value;

            const filtered = locations.filter(loc => {
                const matchesSearch = !searchTerm ||
                    loc.name.toLowerCase().includes(searchTerm) ||
                    loc.code.toLowerCase().includes(searchTerm);
                const matchesType = !typeFilter || loc.type === typeFilter;
                const matchesStatus = !statusFilter || loc.status === statusFilter;

                return matchesSearch && matchesType && matchesStatus;
            });

            renderLocations(filtered);
        }
    } // End of warehouse page check
</script>

<style>
    /* Smaller table font size */
    .table-sm {
        font-size: 0.875rem;
    }

    .table-sm th,
    .table-sm td {
        padding: 0.5rem;
        font-size: 0.8125rem;
    }

    .table-sm .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .table-sm code {
        font-size: 0.8125rem;
    }

    .table-sm .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>