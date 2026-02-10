/**
 * Unit Tests for Sidebar Expansion/Collapse Logic
 * 
 * Tests the toggleMenuGroup() method and accordion behavior
 * Requirements: 2.2, 2.3, 2.5
 */

describe('Sidebar Expansion/Collapse Logic', () => {
    let sidebar;
    let mockMenuData;
    
    beforeEach(() => {
        // Set up DOM
        document.body.innerHTML = `
            <aside id="sidebar" class="sidebar" data-user-id="1" data-branch-id="1">
                <nav class="sidebar-menu" id="sidebarMenu"></nav>
            </aside>
        `;
        
        // Mock localStorage
        global.localStorage = {
            getItem: jest.fn(),
            setItem: jest.fn(),
            removeItem: jest.fn(),
            clear: jest.fn()
        };
        
        // Mock fetch
        global.fetch = jest.fn();
        
        // Create mock menu data
        mockMenuData = [
            {
                menu_key: 'front_office',
                menu_label: 'Front Office',
                menu_icon: 'ri-service-line',
                route_path: null,
                menu_level: 1,
                badge_source: null,
                children: [
                    {
                        menu_key: 'registration',
                        menu_label: 'Registration',
                        menu_icon: 'ri-user-add-line',
                        route_path: '/front-office/registration',
                        menu_level: 2,
                        badge_source: null,
                        children: []
                    }
                ]
            },
            {
                menu_key: 'opd',
                menu_label: 'OPD',
                menu_icon: 'ri-stethoscope-line',
                route_path: null,
                menu_level: 1,
                badge_source: null,
                children: [
                    {
                        menu_key: 'opd_list',
                        menu_label: 'OPD List',
                        menu_icon: 'ri-list-check',
                        route_path: '/opd/list',
                        menu_level: 2,
                        badge_source: null,
                        children: []
                    }
                ]
            }
        ];
        
        // Initialize sidebar controller
        sidebar = new SidebarController();
        sidebar.menuData = mockMenuData;
    });
    
    afterEach(() => {
        jest.clearAllMocks();
    });
    
    describe('toggleMenuGroup()', () => {
        test('should expand a collapsed menu group', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Get menu item
            const menuItem = document.querySelector('[data-menu-key="front_office"]');
            expect(menuItem).toBeTruthy();
            expect(menuItem.classList.contains('expanded')).toBe(false);
            
            // Toggle to expand
            sidebar.toggleMenuGroup('front_office');
            
            // Verify expanded
            expect(menuItem.classList.contains('expanded')).toBe(true);
            expect(sidebar.expandedGroups).toContain('front_office');
        });
        
        test('should collapse an expanded menu group', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Expand first
            sidebar.toggleMenuGroup('front_office');
            const menuItem = document.querySelector('[data-menu-key="front_office"]');
            expect(menuItem.classList.contains('expanded')).toBe(true);
            
            // Toggle to collapse
            sidebar.toggleMenuGroup('front_office');
            
            // Verify collapsed
            expect(menuItem.classList.contains('expanded')).toBe(false);
            expect(sidebar.expandedGroups).not.toContain('front_office');
        });
        
        test('should implement accordion behavior - only one group expanded at a time', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Expand first group
            sidebar.toggleMenuGroup('front_office');
            let frontOfficeItem = document.querySelector('[data-menu-key="front_office"]');
            expect(frontOfficeItem.classList.contains('expanded')).toBe(true);
            
            // Expand second group
            sidebar.toggleMenuGroup('opd');
            
            // Verify only second group is expanded
            frontOfficeItem = document.querySelector('[data-menu-key="front_office"]');
            const opdItem = document.querySelector('[data-menu-key="opd"]');
            
            expect(frontOfficeItem.classList.contains('expanded')).toBe(false);
            expect(opdItem.classList.contains('expanded')).toBe(true);
            expect(sidebar.expandedGroups).not.toContain('front_office');
            expect(sidebar.expandedGroups).toContain('opd');
        });
        
        test('should save state to localStorage after toggle', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Toggle menu group
            sidebar.toggleMenuGroup('front_office');
            
            // Verify saveState was called (localStorage.setItem)
            expect(localStorage.setItem).toHaveBeenCalled();
            const calls = localStorage.setItem.mock.calls;
            const lastCall = calls[calls.length - 1];
            expect(lastCall[0]).toBe('menu_state_1');
            
            const savedState = JSON.parse(lastCall[1]);
            expect(savedState.expandedGroups).toContain('front_office');
        });
        
        test('should handle non-existent menu key gracefully', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Try to toggle non-existent menu
            expect(() => {
                sidebar.toggleMenuGroup('non_existent');
            }).not.toThrow();
            
            // Verify no changes to expandedGroups
            expect(sidebar.expandedGroups).toHaveLength(0);
        });
    });
    
    describe('Event Listeners', () => {
        test('should attach click event listener to menu groups', () => {
            // Render menu
            sidebar.renderMenu();
            sidebar.attachEventListeners();
            
            // Get menu link with children
            const menuLink = document.querySelector('[data-menu-key="front_office"] > .menu-link');
            expect(menuLink).toBeTruthy();
            
            // Simulate click
            const clickEvent = new MouseEvent('click', { bubbles: true });
            menuLink.dispatchEvent(clickEvent);
            
            // Verify menu group was toggled
            const menuItem = document.querySelector('[data-menu-key="front_office"]');
            expect(menuItem.classList.contains('expanded')).toBe(true);
        });
        
        test('should prevent default navigation for menu groups with children', () => {
            // Render menu
            sidebar.renderMenu();
            sidebar.attachEventListeners();
            
            // Get menu link with children
            const menuLink = document.querySelector('[data-menu-key="front_office"] > .menu-link');
            
            // Simulate click with preventDefault spy
            const clickEvent = new MouseEvent('click', { bubbles: true });
            const preventDefaultSpy = jest.spyOn(clickEvent, 'preventDefault');
            menuLink.dispatchEvent(clickEvent);
            
            // Verify preventDefault was called
            expect(preventDefaultSpy).toHaveBeenCalled();
        });
        
        test('should not prevent default navigation for menu items without children', () => {
            // Render menu
            sidebar.renderMenu();
            sidebar.attachEventListeners();
            
            // Expand parent first
            sidebar.toggleMenuGroup('front_office');
            
            // Get menu link without children
            const menuLink = document.querySelector('[data-menu-key="registration"] > .menu-link');
            expect(menuLink).toBeTruthy();
            
            // Verify it has an href
            expect(menuLink.getAttribute('href')).toBe('/front-office/registration');
        });
    });
    
    describe('CSS Classes', () => {
        test('should add "expanded" class when menu group is expanded', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Toggle to expand
            sidebar.toggleMenuGroup('front_office');
            
            // Verify CSS class
            const menuItem = document.querySelector('[data-menu-key="front_office"]');
            expect(menuItem.classList.contains('expanded')).toBe(true);
        });
        
        test('should remove "expanded" class when menu group is collapsed', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Expand then collapse
            sidebar.toggleMenuGroup('front_office');
            sidebar.toggleMenuGroup('front_office');
            
            // Verify CSS class removed
            const menuItem = document.querySelector('[data-menu-key="front_office"]');
            expect(menuItem.classList.contains('expanded')).toBe(false);
        });
        
        test('should render chevron icon for menu groups with children', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Check for chevron icon
            const menuLink = document.querySelector('[data-menu-key="front_office"] > .menu-link');
            const chevron = menuLink.querySelector('.menu-chevron');
            
            expect(chevron).toBeTruthy();
            expect(chevron.classList.contains('ri-arrow-down-s-line')).toBe(true);
        });
        
        test('should not render chevron icon for menu items without children', () => {
            // Render menu
            sidebar.renderMenu();
            
            // Expand parent
            sidebar.toggleMenuGroup('front_office');
            
            // Check registration item has no chevron
            const menuLink = document.querySelector('[data-menu-key="registration"] > .menu-link');
            const chevron = menuLink.querySelector('.menu-chevron');
            
            expect(chevron).toBeFalsy();
        });
    });
    
    describe('State Restoration', () => {
        test('should restore expanded state from localStorage on init', () => {
            // Mock localStorage to return saved state
            localStorage.getItem.mockReturnValue(JSON.stringify({
                expandedGroups: ['front_office'],
                timestamp: Date.now()
            }));
            
            // Load saved state
            sidebar.loadSavedState();
            
            // Verify expanded groups restored
            expect(sidebar.expandedGroups).toContain('front_office');
        });
        
        test('should apply expanded class to restored menu groups', () => {
            // Set expanded groups
            sidebar.expandedGroups = ['front_office'];
            
            // Render menu
            sidebar.renderMenu();
            
            // Verify expanded class applied
            const menuItem = document.querySelector('[data-menu-key="front_office"]');
            expect(menuItem.classList.contains('expanded')).toBe(true);
        });
    });
});
