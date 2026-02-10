<?php
$pageTitle = "Ward Management";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Ward Management</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">In-Patient</a></li>
            <li class="breadcrumb-item active" aria-current="page">Wards</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" onclick="openAddWardModal()">
            <i class="ri-add-line align-middle me-1"></i> Add Ward
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Ward List</div>
                <button class="btn btn-sm btn-light" onclick="fetchWards()">
                    <i class="ri-refresh-line me-1"></i>Refresh
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Ward Name</th>
                                <th>Ward Code</th>
                                <th>Ward Type</th>
                                <th>Floor</th>
                                <th>Department</th>
                                <th>Total Beds</th>
                                <th>Available</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="wardsList">
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Ward Modal -->
<div class="modal fade" id="wardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="wardModalTitle">Add New Ward</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="wardForm">
                    <input type="hidden" id="ward_id">
                    <div class="mb-3">
                        <label class="form-label">Ward Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ward_name" required
                            placeholder="e.g., General Ward A" autofocus oninput="generateWardCode()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ward Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="ward_code" required placeholder="Auto-generated"
                                maxlength="10" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="unlockWardCode()"
                                title="Edit manually">
                                <i class="ri-lock-line" id="lockIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Auto-generated from ward name. Click lock to edit manually.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ward Type</label>
                        <select class="form-select" id="ward_type">
                            <option value="General">General</option>
                            <option value="ICU">ICU</option>
                            <option value="Private">Private</option>
                            <option value="Semi-Private">Semi-Private</option>
                            <option value="Maternity">Maternity</option>
                            <option value="Pediatric">Pediatric</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Floor Number</label>
                            <input type="number" class="form-control" id="floor_number" min="0" max="20">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" id="department"
                                placeholder="e.g., General Medicine">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveWard()">
                    <i class="ri-save-line me-1"></i> Save Ward
                </button>
            </div>
        </div>
    </div>
</div>



<?php $content = ob_get_clean();
ob_start(); ?>
<!-- Ward Management Script v2.0 - Auto-generate Ward Code -->
<style>
    .ward-row {
        transition: background-color 0.2s ease;
    }

    .ward-row:hover {
        background-color: rgba(var(--primary-rgb), 0.05) !important;
    }
</style>
<script>
    let editingWardId = null;
    let wardCodeLocked = true;

    function initWardsPage() {
        console.log('Initializing Wards Page...');
        fetchWards();
    }

    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initWardsPage);
    } else {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWardsPage);
        } else {
            initWardsPage();
        }
    }

    // Expose functions globally for SPA interaction
    window.initWardsPage = initWardsPage;
    window.fetchWards = fetchWards;
    window.openAddWardModal = openAddWardModal;
    window.editWard = editWard;
    window.saveWard = saveWard;
    window.toggleWardStatus = toggleWardStatus;
    window.deleteWard = deleteWard;
    window.generateWardCode = generateWardCode;
    window.unlockWardCode = unlockWardCode;

    function generateWardCode() {
        if (!wardCodeLocked) return; // Don't auto-generate if manually unlocked
        const nameInput = document.getElementById('ward_name');
        if (!nameInput) return;

        const wardName = nameInput.value.trim();
        const codeInput = document.getElementById('ward_code');
        if (!wardName) {
            if (codeInput) codeInput.value = '';
            return;
        }

        // Generate code from ward name
        // Examples:
        // "General Ward A" -> "GWA"
        // "ICU" -> "ICU"
        // "Private Ward" -> "PVT"
        // "Maternity Ward" -> "MAT"

        let code = '';
        const words = wardName.toUpperCase().split(/\s+/);

        if (words.length === 1) {
            // Single word: take first 3-4 letters
            code = words[0].substring(0, 4);
        } else if (words.length === 2) {
            // Two words: take first 2 letters of each
            code = words[0].substring(0, 2) + words[1].substring(0, 2);
        } else {
            // Three or more words: take first letter of each word
            code = words.map(w => w[0]).join('').substring(0, 5);
        }

        // Remove special characters and numbers
        code = code.replace(/[^A-Z]/g, '');

        if (codeInput) codeInput.value = code;
    }

    function unlockWardCode() {
        const codeInput = document.getElementById('ward_code');
        const lockIcon = document.getElementById('lockIcon');

        wardCodeLocked = !wardCodeLocked;

        if (wardCodeLocked) {
            codeInput.readOnly = true;
            lockIcon.className = 'ri-lock-line';
            generateWardCode(); // Regenerate when locking
        } else {
            codeInput.readOnly = false;
            lockIcon.className = 'ri-lock-unlock-line';
            codeInput.focus();
        }
    }

    async function fetchWards() {
        const tbody = document.getElementById('wardsList');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const res = await fetch('/api/v1/ipd/wards');
            const data = await res.json();

            // Re-acquire element in case validation changed async
            const list = document.getElementById('wardsList');
            if (!list) return;
            list.innerHTML = '';

            if (data.success && data.data.wards && data.data.wards.length > 0) {
                data.data.wards.forEach(w => {
                    const row = document.createElement('tr');
                    const statusClass = w.is_active ? 'success' : 'danger';
                    const statusText = w.is_active ? 'Active' : 'Inactive';

                    row.style.cursor = 'pointer';
                    row.className = 'ward-row';
                    row.onclick = () => window.location.href = `/ip/ward-details?ward_id=${w.ward_id}`;

                    row.innerHTML = `
                        <td class="ps-4">
                            <div class="fw-semibold">${w.ward_name}</div>
                        </td>
                        <td><span class="badge bg-primary-transparent">${w.ward_code}</span></td>
                        <td>${w.ward_type || 'General'}</td>
                        <td>${w.floor_number ? 'Floor ' + w.floor_number : '--'}</td>
                        <td>${w.department || '--'}</td>
                        <td><span class="badge bg-info-transparent">${w.total_beds || 0}</span></td>
                        <td><span class="badge bg-success-transparent">${w.available_beds || 0}</span></td>
                        <td><span class="badge bg-${statusClass}-transparent">${statusText}</span></td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-icon btn-primary-light rounded-pill" 
                                    onclick="event.stopPropagation(); editWard(${w.ward_id})" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-${w.is_active ? 'warning' : 'success'}-light rounded-pill" 
                                    onclick="event.stopPropagation(); toggleWardStatus(${w.ward_id}, ${w.is_active})" 
                                    title="${w.is_active ? 'Deactivate' : 'Activate'}">
                                <i class="ri-${w.is_active ? 'close' : 'check'}-line"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-danger-light rounded-pill" 
                                    onclick="event.stopPropagation(); deleteWard(${w.ward_id})" title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No wards found. Click "Add Ward" to create one.</td></tr>';
            }
        } catch (e) {
            console.error('Failed to load wards:', e);
            if (document.getElementById('wardsList')) {
                document.getElementById('wardsList').innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load wards.</td></tr>';
            }
        }
    }

    function openAddWardModal() {
        editingWardId = null;
        wardCodeLocked = true; // Reset to locked for new ward
        document.getElementById('wardModalTitle').textContent = 'Add New Ward';
        document.getElementById('wardForm').reset();
        document.getElementById('ward_id').value = '';
        document.getElementById('ward_code').readOnly = true;
        document.getElementById('lockIcon').className = 'ri-lock-line';

        const modal = new bootstrap.Modal(document.getElementById('wardModal'));
        modal.show();

        setTimeout(() => document.getElementById('ward_name').focus(), 300);
    }

    async function editWard(wardId) {
        try {
            const res = await fetch('/api/v1/ipd/wards');
            const data = await res.json();

            if (data.success && data.data.wards) {
                const ward = data.data.wards.find(w => w.ward_id == wardId);
                if (ward) {
                    editingWardId = wardId;
                    wardCodeLocked = false; // Allow editing when editing existing ward
                    document.getElementById('wardModalTitle').textContent = 'Edit Ward';
                    document.getElementById('ward_id').value = ward.ward_id;
                    document.getElementById('ward_name').value = ward.ward_name;
                    document.getElementById('ward_code').value = ward.ward_code;
                    document.getElementById('ward_code').readOnly = false;
                    document.getElementById('lockIcon').className = 'ri-lock-unlock-line';
                    document.getElementById('ward_type').value = ward.ward_type || 'General';
                    document.getElementById('floor_number').value = ward.floor_number || '';
                    document.getElementById('department').value = ward.department || '';

                    const modal = new bootstrap.Modal(document.getElementById('wardModal'));
                    modal.show();
                }
            }
        } catch (e) {
            console.error('Failed to load ward details:', e);
            alert('Failed to load ward details');
        }
    }

    async function saveWard() {
        const form = document.getElementById('wardForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const wardData = {
            ward_name: document.getElementById('ward_name').value,
            ward_code: document.getElementById('ward_code').value,
            ward_type: document.getElementById('ward_type').value,
            floor_number: document.getElementById('floor_number').value || null,
            department: document.getElementById('department').value || null
        };

        try {
            let res;
            if (editingWardId) {
                // Update existing ward
                res = await fetch(`/api/v1/ipd/wards/${editingWardId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(wardData)
                });
            } else {
                // Create new ward
                res = await fetch('/api/v1/ipd/wards', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(wardData)
                });
            }

            const data = await res.json();

            if (data.success) {
                alert(editingWardId ? 'Ward updated successfully!' : 'Ward created successfully!');
                bootstrap.Modal.getInstance(document.getElementById('wardModal')).hide();
                form.reset();
                fetchWards();
            } else {
                alert('Failed to save ward: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('Failed to save ward:', e);
            alert('Failed to save ward. Please try again.');
        }
    }

    async function toggleWardStatus(wardId, currentStatus) {
        const action = currentStatus ? 'deactivate' : 'activate';
        if (!confirm(`Are you sure you want to ${action} this ward?`)) return;

        try {
            const res = await fetch(`/api/v1/ipd/wards/${wardId}/status`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ is_active: !currentStatus })
            });

            const data = await res.json();

            if (data.success) {
                alert(`Ward ${action}d successfully!`);
                fetchWards();
            } else {
                alert('Failed to update ward status: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('Failed to update ward status:', e);
            alert('Failed to update ward status. Please try again.');
        }
    }

    async function deleteWard(wardId) {
        if (!confirm('Are you sure you want to delete this ward? This action cannot be undone.')) return;

        try {
            const res = await fetch(`/api/v1/ipd/wards/${wardId}`, {
                method: 'DELETE'
            });

            const data = await res.json();

            if (data.success) {
                alert('Ward deleted successfully!');
                fetchWards();
            } else {
                alert('Failed to delete ward: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('Failed to delete ward:', e);
            alert('Failed to delete ward. Please try again.');
        }
    }


</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>