/**
 * Sidebar Diagnostic Tool
 * Run this in the browser console to diagnose sidebar issues
 */
(function() {
    console.log('=== SIDEBAR DIAGNOSTIC ===');
    
    const html = document.documentElement;
    const body = document.body;
    const sidebar = document.querySelector('.app-sidebar');
    
    console.log('HTML Attributes:', {
        'data-nav-layout': html.getAttribute('data-nav-layout'),
        'data-vertical-style': html.getAttribute('data-vertical-style'),
        'data-toggled': html.getAttribute('data-toggled'),
        'data-nav-style': html.getAttribute('data-nav-style'),
        'data-menu-position': html.getAttribute('data-menu-position'),
        'data-theme-mode': html.getAttribute('data-theme-mode'),
        'data-header-styles': html.getAttribute('data-header-styles'),
        'data-menu-styles': html.getAttribute('data-menu-styles'),
        'data-page-style': html.getAttribute('data-page-style'),
        'data-width': html.getAttribute('data-width'),
        'data-icon-overlay': html.getAttribute('data-icon-overlay'),
        'data-icon-text': html.getAttribute('data-icon-text')
    });
    
    if (sidebar) {
        console.log('Sidebar Element:', {
            width: sidebar.offsetWidth + 'px',
            computedWidth: window.getComputedStyle(sidebar).width,
            minWidth: window.getComputedStyle(sidebar).minWidth,
            display: window.getComputedStyle(sidebar).display,
            position: window.getComputedStyle(sidebar).position,
            classes: sidebar.className
        });
    } else {
        console.error('Sidebar element not found!');
    }
    
    console.log('=== END DIAGNOSTIC ===');
    console.log('To fix collapsed sidebar, run: fixSidebar()');
    
    // Provide a fix function
    window.fixSidebar = function() {
        if (!sidebar) {
            console.error('Sidebar not found');
            return;
        }
        
        html.setAttribute('data-nav-layout', 'vertical');
        html.removeAttribute('data-vertical-style');
        html.removeAttribute('data-toggled');
        html.removeAttribute('data-nav-style');
        
        sidebar.style.width = '16rem';
        sidebar.style.minWidth = '16rem';
        
        console.log('Sidebar fixed! Width:', sidebar.offsetWidth + 'px');
    };
})();
