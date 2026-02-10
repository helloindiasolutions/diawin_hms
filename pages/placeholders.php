
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- Prism CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/prismjs/themes/prism-coy.min.css">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Placeholders</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Advanced Ui</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Placeholders</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->

                    <!-- Start:: row-1 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <img class="card-img-top" src="<?php echo $baseUrl; ?>/assets/images/media/media-60.jpg" alt="">
                                        <div class="card-body">
                                            <h5 class="card-title">Card title</h5>
                                            <p class="card-text">Some quick example text to build on the card title and make
                                                up
                                                the bulk of the card's content.</p>
                                            <a href="javascript:void(0);" class="btn btn-primary">Go somewhere</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card" aria-hidden="true">
                                        <img class="card-img-top" src="<?php echo $baseUrl; ?>/assets/images/media/media-61.jpg" alt="">
                                        <div class="card-body">
                                            <div class="h5 card-title placeholder-glow">
                                                <span class="placeholder col-6"></span>
                                            </div>
                                            <p class="card-text placeholder-glow">
                                                <span class="placeholder col-7"></span>
                                                <span class="placeholder col-4"></span>
                                                <span class="placeholder col-4"></span>
                                                <span class="placeholder col-6"></span>
                                            </p>
                                            <a href="javascript:void(0);" tabindex="-1" class="btn btn-primary disabled placeholder col-6"></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Animation
                                            </div>
                                            <div class="prism-toggle">
                                                <button class="btn btn-sm btn-primary-light">Show Code<i class="ri-code-line ms-2 d-inline-block align-middle"></i></button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="placeholder-glow mb-0">
                                                <span class="placeholder col-12"></span>
                                            </p>
                                            <p class="placeholder-wave mb-0">
                                                <span class="placeholder col-12"></span>
                                            </p>
                                        </div>
                                        <div class="card-footer d-none border-top-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;p class="placeholder-glow mb-0"&gt;
    &lt;span class="placeholder col-12"&gt;&lt;/span&gt;
&lt;/p&gt;
&lt;p class="placeholder-wave mb-0"&gt;
    &lt;span class="placeholder col-12"&gt;&lt;/span&gt;
&lt;/p&gt;</code></pre>
<!-- Prism Code -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Sizing
                                                    </div>
                                                    <div class="prism-toggle">
                                                        <button class="btn btn-sm btn-primary-light">Show Code<i class="ri-code-line ms-2 d-inline-block align-middle"></i></button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <span class="placeholder col-12 placeholder-xl mb-1"></span>
                                                    <span class="placeholder col-12 placeholder-lg"></span>
                                                    <span class="placeholder col-12"></span>
                                                    <span class="placeholder col-12 placeholder-sm"></span>
                                                    <span class="placeholder col-12 placeholder-xs"></span>
                                                </div>
                                                <div class="card-footer d-none border-top-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;span class="placeholder col-12 placeholder-xl mb-1"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 placeholder-lg"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 placeholder-sm"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 placeholder-xs"&gt;&lt;/span&gt;</code></pre>
<!-- Prism Code -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Colors
                                            </div>
                                            <div class="prism-toggle">
                                                <button class="btn btn-sm btn-primary-light">Show Code<i class="ri-code-line ms-2 d-inline-block align-middle"></i></button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <span class="placeholder col-12"></span>
                                            <span class="placeholder col-12 bg-primary"></span>
                                            <span class="placeholder col-12 bg-secondary"></span>
                                            <span class="placeholder col-12 bg-success"></span>
                                            <span class="placeholder col-12 bg-danger"></span>
                                            <span class="placeholder col-12 bg-warning"></span>
                                            <span class="placeholder col-12 bg-info"></span>
                                            <span class="placeholder col-12 bg-light"></span>
                                            <span class="placeholder col-12 bg-dark"></span>
                                        </div>
                                        <div class="card-footer d-none border-top-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;span class="placeholder col-12"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 bg-primary"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 bg-secondary"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 bg-success"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 bg-danger"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 bg-warning"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 bg-info"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 bg-light"&gt;&lt;/span&gt;
&lt;span class="placeholder col-12 bg-dark"&gt;&lt;/span&gt;</code></pre>
<!-- Prism Code -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-1 -->

                    <!-- Start:: row-2 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        Width
                                    </div>
                                    <div class="prism-toggle">
                                        <button class="btn btn-sm btn-primary-light">Show Code<i class="ri-code-line ms-2 d-inline-block align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <span class="placeholder bg-primary col-6"></span>
                                    <span class="placeholder bg-primary w-75"></span>
                                    <span class="placeholder bg-primary" style="width: 25%;"></span>
                                </div>
                                <div class="card-footer d-none border-top-0">
<!-- Prism Code -->
<pre class="language-html"><code class="language-html">&lt;span class="placeholder bg-primary col-6"&gt;&lt;/span&gt;
&lt;span class="placeholder bg-primary w-75"&gt;&lt;/span&gt;
&lt;span class="placeholder bg-primary" style="width: 25%;"&gt;&lt;/span&gt;</code></pre>
<!-- Prism Code -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Start:: row-2 -->

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
	
        <!-- Prism JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/prismjs/prism.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/prism-custom.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
