/**
 * Theme Sync - DB to localStorage and save changes
 * 
 * Flow:
 * 1. On first visit (no localStorage): initialize with defaults
 * 2. On login: server clears localStorage, then this script syncs DB theme to localStorage
 * 3. On switcher change: save to API and update localStorage
 * 
 * This ensures:
 * - Fresh visitors get default theme
 * - Logged-in users get their saved theme from DB
 * - custom-switcher.min.js always reads correct values from localStorage
 */
(function () {
    'use strict';

    // Default theme values (must match ThemeService::$defaults)
    const DEFAULTS = {
        themeMode: 'light',
        direction: 'ltr',
        navLayout: 'vertical',
        verticalStyle: 'default',
        menuStyle: 'light',
        headerStyle: 'light',
        pageStyle: 'flat',
        layoutWidth: 'fullwidth',
        menuPosition: 'fixed',
        headerPosition: 'fixed'
    };

    /**
     * Check if localStorage has been initialized with theme values
     */
    function isLocalStorageInitialized() {
        // Check for at least one key that should exist
        return localStorage.getItem('vyzorMenu') !== null ||
            localStorage.getItem('vyzorHeader') !== null ||
            localStorage.getItem('vyzorverticalstyles') !== null;
    }

    /**
     * Initialize localStorage with default theme values
     * Called only when localStorage is empty (first visit)
     */
    function initializeDefaults() {
        // Theme mode - light by default
        localStorage.removeItem('vyzordarktheme');

        // Direction - ltr by default
        localStorage.setItem('vyzorltr', 'true');
        localStorage.removeItem('vyzorrtl');

        // Layout - vertical by default
        localStorage.removeItem('vyzorlayout');

        // Vertical style
        localStorage.setItem('vyzorverticalstyles', DEFAULTS.verticalStyle);

        // Menu style
        localStorage.setItem('vyzorMenu', DEFAULTS.menuStyle);

        // Header style
        localStorage.setItem('vyzorHeader', DEFAULTS.headerStyle);

        // Page style - flat by default
        localStorage.removeItem('vyzorregular');
        localStorage.removeItem('vyzorclassic');
        localStorage.removeItem('vyzormodern');
        localStorage.setItem('vyzorflat', 'true');

        // Layout width - fullwidth by default
        localStorage.removeItem('vyzordefaultwidth');
        localStorage.setItem('vyzorfullwidth', 'true');
        localStorage.removeItem('vyzorboxed');

        // Menu position - fixed by default
        localStorage.setItem('vyzormenufixed', 'true');
        localStorage.removeItem('vyzormenuscrollable');

        // Header position - fixed by default
        localStorage.setItem('vyzorheaderfixed', 'true');
        localStorage.removeItem('vyzorheaderscrollable');
    }

    /**
     * Sync current HTML attributes to localStorage
     * This ensures the switcher JS reads the correct values from DB
     */
    function syncThemeToLocalStorage() {
        const html = document.documentElement;

        // Theme mode
        const themeMode = html.getAttribute('data-theme-mode');
        if (themeMode === 'dark') {
            localStorage.setItem('vyzordarktheme', 'true');
            localStorage.removeItem('vyzorlighttheme');
        } else {
            localStorage.removeItem('vyzordarktheme');
        }

        // Direction
        const dir = html.getAttribute('dir');
        if (dir === 'rtl') {
            localStorage.setItem('vyzorrtl', 'true');
            localStorage.removeItem('vyzorltr');
        } else {
            localStorage.setItem('vyzorltr', 'true');
            localStorage.removeItem('vyzorrtl');
        }

        // Layout
        const navLayout = html.getAttribute('data-nav-layout');
        if (navLayout === 'horizontal') {
            localStorage.setItem('vyzorlayout', 'horizontal');
        } else {
            localStorage.removeItem('vyzorlayout');
        }

        // Vertical style
        const verticalStyle = html.getAttribute('data-vertical-style');
        if (verticalStyle) {
            localStorage.setItem('vyzorverticalstyles', verticalStyle);
        }

        // Menu style
        const menuStyle = html.getAttribute('data-menu-styles');
        if (menuStyle) {
            localStorage.setItem('vyzorMenu', menuStyle);
        }

        // Header style
        const headerStyle = html.getAttribute('data-header-styles');
        if (headerStyle) {
            localStorage.setItem('vyzorHeader', headerStyle);
        }

        // Page style
        const pageStyle = html.getAttribute('data-page-style');
        localStorage.removeItem('vyzorregular');
        localStorage.removeItem('vyzorclassic');
        localStorage.removeItem('vyzormodern');
        localStorage.removeItem('vyzorflat');
        if (pageStyle) {
            localStorage.setItem('vyzor' + pageStyle, 'true');
        }

        // Layout width
        const width = html.getAttribute('data-width');
        localStorage.removeItem('vyzordefaultwidth');
        localStorage.removeItem('vyzorfullwidth');
        localStorage.removeItem('vyzorboxed');
        if (width === 'boxed') {
            localStorage.setItem('vyzorboxed', 'true');
        } else if (width === 'fullwidth') {
            localStorage.setItem('vyzorfullwidth', 'true');
        } else {
            localStorage.setItem('vyzordefaultwidth', 'true');
        }

        // Menu position
        const menuPos = html.getAttribute('data-menu-position');
        localStorage.removeItem('vyzormenufixed');
        localStorage.removeItem('vyzormenuscrollable');
        if (menuPos === 'scrollable') {
            localStorage.setItem('vyzormenuscrollable', 'true');
        } else {
            localStorage.setItem('vyzormenufixed', 'true');
        }

        // Header position
        const headerPos = html.getAttribute('data-header-position');
        localStorage.removeItem('vyzorheaderfixed');
        localStorage.removeItem('vyzorheaderscrollable');
        if (headerPos === 'scrollable') {
            localStorage.setItem('vyzorheaderscrollable', 'true');
        } else {
            localStorage.setItem('vyzorheaderfixed', 'true');
        }
    }

    // Check if this is a fresh login (localStorage was cleared by server)
    // or first visit (localStorage never initialized)
    if (!isLocalStorageInitialized()) {
        // Check if we have server-rendered theme (logged in user)
        const html = document.documentElement;
        const hasServerTheme = html.hasAttribute('data-menu-styles') &&
            html.getAttribute('data-menu-styles') !== '';

        if (hasServerTheme) {
            // Logged in - sync DB theme to localStorage
            syncThemeToLocalStorage();
        } else {
            // First visit - initialize with defaults
            initializeDefaults();
        }
    } else {
        // localStorage exists - sync current HTML to localStorage
        // This handles the case where DB theme was rendered server-side
        syncThemeToLocalStorage();
    }

    // Debounce for API saves
    let saveTimeout = null;

    function saveThemeToDb() {
        if (saveTimeout) clearTimeout(saveTimeout);

        saveTimeout = setTimeout(function () {
            const html = document.documentElement;

            const settings = {
                theme_mode: html.getAttribute('data-theme-mode') || 'light',
                direction: html.getAttribute('dir') || 'ltr',
                nav_layout: html.getAttribute('data-nav-layout') || 'vertical',
                vertical_style: html.getAttribute('data-vertical-style') || 'default',
                menu_style: html.getAttribute('data-menu-styles') || 'light',
                header_style: html.getAttribute('data-header-styles') || 'light',
                page_style: html.getAttribute('data-page-style') || 'flat',
                layout_width: html.getAttribute('data-width') || 'fullwidth',
                menu_position: html.getAttribute('data-menu-position') || 'fixed',
                header_position: html.getAttribute('data-header-position') || 'fixed',
                toggled: html.getAttribute('data-toggled') || 'close'
            };

            const primaryRgb = getComputedStyle(html).getPropertyValue('--primary-rgb').trim();
            if (primaryRgb) {
                settings.primary_rgb = primaryRgb;
            }

            const basePath = window.Melina?.basePath || '';
            const apiUrl = basePath + '/api/v1/theme/settings';

            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(settings)
            }).catch(function (err) {
                console.warn('Theme save failed:', err);
            });
        }, 500);
    }

    // Watch for attribute changes
    const observer = new MutationObserver(function (mutations) {
        let shouldSave = false;
        mutations.forEach(function (m) {
            if (m.type === 'attributes' && m.attributeName &&
                (m.attributeName.startsWith('data-') || m.attributeName === 'dir' || m.attributeName === 'style')) {
                shouldSave = true;
            }
        });
        if (shouldSave) saveThemeToDb();
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme-mode', 'data-nav-layout', 'data-vertical-style', 'data-menu-styles',
            'data-header-styles', 'data-page-style', 'data-width', 'data-menu-position',
            'data-header-position', 'data-toggled', 'dir', 'style']
    });
})();
