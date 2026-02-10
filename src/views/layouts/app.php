<?php
use App\Services\ThemeService;
// Silence potential warnings (like sodium vs database) that break HTML structure
$htmlAttrs = @ThemeService::getHtmlAttributes() ?: 'lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light"';
$primaryStyle = @ThemeService::getPrimaryColorStyle() ?: '--primary-rgb: 78, 172, 76;';
?>
<!DOCTYPE html>
<html <?= $htmlAttrs ?> style="<?= $primaryStyle ?>">

<head>
    <?php if (!empty($_SESSION['clear_theme_localstorage'])):
        unset($_SESSION['clear_theme_localstorage']); ?>
        <script>
            // Clear theme localStorage on login so DB settings take precedence
            // This MUST run before theme-save.js and custom-switcher.min.js
            (function () {
                var keys = ['vyzordarktheme', 'vyzorlighttheme', 'vyzorrtl', 'vyzorltr', 'vyzorlayout', 'vyzorverticalstyles', 'vyzornavstyles',
                    'vyzorMenu', 'vyzorHeader', 'vyzorregular', 'vyzorclassic', 'vyzormodern', 'vyzorflat', 'vyzordefaultwidth',
                    'vyzorfullwidth', 'vyzorboxed', 'vyzorheaderfixed', 'vyzorheaderscrollable', 'vyzormenufixed', 'vyzormenuscrollable',
                    'primaryRGB', 'bodyBgRGB', 'bodylightRGB', 'bgimg', 'loaderEnable'];
                keys.forEach(function (k) { localStorage.removeItem(k); });
            })();
        </script>
    <?php endif; ?>

    <!-- Theme Sync - MUST load early to initialize localStorage before custom-switcher.min.js -->
    <script>
        window.Melina = window.Melina || {};
        window.Melina.basePath = '<?= $_ENV['BASE_PATH'] ?? '' ?>';
    </script>
    <script src="<?= asset('js/theme-save.js') ?>"></script>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="<?= e($_ENV['APP_NAME'] ?? 'Melina HMS') ?> - Hospital Management System">
    <?= csrfMeta() ?>

    <!-- Title -->
    <title><?= e($pageTitle ?? 'Dashboard') ?> - <?= e($_ENV['APP_NAME'] ?? 'Melina HMS') ?></title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180"
        href="<?= asset('images/brand-logos/favicon_io/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32"
        href="<?= asset('images/brand-logos/favicon_io/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16"
        href="<?= asset('images/brand-logos/favicon_io/favicon-16x16.png') ?>">
    <link rel="icon" href="<?= asset('images/brand-logos/favicon_io/favicon.ico') ?>" type="image/x-icon">
    <link rel="manifest" href="<?= asset('images/brand-logos/favicon_io/site.webmanifest') ?>">

    <!-- Google Fonts - Montserrat for Hospital Name -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Choices JS -->
    <script src="<?= asset('libs/choices.js/public/assets/scripts/choices.min.js') ?>"></script>

    <!-- Main Theme JS -->
    <script src="<?= asset('js/main.js') ?>"></script>

    <!-- Bootstrap CSS -->
    <link id="style" href="<?= asset('libs/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">

    <!-- Style CSS -->
    <link href="<?= asset('css/styles.css') ?>?v=<?= time() ?>" rel="stylesheet">

    <!-- Global Validation CSS -->
    <link href="<?= asset('css/validation.css') ?>" rel="stylesheet">

    <!-- Icons CSS -->
    <link href="<?= asset('css/icons.css') ?>" rel="stylesheet">

    <!-- Node Waves CSS -->
    <link href="<?= asset('libs/node-waves/waves.min.css') ?>" rel="stylesheet">

    <!-- Simplebar CSS -->
    <link href="<?= asset('libs/simplebar/simplebar.min.css') ?>" rel="stylesheet">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="<?= asset('libs/flatpickr/flatpickr.min.css') ?>">

    <!-- Color Picker CSS -->
    <link rel="stylesheet" href="<?= asset('libs/@simonwep/pickr/themes/nano.min.css') ?>">

    <!-- Choices CSS -->
    <link rel="stylesheet" href="<?= asset('libs/choices.js/public/assets/styles/choices.min.css') ?>">

    <!-- Auto Complete CSS -->
    <link rel="stylesheet" href="<?= asset('libs/@tarekraafat/autocomplete.js/css/autoComplete.css') ?>">

    <!-- Apex Charts CSS -->
    <link rel="stylesheet" href="<?= asset('libs/apexcharts/apexcharts.css') ?>">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="<?= asset('css/sidebar.css') ?>">

    <!-- Responsive Font Sizing for Large Screens -->
    <style>
        /* Base responsive font scaling for large displays */
        @media (min-width: 1600px) {
            html {
                font-size: 17px;
            }

            .table td,
            .table th {
                font-size: 1rem;
                padding: 1.1rem 1.35rem;
            }

            .table thead tr th {
                font-size: 0.8rem;
            }

            .form-control,
            .form-select {
                font-size: 0.8rem;
            }

            .btn {
                font-size: 0.8rem;
            }
        }

        @media (min-width: 1920px) {
            html {
                font-size: 18px;
            }

            .table td,
            .table th {
                font-size: 0.9rem;
                padding: 1.15rem 1.4rem;
            }

            .table thead tr th {
                font-size: 0.9rem;
            }

            .form-control,
            .form-select {
                font-size: 0.9rem;
            }

            .btn {
                font-size: 0.9rem;
            }
        }

        @media (min-width: 2560px) {
            html {
                font-size: 20px;
            }

            .table td,
            .table th {
                font-size: 1rem;
                padding: 1.25rem 1.5rem;
            }

            .table thead tr th {
                font-size: 1rem;
            }

            .form-control,
            .form-select {
                font-size: 1rem;
            }

            .btn {
                font-size: 1rem;
            }
        }

        /* Toast notification styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff;
        }

        .toast-error {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: #fff;
        }

        .toast-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
        }

        /* Progress Top Bar Styles */
        .progress-top-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(to right, rgb(var(--primary-rgb)), #20c997);
            z-index: 10000;
            transition: width 0.3s ease, opacity 0.3s ease;
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.5);
            opacity: 0;
            width: 0;
        }

        /* Smooth transition for main content */
        .main-content.app-content>.container-fluid {
            transition: opacity 0.2s ease-in-out;
            position: relative;
        }

        /* Global subtle dot pattern background for all pages */
        .main-content.app-content::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 0;
            background-image: radial-gradient(circle, rgba(7, 72, 0, 0.09) 1px, transparent 1px);
            background-size: 18px 18px;
        }
    </style>

    <!-- Global Search CSS -->
    <link rel="stylesheet" href="<?= asset('css/global-search.css') ?>">

    <?= $styles ?? '' ?>
</head>

<body>
    <!-- Progress Top Bar -->
    <div class="progress-top-bar"></div>

    <!-- Global Search Modal -->
    <?php include __DIR__ . '/partials/global-search.php'; ?>

    <!-- Quick Appointment Modal -->
    <?php include __DIR__ . '/partials/quick-appointment.php'; ?>

    <!-- Loader -->
    <div id="loader">
        <img src="<?= asset('images/media/loader.svg') ?>" alt="">
    </div>

    <div class="page <?= ($fullScreenMode ?? false) ? 'p-0 m-0' : '' ?>">
        <!-- Switcher -->
        <?php if (!($fullScreenMode ?? false))
            include __DIR__ . '/partials/switcher.php'; ?>

        <!-- Header -->
        <?php if (!($fullScreenMode ?? false))
            include __DIR__ . '/partials/header.php'; ?>

        <!-- Sidebar -->
        <?php if (!($fullScreenMode ?? false))
            include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content <?= ($fullScreenMode ?? false) ? 'p-0 m-0 w-100' : 'app-content' ?>">
            <div class="<?= ($fullScreenMode ?? false) ? 'container-fluid p-0' : 'container-fluid' ?>">
                <?php if ($success = flash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <?= e($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error = flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?= e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>
        </div>

        <!-- Footer -->
        <?php if (!($fullScreenMode ?? false))
            include __DIR__ . '/partials/footer.php'; ?>
    </div>

    <!-- Scroll To Top -->
    <div class="scrollToTop">
        <span class="arrow lh-1"><i class="ti ti-arrow-big-up fs-18"></i></span>
    </div>
    <div id="responsive-overlay"></div>

    <!-- Popper JS -->
    <script src="<?= asset('libs/@popperjs/core/umd/popper.min.js') ?>"></script>

    <!-- Bootstrap JS -->
    <script src="<?= asset('libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Node Waves JS -->
    <script src="<?= asset('libs/node-waves/waves.min.js') ?>"></script>

    <!-- Simplebar JS -->
    <script src="<?= asset('libs/simplebar/simplebar.min.js') ?>"></script>
    <script src="<?= asset('js/simplebar.js') ?>"></script>

    <!-- Auto Complete JS -->
    <script src="<?= asset('libs/@tarekraafat/autocomplete.js/autoComplete.min.js') ?>"></script>

    <!-- Color Picker JS -->
    <script src="<?= asset('libs/@simonwep/pickr/pickr.es5.min.js') ?>"></script>

    <!-- Flatpickr JS -->
    <script src="<?= asset('libs/flatpickr/flatpickr.min.js') ?>"></script>

    <!-- ApexCharts JS -->
    <script src="<?= asset('libs/apexcharts/apexcharts.min.js') ?>"></script>

    <?php if (!($fullScreenMode ?? false)): ?>
        <!-- Sticky JS -->
        <script src="<?= asset('js/sticky.js') ?>"></script>

        <!-- Default Menu JS -->
        <script src="<?= asset('js/defaultmenu.min.js') ?>"></script>

        <!-- Modal Notification System -->
        <script src="<?= asset('js/modal-notify.js') ?>"></script>

        <!-- Custom JS -->
        <script src="<?= asset('js/custom.js') ?>"></script>

        <!-- Custom Switcher JS -->
        <script src="<?= asset('js/custom-switcher.min.js') ?>"></script>

        <!-- Sidebar Controller JS -->
        <script src="<?= asset('js/sidebar.js') ?>"></script>

        <!-- Global Search JS -->
        <script src="<?= asset('js/global-search.js') ?>"></script>

        <!-- Global Validation JS -->
        <script src="<?= asset('js/validation.js') ?>"></script>

        <!-- Dynamic SPA Navigation -->
        <script src="<?= asset('js/spa-navigation.js') ?>"></script>

        <!-- Page Initialization System (React-like useEffect for SPA) -->
        <script src="<?= asset('js/page-init-v2.js') ?>?v=<?= time() ?>"></script>

    <?php endif; ?>

    <!-- Modal Backdrop Fix: Move backdrop inside main-content -->
    <script>
        (function () {
            // Override Bootstrap's default backdrop container to use main-content
            const mainContent = document.querySelector('.main-content.app-content');
            if (mainContent) {
                // MutationObserver to catch backdrop when it's added to body
                const observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        mutation.addedNodes.forEach(function (node) {
                            if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
                                // Move backdrop inside main-content
                                mainContent.appendChild(node);
                            }
                        });
                    });
                });
                observer.observe(document.body, { childList: true });
            }
        })();
    </script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <script>
        /**
         * Global Toast System Wrapper
         * Uses Toastify for consistent notifications
         */
        window.showToast = function (message, type = 'success') {
            const bg = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#f59e0b');

            // Check if Toastify is loaded
            if (typeof Toastify === 'function') {
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: bg,
                    stopOnFocus: true,
                    className: "shadow-lg rounded-3 fw-medium"
                }).showToast();
            } else {
                console.warn('Toastify not loaded, falling back to alert', message);
                // Fallback only if absolutely necessary (should not happen with CDN)
            }
        };
    </script>

    <?= $scripts ?? '' ?>
</body>

</html>