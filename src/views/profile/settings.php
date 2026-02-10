<?php
$pageTitle = 'Profile Settings';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card custom-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Update Profile</h5>
                </div>
                <div class="card-body">
                    <form action="<?= baseUrl('/profile/update') ?>" method="POST" class="needs-validation" novalidate>
                        <?= csrfField() ?>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                value="<?= e(user('full_name') ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= e(user('email') ?? '') ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="mb-3">
                            <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="mobile" name="mobile"
                                value="<?= e(user('mobile') ?? '') ?>" required maxlength="10" pattern="[0-9]{10}"
                                placeholder="Enter 10-digit number">
                            <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card custom-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="<?= baseUrl('/profile/password') ?>" method="POST" class="needs-validation"
                        novalidate>
                        <?= csrfField() ?>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                        </div>
                        <button type="submit" class="btn btn-warning">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>