<?php
/**
 * Horizontal Navbar Component
 * 
 * Renders a horizontal navigation bar for the HMS application.
 * Displays menu items horizontally below the main header with support for:
 * - Role-based permission filtering
 * - Multi-level dropdown menus (up to 3 levels)
 * - Active state indication
 * - Semantic HTML with ARIA attributes for accessibility
 * 
 * Requirements: 1.1, 2.1, 3.1, 8.1, 8.2, 10.3
 * 
 * @package HMS
 * @subpackage Components
 */

declare(strict_types=1);

namespace App\Components;

use App\Services\MenuService;

class HorizontalNavbar
{
    private MenuService $menuService;
    private array $menuItems;
    private string $currentPage;
    
    /**
     * Constructor
     * 
     * @param MenuService $menuService Service for fetching filtered menu items
     * @param string $currentPage Current page URL path for active state detection
     */
    public function __construct(MenuService $menuService, string $currentPage)
    {
        $this->menuService = $menuService;
        $this->currentPage = $currentPage;
        $this->menuItems = $this->menuService->getUserMenu();
    }
    
    /**
     * Render the complete horizontal navbar
     * 
     * Outputs the navbar HTML with semantic structure and ARIA attributes.
     * Returns empty string if no menu items are available.
     * 
     * @return string Complete navbar HTML
     */
    public function render(): string
    {
        // Return empty if no menu items
        if (empty($this->menuItems)) {
            return '';
        }
        
        $html = '<nav class="navbar" role="navigation" aria-label="Main navigation">';
        $html .= '<ul class="navbar__menu">';
        
        foreach ($this->menuItems as $item) {
            // Only render level 1 (top-level) items
            if ($item['menu_level'] === 1) {
                $html .= $this->renderMenuItem($item);
            }
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Render an individual menu item
     * 
     * Renders a top-level menu item with icon, label, and optional dropdown indicator.
     * Applies active state class if the item or its children match the current page.
     * 
     * @param array $item Menu item data from MenuConfig
     * @return string Menu item HTML
     */
    private function renderMenuItem(array $item): string
    {
        $hasChildren = !empty($item['children']);
        $isActive = $this->isActive($item);
        $activeClass = $isActive ? ' navbar__item--active' : '';
        
        $html = '<li class="navbar__item' . $activeClass . '">';
        
        // Render link or button based on whether item has children
        if ($hasChildren) {
            // Item with dropdown - use button for accessibility
            $html .= '<button class="navbar__link" ';
            $html .= 'aria-haspopup="true" ';
            $html .= 'aria-expanded="false" ';
            $html .= 'aria-label="' . htmlspecialchars($item['menu_label']) . ' menu">';
        } else {
            // Regular link
            $url = $item['route_path'] ?? '#';
            $html .= '<a href="' . htmlspecialchars($url) . '" class="navbar__link">';
        }
        
        // Icon
        if (!empty($item['menu_icon'])) {
            $html .= '<i class="' . htmlspecialchars($item['menu_icon']) . ' navbar__icon" aria-hidden="true"></i>';
        }
        
        // Label
        $html .= '<span class="navbar__label">' . htmlspecialchars($item['menu_label']) . '</span>';
        
        // Dropdown indicator for items with children
        if ($hasChildren) {
            $html .= '<i class="ri-arrow-down-s-line navbar__dropdown-indicator" aria-hidden="true"></i>';
        }
        
        // Badge if badge_source is present
        if (!empty($item['badge_source'])) {
            $html .= '<span class="navbar__badge" data-badge-source="' . htmlspecialchars($item['badge_source']) . '"></span>';
        }
        
        // Close link/button
        $html .= $hasChildren ? '</button>' : '</a>';
        
        // Render dropdown panel if item has children
        if ($hasChildren) {
            $html .= $this->renderDropdown($item['children']);
        }
        
        $html .= '</li>';
        
        return $html;
    }
    
    /**
     * Render dropdown panel for submenu items
     * 
     * Renders a dropdown panel containing second-level and third-level menu items.
     * Third-level items are rendered as nested items within their parent.
     * 
     * @param array $children Array of child menu items
     * @return string Dropdown panel HTML
     */
    private function renderDropdown(array $children): string
    {
        if (empty($children)) {
            return '';
        }
        
        $html = '<div class="navbar__dropdown" role="menu">';
        $html .= '<ul class="navbar__dropdown-menu">';
        
        foreach ($children as $child) {
            $hasGrandchildren = !empty($child['children']);
            $isActive = $this->isActive($child);
            $activeClass = $isActive ? ' navbar__dropdown-item--active' : '';
            
            $html .= '<li class="navbar__dropdown-item' . $activeClass . '">';
            
            if ($hasGrandchildren) {
                // Second-level item with third-level children
                $html .= '<div class="navbar__dropdown-parent">';
                $html .= '<span class="navbar__dropdown-parent-label">';
                
                // Icon
                if (!empty($child['menu_icon'])) {
                    $html .= '<i class="' . htmlspecialchars($child['menu_icon']) . ' navbar__dropdown-icon" aria-hidden="true"></i>';
                }
                
                // Label
                $html .= '<span>' . htmlspecialchars($child['menu_label']) . '</span>';
                $html .= '</span>';
                $html .= '</div>';
                
                // Render third-level items as nested list
                $html .= '<ul class="navbar__dropdown-nested">';
                foreach ($child['children'] as $grandchild) {
                    $isGrandchildActive = $this->isActive($grandchild);
                    $grandchildActiveClass = $isGrandchildActive ? ' navbar__dropdown-nested-item--active' : '';
                    
                    $html .= '<li class="navbar__dropdown-nested-item' . $grandchildActiveClass . '">';
                    $url = $grandchild['route_path'] ?? '#';
                    $html .= '<a href="' . htmlspecialchars($url) . '" class="navbar__dropdown-nested-link" role="menuitem">';
                    
                    // Icon
                    if (!empty($grandchild['menu_icon'])) {
                        $html .= '<i class="' . htmlspecialchars($grandchild['menu_icon']) . ' navbar__dropdown-icon" aria-hidden="true"></i>';
                    }
                    
                    // Label
                    $html .= '<span>' . htmlspecialchars($grandchild['menu_label']) . '</span>';
                    
                    // Badge if present
                    if (!empty($grandchild['badge_source'])) {
                        $html .= '<span class="navbar__badge" data-badge-source="' . htmlspecialchars($grandchild['badge_source']) . '"></span>';
                    }
                    
                    $html .= '</a>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            } else {
                // Regular second-level item without children
                $url = $child['route_path'] ?? '#';
                $html .= '<a href="' . htmlspecialchars($url) . '" class="navbar__dropdown-link" role="menuitem">';
                
                // Icon
                if (!empty($child['menu_icon'])) {
                    $html .= '<i class="' . htmlspecialchars($child['menu_icon']) . ' navbar__dropdown-icon" aria-hidden="true"></i>';
                }
                
                // Label
                $html .= '<span>' . htmlspecialchars($child['menu_label']) . '</span>';
                
                // Badge if present
                if (!empty($child['badge_source'])) {
                    $html .= '<span class="navbar__badge" data-badge-source="' . htmlspecialchars($child['badge_source']) . '"></span>';
                }
                
                $html .= '</a>';
            }
            
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Determine if a menu item is active
     * 
     * Checks if the menu item or any of its descendants match the current page URL.
     * An item is considered active if:
     * - Its route_path matches the current page
     * - Any of its children or grandchildren match the current page
     * 
     * @param array $item Menu item to check
     * @return bool True if item is active, false otherwise
     */
    private function isActive(array $item): bool
    {
        // Check if current item matches
        if (!empty($item['route_path']) && $item['route_path'] === $this->currentPage) {
            return true;
        }
        
        // Check children recursively
        if (!empty($item['children'])) {
            foreach ($item['children'] as $child) {
                if ($this->isActive($child)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}
