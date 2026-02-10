<?php
/**
 * Billing Packages Management
 */
$pageTitle = "Billing Packages";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Billing Packages</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Billing & Finance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Packages</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave"><i class="ri-add-line align-middle me-1"></i> Create Package</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary">
                            <i class="ri-gift-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0">24</h5>
                        <p class="text-muted mb-0 fs-12">Total Packages</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-success-transparent text-success">
                            <i class="ri-checkbox-circle-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0">18</h5>
                        <p class="text-muted mb-0 fs-12">Active Packages</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-body p-3">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search packages by name or code..."
                        aria-label="Search Packages">
                    <button class="btn btn-primary" type="button"><i class="ri-search-line"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Package List</div>
                <div class="d-flex">
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="btn btn-sm btn-light" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            All Status <i class="ri-arrow-down-s-line align-middle ms-1"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="javascript:void(0);">All Status</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Active</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Inactive</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Package Code</th>
                                <th>Package Name</th>
                                <th>Category</th>
                                <th>Total amount</th>
                                <th>Validity (Days)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="fw-semibold text-primary">PKG-DIA-001</span></td>
                                <td>Diabetes Comprehensive Checkup</td>
                                <td>Diagnostics</td>
                                <td><span class="text-success fw-semibold">₹ 4,500</span></td>
                                <td>30</td>
                                <td><span class="badge bg-success-transparent text-success">Active</span></td>
                                <td>
                                    <div class="btn-list">
                                        <button class="btn btn-sm btn-icon btn-info-light"><i
                                                class="ri-eye-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-primary-light"><i
                                                class="ri-edit-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-danger-light"><i
                                                class="ri-delete-bin-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="fw-semibold text-primary">PKG-MOT-002</span></td>
                                <td>Mother & Child Wellness</td>
                                <td>Maternity</td>
                                <td><span class="text-success fw-semibold">₹ 15,000</span></td>
                                <td>90</td>
                                <td><span class="badge bg-success-transparent text-success">Active</span></td>
                                <td>
                                    <div class="btn-list">
                                        <button class="btn btn-sm btn-icon btn-info-light"><i
                                                class="ri-eye-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-primary-light"><i
                                                class="ri-edit-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-danger-light"><i
                                                class="ri-delete-bin-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="fw-semibold text-primary">PKG-PHY-003</span></td>
                                <td>Advanced Physiotherapy Pack</td>
                                <td>Therapy</td>
                                <td><span class="text-success fw-semibold">₹ 8,200</span></td>
                                <td>45</td>
                                <td><span class="badge bg-success-transparent text-success">Active</span></td>
                                <td>
                                    <div class="btn-list">
                                        <button class="btn btn-sm btn-icon btn-info-light"><i
                                                class="ri-eye-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-primary-light"><i
                                                class="ri-edit-line"></i></button>
                                        <button class="btn btn-sm btn-icon btn-danger-light"><i
                                                class="ri-delete-bin-line"></i></button>
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

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>