<?php
/**
 * Therapy Rules & Pricing
 * Configurable rules for Siddha/Ayurvedic Treatment
 */
$pageTitle = "Therapy Rules";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Therapy Rules</h2>
        <span class="text-muted fs-12">Define pricing, session frequency, and clinical guardrails for therapies.</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave">
            <i class="ri-add-line align-middle me-1"></i> Add Rule
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Configured Therapy Rules</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0">
                        <thead class="bg-light fs-11 text-uppercase">
                            <tr>
                                <th>Rule Name</th>
                                <th>Applicable To</th>
                                <th>Configuration</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="fs-13">
                            <tr>
                                <td>
                                    <div class="fw-semibold">Senior Citizen Discount</div>
                                    <div class="text-muted fs-11">Automatically applied during billing</div>
                                </td>
                                <td><span class="badge bg-outline-info">All Therapies</span></td>
                                <td><span class="text-primary fw-medium">-15% on Proc. Fee</span></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-icon btn-light"><i class="ri-edit-line"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">7-Day Package Rule</div>
                                    <div class="text-muted fs-11">7th session is complimentary for selected protocols
                                    </div>
                                </td>
                                <td><span class="badge bg-outline-secondary">Kizhi Protocols</span></td>
                                <td><span class="text-primary fw-medium">Buy 6 Get 1</span></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-icon btn-light"><i class="ri-edit-line"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">BP Contraindication</div>
                                    <div class="text-muted fs-11">Block Varmam if Systolic > 160</div>
                                </td>
                                <td><span class="badge bg-outline-danger">Varmam Point Stim</span></td>
                                <td><span class="text-danger fw-medium">Clinical Block</span></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-icon btn-light"><i class="ri-edit-line"></i></button>
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
    // Placeholder for future logic
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>