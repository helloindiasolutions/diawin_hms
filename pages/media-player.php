
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- Plyr CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/plyr/plyr.css">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Media Player</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Advanced UI</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Media Player</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->

                    <!-- Start::row-1 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        VIDEO PLAYER
                                    </div>
                                </div>
                                <div class="card-body px-2 px-sm-3">
                                    <video id="player" playsinline controls data-poster="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.jpg">
                                        <source src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4" type="video/mp4" >
                                        <source src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-720p.mp4" type="video/mp4" >
                                        <source src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-1080p.mp4" type="video/mp4" >
                                    </video>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card custom-card overflow-hidden">
                                <div class="card-header">
                                    <div class="card-title">YOUTUBE VIDEO</div>
                                </div>
                                <div class="card-body">
                                    <div class="plyr__video-embed" id="player1">
                                        <iframe width="560" height="315" src="https://www.youtube.com/embed/-lDlbQ7DiCI?si=EerYCepJFDTb5xOl" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        AUDIO PLAYER
                                    </div>
                                </div>
                                <div class="card-body px-0 px-sm-3">
                                    <audio id="player2" controls>
                                        <source src="<?php echo $baseUrl; ?>/assets/audio/perfect-beauty.mp3" type="audio/mp3" >
                                    </audio>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- Plyr JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/plyr/plyr.min.js"></script>

        <!-- Internal Plyr JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/media-player.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
