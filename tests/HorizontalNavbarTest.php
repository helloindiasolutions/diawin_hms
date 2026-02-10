<?php
/**
 * Unit tests for HorizontalNavbar component
 * 
 * Tests the rendering and functionality of the horizontal navigation bar.
 * 
 * Requirements: 1.1, 2.1, 3.1, 5.1, 8.1, 8.2, 10.3
 */

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Components\HorizontalNavbar;
use App\Services\MenuService;
use System\Database;

class HorizontalNavbarTest extends TestCase
{
    private $mockMenuService;
    private $mockDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock database
        $this->mockDatabase = $this->createMock(Database::class);
        
        // Create mock MenuService
        $this->mockMenuService = $this->createMock(MenuService::class);
    }
    
    /**
     * Test that navbar renders with basic menu structure
     * 
     * Validates: Requirements 1.1, 2.1
     */
    public function testRenderBasicNavbar(): void
    {
        $menuItems = [
            [
                'menu_key' => 'dashboard',
                'menu_label' => 'Dashboard',
                'menu_icon' => 'ri-dashboard-line',
                'route_path' => '/dashboard',
                'menu_level' => 1,
                'required_permission' => null,
                'badge_source' => null,
                'children' => []
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/dashboard');
        $html = $navbar->render();
        
        // Assert navbar structure
        $this->assertStringContainsString('<nav class="navbar"', $html);
        $this->assertStringContainsString('role="navigation"', $html);
        $this->assertStringContainsString('aria-label="Main navigation"', $html);
        
        // Assert menu item is rendered
        $this->assertStringContainsString('Dashboard', $html);
        $this->assertStringContainsString('ri-dashboard-line', $html);
        $this->assertStringContainsString('/dashboard', $html);
    }
    
    /**
     * Test that navbar renders empty when no menu items
     * 
     * Validates: Requirements 1.1
     */
    public function testRenderEmptyNavbar(): void
    {
        $this->mockMenuService->method('getUserMenu')
            ->willReturn([]);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/');
        $html = $navbar->render();
        
        $this->assertEmpty($html);
    }
    
    /**
     * Test that menu items with children show dropdown indicator
     * 
     * Validates: Requirements 3.1
     */
    public function testMenuItemWithChildrenHasDropdownIndicator(): void
    {
        $menuItems = [
            [
                'menu_key' => 'front_office',
                'menu_label' => 'Front Office',
                'menu_icon' => 'ri-service-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => 'view_front_office',
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'registration',
                        'menu_label' => 'Registration',
                        'menu_icon' => 'ri-user-add-line',
                        'route_path' => '/front-office/registration',
                        'menu_level' => 2,
                        'required_permission' => 'view_front_office',
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/');
        $html = $navbar->render();
        
        // Assert dropdown indicator is present
        $this->assertStringContainsString('ri-arrow-down-s-line', $html);
        $this->assertStringContainsString('navbar__dropdown-indicator', $html);
        
        // Assert ARIA attributes for dropdown
        $this->assertStringContainsString('aria-haspopup="true"', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
    }
    
    /**
     * Test that dropdown panel renders with child items
     * 
     * Validates: Requirements 3.1, 3.4
     */
    public function testDropdownPanelRendersChildren(): void
    {
        $menuItems = [
            [
                'menu_key' => 'opd',
                'menu_label' => 'OPD',
                'menu_icon' => 'ri-stethoscope-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => 'view_opd',
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'opd_today',
                        'menu_label' => 'Today\'s OPD',
                        'menu_icon' => 'ri-file-list-3-line',
                        'route_path' => '/opd/today',
                        'menu_level' => 2,
                        'required_permission' => 'view_opd',
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'my_patients',
                        'menu_label' => 'My Patients',
                        'menu_icon' => 'ri-user-heart-line',
                        'route_path' => '/opd/my-patients',
                        'menu_level' => 2,
                        'required_permission' => 'view_opd',
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/');
        $html = $navbar->render();
        
        // Assert dropdown structure
        $this->assertStringContainsString('navbar__dropdown', $html);
        $this->assertStringContainsString('role="menu"', $html);
        
        // Assert child items are rendered
        $this->assertStringContainsString('Today\'s OPD', $html);
        $this->assertStringContainsString('My Patients', $html);
        $this->assertStringContainsString('/opd/today', $html);
        $this->assertStringContainsString('/opd/my-patients', $html);
    }
    
    /**
     * Test that third-level items render as nested items
     * 
     * Validates: Requirements 3.5
     */
    public function testThirdLevelItemsRenderAsNested(): void
    {
        $menuItems = [
            [
                'menu_key' => 'opd',
                'menu_label' => 'OPD',
                'menu_icon' => 'ri-stethoscope-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => 'view_opd',
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'siddha_notes',
                        'menu_label' => 'Siddha Notes',
                        'menu_icon' => 'ri-ancient-pavilion-line',
                        'route_path' => null,
                        'menu_level' => 2,
                        'required_permission' => 'view_opd',
                        'badge_source' => null,
                        'children' => [
                            [
                                'menu_key' => 'pulse_diagnosis',
                                'menu_label' => 'Pulse Diagnosis',
                                'menu_icon' => 'ri-pulse-line',
                                'route_path' => '/opd/siddha/pulse-diagnosis',
                                'menu_level' => 3,
                                'required_permission' => 'view_opd',
                                'badge_source' => null,
                                'children' => []
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/');
        $html = $navbar->render();
        
        // Assert nested structure
        $this->assertStringContainsString('navbar__dropdown-nested', $html);
        $this->assertStringContainsString('navbar__dropdown-parent', $html);
        
        // Assert third-level item is rendered
        $this->assertStringContainsString('Pulse Diagnosis', $html);
        $this->assertStringContainsString('/opd/siddha/pulse-diagnosis', $html);
    }
    
    /**
     * Test that active state is applied to current page
     * 
     * Validates: Requirements 5.1
     */
    public function testActiveStateAppliedToCurrentPage(): void
    {
        $menuItems = [
            [
                'menu_key' => 'dashboard',
                'menu_label' => 'Dashboard',
                'menu_icon' => 'ri-dashboard-line',
                'route_path' => '/dashboard',
                'menu_level' => 1,
                'required_permission' => null,
                'badge_source' => null,
                'children' => []
            ],
            [
                'menu_key' => 'patients',
                'menu_label' => 'Patients',
                'menu_icon' => 'ri-user-line',
                'route_path' => '/patients',
                'menu_level' => 1,
                'required_permission' => null,
                'badge_source' => null,
                'children' => []
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/dashboard');
        $html = $navbar->render();
        
        // Assert active class is present
        $this->assertStringContainsString('navbar__item--active', $html);
    }
    
    /**
     * Test that parent is active when on child page
     * 
     * Validates: Requirements 5.3
     */
    public function testParentActiveWhenOnChildPage(): void
    {
        $menuItems = [
            [
                'menu_key' => 'opd',
                'menu_label' => 'OPD',
                'menu_icon' => 'ri-stethoscope-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => 'view_opd',
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'opd_today',
                        'menu_label' => 'Today\'s OPD',
                        'menu_icon' => 'ri-file-list-3-line',
                        'route_path' => '/opd/today',
                        'menu_level' => 2,
                        'required_permission' => 'view_opd',
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        // Current page is a child page
        $navbar = new HorizontalNavbar($this->mockMenuService, '/opd/today');
        $html = $navbar->render();
        
        // Assert parent has active class
        $this->assertStringContainsString('navbar__item--active', $html);
        
        // Assert child also has active class
        $this->assertStringContainsString('navbar__dropdown-item--active', $html);
    }
    
    /**
     * Test that both icon and label are rendered for each item
     * 
     * Validates: Requirements 2.1
     */
    public function testMenuItemRendersIconAndLabel(): void
    {
        $menuItems = [
            [
                'menu_key' => 'dashboard',
                'menu_label' => 'Dashboard',
                'menu_icon' => 'ri-dashboard-line',
                'route_path' => '/dashboard',
                'menu_level' => 1,
                'required_permission' => null,
                'badge_source' => null,
                'children' => []
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/');
        $html = $navbar->render();
        
        // Assert icon is rendered
        $this->assertStringContainsString('ri-dashboard-line', $html);
        $this->assertStringContainsString('navbar__icon', $html);
        
        // Assert label is rendered
        $this->assertStringContainsString('navbar__label', $html);
        $this->assertStringContainsString('Dashboard', $html);
    }
    
    /**
     * Test that ARIA attributes are present for accessibility
     * 
     * Validates: Requirements 10.3
     */
    public function testAriaAttributesPresent(): void
    {
        $menuItems = [
            [
                'menu_key' => 'front_office',
                'menu_label' => 'Front Office',
                'menu_icon' => 'ri-service-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => 'view_front_office',
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'registration',
                        'menu_label' => 'Registration',
                        'menu_icon' => 'ri-user-add-line',
                        'route_path' => '/front-office/registration',
                        'menu_level' => 2,
                        'required_permission' => 'view_front_office',
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/');
        $html = $navbar->render();
        
        // Assert main navigation ARIA attributes
        $this->assertStringContainsString('role="navigation"', $html);
        $this->assertStringContainsString('aria-label="Main navigation"', $html);
        
        // Assert dropdown ARIA attributes
        $this->assertStringContainsString('aria-haspopup="true"', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertStringContainsString('role="menu"', $html);
        $this->assertStringContainsString('role="menuitem"', $html);
        
        // Assert icons have aria-hidden
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }
    
    /**
     * Test that badges are rendered when badge_source is present
     * 
     * Validates: Requirements 2.1
     */
    public function testBadgeRenderedWhenPresent(): void
    {
        $menuItems = [
            [
                'menu_key' => 'front_office',
                'menu_label' => 'Front Office',
                'menu_icon' => 'ri-service-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => 'view_front_office',
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'token_management',
                        'menu_label' => 'Token/Queue',
                        'menu_icon' => 'ri-list-ordered',
                        'route_path' => '/front-office/queue',
                        'menu_level' => 2,
                        'required_permission' => 'view_front_office',
                        'badge_source' => 'queue_count',
                        'children' => []
                    ]
                ]
            ]
        ];
        
        $this->mockMenuService->method('getUserMenu')
            ->willReturn($menuItems);
        
        $navbar = new HorizontalNavbar($this->mockMenuService, '/');
        $html = $navbar->render();
        
        // Assert badge is rendered with data attribute
        $this->assertStringContainsString('navbar__badge', $html);
        $this->assertStringContainsString('data-badge-source="queue_count"', $html);
    }
}
