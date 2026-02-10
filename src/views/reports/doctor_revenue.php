<?php
/**
 * Doctor-wise Revenue Report
 * Dynamic API Integrated
 */
$pageTitle = "Physician Performance Report";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Physician Performance</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Reports & Analytics</a></li>
            <li class="breadcrumb-item active" aria-current="page">Doctor Revenue</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-outline-secondary btn-wave" id="export-pdf"><i
                class="ri-download-line align-middle me-1"></i> Export PDF</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-body">
                <form class="row g-3" id="revenue-filter">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Select Siddha Practitioner</label>
                        <select class="form-select" id="doctor-select">
                            <option value="all">All Siddha Consultants</option>
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="start-date" value="<?= date('Y-m-01') ?>">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" id="end-date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="fetch-btn">Generate Siddha
                            Audit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Consolidated Performance Ledger (Live)</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100" id="revenue-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Doctor Details</th>
                                <th>Consultations (OP)</th>
                                <th>Therapy Shares</th>
                                <th>Pharmacy Vol.</th>
                                <th>Total Payout Share</th>
                                <th>Net Hospital Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="revenue-body">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="mt-2 text-muted mb-0">Calculating Siddha physician analytics...</p>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-primary-transparent fw-bold" id="revenue-footer">
                            <!-- Populated dynamically -->
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadDoctors();
        loadRevenueReport();

        document.getElementById('fetch-btn').addEventListener('click', loadRevenueReport);

        async function loadDoctors() {
            const select = document.getElementById('doctor-select');
            try {
                const resp = await fetch('<?= baseUrl('/api/v1/doctors') ?>');
                const result = await resp.json();
                if (result.success && result.data.doctors) {
                    result.data.doctors.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.doctor_id;
                        opt.textContent = `${d.name} (${d.specialization || 'Siddha'})`;
                        select.appendChild(opt);
                    });
                }
            } catch (e) { console.error('Failed to load doctors'); }
        }

        async function loadRevenueReport() {
            const body = document.getElementById('revenue-body');
            const doctorId = document.getElementById('doctor-select').value;
            const start = document.getElementById('start-date').value;
            const end = document.getElementById('end-date').value;

            try {
                const resp = await fetch(`<?= baseUrl('/api/v1/billing/dcr') ?>?doctor_id=${doctorId}&start=${start}&end=${end}`);
                const result = await resp.json();

                if (result.success && result.data.reports) {
                    renderReport(result.data.reports);
                } else {
                    body.innerHTML = '<tr><td colspan="6" class="text-center py-5">No clinical revenue found for this period.</td></tr>';
                }
            } catch (e) {
                body.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-danger">Report engine failed to initialize.</td></tr>';
            }
        }

        function renderReport(reports) {
            const body = document.getElementById('revenue-body');
            body.innerHTML = '';

            let totals = { op: 0, therapy: 0, pharm: 0, share: 0, net: 0 };

            reports.forEach(r => {
                totals.op += parseFloat(r.op_revenue);
                totals.therapy += parseFloat(r.therapy_revenue);
                totals.pharm += parseFloat(r.pharmacy_revenue);
                totals.share += parseFloat(r.payout_share);
                totals.net += parseFloat(r.net_revenue);

                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">${r.doctor_name.substring(0, 2)}</div>
                        <div class="fw-bold">${r.doctor_name}<br><small class="text-muted fs-10">${r.specialization || 'Siddha'}</small></div>
                    </div>
                </td>
                <td>₹ ${parseFloat(r.op_revenue).toLocaleString('en-IN')}</td>
                <td>₹ ${parseFloat(r.therapy_revenue).toLocaleString('en-IN')}</td>
                <td>₹ ${parseFloat(r.pharmacy_revenue).toLocaleString('en-IN')}</td>
                <td class="fw-bold">₹ ${parseFloat(r.payout_share).toLocaleString('en-IN')}</td>
                <td class="text-success fw-bold">₹ ${parseFloat(r.net_revenue).toLocaleString('en-IN')}</td>
            `;
                body.appendChild(tr);
            });

            // Update Footer
            const foot = document.getElementById('revenue-footer');
            foot.innerHTML = `
            <tr>
                <td>Hospital Totals</td>
                <td>₹ ${totals.op.toLocaleString('en-IN')}</td>
                <td>₹ ${totals.therapy.toLocaleString('en-IN')}</td>
                <td>₹ ${totals.pharm.toLocaleString('en-IN')}</td>
                <td>₹ ${totals.share.toLocaleString('en-IN')}</td>
                <td class="text-primary fs-16">₹ ${totals.net.toLocaleString('en-IN')}</td>
            </tr>
        `;
        }
    });
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>