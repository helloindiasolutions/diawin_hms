/**
 * HMS Sidebar Menu Controller
 * 
 * Manages the sidebar menu functionality including:
 * - Loading menu structure from API
 * - Rendering menu items with icons, labels, and badges
 * - Handling menu expansion/collapse
 * - Active state highlighting
 * - Real-time badge updates
 * - State persistence
 * - Search functionality
 * - Branch switching
 * - Keyboard navigation
 * 
 * @class SidebarController
 * @version 1.0.0
 */

// Prevent class re-declaration during SPA navigation
if (typeof window.SidebarController === 'undefined') {

    class SidebarController {
        /**
         * Initialize the SidebarController
         */
        constructor() {
            this.menuData = [];
            this.badgeData = {};
            this.expandedGroups = [];
            this.currentRoute = window.location.pathname;
            this.badgeUpdateInterval = null;
            this.searchQuery = '';
        }

        /**
         * Initialize the sidebar controller
         * Orchestrates the complete initialization process
         * 
         * @async
         * @returns {Promise<void>}
         */
        async init() {
            try {
                // Add loading state
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.add('sidebar-loading');
                }

                // Load menu data for role-based filtering (future enhancement)
                try {
                    await this.loadMenu();
                } catch (error) {
                    console.warn('Menu API not available, using existing sidebar:', error);
                    // Continue with existing sidebar - this is expected
                }

                // Load saved state and apply to existing sidebar
                this.loadSavedState();

                // Attach event listeners to existing sidebar elements
                this.attachEventListeners();

                // Start badge updates
                this.startBadgeUpdates();

                // Highlight active menu based on current route
                this.highlightActiveMenu();

                // Remove loading state
                if (sidebar) {
                    sidebar.classList.remove('sidebar-loading');
                }

                console.log('Sidebar controller initialized successfully');
            } catch (error) {
                console.error('Failed to initialize sidebar:', error);
                // Remove loading state
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.remove('sidebar-loading');
                }
                // Don't show fallback - let the existing sidebar work normally
            }
        }

        /**
         * Load menu structure from API
         * Fetches the role-based filtered menu for the current user
         * 
         * @async
         * @returns {Promise<void>}
         * @throws {Error} If API request fails
         */
        async loadMenu() {
            try {
                const response = await fetch('/api/v1/menu', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include'
                });

                if (!response.ok) {
                    throw new Error(`Menu API returned ${response.status}`);
                }

                const data = await response.json();

                if (data.success && data.data && data.data.menus) {
                    this.menuData = data.data.menus;
                } else {
                    throw new Error('Invalid menu data structure');
                }
            } catch (error) {
                console.error('Failed to load menu:', error);
                throw error;
            }
        }

        /**
         * Load badge counters from API
         * Fetches real-time counts for various menu badges
         * 
         * @async
         * @returns {Promise<void>}
         */
        async loadBadges() {
            try {
                const response = await fetch('/api/v1/menu/badges', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include'
                });

                if (!response.ok) {
                    console.warn('Badge API request failed:', response.status);
                    return;
                }

                const data = await response.json();

                if (data.success && data.data) {
                    this.badgeData = data.data;
                    this.updateBadges(this.badgeData);
                }
            } catch (error) {
                console.warn('Failed to load badges:', error);
            }
        }

        /**
         * Render the complete menu structure
         * NOTE: This method is disabled - we work with the existing sidebar HTML
         * The existing sidebar in pages/layouts/components/main-sidebar.php is used
         * 
         * @returns {void}
         */
        renderMenu() {
            // DO NOT render - the existing sidebar HTML is already in place
            // This method is kept for future role-based filtering enhancement
            console.log('Menu data loaded for future role-based filtering:', this.menuData);
        }

        /**
         * Render a single menu item recursively
         * Handles icons, labels, badges, and nested children
         * 
         * @param {Object} item - Menu item data
         * @param {string} item.menu_key - Unique identifier for the menu item
         * @param {string} item.menu_label - Display text for the menu item
         * @param {string|null} item.menu_icon - RemixIcon class name
         * @param {string|null} item.route_path - URL path for navigation
         * @param {number} item.menu_level - Hierarchy level (1-3)
         * @param {string|null} item.badge_source - Badge counter identifier
         * @param {Array} item.children - Nested menu items
         * @param {number} level - Current nesting level
         * @returns {HTMLElement} The rendered menu item element
         */
        renderMenuItem(item, level) {
            const li = document.createElement('li');
            li.className = `menu-item menu-level-${level}`;
            li.dataset.menuKey = item.menu_key;

            const link = document.createElement('a');
            link.href = item.route_path || '#';
            link.className = 'menu-link';

            // Prevent default navigation for items with children
            if (item.children && item.children.length > 0) {
                link.href = '#';
            }

            // Render icon
            if (item.menu_icon) {
                const icon = document.createElement('i');
                icon.className = item.menu_icon;
                link.appendChild(icon);
            }

            // Render label
            const label = document.createElement('span');
            label.className = 'menu-label';
            label.textContent = item.menu_label;
            link.appendChild(label);

            // Render badge if applicable
            if (item.badge_source) {
                const badgeCount = this.badgeData[item.badge_source] || 0;
                if (badgeCount > 0) {
                    const badge = document.createElement('span');
                    badge.className = 'menu-badge';
                    badge.dataset.badgeSource = item.badge_source;
                    badge.textContent = badgeCount;
                    link.appendChild(badge);
                }
            }

            // Add chevron icon for items with children
            if (item.children && item.children.length > 0) {
                const chevron = document.createElement('i');
                chevron.className = 'ri-arrow-down-s-line menu-chevron';
                link.appendChild(chevron);
            }

            li.appendChild(link);

            // Render children recursively
            if (item.children && item.children.length > 0) {
                const ul = document.createElement('ul');
                ul.className = 'menu-submenu';

                item.children.forEach(child => {
                    ul.appendChild(this.renderMenuItem(child, level + 1));
                });

                li.appendChild(ul);

                // Check if this group should be expanded
                if (this.expandedGroups.includes(item.menu_key)) {
                    li.classList.add('expanded');
                }
            }

            return li;
        }

        /**
         * Render a fallback minimal menu when API fails
         * Shows only essential navigation items
         * Works with existing sidebar structure
         * 
         * @returns {void}
         */
        renderFallbackMenu() {
            const menuContainer = document.querySelector('.main-menu');

            if (!menuContainer) {
                return;
            }

            const fallbackMenu = [
                { menu_key: 'dashboard', menu_label: 'Dashboard', menu_icon: 'ri-dashboard-line', route_path: '/dashboard' },
                { menu_key: 'profile', menu_label: 'Profile', menu_icon: 'ri-user-line', route_path: '/profile' },
                { menu_key: 'logout', menu_label: 'Logout', menu_icon: 'ri-logout-box-line', route_path: '/logout' }
            ];

            menuContainer.innerHTML = '';

            fallbackMenu.forEach(item => {
                const li = document.createElement('li');
                li.className = 'slide';

                const link = document.createElement('a');
                link.href = item.route_path;
                link.className = 'side-menu__item';

                const icon = document.createElement('i');
                icon.className = item.menu_icon;
                link.appendChild(icon);

                const label = document.createElement('span');
                label.className = 'side-menu__label';
                label.textContent = item.menu_label;
                link.appendChild(label);

                li.appendChild(link);
                menuContainer.appendChild(li);
            });

            // Show error notification
            this.showNotification('Failed to load menu. Showing minimal navigation.', 'error');
        }

        /**
         * Toggle menu group expansion state
         * Implements accordion behavior (only one group expanded at a time)
         * Works with existing sidebar structure using 'slide' and 'open' classes
         * 
         * @param {HTMLElement} menuItem - The menu item element to toggle
         * @returns {void}
         */
        toggleMenuGroup(menuItem) {
            if (!menuItem) {
                return;
            }

            const isOpen = menuItem.classList.contains('open');

            // Determine the level of this menu item
            const isTopLevel = menuItem.parentElement.classList.contains('main-menu');

            // Accordion behavior: collapse all other groups at the same level
            if (isTopLevel) {
                const siblings = menuItem.parentElement.querySelectorAll(':scope > .slide.has-sub.open');
                siblings.forEach(sibling => {
                    if (sibling !== menuItem) {
                        sibling.classList.remove('open');
                        // Remove from expanded groups tracking
                        const siblingKey = this.getMenuKey(sibling);
                        if (siblingKey) {
                            const index = this.expandedGroups.indexOf(siblingKey);
                            if (index > -1) {
                                this.expandedGroups.splice(index, 1);
                            }
                        }
                    }
                });
            }

            // Toggle current item
            if (isOpen) {
                menuItem.classList.remove('open');
                const menuKey = this.getMenuKey(menuItem);
                if (menuKey) {
                    const index = this.expandedGroups.indexOf(menuKey);
                    if (index > -1) {
                        this.expandedGroups.splice(index, 1);
                    }
                }
            } else {
                menuItem.classList.add('open');
                const menuKey = this.getMenuKey(menuItem);
                if (menuKey && !this.expandedGroups.includes(menuKey)) {
                    this.expandedGroups.push(menuKey);
                }
            }

            this.saveState();
        }

        /**
         * Force collapse all open menu groups
         * Useful for clearing the UI after navigation or on escaped key
         * 
         * @returns {void}
         */
        collapseAllMenus() {
            document.querySelectorAll('.slide.has-sub.open').forEach(slide => {
                slide.classList.remove('open');
            });
            this.expandedGroups = [];
            this.saveState();
        }

        /**
         * Get a unique key for a menu item
         * Uses data-menu-key if available, otherwise generates from link text
         * 
         * @param {HTMLElement} menuItem - The menu item element
         * @returns {string|null} The menu key or null
         */
        getMenuKey(menuItem) {
            if (menuItem.dataset.menuKey) {
                return menuItem.dataset.menuKey;
            }

            // Generate key from link text
            const link = menuItem.querySelector('.side-menu__item');
            if (link) {
                const label = link.querySelector('.side-menu__label');
                if (label) {
                    return label.textContent.trim().toLowerCase().replace(/\s+/g, '_');
                }
            }

            return null;
        }

        /**
         * Highlight the active menu item based on current route
         * Also highlights parent items and auto-expands parent groups
         * Works with existing sidebar structure
         * 
         * @returns {void}
         */
        highlightActiveMenu() {
            // Remove all active states
            document.querySelectorAll('.side-menu__item.active').forEach(link => {
                link.classList.remove('active');
            });

            // Find and highlight the matching menu item
            document.querySelectorAll('.side-menu__item').forEach(link => {
                const href = link.getAttribute('href');

                if (href && href !== '#' && href !== 'javascript:void(0);' && href === this.currentRoute) {
                    link.classList.add('active');

                    // Expand all parent groups
                    let parent = link.closest('.slide-menu');
                    while (parent) {
                        const parentSlide = parent.closest('.slide.has-sub');
                        if (parentSlide) {
                            parentSlide.classList.add('open');
                            const menuKey = this.getMenuKey(parentSlide);
                            if (menuKey && !this.expandedGroups.includes(menuKey)) {
                                this.expandedGroups.push(menuKey);
                            }
                        }
                        parent = parentSlide ? parentSlide.parentElement.closest('.slide-menu') : null;
                    }

                    // Also mark parent links as active
                    let parentMenu = link.closest('.slide-menu');
                    while (parentMenu) {
                        const parentSlide = parentMenu.closest('.slide.has-sub');
                        if (parentSlide) {
                            const parentLink = parentSlide.querySelector(':scope > .side-menu__item');
                            if (parentLink) {
                                parentLink.classList.add('active');
                            }
                        }
                        parentMenu = parentSlide ? parentSlide.parentElement.closest('.slide-menu') : null;
                    }
                }
            });

            this.saveState();
        }

        /**
         * Save menu state to localStorage
         * Stores expanded groups for persistence across page loads
         * 
         * @returns {void}
         */
        saveState() {
            try {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar) {
                    return;
                }

                const userId = sidebar.dataset.userId;
                if (!userId) {
                    console.warn('User ID not found, cannot save menu state');
                    return;
                }

                const stateKey = `menu_state_${userId}`;
                const state = {
                    expandedGroups: this.expandedGroups,
                    timestamp: Date.now()
                };

                localStorage.setItem(stateKey, JSON.stringify(state));
            } catch (error) {
                console.warn('Failed to save menu state to localStorage:', error);
            }
        }

        /**
         * Load saved menu state from localStorage
         * Restores previously expanded groups
         * Works with existing sidebar structure
         * 
         * @returns {void}
         */
        loadSavedState() {
            try {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar) {
                    return;
                }

                const userId = sidebar.dataset.userId;
                if (!userId) {
                    return;
                }

                const stateKey = `menu_state_${userId}`;
                const savedState = localStorage.getItem(stateKey);

                if (savedState) {
                    const state = JSON.parse(savedState);
                    if (state.expandedGroups && Array.isArray(state.expandedGroups)) {
                        this.expandedGroups = state.expandedGroups;

                        // Apply the expanded state to menu items
                        this.expandedGroups.forEach(menuKey => {
                            // Find menu items by generated key
                            document.querySelectorAll('.slide.has-sub').forEach(slide => {
                                const key = this.getMenuKey(slide);
                                if (key === menuKey) {
                                    slide.classList.add('open');
                                }
                            });
                        });
                    }
                }
            } catch (error) {
                console.warn('Failed to load menu state from localStorage:', error);
                // Clear corrupted data
                try {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar && sidebar.dataset.userId) {
                        localStorage.removeItem(`menu_state_${sidebar.dataset.userId}`);
                    }
                } catch (e) {
                    // Ignore cleanup errors
                }
            }
        }

        /**
         * Start periodic badge updates
         * Polls the badge API every 5 seconds
         * 
         * @returns {void}
         */
        startBadgeUpdates() {
            // Initial load
            this.loadBadges();

            // Set up polling interval - Update every 30 seconds (reduced from 5 seconds)
            this.badgeUpdateInterval = setInterval(() => {
                this.loadBadges();
            }, 30000); // Update every 30 seconds
        }

        /**
         * Update badge displays in the DOM
         * Shows/hides badges based on count values
         * 
         * @param {Object} badgeData - Badge counter values
         * @returns {void}
         */
        updateBadges(badgeData) {
            Object.keys(badgeData).forEach(badgeSource => {
                const badges = document.querySelectorAll(`[data-badge-source="${badgeSource}"]`);
                const count = badgeData[badgeSource];

                badges.forEach(badge => {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                });
            });
        }

        /**
         * Handle search input
         * Filters menu items based on query string
         * Works with existing sidebar structure
         * 
         * @param {string} query - Search query
         * @returns {void}
         */
        handleSearch(query) {
            this.searchQuery = query.toLowerCase().trim();

            document.querySelectorAll('.slide').forEach(item => {
                const link = item.querySelector('.side-menu__item');
                const label = link ? link.querySelector('.side-menu__label') : null;
                const labelText = label ? label.textContent.toLowerCase() : '';

                if (this.searchQuery === '' || labelText.includes(this.searchQuery)) {
                    item.style.display = '';

                    // Show parent items if child matches
                    let parent = item.parentElement.closest('.slide');
                    while (parent) {
                        parent.style.display = '';
                        parent = parent.parentElement.closest('.slide');
                    }
                } else {
                    // Check if any children match
                    const hasMatchingChild = item.querySelectorAll('.slide').length > 0 &&
                        Array.from(item.querySelectorAll('.slide')).some(child => {
                            const childLink = child.querySelector('.side-menu__item');
                            const childLabel = childLink ? childLink.querySelector('.side-menu__label') : null;
                            const childText = childLabel ? childLabel.textContent.toLowerCase() : '';
                            return childText.includes(this.searchQuery);
                        });

                    if (!hasMatchingChild) {
                        item.style.display = 'none';
                    }
                }
            });
        }

        /**
         * Handle branch switching for Super Admin
         * Calls API to switch branch context and reloads page
         * 
         * @async
         * @param {number} branchId - The branch ID to switch to
         * @returns {Promise<void>}
         */
        async handleBranchSwitch(branchId) {
            try {
                const response = await fetch('/api/v1/menu/branch-switch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({ branch_id: branchId })
                });

                if (!response.ok) {
                    throw new Error(`Branch switch failed: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.showNotification('Branch switched successfully', 'success');
                    // Reload page to apply new branch context
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    throw new Error(data.message || 'Branch switch failed');
                }
            } catch (error) {
                console.error('Failed to switch branch:', error);
                this.showNotification('Failed to switch branch', 'error');
            }
        }

        /**
         * Attach event listeners to sidebar elements
         * Sets up click handlers, search input, branch selector, etc.
         * Works with existing sidebar structure
         * 
         * @returns {void}
         */
        attachEventListeners() {
            // NOTE: We let defaultmenu.min.js handle the menu click animations
            // This controller only handles: search, badges, branch switching, and state persistence

            // Search input handler
            const searchInput = document.getElementById('menuSearch');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    this.handleSearch(e.target.value);
                });
            }

            // Branch selector handler
            const branchSelector = document.getElementById('branchSelector');
            if (branchSelector) {
                branchSelector.addEventListener('change', (e) => {
                    const branchId = e.target.value;
                    if (branchId && branchId !== 'all') {
                        this.handleBranchSwitch(parseInt(branchId, 10));
                    }
                });
            }

            // Sidebar toggle handler for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    this.toggleSidebarMobile();
                });
            }

            // Responsive overlay handler
            const overlay = document.getElementById('responsive-overlay');
            if (overlay) {
                overlay.addEventListener('click', () => {
                    this.collapseSidebarMobile();
                });
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                this.handleKeyboardShortcuts(e);
            });

            // Handle window resize
            window.addEventListener('resize', () => {
                this.handleResize();
            });
        }

        /**
         * Toggle sidebar on mobile devices
         * 
         * @returns {void}
         */
        toggleSidebarMobile() {
            if (window.innerWidth >= 768) {
                return;
            }

            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('responsive-overlay');

            if (sidebar && overlay) {
                sidebar.classList.toggle('expanded');
                overlay.classList.toggle('active');
            }
        }

        /**
         * Collapse sidebar on mobile devices
         * 
         * @returns {void}
         */
        collapseSidebarMobile() {
            if (window.innerWidth >= 768) {
                return;
            }

            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('responsive-overlay');

            if (sidebar && overlay) {
                sidebar.classList.remove('expanded');
                overlay.classList.remove('active');
            }
        }

        /**
         * Handle keyboard shortcuts
         * 
         * @param {KeyboardEvent} e - The keyboard event
         * @returns {void}
         */
        handleKeyboardShortcuts(e) {
            // Ctrl+K or Cmd+K - Focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('menuSearch');
                if (searchInput) {
                    searchInput.focus();
                }
            }

            // Escape - Collapse all groups or clear search
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('menuSearch');
                if (searchInput && searchInput.value) {
                    searchInput.value = '';
                    this.handleSearch('');
                } else {
                    // Collapse all groups
                    this.collapseAllMenus();
                }
            }

            // Alt+1 through Alt+9 - Navigate to top-level menu groups
            if (e.altKey && e.key >= '1' && e.key <= '9') {
                e.preventDefault();
                const index = parseInt(e.key, 10) - 1;
                const topLevelGroups = document.querySelectorAll('.main-menu > .slide.has-sub');
                if (topLevelGroups[index]) {
                    const link = topLevelGroups[index].querySelector('.side-menu__item');
                    if (link) {
                        link.focus();
                        link.click();
                    }
                }
            }
        }

        /**
         * Handle window resize
         * 
         * @returns {void}
         */
        handleResize() {
            // Collapse mobile sidebar when resizing to desktop
            if (window.innerWidth >= 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('responsive-overlay');

                if (sidebar && overlay) {
                    sidebar.classList.remove('expanded');
                    overlay.classList.remove('active');
                }
            }
        }

        /**
         * Show a notification message to the user
         * 
         * @param {string} message - The notification message
         * @param {string} type - The notification type (success, error, warning, info)
         * @returns {void}
         */
        showNotification(message, type = 'info') {
            // This is a placeholder - implement based on your notification system
            console.log(`[${type.toUpperCase()}] ${message}`);

            // You can integrate with your existing notification system here
            // For example: toastr, sweetalert, or custom notification component
        }

        /**
         * Cleanup method to remove event listeners and intervals
         * Call this when destroying the sidebar instance
         * 
         * @returns {void}
         */
        destroy() {
            if (this.badgeUpdateInterval) {
                clearInterval(this.badgeUpdateInterval);
                this.badgeUpdateInterval = null;
            }
        }
    }

    // Make class globally accessible
    window.SidebarController = SidebarController;

} // End of SPA guard if block

// Initialize sidebar on page load
document.addEventListener('DOMContentLoaded', () => {
    // Prevent re-initialization during SPA navigation
    if (window.sidebarController) {
        return;
    }

    const sidebar = new window.SidebarController();
    sidebar.init();

    // Store instance globally for debugging and external access
    window.sidebarController = sidebar;
});
