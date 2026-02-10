/**
 * Sidebar Width Fix
 * Ensures the sidebar displays at full width on page load
 */
(function() {
    'use strict';
    
    // Run immediately on script load
    function fixSidebarWidth() {
        const html = document.documentElement;
        const body = document.body;
        const sidebar = document.querySelector('.app-sidebar');
        
        if (!sidebar) {
            console.warn('Sidebar element not found');
            return;
        }
        
        // Check current layout mode
        const navLayout = html.getAttribute('data-nav-layout') || 'vertical';
        const verticalStyle = html.getAttribute('data-vertical-style');
        const toggled = html.getAttribute('data-toggled');
        
        console.log('Current sidebar state:', {
            navLayout,
            verticalStyle,
            toggled,
            sidebarWidth: sidebar.offsetWidth
        });
        
        // If sidebar is too narrow (less than 100px), force it to full width
        if (sidebar.offsetWidth < 100) {
            console.warn('Sidebar is too narrow, forcing full width');
            
            // Remove collapsed states
            html.removeAttribute('data-toggled');
            html.setAttribute('data-nav-layout', 'vertical');
            
            // Remove any vertical styles that collapse the sidebar
            if (verticalStyle && ['overlay', 'detached', 'icontext', 'closed'].includes(verticalStyle)) {
                html.removeAttribute('data-vertical-style');
            }
            
            // Force sidebar width
            sidebar.style.width = '16rem';
            sidebar.style.minWidth = '16rem';
            
            console.log('Sidebar width fixed to 16rem');
        }
    }
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixSidebarWidth);
    } else {
        fixSidebarWidth();
    }
    
    // Also run after a short delay to catch any dynamic changes
    setTimeout(fixSidebarWidth, 100);
    setTimeout(fixSidebarWidth, 500);
})();
