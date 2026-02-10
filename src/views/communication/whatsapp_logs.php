<?php
/**
 * WhatsApp Message Logs
 */
$pageTitle = "WhatsApp Communication Logs";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">WhatsApp History</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Communication</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">WhatsApp</a></li>
            <li class="breadcrumb-item active" aria-current="page">Logs</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Recent Transmissions</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100">
                        <thead class="bg-light">
                            <tr>
                                <th>Sent Time</th>
                                <th>Patient Name</th>
                                <th>Message Body</th>
                                <th>Status</th>
                                <th>Reason (If Failed)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>24 Jan, 10:25 AM</td>
                                <td>Mr. Kumar</td>
                                <td class="text-truncate" style="max-width: 300px;">"Hi Kumar, your lab results for CBC
                                    are now available..."</td>
                                <td><span class="badge bg-success-transparent text-success">Delivered</span></td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>24 Jan, 09:12 AM</td>
                                <td>Ms. Anitha</td>
                                <td class="text-truncate" style="max-width: 300px;">"Hi Anitha, this is a reminder for
                                    your appointment..."</td>
                                <td><span class="badge bg-primary-transparent text-primary">Sent</span></td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>23 Jan, 04:30 PM</td>
                                <td>Mr. David</td>
                                <td class="text-truncate" style="max-width: 300px;">"Thank you for choosing Melina
                                    Hospital..."</td>
                                <td><span class="badge bg-danger-transparent text-danger">Failed</span></td>
                                <td>User opted out/blocked</td>
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