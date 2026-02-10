<?php
/**
 * Therapy Billing Management
 * Invoicing for Siddha/Ayurvedic Treatment Sessions
 */
$pageTitle = "Therapy Billing";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Therapy Billing</h2>
        <span class="text-muted fs-12">Generate invoices for therapy sessions and treatment packages.</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave" id="newBillBtn">
            <i class="ri-receipt-line align-middle me-1"></i> New Therapy Bill
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle"><i
                                class="ri-money-rupee-circle-line fs-18"></i></span>
                    </div>
                    <div>
                        <span class="text-muted fs-12">Today's Revenue</span>
                        <h5 class="fw-bold mb-0">₹ 8,450</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-warning-transparent text-warning rounded-circle"><i
                                class="ri-timer-line fs-18"></i></span>
                    </div>
                    <div>
                        <span class="text-muted fs-12">Pending Bills</span>
                        <h5 class="fw-bold mb-0">12 Sessions</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Recent Therapy Invoices</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0">
                        <thead class="bg-light text-uppercase fs-11">
                            <tr>
                                <th>Invoice #</th>
                                <th>Patient</th>
                                <th>Session / Protocol</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="fs-13">
                            <!-- Simulated Data -->
                            <tr>
                                <td class="fw-bold text-primary">INV-T-2026110</td>
                                <td>
                                    <div class="fw-semibold">Sakthi Kumar</div>
                                    <div class="text-muted fs-11">MRN26000001</div>
                                </td>
                                <td>
                                    <div class="fw-medium">Elakizhi - Day 1/7</div>
                                    <div class="text-muted fs-11">Protocol: PRO-KIZ-01</div>
                                </td>
                                <td>Feb 05, 2026</td>
                                <td class="fw-bold text-success">₹ 1,200.00</td>
                                <td><span class="badge bg-success-transparent">Paid</span></td>
                                <td class="text-center">
                                    <div class="btn-list justify-content-center">
                                        <button class="btn btn-sm btn-icon btn-light"><i
                                                class="ri-printer-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-light"><i
                                                class="ri-eye-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">INV-T-2026111</td>
                                <td>
                                    <div class="fw-semibold">Meera</div>
                                    <div class="text-muted fs-11">MRN26000022</div>
                                </td>
                                <td>
                                    <div class="fw-medium">Full Abhyanga</div>
                                    <div class="text-muted fs-11">Procedure Only</div>
                                </td>
                                <td>Feb 05, 2026</td>
                                <td class="fw-bold text-success">₹ 850.00</td>
                                <td><span class="badge bg-warning-transparent">Pending</span></td>
                                <td class="text-center">
                                    <div class="btn-list justify-content-center">
                                        <button class="btn btn-sm btn-primary-light">Pay Now</button>
                                        <button class="btn btn-sm btn-icon btn-light"><i
                                                class="ri-printer-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('newBillBtn').onclick = () => {
        Swal.fire({
            title: 'New Therapy Bill',
            html: `
                <div class="text-start">
                    <label class="form-label">Search Patient Session</label>
                    <input type="text" class="form-control mb-3" placeholder="Enter session ID or MRN">
                    <div class="alert alert-info fs-12">This will pull session details and usage logs to generate a comprehensive bill.</div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Generate Bill'
        }).then((result) => {
            if (result.isConfirmed) {
                showToast('Bill generation system initialized', 'success');
            }
        });
    };
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>