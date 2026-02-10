
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>



<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Chartjs</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);">
                                        Charts
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Chartjs</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->

                    <!-- Start::row-1 -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Line Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-line" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Bar Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-bar" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Pie Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-pie" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Doughnut Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-doughnut" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Mixed Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-mixed" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Polar Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-polar" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Radial Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-radar" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Scatter Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-scatter" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">Chartjs Bubble Chart</div>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartjs-bubble" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- Chartjs Chart JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/chart.js/chart.umd.js"></script>

        <!-- Imternal Chartjs JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/chartjs-charts.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
