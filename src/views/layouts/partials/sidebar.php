<?php
use App\Services\MenuService;
use System\Database;

$userId = $_SESSION['user_id'] ?? null;
$userData = $_SESSION['user_data'] ?? [];
$branchId = $userData['branch_id'] ?? null;
$userType = $userData['type'] ?? 'user';

// Initialize Menu Service to get filtered menu
$db = Database::getInstance();
$menuService = new MenuService($db, (int) $userId, (int) $branchId, $userType);
$menus = $menuService->getUserMenu();

/**
 * Recursive function to render menu items
 */
function renderSidebarItem(array $item, int $level = 1, bool $isVertical = true)
{
    $hasChildren = !empty($item['children']);
    $itemClass = $hasChildren ? 'slide has-sub' : 'slide';
    $baseUrl = baseUrl();
    $route = $item['route_path'] ? (strpos($item['route_path'], 'http') === 0 ? $item['route_path'] : baseUrl($item['route_path'])) : 'javascript:void(0);';

    // Determine active state manually (JS also handles this but good for server-side)
    $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
    $currentPath = rtrim(parse_url($currentUri, PHP_URL_PATH), '/');

    // More precise active state detection
    $isActive = '';
    if ($item['route_path']) {
        $itemPath = rtrim($item['route_path'], '/');

        // For parent menus with children, check if any child is active
        if ($hasChildren) {
            foreach ($item['children'] as $child) {
                $childPath = rtrim($child['route_path'] ?? '', '/');
                if ($childPath && $currentPath === $childPath) {
                    $isActive = 'active open';
                    break;
                }
            }
        } else {
            // For leaf items, check if current path starts with item path (for sub-pages like /create)
            if ($itemPath !== '/' && strpos($currentPath, $itemPath) === 0) {
                // Ensure exact match or followed by slash to avoid partial matches like /test vs /testing
                if (strlen($currentPath) === strlen($itemPath) || $currentPath[strlen($itemPath)] === '/') {
                    $isActive = 'active';
                }
            } elseif ($currentPath === $itemPath) {
                $isActive = 'active';
            }
        }
    }

    // Level-specific classes
    $ulClass = "slide-menu child" . $level;
    $angleClass = ($level == 1) ? "ri-arrow-right-s-line side-menu__angle" : "ri-arrow-right-s-line side-menu__angle";
    ?>
    <li class="<?= $itemClass ?>">
        <a href="<?= $route ?>" class="side-menu__item <?= $isActive ?>" <?= $hasChildren ? 'data-bs-toggle="sidebar"' : '' ?>>
            <?php if ($item['menu_icon']): ?>
                <i class="<?= $item['menu_icon'] ?> side-menu__icon"></i>
            <?php endif; ?>
            <span class="side-menu__label"><?= e($item['menu_label']) ?></span>
            <?php if ($item['badge_source']): ?>
                <span class="badge rounded-pill bg-danger-transparent ms-2 menu-badge"
                    data-badge-source="<?= $item['badge_source'] ?>" style="display:none;">0</span>
            <?php endif; ?>
            <?php if ($hasChildren): ?>
                <i class="<?= $angleClass ?>"></i>
            <?php endif; ?>
        </a>
        <?php if ($hasChildren): ?>
            <ul class="<?= $ulClass ?>">
                <?php foreach ($item['children'] as $child): ?>
                    <?= renderSidebarItem($child, $level + 1, $isVertical) ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </li>
    <?php
}
?>

<aside class="app-sidebar sticky" id="sidebar" data-user-id="<?= $userId ?>">
    <!-- Sidebar Header -->
    <div class="main-sidebar-header">
        <a href="<?= baseUrl('/dashboard') ?>" class="header-logo d-flex align-items-center gap-2">
            <img src="<?= asset('images/brand-logos/toggle-logo.png') ?>" alt="logo" class="desktop-logo">
            <img src="<?= asset('images/brand-logos/toggle-dark.png') ?>" alt="logo" class="toggle-dark">
            <img src="<?= asset('images/brand-logos/toggle-dark.png') ?>" alt="logo" class="desktop-dark">
            <img src="<?= asset('images/brand-logos/toggle-logo.png') ?>" alt="logo" class="toggle-logo">
            <span class="hospital-name-sidebar"><?= e($_ENV['APP_NAME'] ?? 'Hospital') ?></span>
        </a>
    </div>

    <!-- Sidebar Menu -->
    <div class="main-sidebar" id="sidebar-scroll">
        <?php
        $navLayout = $_SESSION['theme']['nav_layout'] ?? 'vertical';
        $isVertical = ($navLayout === 'vertical');
        ?>
        <nav class="main-menu-container nav nav-pills sub-open <?= $isVertical ? 'flex-column' : '' ?>">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>

            <ul class="main-menu">
                <?php foreach ($menus as $menu): ?>
                    <?= renderSidebarItem($menu, 1, $isVertical) ?>
                <?php endforeach; ?>

                <?php if ($isVertical): ?>
                    <!-- Divider -->
                    <li class="slide__category border-top mt-3 pt-2"><span class="category-name">System</span></li>
                <?php endif; ?>


                <li class="slide">
                    <a href="<?= baseUrl('/logout') ?>" class="side-menu__item text-danger" data-no-spa="true">
                        <i class="ri-logout-box-line side-menu__icon"></i>
                        <span class="side-menu__label">Logout</span>
                    </a>
                </li>
            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>
        </nav>
    </div>
</aside>