<?php
/**
 * Staff Attendance Management
 */
$pageTitle = "Staff Attendance";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Staff Attendance</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Staff & HR</a></li>
            <li class="breadcrumb-item active" aria-current="page">Attendance</li>
        </ol>
    </div>
    <div class="btn-list">
        <button class="btn btn-primary btn-wave"><i class="ri-fingerprint-line align-middle me-1"></i> Biometric
            Sync</button>
    </div>
</div>

<div class="row">
    <div class="col-xl-9">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Daily Attendance -
                    <?= date('d M, Y') ?>
                </div>
                <div class="d-flex align-items-center">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="Search staff...">
                        <button class="btn btn-primary"><i class="ri-search-line"></i></button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Department</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-primary-transparent me-2">SK</span>
                                        <span class="fw-semibold">Mr. Selvakumar</span>
                                    </div>
                                </td>
                                <td>Nursing</td>
                                <td>08:54 AM</td>
                                <td>05:32 PM</td>
                                <td>08h 38m</td>
                                <td><span class="badge bg-success-transparent text-success">Present</span></td>
                                <td><button class="btn btn-sm btn-icon btn-primary-light"><i
                                            class="ri-edit-line"></i></button></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-info-transparent me-2">AD</span>
                                        <span class="fw-semibold">Dr. Ayswarya D</span>
                                    </div>
                                </td>
                                <td>OPD</td>
                                <td>09:12 AM</td>
                                <td>-</td>
                                <td>-</td>
                                <td><span class="badge bg-primary-transparent text-primary">In Office</span></td>
                                <td><button class="btn btn-sm btn-icon btn-primary-light"><i
                                            class="ri-edit-line"></i></button></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-danger-transparent me-2">RR</span>
                                        <span class="fw-semibold">Mr. Rahul R</span>
                                    </div>
                                </td>
                                <td>Administration</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td><span class="badge bg-danger-transparent text-danger">Absent</span></td>
                                <td><button class="btn btn-sm btn-icon btn-primary-light"><i
                                            class="ri-edit-line"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Attendance Summary</div>
            </div>
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-13">Total Staff</span>
                        <span class="fw-bold">45</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-13">Present</span>
                        <span class="fw-bold text-success">38</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted fs-13">Late Entry</span>
                        <span class="fw-bold text-warning">4</span>
                    </div>
                </div>
                <div class="p-3">
                    <p class="fs-12 fw-semibold mb-2">Shift Occupancy</p>
                    <div class="progress progress-md mb-2">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="85"
                            aria-valuemin="0" aria-valuemax="100">85%</div>
                    </div>
                    <small class="text-muted">Morning Shift (08:00 AM - 04:00 PM)</small>
                </div>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Today's Leaves</h6>
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar avatar-sm rounded-circle me-2 bg-pink-transparent">MP</span>
                    <div>
                        <p class="mb-0 fs-12 fw-semibold">Meera P</p>
                        <small class="text-muted">Sick Leave</small>
                    </div>
                    <span class="ms-auto badge bg-pink text-white fs-10">Approved</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>