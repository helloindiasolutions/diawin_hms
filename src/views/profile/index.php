<?php
$pageTitle = 'Profile';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="avatar avatar-xxl mb-3">
                                <img src="<?= asset('images/faces/9.jpg') ?>" alt="avatar" class="rounded-circle">
                            </div>
                            <h5><?= e(user('full_name') ?? 'User') ?></h5>
                            <p class="text-muted">@<?= e(user('username') ?? '') ?></p>
                        </div>
                        <div class="col-md-8">
                            <table class="table">
                                <tr>
                                    <th width="150">Full Name</th>
                                    <td><?= e(user('full_name') ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Username</th>
                                    <td><?= e(user('username') ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?= e(user('email') ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Mobile</th>
                                    <td><?= e(user('mobile') ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Roles</th>
                                    <td>
                                        <?php foreach ((user('roles') ?? []) as $role): ?>
                                            <span class="badge bg-primary"><?= e($role) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (empty(user('roles'))): ?>-<?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                            <a href="<?= baseUrl('/profile/settings') ?>" class="btn btn-primary">
                                <i class="ri-settings-3-line me-1"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
