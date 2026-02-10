<?php
/**
 * WhatsApp Scheduled Messages
 */
$pageTitle = "Scheduled WhatsApp Messages";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Scheduled Messages</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Communication</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">WhatsApp</a></li>
            <li class="breadcrumb-item active" aria-current="page">Scheduled</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Pending Messages Queue</div>
                <div class="btn-list">
                    <button class="btn btn-sm btn-light btn-wave"><i class="ri-refresh-line me-1"></i> Refresh
                        Queue</button>
                    <button class="btn btn-sm btn-danger-light btn-wave"><i class="ri-close-circle-line me-1"></i>
                        Cancel All</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap w-100">
                        <thead class="bg-light">
                            <tr>
                                <th>Schedule Time</th>
                                <th>Patient Name</th>
                                <th>Phone Number</th>
                                <th>Template</th>
                                <th>Priority</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="text-primary fw-semibold">Today, 06:00 PM</span></td>
                                <td>Mr. Selvam K</td>
                                <td>+91 98XXX XXXX1</td>
                                <td>Appointment Reminder</td>
                                <td><span class="badge bg-danger-transparent text-danger">High</span></td>
                                <td>
                                    <button class="btn btn-sm btn-icon btn-danger-light"><i
                                            class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="text-primary fw-semibold">Tonight, 08:30 PM</span></td>
                                <td>Ms. Priya R</td>
                                <td>+91 97XXX XXXX4</td>
                                <td>Visit Follow-up</td>
                                <td><span class="badge bg-primary-transparent text-primary">Normal</span></td>
                                <td>
                                    <button class="btn btn-sm btn-icon btn-danger-light"><i
                                            class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="text-primary fw-semibold">Tomorrow, 09:00 AM</span></td>
                                <td>Master Aryan</td>
                                <td>+91 96XXX XXXX2</td>
                                <td>General Greeting</td>
                                <td><span class="badge bg-secondary-transparent text-secondary">Low</span></td>
                                <td>
                                    <button class="btn btn-sm btn-icon btn-danger-light"><i
                                            class="ri-delete-bin-line"></i></button>
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