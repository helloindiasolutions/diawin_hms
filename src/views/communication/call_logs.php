<?php
/**
 * Call Communication Logs
 */
$pageTitle = "Telephony Call Logs";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Telephony Logs</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Communication</a></li>
            <li class="breadcrumb-item active" aria-current="page">Call Logs</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-md bg-primary-transparent text-primary">
                            <i class="ri-phone-receive-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0">142</h5>
                        <p class="text-muted mb-0 fs-12">Total Calls Today</p>
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
                            <i class="ri-phone-fill fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0">128</h5>
                        <p class="text-muted mb-0 fs-12">Answered Calls</p>
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
                        <span class="avatar avatar-md bg-danger-transparent text-danger">
                            <i class="ri-phone-missed-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0">14</h5>
                        <p class="text-muted mb-0 fs-12">Missed Calls</p>
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
                        <span class="avatar avatar-md bg-warning-transparent text-warning">
                            <i class="ri-timer-line fs-20"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0">4.5m</h5>
                        <p class="text-muted mb-0 fs-12">Avg. Duration</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Recent Call Activity</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100">
                        <thead class="bg-light">
                            <tr>
                                <th>Time</th>
                                <th>Direction</th>
                                <th>Caller/Reciever</th>
                                <th>Duration</th>
                                <th>Agent</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>10:45 AM</td>
                                <td><i class="ri-arrow-left-down-line text-success me-1"></i> Inbound</td>
                                <td>+91 98XXX XXXX0</td>
                                <td>05:24</td>
                                <td>Front Desk - Anita</td>
                                <td><span class="badge bg-success-transparent text-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>10:30 AM</td>
                                <td><i class="ri-arrow-right-up-line text-primary me-1"></i> Outbound</td>
                                <td>+91 97XXX XXXX2</td>
                                <td>02:12</td>
                                <td>OPD - Rahul</td>
                                <td><span class="badge bg-success-transparent text-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>10:15 AM</td>
                                <td><i class="ri-arrow-left-down-line text-danger me-1"></i> Inbound</td>
                                <td>+91 99XXX XXXX5</td>
                                <td>00:00</td>
                                <td>-</td>
                                <td><span class="badge bg-danger-transparent text-danger">Missed</span></td>
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