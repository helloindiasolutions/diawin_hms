<?php
$pageTitle = 'Admin Access';
ob_start();
?>

<div class="authentication-basic-background">
    <img src="<?= asset('images/media/backgrounds/9.png') ?>" alt="">
</div>

<div class="container h-100">
    <div class="row justify-content-center align-items-center h-100" style="min-height: 100vh;">
        <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">
            <div class="card custom-card border-0 my-4 shadow-lg" style="border-radius: 16px;">
                <div class="card-body p-5">
                    <div class="mb-4 text-center">
                        <a href="<?= baseUrl('/') ?>">
                            <img src="<?= asset('images/brand-logos/toggle-logo.png') ?>" alt="logo"
                                class="desktop-dark" style="height: 40px;">
                        </a>
                    </div>
                    <div class="text-center mb-4">
                        <h4 class="mb-1 fw-bold">Management Login</h4>
                        <p class="text-muted small">Enter administrative credentials to proceed</p>
                    </div>

                    <?php if ($success = flash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show border-0 bg-success-transparent"
                            role="alert">
                            <?= e($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error = flash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger-transparent"
                            role="alert">
                            <?= e($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= baseUrl('/login') ?>" method="POST" id="loginForm">
                        <?= csrfField() ?>
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <label for="username" class="form-label text-default fw-medium">Admin ID / Email</label>
                                <input type="text" class="form-control form-control-lg bg-light border-0" id="username"
                                    name="username" placeholder="Username" value="<?= e(old('username')) ?>" required
                                    autofocus>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <label for="password" class="form-label text-default fw-medium d-block">Secure
                                    Password</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control form-control-lg bg-light border-0"
                                        id="password" name="password" placeholder="••••••••" required>
                                    <a href="javascript:void(0);" class="show-password-button text-muted"
                                        onclick="createpassword('password',this)">
                                        <i class="ri-eye-off-line align-middle"></i>
                                    </a>
                                </div>
                                <div class="mt-2 d-flex justify-content-between">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                        <label class="form-check-label fs-12 text-muted" for="remember">Keep me signed
                                            in</label>
                                    </div>
                                    <a href="javascript:void(0);" class="text-primary fs-12">Forgot Key?</a>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">Authorize & Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-5 pt-3 border-top">
                        <p class="text-muted small mb-1">Are you a Doctor or Patient?</p>
                        <a href="<?= baseUrl('/portal') ?>"
                            class="fw-bold text-success text-decoration-underline">Access Medical Portal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>