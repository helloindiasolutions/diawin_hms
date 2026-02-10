
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/gridjs/theme/mermaid.min.css">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Grid Js</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Tables</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Grid Js</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->

                    <!--Start::row-1 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Basic Table
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="grid-example1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

                    <!--Start:: row-2 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Table With Pagination
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="grid-pagination"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-2 -->
                    
                    <!--Start:: row-3 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Table With Search
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="grid-search"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End:: row-3 -->

                    <!--Start:: row-4 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Table Sorting
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="grid-sorting"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End:: row-4 -->

                    <!--Start:: row-5 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Table Loading
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="grid-loading"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End:: row-5 -->

                    <!--Start:: row-6 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Wide Table 
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="grid-wide"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End:: row-6 -->

                    <!--Start:: row-7 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Fixed Header
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="grid-header-fixed"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End:: row-7 -->

                    <!--Start:: row-8 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Hidden Columns
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="grid-hidden-column"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-8 -->

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- Grid JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/gridjs/gridjs.umd.js"></script>

        <!-- Internal Grid JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/grid.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
