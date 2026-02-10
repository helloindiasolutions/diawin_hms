<header class="app-header sticky" id="header">
    <div class="main-header-container container-fluid">
        <!-- Header Left -->
        <div class="header-content-left">
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="<?= baseUrl('/dashboard') ?>" class="header-logo d-flex align-items-center gap-2">
                        <img src="<?= asset('images/brand-logos/toggle-logo.png') ?>" alt="logo" class="desktop-logo">
                        <img src="<?= asset('images/brand-logos/toggle-logo.png') ?>" alt="logo" class="toggle-logo">
                        <img src="<?= asset('images/brand-logos/toggle-dark.png') ?>" alt="logo" class="desktop-dark">
                        <img src="<?= asset('images/brand-logos/toggle-dark.png') ?>" alt="logo" class="toggle-dark">
                        <span class="hospital-name"><?= e($_ENV['APP_NAME'] ?? 'Hospital') ?></span>
                    </a>
                </div>
            </div>

            <div class="header-element mx-lg-0 mx-2">
                <a aria-label="Hide Sidebar"
                    class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                    data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
            </div>


        </div>

        <!-- Header Right -->
        <ul class="header-content-right">
            <!-- Branch Selector/Display -->
            <li class="header-element me-2 d-none d-lg-block">
                <!-- Branch Selector/Display -->
                <?php
                $userRoles = user('roles') ?? [];
                $isSuperAdmin = false;
                // ONLY Super Admin can switch branches (code: SUPER_ADMIN, name: super_admin)
                $superAdminRoles = ['SUPER_ADMIN', 'super_admin', 'Super Admin', 'System Administrator'];
                foreach ($superAdminRoles as $role) {
                    if (in_array($role, $userRoles)) {
                        $isSuperAdmin = true;
                        break;
                    }
                }

                $db = \System\Database::getInstance();
                $currentBranchId = $_SESSION['user_data']['branch_id'] ?? user('branch_id') ?? null;

                // Handle "All Branches" case for Super Admin
                $isViewingAllBranches = $isSuperAdmin && (empty($currentBranchId) || $currentBranchId === 0 || $currentBranchId === '0');

                if ($isViewingAllBranches) {
                    $currentBranchName = 'All Branches';
                } else {
                    $branchData = $db->fetch("SELECT name FROM branches WHERE branch_id = ?", [(int) $currentBranchId]);
                    $currentBranchName = $branchData['name'] ?? 'Main Branch';
                }

                if ($isSuperAdmin):
                    $allBranches = $db->fetchAll("SELECT branch_id, name FROM branches WHERE is_active = 1 ORDER BY is_main DESC, name ASC");
                    ?>
                    <div class="dropdown branch-selector-wrapper">
                        <button class="branch-pill-button dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <div class="branch-icon-circle <?= $isViewingAllBranches ? 'all-branches' : '' ?>">
                                <i class="<?= $isViewingAllBranches ? 'ri-building-4-line' : 'ri-building-line' ?>"></i>
                            </div>
                            <div class="branch-details">
                                <span
                                    class="branch-label"><?= $isViewingAllBranches ? 'VIEWING' : 'CURRENT BRANCH' ?></span>
                                <span class="branch-name-text"><?= e($currentBranchName) ?></span>
                            </div>
                            <i class="ri-arrow-down-s-line ms-2 opacity-50"></i>
                        </button>
                        <ul class="dropdown-menu shadow-xl border-0 p-2"
                            style="border-radius: 12px; min-width: 220px; margin-top: 10px !important;">
                            <li class="dropdown-header text-uppercase fs-10 fw-bold text-primary mb-1">Switch Workspace</li>
                            <!-- All Branches Option (Super Admin only) -->
                            <li>
                                <a class="dropdown-item rounded-2 py-2 px-3 d-flex align-items-center justify-content-between <?= $isViewingAllBranches ? 'active bg-primary text-white' : '' ?>"
                                    href="javascript:void(0);" onclick="switchHeaderBranch(0)">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ri-building-4-line fs-14"></i>
                                        <span class="fw-medium">All Branches</span>
                                    </div>
                                    <?php if ($isViewingAllBranches): ?>
                                        <i class="ri-checkbox-circle-fill"></i>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <?php foreach ($allBranches as $branch): ?>
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 px-3 d-flex align-items-center justify-content-between <?= !$isViewingAllBranches && $branch['branch_id'] == $currentBranchId ? 'active bg-primary text-white' : '' ?>"
                                        href="javascript:void(0);" onclick="switchHeaderBranch(<?= $branch['branch_id'] ?>)">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ri-hospital-line fs-14"></i>
                                            <span class="fw-medium"><?= e($branch['name']) ?></span>
                                        </div>
                                        <?php if (!$isViewingAllBranches && $branch['branch_id'] == $currentBranchId): ?>
                                            <i class="ri-checkbox-circle-fill"></i>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="branch-pill-readonly">
                        <div class="branch-icon-circle secondary">
                            <i class="ri-map-pin-user-line"></i>
                        </div>
                        <div class="branch-details">
                            <span class="branch-label">MY BRANCH</span>
                            <span class="branch-name-text"><?= e($currentBranchName) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </li>
            <!-- Global Token Trigger - Show for Pharmacist, Receptionist, Front Office and non-Doctor roles -->
            <?php
            // Role codes from database: DOCTOR, NURSE, PHARMACIST, RECEPTIONIST
            $isDoctor = in_array('DOCTOR', $userRoles) || in_array('Doctor', $userRoles) || in_array('doctor', $userRoles);
            $isPharmacist = in_array('PHARMACIST', $userRoles) || in_array('Pharmacist', $userRoles) || in_array('pharmacist', $userRoles);
            $isNurse = in_array('NURSE', $userRoles) || in_array('Nurse', $userRoles) || in_array('nurse', $userRoles);
            $isReceptionist = in_array('RECEPTIONIST', $userRoles) || in_array('Receptionist', $userRoles) || in_array('receptionist', $userRoles) || in_array('Front Office', $userRoles);

            // Show TOKEN button for: Pharmacist, Receptionist, Front Office (NOT Doctor)
            $showTokenButton = $isPharmacist || $isReceptionist || (!$isDoctor && !$isNurse);
            ?>
            <?php if ($showTokenButton): ?>
                <li class="header-element me-2 d-none d-sm-block">
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 fw-bold"
                        id="headerTokenBtn" onclick="window.openQuickApt()"
                        style="background: #007a18; border: none; box-shadow: 0 4px 10px rgba(0, 122, 24, 0.2); padding: 7px 14px; border-radius: 6px;">
                        <i class="ri-ticket-line fs-14"></i>
                        <span>TOKEN</span>
                        <span class="badge bg-white text-success ms-1" style="font-size: 9px; padding: 2px 5px;">F1</span>
                    </button>
                </li>
            <?php endif; ?>

            <!-- Queue Button - Show for Receptionist and Nurse roles -->
            <?php if ($isReceptionist || $isNurse): ?>
                <li class="header-element me-2 d-none d-sm-block">
                    <a href="<?= baseUrl('/appointments') ?>" class="btn btn-sm d-flex align-items-center gap-1 fw-bold"
                        style="background: #f59e0b; border: none; color: #fff; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2); padding: 7px 14px; border-radius: 6px;">
                        <i class="ri-calendar-check-line fs-14"></i>
                        <span>Appts</span>
                    </a>
                </li>
                <li class="header-element me-2 d-none d-sm-block">
                    <a href="<?= baseUrl('/queue') ?>" class="btn btn-sm d-flex align-items-center gap-1 fw-bold"
                        style="background: #6366f1; border: none; color: #fff; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.2); padding: 7px 14px; border-radius: 6px;">
                        <i class="ri-list-ordered-2 fs-14"></i>
                        <span>Queue</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Global Search Trigger -->
            <li class="header-element me-2">
                <button type="button" class="global-search-trigger" onclick="globalSearch.open()">
                    <i class="ri-search-line"></i>
                    <input type="text" placeholder="Search anything" readonly
                        style="border: none; background: transparent; outline: none; cursor: pointer; width: 180px; color: inherit;">
                    <kbd class="d-none d-lg-inline">⌘K</kbd>
                </button>
            </li>

            <!-- Workspace / Quick Go Button - Role-based display -->
            <?php if ($isDoctor): ?>
                <!-- Doctor: Show Workspace button -->
                <li class="header-element">
                    <a href="<?= baseUrl('/opd/workspace') ?>"
                        class="btn btn-sm d-flex align-items-center justify-content-center"
                        style="background: #007a18; border: none; color: #fff; font-weight: 600; border-radius: 8px; padding: 8px 16px; min-height: 36px; line-height: 1;">
                        <i class="ri-ancient-gate-line me-2 fs-16"></i> Workspace
                    </a>
                </li>
            <?php elseif ($isPharmacist): ?>
                <!-- Pharmacist: Show Dispensing-focused Quick Actions -->
                <li class="header-element dropdown quick-access-dropdown">
                    <a href="javascript:void(0);" class="btn btn-sm d-flex align-items-center justify-content-center"
                        id="quickGoButton" data-bs-toggle="dropdown" aria-expanded="false"
                        style="background: #007a18; border: none; color: #fff; font-weight: 600; border-radius: 8px; padding: 8px 16px; min-height: 36px; line-height: 1;">
                        <i class="ri-capsule-line me-2 fs-16"></i> Quick Go
                    </a>
                    <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                        <div class="p-4">
                            <div class="row g-3">
                                <div class="col-4">
                                    <a href="<?= baseUrl('/pharmacy/dispensing') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-medicine-bottle-line"></i></div>
                                        <span class="q-label">Dispense</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/pharmacy') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-capsule-line"></i></div>
                                        <span class="q-label">Pharmacy</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/inventory/stock') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-stack-line"></i></div>
                                        <span class="q-label">Stock</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/pharmacy/expiry-alerts') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-error-warning-line"></i></div>
                                        <span class="q-label">Expiry</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/purchase-orders') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-shopping-bag-line"></i></div>
                                        <span class="q-label">PO</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/grn') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-inbox-line"></i></div>
                                        <span class="q-label">GRN</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            <?php elseif ($isNurse): ?>
                <!-- Nurse: Show Patient Care focused Quick Actions -->
                <li class="header-element dropdown quick-access-dropdown">
                    <a href="javascript:void(0);" class="btn btn-sm d-flex align-items-center justify-content-center"
                        id="quickGoButton" data-bs-toggle="dropdown" aria-expanded="false"
                        style="background: #007a18; border: none; color: #fff; font-weight: 600; border-radius: 8px; padding: 8px 16px; min-height: 36px; line-height: 1;">
                        <i class="ri-nurse-line me-2 fs-16"></i> Quick Go
                    </a>
                    <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                        <div class="p-4">
                            <div class="row g-3">
                                <div class="col-4">
                                    <a href="<?= baseUrl('/visits/vitals') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-heart-pulse-line"></i></div>
                                        <span class="q-label">Vitals</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/visits') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-file-list-3-line"></i></div>
                                        <span class="q-label">OPD List</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/ip/nursing-notes') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-nurse-line"></i></div>
                                        <span class="q-label">Notes</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/admissions') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-hospital-line"></i></div>
                                        <span class="q-label">IPD</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/ip/beds') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-hotel-bed-line"></i></div>
                                        <span class="q-label">Beds</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="<?= baseUrl('/ip/rounds') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-walk-line"></i></div>
                                        <span class="q-label">Rounds</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            <?php elseif ($isReceptionist): ?>
                <!-- Receptionist: Show Front Office focused Quick Actions only -->
                <li class="header-element dropdown quick-access-dropdown">
                    <a href="javascript:void(0);" class="btn btn-sm d-flex align-items-center justify-content-center"
                        id="quickGoButton" data-bs-toggle="dropdown" aria-expanded="false"
                        style="background: #007a18; border: none; color: #fff; font-weight: 600; border-radius: 8px; padding: 8px 16px; min-height: 36px; line-height: 1;">
                        <i class="ri-focus-3-line me-2 fs-16"></i> Quick Go
                    </a>
                    <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                        <div class="p-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <a href="<?= baseUrl('/registrations/create') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-user-add-line"></i></div>
                                        <span class="q-label">Register</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="<?= baseUrl('/appointments') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-calendar-check-line"></i></div>
                                        <span class="q-label">Appts</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="<?= baseUrl('/invoices/create?type=general') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-file-list-3-line"></i></div>
                                        <span class="q-label">OP Bill</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="<?= baseUrl('/queue') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-list-ordered-2"></i></div>
                                        <span class="q-label">Queue</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            <?php else: ?>
                <!-- Admin/Super Admin: Show full Quick Go menu -->
                <li class="header-element dropdown quick-access-dropdown">
                    <a href="javascript:void(0);" class="btn btn-sm d-flex align-items-center justify-content-center"
                        id="quickGoButton" data-bs-toggle="dropdown" aria-expanded="false"
                        style="background: #007a18; border: none; color: #fff; font-weight: 600; border-radius: 8px; padding: 8px 16px; min-height: 36px; line-height: 1;">
                        <i class="ri-focus-3-line me-2 fs-16"></i> Quick Go
                    </a>
                    <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                        <div class="p-4">
                            <div class="row g-3">
                                <div class="col-3">
                                    <a href="<?= baseUrl('/registrations/create') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-user-add-line"></i></div>
                                        <span class="q-label">Register</span>
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="<?= baseUrl('/appointments') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-calendar-check-line"></i></div>
                                        <span class="q-label">Appts</span>
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="<?= baseUrl('/invoices/create?type=general') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-file-list-3-line"></i></div>
                                        <span class="q-label">OP Bill</span>
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="<?= baseUrl('/invoices/create?type=pharmacy') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-capsule-line"></i></div>
                                        <span class="q-label">Pharmacy</span>
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="<?= baseUrl('/ip/admissions') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-hospital-line"></i></div>
                                        <span class="q-label">Admissions</span>
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="<?= baseUrl('/visits/vitals') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-heart-pulse-line"></i></div>
                                        <span class="q-label">Vitals</span>
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="<?= baseUrl('/products/create') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-shopping-cart-2-line"></i></div>
                                        <span class="q-label">Product</span>
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="<?= baseUrl('/dcr') ?>" class="q-go-card">
                                        <div class="q-icon-box"><i class="ri-bar-chart-grouped-line"></i></div>
                                        <span class="q-label">Reports</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endif; ?>
            <!-- IP Beds Quick Access -->
            <li class="header-element" style="margin-left: 8px;">
                <a href="javascript:void(0);" onclick="Melina.navigate('/ip/beds')"
                    class="btn btn-success btn-wave d-flex align-items-center gap-2 px-3 py-2"
                    style="border-radius: 8px; font-size: 13px; font-weight: 600;">
                    <img src="<?= asset('images/bed.png') ?>" alt="Beds"
                        style="width: 18px; height: 18px; filter: brightness(0) invert(1);">
                    <span>BEDS</span>
                </a>
            </li>

            <!-- Fullscreen -->
            <li class="header-element header-fullscreen d-xl-flex d-none">
                <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                    <i class="ri-fullscreen-line full-screen-open header-link-icon"></i>
                    <i class="ri-fullscreen-exit-line full-screen-close header-link-icon d-none"></i>
                </a>
            </li>

            <!-- Switcher Icon -->
            <li class="header-element">
                <a href="javascript:void(0);" class="header-link switcher-icon" data-bs-toggle="offcanvas"
                    data-bs-target="#switcher-canvas">
                    <i class="ri-settings-4-line header-link-icon"></i>
                </a>
            </li>

            <!-- User Profile Dropdown -->
            <li class="header-element dropdown">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="me-sm-2 me-0">
                            <img src="<?= asset('images/faces/9.jpg') ?>" alt="avatar"
                                class="avatar avatar-sm avatar-rounded">
                        </div>
                        <div class="d-sm-block d-none">
                            <p class="fw-medium mb-0 lh-1"><?= e(user('full_name') ?? 'User') ?></p>
                            <span
                                class="op-7 fw-normal d-block fs-11"><?= e((user('roles') ?? [])[0] ?? 'Member') ?></span>
                        </div>
                    </div>
                </a>
                <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                    aria-labelledby="mainHeaderProfile">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="<?= baseUrl('/profile') ?>">
                            <i class="ri-user-line fs-18 me-2 op-7"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="<?= baseUrl('/profile/settings') ?>">
                            <i class="ri-settings-3-line fs-18 me-2 op-7"></i>Settings
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center text-danger" href="<?= baseUrl('/logout') ?>"
                            data-no-spa="true">
                            <i class="ri-logout-box-line fs-18 me-2 op-7"></i>Log Out
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

</header>
<script>
    // Global Branch ID for API calls - Default to 0 (All Branches) for Super Admin if not specifically set
    window.currentBranchId = <?= (int) ($_SESSION['user_data']['branch_id'] ?? ($isSuperAdmin ? 0 : (user('branch_id') ?? 1))) ?>;

    /**
     * Switch global branch context (Super Admin only)
     */
    async function switchHeaderBranch(branchId) {
        if (!branchId || branchId == window.currentBranchId) return;

        try {
            const response = await fetch('<?= baseUrl('/api/v1/menu/branch-switch') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ branch_id: branchId })
            });

            const result = await response.json();
            if (result.success) {
                // Show success message or just reload
                location.reload();
            } else {
                alert('Switch failed: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Branch switch error:', error);
            alert('An error occurred while switching branches.');
        }
    }

    // Global keyboard shortcut for TOKEN button (F1)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F1') {
            e.preventDefault();
            const tokenBtn = document.getElementById('headerTokenBtn');
            if (tokenBtn && !tokenBtn.disabled) {
                tokenBtn.click();
            }
        }
    });
</script>
<script>
    // Show horizontal nav when layout is horizontal
    document.addEventListener('DOMContentLoaded', function () {
        // No horizontal nav logic needed
    });
</script>
<style>
    /* Header Right Alignment */
    .header-content-right {
        display: flex !important;
        align-items: center !important;
        height: 100%;
    }

    .header-content-right .header-element {
        display: flex;
        align-items: center;
    }

    /* Fix switcher icon alignment */
    .switcher-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Stop the weird spinning/moving animation */
    .switcher-icon i {
        animation: none !important;
        transform: none !important;
    }

    /* Refined Commercial Header UI */
    .quick-access-dropdown .dropdown-menu {
        min-width: 480px;
        width: 480px;
        background: #fff;
        border: 1px solid #eef1f6;
        border-radius: 12px;
        box-shadow: 0 10px 45px rgba(0, 0, 0, 0.12);
        padding: 12px;
        overflow: hidden;
        margin-top: 10px !important;
        top: 100% !important;
        right: 0;
        border-top: 3px solid #007a18;
        transform-origin: top right;
    }

    @media (max-width: 576px) {
        .quick-access-dropdown .dropdown-menu {
            min-width: 300px;
            width: 90vw;
            max-width: 350px;
            right: 0 !important;
            /* Align to right edge of parent to prevent overflow */
            left: auto !important;
        }

        .quick-access-dropdown .dropdown-menu .col-3 {
            width: 50%;
            margin-bottom: 10px;
        }
    }

    .q-go-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 18px 5px;
        border: 1px solid #f1f4f8;
        border-radius: 8px;
        background: #fff;
        text-decoration: none !important;
        transition: all 0.2s ease;
        height: 100%;
    }

    .q-go-card:hover {
        border-color: #007a18;
        background: #fffaff;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
    }

    .q-icon-box {
        width: 44px;
        height: 44px;
        background: #f3f6f9;
        color: #2b334a;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }

    .q-go-card:hover .q-icon-box {
        background: #007a18;
        color: #fff;
    }

    .q-label {
        font-size: 13px;
        font-weight: 600;
        color: #444a6d;
        text-align: center;
        display: block;
    }

    #quickGoButton {
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(255, 159, 28, 0.25);
    }

    #quickGoButton:active {
        transform: scale(0.96);
    }

    /* Hospital Name Styling - Premium Font */
    .hospital-name {
        font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.3px;
        color: var(--default-text-color);
        text-decoration: none;
        white-space: nowrap;
        transition: color 0.2s ease;
        line-height: 1.2;
    }

    [data-theme-mode="dark"] .hospital-name {
        color: #fff;
    }

    .header-logo:hover .hospital-name {
        color: var(--primary-color);
    }

    /* Responsive - Hide hospital name on smaller screens */
    @media (max-width: 768px) {
        .hospital-name {
            display: none;
        }
    }

    @media (min-width: 769px) and (max-width: 991px) {
        .hospital-name {
            font-size: 16px;
        }
    }

    @media (min-width: 992px) and (max-width: 1199px) {
        .hospital-name {
            font-size: 17px;
        }
    }

    /* IP Beds Quick Access Button */
    .bed-quick-btn {
        padding: 0 !important;
    }

    .bed-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.15) 100%);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .bed-icon-wrapper:hover {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.25) 100%);
        border-color: rgba(16, 185, 129, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .bed-icon-img {
        width: 20px;
        height: 20px;
        opacity: 0.9;
        transition: all 0.2s ease;
    }

    .bed-icon-wrapper:hover .bed-icon-img {
        opacity: 1;
        transform: scale(1.05);
    }



    /* Premium Branch Selector Styling */
    .branch-pill-button {
        display: flex;
        align-items: center;
        background: rgba(var(--primary-rgb), 0.05);
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        border-radius: 10px;
        padding: 6px 14px 6px 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .branch-pill-button:hover {
        background: rgba(var(--primary-rgb), 0.1);
        border-color: rgba(var(--primary-rgb), 0.3);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .branch-pill-button:active {
        transform: translateY(0);
    }

    .branch-pill-button::after {
        display: none !important;
        /* Hide default bootstrap arrow */
    }

    .branch-icon-circle {
        width: 32px;
        height: 32px;
        background: var(--primary-color);
        color: #fff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        margin-right: 10px;
        box-shadow: 0 2px 6px rgba(var(--primary-rgb), 0.3);
    }

    .branch-icon-circle.secondary {
        background: #f3f6f9;
        color: var(--primary-color);
        box-shadow: none;
        border: 1px solid #eef1f6;
    }

    .branch-icon-circle.all-branches {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
    }

    .branch-details {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        line-height: 1.2;
    }

    .branch-label {
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--primary-color);
        opacity: 0.7;
    }

    .branch-name-text {
        font-size: 13px;
        font-weight: 700;
        color: var(--default-text-color);
        white-space: nowrap;
    }

    .branch-pill-readonly {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 14px 6px 8px;
        cursor: default;
    }

    .branch-selector-wrapper {
        position: relative;
    }

    .branch-selector-wrapper .dropdown-menu {
        animation: smoothDrop 0.3s ease-out;
        inset: 100% 0 auto auto !important;
        /* Force alignment with button */
        margin-top: 8px !important;
        transform: none !important;
        /* Prevent popper.js from over-shifting */
        min-width: 240px;
    }

    .branch-selector-wrapper .dropdown-item {
        transition: all 0.2s ease;
    }

    .branch-selector-wrapper .dropdown-item:not(.active):hover {
        background: rgba(var(--primary-rgb), 0.05);
        transform: translateX(3px);
    }

    [data-theme-mode="dark"] .branch-pill-button {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme-mode="dark"] .branch-name-text {
        color: #fff;
    }

    [data-theme-mode="dark"] .branch-pill-readonly {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.05);
    }

    @keyframes smoothDrop {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
</header>