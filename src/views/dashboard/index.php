<?php
$pageTitle = 'Dashboard';
$userRole = user('roles')[0] ?? 'staff';
$userRoleLower = strtolower($userRole); // For case-insensitive comparison
ob_start();
?>

<!-- Dashboard Header -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <?php if ($userRoleLower === 'doctor'): ?>
            <h1 class="page-title fw-semibold fs-18 mb-0">Doctor Dashboard</h1>
            <p class="text-muted mb-0 fs-12">Your patients and consultations overview</p>
        <?php else: ?>
            <h1 class="page-title fw-semibold fs-18 mb-0">Hospital Dashboard</h1>
            <p class="text-muted mb-0 fs-12">Real-time overview of hospital operations</p>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-light border-0" onclick="refreshDashboard()">
            <i class="ri-refresh-line"></i>
        </button>
    </div>
</div>

<!-- Main Dashboard Container -->
<div class="row g-2">
    <!-- Top Stats Cards Row (Full Width) -->
    <div class="col-12">
        <div class="row g-2 mb-0">
            <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'receptionist', 'inventory_manager', 'pharmacist'])): ?>
                <!-- Total Invoice Card -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card custom-card border-0 shadow-sm stat-card cursor-pointer mb-0"
                        onclick="Melina.navigate('/invoices')">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="stat-icon bg-success-transparent rounded-2"><i
                                        class="ri-money-dollar-circle-line fs-14 text-success"></i></span>
                                <span class="text-muted fs-11 fw-medium">Revenue</span>
                            </div>
                            <h4 class="fw-bold mb-1 stat-number" id="stat_total_invoice"><span
                                    class="placeholder-glow"><span class="placeholder col-6"></span></span></h4>
                            <div class="fs-10" id="stat_total_invoice_trend"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'receptionist']) && $userRoleLower !== 'doctor'): ?>

                <!-- Total Patients Card -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card custom-card border-0 shadow-sm stat-card cursor-pointer mb-0"
                        onclick="Melina.navigate('/patients')">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="stat-icon bg-info-transparent rounded-2"><i
                                        class="ri-user-heart-line fs-14 text-info"></i></span>
                                <span class="text-muted fs-11 fw-medium">Patients</span>
                            </div>
                            <h4 class="fw-bold mb-1 stat-number" id="stat_total_patients"><span
                                    class="placeholder-glow"><span class="placeholder col-6"></span></span></h4>
                            <div class="fs-10" id="stat_total_patients_trend"></div>
                        </div>
                    </div>
                </div>

                <!-- Appointments Card -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card custom-card border-0 shadow-sm stat-card cursor-pointer mb-0"
                        onclick="Melina.navigate('/appointments')">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="stat-icon bg-warning-transparent rounded-2"><i
                                        class="ri-calendar-check-line fs-14 text-warning"></i></span>
                                <span class="text-muted fs-11 fw-medium">Appointments</span>
                            </div>
                            <h4 class="fw-bold mb-1 stat-number" id="stat_appointments"><span class="placeholder-glow"><span
                                        class="placeholder col-6"></span></span></h4>
                            <div class="fs-10" id="stat_appointments_trend"></div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'inventory_manager', 'pharmacist'])): ?>
                <!-- PO Card -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card custom-card border-0 shadow-sm stat-card cursor-pointer mb-0"
                        onclick="Melina.navigate('/purchase-orders')">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="stat-icon bg-primary-transparent rounded-2"><i
                                        class="ri-shopping-cart-2-line fs-14 text-primary"></i></span>
                                <span class="text-muted fs-11 fw-medium">Daily POs</span>
                            </div>
                            <h4 class="fw-bold mb-1 stat-number" id="stat_po_count"><span class="placeholder-glow"><span
                                        class="placeholder col-6"></span></span></h4>
                            <div class="fs-10" id="stat_po_count_trend"></div>
                        </div>
                    </div>
                </div>

                <!-- GRN Card -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card custom-card border-0 shadow-sm stat-card cursor-pointer mb-0"
                        onclick="Melina.navigate('/grn')">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="stat-icon bg-secondary-transparent rounded-2"><i
                                        class="ri-truck-line fs-14 text-secondary"></i></span>
                                <span class="text-muted fs-11 fw-medium">Daily GRNs</span>
                            </div>
                            <h4 class="fw-bold mb-1 stat-number" id="stat_grn_count"><span class="placeholder-glow"><span
                                        class="placeholder col-6"></span></span></h4>
                            <div class="fs-10" id="stat_grn_count_trend"></div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'nurse'])): ?>
                <!-- Bed Occupancy Card -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card custom-card border-0 shadow-sm stat-card cursor-pointer mb-0"
                        onclick="Melina.navigate('/ip/wards')">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="stat-icon bg-danger-transparent rounded-2"><i
                                        class="ri-hotel-bed-line fs-14 text-danger"></i></span>
                                <span class="text-muted fs-11 fw-medium">Ward Status</span>
                            </div>
                            <h4 class="fw-bold mb-1 stat-number" id="stat_bedrooms"><span class="placeholder-glow"><span
                                        class="placeholder col-6"></span></span></h4>
                            <div class="fs-10" id="stat_bedrooms_trend"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Row 1: For Doctors - Stats + Trends & Calendar in same row -->
    <?php if ($userRoleLower === 'doctor'): ?>
        <!-- Doctor Row 1: (Stacked Stats) + Consultation Trends + Calendar -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-12">
            <div class="d-flex flex-column gap-2" style="height: 340px;">
                <!-- Patients Card -->
                <div class="card custom-card border-0 shadow-sm stat-card cursor-pointer mb-0 flex-fill"
                    onclick="Melina.navigate('/patients')">
                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="stat-icon bg-info-transparent rounded-2"><i
                                    class="ri-user-heart-line fs-14 text-info"></i></span>
                            <span class="text-muted fs-11 fw-medium">Patients</span>
                        </div>
                        <h4 class="fw-bold mb-1 stat-number fs-18" id="stat_total_patients"><span
                                class="placeholder-glow"><span class="placeholder col-6"></span></span></h4>
                        <div class="fs-10" id="stat_total_patients_trend"></div>
                    </div>
                </div>

                <!-- Appointments Card -->
                <div class="card custom-card border-0 shadow-sm stat-card cursor-pointer mb-0 flex-fill"
                    onclick="Melina.navigate('/appointments')">
                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="stat-icon bg-warning-transparent rounded-2"><i
                                    class="ri-calendar-check-line fs-14 text-warning"></i></span>
                            <span class="text-muted fs-11 fw-medium">Appointments</span>
                        </div>
                        <h4 class="fw-bold mb-1 stat-number fs-18" id="stat_appointments"><span
                                class="placeholder-glow"><span class="placeholder col-6"></span></span></h4>
                        <div class="fs-10" id="stat_appointments_trend"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consultation Trends Chart -->
        <div class="col-xl-7 col-lg-6">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 340px;">
                <div
                    class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0 fs-13 fw-semibold">Consultation & Appointment Trends</h6>
                        <span class="text-muted fs-10">Historical analysis of patient visits</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle fs-10 px-2" data-bs-toggle="dropdown"
                            id="aptTrendPeriod">Last 30 Days</button>
                        <ul class="dropdown-menu dropdown-menu-end fs-12">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="loadAppointmentTrends(7)">Last 7
                                    Days</a></li>
                            <li><a class="dropdown-item active" href="javascript:void(0)"
                                    onclick="loadAppointmentTrends(30)">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="loadAppointmentTrends(90)">Last
                                    3 Months</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-3 px-3 pb-4">
                    <div id="aptTrendsChart" style="height: 250px;"></div>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="col-xl-3 col-lg-3">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 340px;">
                <div
                    class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-13 fw-semibold" id="calendarMonthYear">Calendar</h6>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-icon btn-light border-0 p-0" onclick="changeCalendarMonth(-1)"><i
                                class="ri-arrow-left-s-line"></i></button>
                        <button class="btn btn-sm btn-icon btn-light border-0 p-0" onclick="changeCalendarMonth(1)"><i
                                class="ri-arrow-right-s-line"></i></button>
                    </div>
                </div>
                <div class="card-body p-3 pb-2 text-center" style="overflow: hidden;">
                    <div id="miniCalendar" class="mini-calendar mx-auto"></div>
                    <div class="mt-2 pt-2 border-top text-start">
                        <div class="fs-11 fw-semibold mb-1" id="selectedDateLabel">Today</div>
                        <div id="calendarEvents" style="max-height: 80px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Row 1: For Admin/Others - Trends & Calendar -->
    <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'receptionist']) && $userRoleLower !== 'doctor'): ?>
        <div class="col-xl-9 col-lg-8">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 340px;">
                <div
                    class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0 fs-13 fw-semibold">Consultation & Appointment Trends</h6>
                        <span class="text-muted fs-10">Historical analysis of patient visits</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle fs-10 px-2" data-bs-toggle="dropdown"
                            id="aptTrendPeriod">Last 30 Days</button>
                        <ul class="dropdown-menu dropdown-menu-end fs-12">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="loadAppointmentTrends(7)">Last 7
                                    Days</a></li>
                            <li><a class="dropdown-item active" href="javascript:void(0)"
                                    onclick="loadAppointmentTrends(30)">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="loadAppointmentTrends(90)">Last
                                    3 Months</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="aptTrendsChart" style="height: 240px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 340px;">
                <div
                    class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-13 fw-semibold" id="calendarMonthYear">Calendar</h6>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-icon btn-light border-0 p-0" onclick="changeCalendarMonth(-1)"><i
                                class="ri-arrow-left-s-line"></i></button>
                        <button class="btn btn-sm btn-icon btn-light border-0 p-0" onclick="changeCalendarMonth(1)"><i
                                class="ri-arrow-right-s-line"></i></button>
                    </div>
                </div>
                <div class="card-body p-3 pb-2 text-center" style="overflow: hidden;">
                    <div id="miniCalendar" class="mini-calendar mx-auto"></div>
                    <div class="mt-2 pt-2 border-top text-start">
                        <div class="fs-11 fw-semibold mb-1" id="selectedDateLabel">Today</div>
                        <div id="calendarEvents" style="max-height: 80px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Row 2: Financials & Efficiency -->
    <?php if (in_array($userRoleLower, ['super_admin', 'admin'])): ?>
        <div class="col-xl-6 col-lg-6">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 340px;">
                <div
                    class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">Financial Performance</h6>
                    <div class="btn-group btn-group-sm" id="revenuePeriodBtns">
                        <button class="btn btn-dark text-white border px-2 active fs-10"
                            onclick="loadRevenueChart('week')">Week</button>
                        <button class="btn btn-outline-light text-dark border px-2 fs-10"
                            onclick="loadRevenueChart('month')">Month</button>
                        <button class="btn btn-outline-light text-dark border px-2 fs-10"
                            onclick="loadRevenueChart('year')">Year</button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex gap-3 fs-10">
                            <div class="d-flex align-items-center gap-1"><span class="legend-dot"
                                    style="background: #3b82f6;"></span> <span>Income</span></div>
                            <div class="d-flex align-items-center gap-1"><span class="legend-dot"
                                    style="background: #10b981;"></span> <span>Expense</span></div>
                        </div>
                        <h4 class="fw-bold mb-0 text-success fs-16" id="totalRevenue">₹ 0</h4>
                    </div>
                    <div id="revenueChart" style="height: 240px;"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'doctor'])): ?>
        <div class="<?php echo ($userRoleLower === 'doctor') ? 'col-xl-4' : 'col-xl-6'; ?> col-lg-6">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 340px;">
                <div class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">
                        <?php echo ($userRoleLower === 'doctor') ? 'My Performance' : 'Doctor Efficiency'; ?>
                    </h6>
                    <span class="text-muted fs-10">Performance metrics (Last 30 Days)</span>
                </div>
                <div class="card-body p-3">
                    <div id="doctorPerformanceChart" style="height: 240px;"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'doctor'])): ?>
        <div class="<?php echo ($userRoleLower === 'doctor') ? 'col-xl-4' : 'col-xl-3'; ?> col-lg-6 col-md-6">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 320px;">
                <div class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">Patient Overview</h6>
                    <span class="text-muted fs-10">Department distribution</span>
                </div>
                <div class="card-body p-2 text-center">
                    <div id="patientDonutChart" style="height: 180px;"></div>
                    <div class="mt-2 px-2 text-start" id="departmentLegend"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'receptionist'])): ?>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 320px; overflow: hidden;">
                <div class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">Doctors' Schedule</h6>
                    <span class="text-muted fs-10">On-duty status</span>
                </div>
                <div class="card-body p-3" style="overflow-y: auto; max-height: 260px;">
                    <div class="list-group list-group-flush" id="doctorsScheduleList">
                        <div class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'nurse'])): ?>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 320px; overflow: hidden;">
                <div class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">Alerts & Reports</h6>
                    <span class="text-muted fs-10">Critical notifications</span>
                </div>
                <div class="card-body p-3" style="overflow-y: auto; max-height: 260px;">
                    <div class="list-group list-group-flush" id="reportsList">
                        <div class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userRoleLower === 'doctor'): ?>
        <!-- Doctor: Activity in Row 2 (4-column) to fill 4-4-4 split -->
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 320px;">
                <div class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">Recent Activity</h6>
                    <span class="text-muted fs-10">Last 5 actions</span>
                </div>
                <div class="card-body p-3" style="max-height: 260px; overflow-y: auto;">
                    <div id="recentActivityList">
                        <div class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (in_array($userRoleLower, ['super_admin', 'admin'])): ?>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card border-0 shadow-sm mb-0" style="height: 320px;">
                <div class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">Departmental Load</h6>
                    <span class="text-muted fs-10">Visits by specialization</span>
                </div>
                <div class="card-body p-2">
                    <div id="deptLoadChart" style="height: 140px;"></div>
                    <div class="table-responsive mt-1">
                        <table class="table table-sm table-borderless fs-10 mb-0">
                            <tbody id="deptStatsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Row 4: Appointment Table & Activity -->
    <div class="<?php echo ($userRoleLower === 'doctor') ? 'col-xl-12' : 'col-xl-9 col-lg-8'; ?>">
        <?php if (in_array($userRoleLower, ['super_admin', 'admin', 'doctor', 'receptionist'])): ?>
            <div class="card custom-card border-0 shadow-sm mb-0">
                <div
                    class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">Patient Appointment</h6>
                    <a href="/appointments" class="text-primary fs-11 fw-medium text-decoration-none">View All</a>
                </div>
                <div class="card-body p-3">
                    <div class="date-selector-wrapper mb-3">
                        <button class="date-nav-btn prev" onclick="scrollDates('prev')"><i
                                class="ri-arrow-left-s-line"></i></button>
                        <div class="d-flex gap-1 overflow-hidden" id="dateSelector"></div>
                        <button class="date-nav-btn next" onclick="scrollDates('next')"><i
                                class="ri-arrow-right-s-line"></i></button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 appointment-table" id="appointmentsTable">
                            <thead>
                                <tr class="fs-11 text-muted border-0">
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Doctor</th>
                                    <th>Treatment</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="appointmentsTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border spinner-border-sm text-primary"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($userRoleLower !== 'doctor'): ?>
        <!-- Admin/Others: Activity in Row 4 (3-column) -->
        <div class="col-xl-3 col-lg-4">
            <div class="card custom-card border-0 shadow-sm mb-0">
                <div class="card-header border-bottom-0 pb-0 pt-3 px-3 bg-transparent">
                    <h6 class="card-title mb-0 fs-13 fw-semibold">Recent Activity</h6>
                    <span class="text-muted fs-10">Last 5 actions</span>
                </div>
                <div class="card-body p-3" style="max-height: 400px; overflow-y: auto;">
                    <div id="recentActivityList">
                        <div class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .stat-card {
        transition: all 0.2s ease;
        border-radius: 12px;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
    }

    .stat-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-number {
        font-size: 1.4rem;
        color: #1e293b;
        letter-spacing: -0.5px;
    }

    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    /* Remove gaps between rows */
    .row.g-2 {
        row-gap: 0.5rem !important;
    }

    /* Ensure cards fill their containers */
    .card.custom-card {
        display: flex;
        flex-direction: column;
    }

    .card.custom-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .date-selector-wrapper {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.6rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .date-nav-btn {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        color: #64748b;
        cursor: pointer;
    }

    .date-nav-btn:hover {
        background: #f8fafc;
        color: #2563eb;
        border-color: #dbeafe;
        transform: scale(1.05);
    }

    .date-selector-item {
        min-width: 52px;
        height: 64px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
        flex-shrink: 0;
    }

    .date-selector-item:hover {
        background: #f1f5f9;
        color: #2563eb;
    }

    .date-selector-item.active {
        background: #eff6ff;
        color: #2563eb;
        border-color: #dbeafe;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1);
    }

    .date-selector-item .day-name {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 2px;
        opacity: 0.7;
    }

    .date-selector-item.active .day-name {
        opacity: 1;
    }

    .date-selector-item .day-num {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1;
    }

    .appointment-table tbody td {
        padding: 0.8rem 0.6rem;
        font-size: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .mini-calendar {
        font-size: 0.75rem;
        width: 100%;
        max-width: 250px;
    }

    .mini-calendar .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        color: #94a3b8;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        padding-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .mini-calendar .calendar-body {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }

    .mini-calendar .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.75rem;
        position: relative;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        color: #475569;
        fw-medium;
    }

    .mini-calendar .calendar-day:hover {
        background: #f1f5f9;
        color: #1e293b;
        transform: scale(1.1);
        z-index: 1;
    }

    .mini-calendar .calendar-day.other-month {
        color: #cbd5e1;
        opacity: 0.5;
    }

    .mini-calendar .calendar-day.today {
        color: #2563eb;
        font-weight: 700;
        background: #eff6ff;
        border: 1px solid #dbeafe;
    }

    .mini-calendar .calendar-day.selected {
        background: #1e293b;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .mini-calendar .calendar-day.has-event::after {
        content: "";
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: #2563eb;
        box-shadow: 0 0 0 1px #fff;
    }

    .mini-calendar .calendar-day.selected.has-event::after {
        background: #fff;
    }

    .event-item {
        padding: 0.6rem 0.8rem;
        border-radius: 10px;
        margin-bottom: 0.5rem;
        border-left: 4px solid;
        font-size: 0.7rem;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s;
        border: 1px solid #f1f5f9;
        border-left-width: 4px;
    }

    .event-item:hover {
        transform: translateX(3px);
        background: #f8fafc;
    }

    .event-primary {
        border-left-color: #3b82f6;
    }

    .event-success {
        border-left-color: #10b981;
    }

    .event-warning {
        border-left-color: #f59e0b;
    }

    .event-danger {
        border-left-color: #ef4444;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        transition: 0.2s;
        border-radius: 8px !important;
    }

    .btn-icon:hover {
        background: #f1f5f9 !important;
        color: #1e293b !important;
    }
</style>

<script>
    let patientOverviewChart, revenueChart, donutChart, aptTrendsChart, deptLoadChart, doctorPerformanceChart;
    let currentCalendarMonth = new Date().getMonth();
    let currentCalendarYear = new Date().getFullYear();
    let selectedDate = new Date().getDate();
    let selectedAppointmentDate = new Date().toISOString().split('T')[0];
    let daysWithEvents = [], _dashboardInitialized = false;

    function cleanupDashboard() {
        if (patientOverviewChart) patientOverviewChart.destroy();
        if (revenueChart) revenueChart.destroy();
        if (donutChart) donutChart.destroy();
        if (aptTrendsChart) aptTrendsChart.destroy();
        if (deptLoadChart) deptLoadChart.destroy();
        if (doctorPerformanceChart) doctorPerformanceChart.destroy();
        _dashboardInitialized = false;
    }

    // Use the global onPageLoad helper (safer than checking Melina directly)
    if (typeof onPageLoad === 'function') {
        onPageLoad(initDashboard);
        onPageUnload(cleanupDashboard);
    } else {
        document.addEventListener('DOMContentLoaded', initDashboard);
    }

    async function initDashboard() {
        if (_dashboardInitialized) return; _dashboardInitialized = true;
        await loadStats();
        await Promise.all([loadAppointmentTrends(30), loadDepartmentLoad(), loadRevenueChart('week'), loadDoctorPerformance()]);
        await Promise.all([loadDoctorsSchedule(), loadReports(), loadRecentActivity(), loadAppointments(selectedAppointmentDate), loadCalendarEvents(), loadPatientDepartments()]);
        initDateSelector(); initMiniCalendar();
    }

    const formatNumber = n => n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    async function fetchAPI(e, p = {}) {
        const u = new URL('/api/v1/dashboard/' + e, window.location.origin);
        Object.keys(p).forEach(k => u.searchParams.append(k, p[k]));
        try { const r = await fetch(u, { headers: { 'Accept': 'application/json' } }); const d = await r.json(); return d.success ? d.data : null; }
        catch (err) { console.error('API Error:', err); return null; }
    }

    async function loadStats() {
        const d = await fetchAPI('stats'); if (!d) return;
        const set = (id, v, c) => {
            const el = document.getElementById(id); if (!el) return;
            el.textContent = v;
            const t = document.getElementById(id + '_trend');
            if (t && c && (c.trend !== undefined || c.diff !== undefined)) {
                const dir = c.trendDir || (c.trend >= 0 ? 'up' : 'down');
                const val = c.trend !== undefined ? Math.abs(c.trend) : (c.diff || 0);
                t.innerHTML = `<span class="text-${dir === 'up' ? 'success' : 'danger'} fw-medium"><i class="ri-arrow-${dir}-line"></i> ${val}%</span> <span class="text-muted ms-1">vs yesterday</span>`;
            }
        };
        set('stat_total_invoice', '₹ ' + formatNumber(d.totalInvoice.count), d.totalInvoice);
        set('stat_total_patients', d.totalPatients.count, d.totalPatients);
        set('stat_appointments', d.appointments.count, d.appointments);
        set('stat_po_count', d.inventory.po.count, d.inventory.po);
        set('stat_grn_count', d.inventory.grn.count, d.inventory.grn);
        set('stat_bedrooms', (d.bedroom.occupied || 0) + '/' + (d.bedroom.count || 0));
        const bt = document.getElementById('stat_bedrooms_trend');
        if (bt) bt.innerHTML = `<span class="text-info-transparent text-info px-1 rounded">${d.bedroom.available || 0} Available</span>`;
    }

    async function loadAppointmentTrends(days) {
        const d = await fetchAPI('appointment-trends', { days });
        const el = document.getElementById('aptTrendsChart'); if (!d || !el || typeof ApexCharts === 'undefined') return;
        const opts = {
            series: d.series, chart: { type: 'area', height: 250, toolbar: { show: false }, zoom: { enabled: false } },
            colors: ['#3b82f6', '#10b981', '#ef4444'], stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: d.categories,
                tickAmount: 10,
                labels: {
                    show: true,
                    rotate: 0,
                    style: {
                        colors: '#1e293b',
                        fontSize: '11px',
                        fontWeight: 600
                    },
                    hideOverlappingLabels: true,
                    trim: true
                },
                axisBorder: { show: false },
                axisTicks: { show: true, color: '#e2e8f0' }
            },
            yaxis: {
                forceNiceScale: true,
                decimalsInFloat: 0,
                labels: { style: { colors: '#64748b', fontSize: '10px' } }
            },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px', markers: { radius: 12 } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 }
        };
        if (aptTrendsChart) aptTrendsChart.destroy(); aptTrendsChart = new ApexCharts(el, opts); aptTrendsChart.render();
    }

    async function loadDepartmentLoad() {
        const d = await fetchAPI('departmental-trends');
        const el = document.getElementById('deptLoadChart'); if (!d || !el || typeof ApexCharts === 'undefined') return;
        const opts = {
            series: [{ name: 'Patients', data: d.map(x => parseInt(x.visits)) }],
            chart: { type: 'bar', height: 140, toolbar: { show: false } }, colors: ['#6366f1'],
            plotOptions: { bar: { borderRadius: 3, horizontal: true, barHeight: '50%' } },
            xaxis: { categories: d.map(x => x.department), labels: { style: { fontSize: '9px' } } },
            grid: { show: false }, dataLabels: { enabled: false }
        };
        if (deptLoadChart) deptLoadChart.destroy(); deptLoadChart = new ApexCharts(el, opts); deptLoadChart.render();
        const tb = document.getElementById('deptStatsBody'); if (tb) tb.innerHTML = d.map(x => `<tr><td><span class="fw-medium">${x.department}</span></td><td class="text-end text-muted">${x.visits}</td></tr>`).join('');
    }

    async function loadRevenueChart(period) {
        const d = await fetchAPI('revenue-chart', { period });
        const el = document.getElementById('revenueChart'); if (!d || !el || typeof ApexCharts === 'undefined') return;
        const tr = document.getElementById('totalRevenue'); if (tr) tr.textContent = '₹ ' + formatNumber(d.totalIncome);
        document.querySelectorAll('#revenuePeriodBtns button').forEach(b => {
            b.classList.remove('active', 'btn-dark', 'text-white'); b.classList.add('text-dark', 'btn-outline-light');
            if (b.textContent.toLowerCase() === period) { b.classList.add('active', 'btn-dark', 'text-white'); b.classList.remove('text-dark', 'btn-outline-light'); }
        });
        const opts = {
            series: d.series, chart: { type: 'bar', height: 240, stacked: true, toolbar: { show: false } },
            colors: ['#3b82f6', '#10b981'], plotOptions: { bar: { columnWidth: '35%', borderRadius: 3 } },
            xaxis: {
                categories: d.categories,
                tickAmount: period === 'year' ? 12 : (period === 'month' ? 10 : 7),
                labels: {
                    show: true,
                    rotate: period === 'year' ? -45 : 0,
                    rotateAlways: period === 'year',
                    style: { fontSize: '10px', fontWeight: 500 },
                    hideOverlappingLabels: true,
                    trim: true,
                    maxHeight: 60,
                    formatter: function (value) {
                        if (period === 'year' && value) {
                            // Format long dates to shorter format (e.g., "Jan 15")
                            const parts = value.split('-');
                            if (parts.length >= 2) {
                                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                const monthIdx = parseInt(parts[1]) - 1;
                                return months[monthIdx] + ' ' + parseInt(parts[2]);
                            }
                        }
                        return value;
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: true, color: '#e2e8f0' }
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 3 }, legend: { show: false }
        };
        if (revenueChart) revenueChart.destroy(); revenueChart = new ApexCharts(el, opts); revenueChart.render();
    }

    async function loadDoctorPerformance() {
        const d = await fetchAPI('doctor-trends');
        const el = document.getElementById('doctorPerformanceChart'); if (!d || !el || typeof ApexCharts === 'undefined') return;
        const opts = {
            series: [{ name: 'Consultations', data: d.map(x => parseInt(x.consultations)) }, { name: 'Completed', data: d.map(x => parseInt(x.completed)) }],
            chart: { type: 'bar', height: 240, toolbar: { show: false } }, colors: ['#3b82f6', '#10b981'],
            dataLabels: { enabled: false },
            plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
            xaxis: {
                categories: d.map(x => x.doctor),
                labels: {
                    show: true,
                    rotate: 0,
                    style: { colors: '#1e293b', fontSize: '11px', fontWeight: 600 },
                    hideOverlappingLabels: true
                },
                tickAmount: 10
            },
            yaxis: { labels: { style: { colors: '#64748b', fontSize: '10px' } } },
            legend: { position: 'top', fontSize: '11px', markers: { radius: 12 } }, grid: { borderColor: '#f1f5f9' }
        };
        if (doctorPerformanceChart) doctorPerformanceChart.destroy(); doctorPerformanceChart = new ApexCharts(el, opts); doctorPerformanceChart.render();
    }

    async function loadPatientDepartments() {
        const d = await fetchAPI('patient-departments');
        const el = document.getElementById('patientDonutChart'); if (!d || !el || typeof ApexCharts === 'undefined') return;
        const opts = {
            series: d.series || [], labels: d.labels || [],
            chart: { type: 'donut', height: 180 }, colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#8b5cf6'],
            legend: { show: false }, dataLabels: { enabled: false }, plotOptions: { pie: { donut: { size: '75%' } } }
        };
        if (donutChart) donutChart.destroy(); donutChart = new ApexCharts(el, opts); donutChart.render();
        const leg = document.getElementById('departmentLegend');
        if (leg && d.labels) leg.innerHTML = d.labels.map((name, i) => `<div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 10px;"><div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background: ${opts.colors[i % opts.colors.length]}"></span><span>${name}</span></div><span class="fw-semibold">${d.series[i]}</span></div>`).join('');
    }

    async function loadDoctorsSchedule() {
        const d = await fetchAPI('doctors-schedule'); const c = document.getElementById('doctorsScheduleList'); if (!d || !c) return;
        c.innerHTML = d.doctors.length ? d.doctors.map(x => `<div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom border-light"><div class="avatar avatar-xs rounded-circle bg-primary-transparent text-primary fw-bold" style="font-size: 9px; min-width: 24px;">${x.name.split(' ').map(n => n[0]).join('')}</div><div class="flex-grow-1 overflow-hidden"><div class="fs-11 fw-semibold text-truncate">${x.name}</div><div class="fs-10 text-muted text-truncate">${x.dept}</div></div><span class="badge bg-${x.statusClass}-transparent text-${x.statusClass}" style="font-size: 8px;">${x.status}</span></div>`).join('') : '<div class="text-center py-4 fs-11 text-muted">No doctors on duty</div>';
    }

    async function loadReports() {
        const d = await fetchAPI('reports'); const c = document.getElementById('reportsList'); if (!d || !c) return;
        c.innerHTML = d.reports.length ? d.reports.map(x => `<div class="report-item p-2 mb-2 rounded border-start border-3 border-${x.color}" style="background: rgba(var(--${x.color}-rgb), 0.03)"><div class="d-flex align-items-center gap-2"><i class="${x.icon} fs-14 text-${x.color}"></i><div class="flex-grow-1"><div class="fs-11 fw-semibold text-dark">${x.title}</div><div class="fs-10 text-muted">${x.time}</div></div></div></div>`).join('') : '<div class="text-center py-4"><i class="ri-checkbox-circle-line text-success fs-24 d-block mb-1"></i><span class="fs-11 text-muted">All systems optimal</span></div>';
    }

    async function loadRecentActivity() {
        const d = await fetchAPI('activity'); const c = document.getElementById('recentActivityList'); if (!d || !c) return;
        c.innerHTML = d.activities.length ? d.activities.slice(0, 5).map(x => `<div class="activity-item d-flex gap-2 mb-3"><div class="activity-icon bg-${x.color}-transparent border border-${x.color} flex-shrink-0 d-flex align-items-center justify-content-center" style="width:26px; height:26px; border-radius:6px"><i class="${x.icon} fs-10 text-${x.color}"></i></div><div class="flex-grow-1"><div class="fs-11 text-dark line-clamp-2">${x.text}</div><div class="fs-10 text-muted">${x.time}</div></div></div>`).join('') : '<div class="text-center py-4 fs-11 text-muted">No recent activity</div>';
    }

    function initMiniCalendar() { renderMiniCalendar(); loadCalendarEvents(); }
    function changeCalendarMonth(d) {
        currentCalendarMonth += d; if (currentCalendarMonth < 0) { currentCalendarMonth = 11; currentCalendarYear--; } else if (currentCalendarMonth > 11) { currentCalendarMonth = 0; currentCalendarYear++; }
        renderMiniCalendar(); loadCalendarEvents();
    }
    function renderMiniCalendar() {
        const c = document.getElementById('miniCalendar'); const my = document.getElementById('calendarMonthYear'); if (!c || !my) return;
        const ms = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        my.textContent = `${ms[currentCalendarMonth]} ${currentCalendarYear}`;
        const f = new Date(currentCalendarYear, currentCalendarMonth, 1).getDay(); const d = new Date(currentCalendarYear, currentCalendarMonth + 1, 0).getDate();
        let h = '<div class="calendar-header"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div><div class="calendar-body">';
        const pm = new Date(currentCalendarYear, currentCalendarMonth, 0).getDate();
        for (let i = f - 1; i >= 0; i--) h += `<div class="calendar-day other-month">${pm - i}</div>`;
        const today = new Date();
        for (let i = 1; i <= d; i++) {
            const isT = i === today.getDate() && currentCalendarMonth === today.getMonth() && currentCalendarYear === today.getFullYear();
            const isS = i === selectedDate; const hasE = daysWithEvents.includes(i);
            h += `<div class="calendar-day ${isT ? 'today' : (isS ? 'selected' : '')} ${hasE ? 'has-event' : ''}" onclick="selectCalendarDate(${i})">${i}</div>`;
        }
        c.innerHTML = h + '</div>';
    }
    function selectCalendarDate(d) { selectedDate = d; renderMiniCalendar(); loadCalendarEvents(); }
    async function loadCalendarEvents() {
        const ds = `${currentCalendarYear}-${String(currentCalendarMonth + 1).padStart(2, '0')}-${String(selectedDate).padStart(2, '0')}`;
        const r = await fetchAPI('calendar-events', { date: ds, month: currentCalendarMonth + 1, year: currentCalendarYear });
        if (r) {
            daysWithEvents = r.daysWithEvents || []; renderMiniCalendar();
            const c = document.getElementById('calendarEvents'); if (c) c.innerHTML = r.events?.length ? r.events.map(e => `<div class="event-item event-${e.color} p-2 border-start border-3" onclick="Melina.navigate('/appointments/${e.id}')"><div class="d-flex justify-content-between align-items-center mb-1"><span class="fw-bold fs-10 text-truncate">${e.title.split(' - ')[0]}</span><span class="badge bg-light text-dark fs-8">${e.time.split(' - ')[0]}</span></div></div>`).join('') : '<div class="text-center py-2 fs-10 text-muted">No appointments</div>';
        }
        const sdl = document.getElementById('selectedDateLabel');
        if (sdl) sdl.textContent = new Date(currentCalendarYear, currentCalendarMonth, selectedDate).toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long' });
    }

    function initDateSelector() {
        const c = document.getElementById('dateSelector'); if (!c) return; const t = new Date(); let h = '';
        for (let i = -3; i <= 10; i++) {
            const d = new Date(t); d.setDate(t.getDate() + i); const s = d.toISOString().split('T')[0];
            h += `<div class="date-selector-item ${s === selectedAppointmentDate ? 'active' : ''}" onclick="selectAppointmentDate('${s}')"><span class="day-name">${d.toLocaleDateString(undefined, { weekday: 'short' })}</span><span class="day-num">${d.getDate()}</span></div>`;
        }
        c.innerHTML = h;
    }
    function selectAppointmentDate(d) { selectedAppointmentDate = d; initDateSelector(); loadAppointments(d); }
    async function loadAppointments(date) {
        const d = await fetchAPI('appointments', { date }); const tb = document.getElementById('appointmentsTableBody'); if (!tb) return;
        tb.innerHTML = d?.appointments?.length ? d.appointments.map(a => `<tr class="cursor-pointer" onclick="Melina.navigate('/appointments/${a.id}')"><td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-xs rounded-circle bg-primary-transparent text-primary">${a.name[0]}</span><span class="fw-medium">${a.name}</span></div></td><td>${a.date}</td><td>${a.time}</td><td>${a.doctor}</td><td><span class="fs-11 text-muted">${a.treatment}</span></td><td><span class="badge bg-${a.statusClass}-transparent text-${a.statusClass} fs-10 px-2">${a.status}</span></td><td class="text-end text-muted"><i class="ri-arrow-right-s-line"></i></td></tr>`).join('') : '<tr><td colspan="7" class="text-center py-5"><img src="<?= asset("images/no-data.svg") ?>" style="height: 50px" class="mb-2 d-block mx-auto opacity-50"><span class="text-muted fs-11">No appointments found for this date.</span></td></tr>';
    }
    function scrollDates(d) { const c = document.getElementById('dateSelector'); if (c) c.scrollBy({ left: d === 'next' ? 100 : -100, behavior: 'smooth' }); }
    function refreshDashboard() { cleanupDashboard(); initDashboard(); }
</script>

<?php $content = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>