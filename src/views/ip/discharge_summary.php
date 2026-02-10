<?php
/**
 * Siddha IP Discharge Summary
 * Dynamic API Integrated
 */
$pageTitle = "Siddha Discharge Summary";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Siddha Discharge Summary</h2>
        <span class="text-muted fs-12">Expert documentation for Siddha clinical recovery and post-therapy advice</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" id="new-summary"><i class="ri-add-line align-middle me-1"></i> New
            Summary</button>
    </div>
</div>

<div class="row">
    <!-- Patients Awaiting Discharge -->
    <div class="col-xl-4 col-md-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between border-bottom">
                <div class="card-title">Pending Siddha Discharges <span class="badge bg-primary rounded-pill ms-2"
                        id="pending-count">...</span></div>
                <button class="btn btn-sm btn-icon btn-light" id="refresh-list"><i class="ri-refresh-line"></i></button>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="discharge-list"
                    style="max-height: 500px; overflow-y: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary spinner-border-sm"></div>
                        <p class="mt-2 text-muted fs-12">Syncing clinical records...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Editor Area -->
    <div class="col-xl-8 col-md-12" id="summary-editor" style="display: none;">
        <div class="card custom-card shadow-lg">
            <div class="card-header border-bottom d-flex justify-content-between bg-primary-transparent">
                <div class="card-title">Discharge Protocol: <span id="active-pt-name">Patient</span></div>
                <div class="btn-list">
                    <button class="btn btn-sm btn-outline-secondary" onclick="previewSummary()"><i
                            class="ri-eye-line me-1"></i>Preview</button>
                    <button class="btn btn-sm btn-close" onclick="closeEditor()"></button>
                </div>
            </div>
            <div class="card-body">
                <form id="dischargeForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-primary">Siddha clinical Diagnosis / Pathological
                            State</label>
                        <textarea class="form-control" id="form-diagnosis" rows="2"
                            placeholder="Clinical impression based on Siddha parameters..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-primary">Therapy Course & Clinical Improvement</label>
                        <textarea class="form-control" id="form-course" rows="4"
                            placeholder="Summarize internal medications and external therapies (Thokkanam, etc.) administered..."></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Condition at Discharge</label>
                            <select class="form-select" id="form-status">
                                <option>Clinically Recovered</option>
                                <option>Significantly Improved</option>
                                <option>Stability Attained</option>
                                <option>Follow-up Advised</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Review Date (Siddha Consultation)</label>
                            <input type="date" class="form-control" id="form-review-date">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-primary">Post-Discharge Internal Medicines (Anupanam
                            Advice)</label>
                        <div class="bg-light p-3 rounded border">
                            <div id="discharge-rx-summary" class="fs-12 italic text-muted">No medicines currently listed
                                for discharge...</div>
                            <button type="button" class="btn btn-xs btn-outline-primary mt-2"
                                onclick="importCurrentRx()"><i class="ri-import-line me-1"></i>Import IP Rx</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-light me-2" onclick="closeEditor()">Save Draft</button>
                <button class="btn btn-primary" onclick="finalizeSummary()"><i class="ri-printer-line me-1"></i>
                    Finalize & Generate PDF</button>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div class="col-xl-8 col-md-12" id="empty-editor-state">
        <div class="card custom-card py-5 border-dashed">
            <div class="card-body text-center py-5">
                <div class="mb-4"><i class="ri-file-list-3-line fs-60 text-muted opacity-25"></i></div>
                <h5>Select Patient for Siddha Discharge</h5>
                <p class="text-muted">Choose a patient from the awaiting list to prepare their clinical discharge
                    summary and recovery advice.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function initDischargeSummaryPage() {
        loadAwaitingDischarge();
        document.getElementById('refresh-list').addEventListener('click', loadAwaitingDischarge);
    }

    // SPA Support: Run immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDischargeSummaryPage);
    } else {
        initDischargeSummaryPage();
    }

    async function loadAwaitingDischarge() {
        const list = document.getElementById('discharge-list');
        try {
            const response = await fetch('/api/v1/ipd/admissions?status=Active');
            const result = await response.json();

            if (result.success && result.data.admissions) {
                document.getElementById('pending-count').textContent = result.data.admissions.length;
                renderList(result.data.admissions);
            } else {
                list.innerHTML = '<div class="text-center py-5 text-muted">No patients scheduled for discharge.</div>';
                document.getElementById('pending-count').textContent = '0';
            }
        } catch (error) {
            console.error(error);
            list.innerHTML = '<div class="text-center py-5 text-danger">Failed to load admissions.</div>';
        }
    }

    function renderList(admissions) {
        const list = document.getElementById('discharge-list');
        list.innerHTML = '';

        admissions.forEach(adm => {
            const btn = document.createElement('a');
            btn.href = 'javascript:void(0);';
            btn.className = 'list-group-item list-group-item-action p-3';
            btn.onclick = () => openSummaryEditor(adm);

            const patientName = `${adm.first_name} ${adm.last_name || ''}`;
            const admDate = new Date(adm.admission_date);
            const today = new Date();
            const daysAdmitted = Math.ceil((today - admDate) / (1000 * 60 * 60 * 24));

            btn.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="fw-bold mb-0">${patientName}</h6>
                    <span class="badge bg-light text-muted fs-10">Days: ${daysAdmitted}</span>
                </div>
                <div class="d-flex justify-content-between fs-11 text-muted">
                    <span>#${adm.admission_number}</span>
                    <span>Ward: ${adm.ward_name || '--'}</span>
                </div>
            `;
            list.appendChild(btn);
        });
    }

    function openSummaryEditor(adm) {
        document.getElementById('empty-editor-state').style.display = 'none';
        document.getElementById('summary-editor').style.display = 'block';
        document.getElementById('active-pt-name').textContent = `${adm.first_name} ${adm.last_name || ''}`;

        // Dynamic animation/scroll
        document.getElementById('summary-editor').scrollIntoView({ behavior: 'smooth' });
    }

    function closeEditor() {
        document.getElementById('summary-editor').style.display = 'none';
        document.getElementById('empty-editor-state').style.display = 'block';
    }

    function importCurrentRx() { alert('Importing live Siddha prescriptions for discharge...'); }
    function finalizeSummary() { alert('Finalizing Siddha Discharge Summary for printing...'); }
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>