<?php
$pageTitle = "Bed Allocations";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">In-Patient Bed Allocations</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">In-Patient</a></li>
            <li class="breadcrumb-item active" aria-current="page">Allocations</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Active Bed Assignments</div>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Search Assignments..."
                        onkeyup="fetchAllocations()">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Bed No</th>
                                <th>Ward / Room</th>
                                <th>Patient Name</th>
                                <th>Allocated Date</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="allocationList">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bed Transfer Modal -->
<div class="modal fade" id="transferBedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="ri-arrow-left-right-line me-2"></i>Transfer
                    Bed</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferBedForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="transferAdmissionId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Current Patient</label>
                        <div id="transferPatientInfo" class="p-3 bg-light rounded-3 border">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small text-uppercase">New Ward / Bed</label>
                        <select id="newBedId" class="form-select form-select-lg border-2" required>
                            <option value="">Select Available Bed...</option>
                            <!-- Populated via JS -->
                        </select>
                        <div class="form-text text-muted">Only unoccupied beds are shown.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Transfer Date</label>
                        <input type="datetime-local" id="transferDate" class="form-control border-2" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    function initAllocations() {
        fetchAllocations();
        const transferForm = document.getElementById('transferBedForm');
        if (transferForm) {
            transferForm.onsubmit = confirmTransfer;
        }
    }

    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initAllocations);
    }

    // Robust fallback for direct load or SPA without Melina
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllocations);
    } else {
        // Only run if Melina didn't already handle it (naive check, but safe if Melina is undefined)
        if (typeof Melina === 'undefined') {
            initAllocations();
        }
    }

    async function fetchAllocations() {
        const list = document.getElementById('allocationList');
        if (!list) return;

        list.innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary"></div><div class="text-muted mt-2">Loading allocations...</div></td></tr>';

        try {
            const res = await fetch('/api/v1/ipd/admissions?status=Active');
            const data = await res.json();

            if (!document.getElementById('allocationList')) return;
            list.innerHTML = '';

            if (data.success && data.data.admissions.length > 0) {
                const allocated = data.data.admissions.filter(a => a.bed_id && a.bed_number);

                if (allocated.length === 0) {
                    list.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No active bed allocations at the moment.</td></tr>';
                    return;
                }

                allocated.forEach(a => {
                    const allocDate = new Date(a.admission_date).toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td class="ps-4"><span class="fw-bold fs-14">${a.bed_number}</span></td>
                    <td><span class="text-muted">${a.ward_name || 'N/A'}</span></td>
                    <td>
                        <div class="fw-bold text-dark">${a.first_name} ${a.last_name || ''}</div>
                        <div class="text-muted fs-11">MRN: <span class="fw-medium">${a.mrn}</span> | Admission <span class="fw-medium">#${a.admission_number}</span></div>
                    </td>
                    <td class="text-muted">${allocDate}</td>
                    <td class="text-center"><span class="badge bg-danger-transparent text-danger px-3 rounded-pill border border-danger border-opacity-10">Occupied</span></td>
                    <td class="text-end pe-4">
                        <div class="btn-list">
                            <button class="btn btn-sm btn-icon btn-primary-light rounded-pill border-0 shadow-none" onclick="openTransferModal(${JSON.stringify(a).replace(/"/g, '&quot;')})" title="Transfer Bed"><i class="ri-arrow-left-right-line fs-14"></i></button>
                            <button class="btn btn-sm btn-icon btn-danger-light rounded-pill border-0 shadow-none" onclick="releaseBed(${a.admission_id})" title="Release Bed"><i class="ri-logout-box-r-line fs-14"></i></button>
                        </div>
                    </td>
                `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No active admissions found.</td></tr>';
            }
        } catch (e) {
            console.error(e);
            if (document.getElementById('allocationList')) list.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-danger">Failed to load allocations.</td></tr>';
        }
    }

    async function openTransferModal(admission) {
        // ... existing code ...
        document.getElementById('transferAdmissionId').value = admission.admission_id;
        document.getElementById('transferPatientInfo').innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold">${admission.first_name} ${admission.last_name || ''}</h6>
                    <div class="text-muted small">Current: ${admission.ward_name} / Bed ${admission.bed_number}</div>
                </div>
            </div>
        `;
        // ... existing code for loading beds ...
        // Set current time
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('transferDate').value = now.toISOString().slice(0, 16);

        // Load available beds
        const bedSelect = document.getElementById('newBedId');
        bedSelect.innerHTML = '<option value="">Loading available beds...</option>';

        try {
            const res = await fetch('/api/v1/ipd/beds?status=Available');
            const data = await res.json();

            if (data.success && data.data.beds.length > 0) {
                bedSelect.innerHTML = '<option value="">Select New Bed...</option>';
                data.data.beds.forEach(bed => {
                    bedSelect.innerHTML += `<option value="${bed.bed_id}">${bed.ward_name} - ${bed.bed_number} (${bed.bed_type})</option>`;
                });
            } else {
                bedSelect.innerHTML = '<option value="">No available beds found</option>';
            }
        } catch (e) {
            bedSelect.innerHTML = '<option value="">Error loading beds</option>';
        }

        const modal = new bootstrap.Modal(document.getElementById('transferBedModal'));
        modal.show();
    }
    window.openTransferModal = openTransferModal;

    async function confirmTransfer(e) {
        e.preventDefault();
        const admissionId = document.getElementById('transferAdmissionId').value;
        const newBedId = document.getElementById('newBedId').value;
        const transferDate = document.getElementById('transferDate').value;

        try {
            const res = await fetch('/api/v1/ipd/transfer-bed', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ admission_id: admissionId, new_bed_id: newBedId, transfer_date: transferDate })
            });
            const data = await res.json();

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('transferBedModal')).hide();
                Swal.fire({ icon: 'success', title: 'Bed Transferred', text: 'Patient has been moved successfully.', timer: 2000, showConfirmButton: false });
                fetchAllocations();
            } else {
                Swal.fire({ icon: 'error', title: 'Transfer Failed', text: data.message });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.' });
        }
    }

    async function releaseBed(admissionId) {
        const result = await Swal.fire({
            title: 'Deallocate Bed?',
            text: "This will mark the current bed as Available. The patient will still be 'Admitted' but without a bed assignment.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Deallocate Bed'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch('/api/v1/ipd/deallocate-bed', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ admission_id: admissionId })
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Success', text: 'Bed has been deallocated.', timer: 1500, showConfirmButton: false });
                    fetchAllocations();
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.' });
            }
        }
    }
    window.releaseBed = releaseBed;
</script>
<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>