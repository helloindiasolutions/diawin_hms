/**
 * Melina SPA Navigation Engine
 * 
 * Provides smooth, flash-free navigation by dynamically loading page content
 * without a full browser refresh.
 */

// Prevent class re-declaration during SPA navigation
if (typeof window.SpaNavigation === 'undefined') {

    class SpaNavigation {
        constructor() {
            this.contentContainerSelector = '.main-content.app-content > .container-fluid';
            this.loaderSelector = '.progress-top-bar';
            this.isNavigating = false;

            // Initial setup
            this.init();
        }

        init() {
            // Listen for all link clicks
            document.addEventListener('click', (e) => this.handleLinkClick(e));

            // Handle back/forward buttons
            window.addEventListener('popstate', (e) => this.handlePopState(e));

            // Close horizontal menus on page load complete
            document.addEventListener('melina:page-loaded', () => {
                if (typeof clearNavDropdown === 'function') {
                    clearNavDropdown();
                }
            });

            console.log('SpaNavigation Engine Ready');
        }

        /**
         * Intercept link clicks
         */
        handleLinkClick(e) {
            // Find the nearest link
            const link = e.target.closest('a');

            // Only intercept if:
            // 1. It's an internal link
            // 2. It's not a hash/void link
            // 3. It's not opening in a new tab
            // 4. It's not explicitly marked to ignore SPA
            if (this.shouldIntercept(link)) {
                e.preventDefault();
                this.navigate(link.href);
            }
        }

        /**
         * Determine if a link should be handled by SPA engine
         */
        shouldIntercept(link) {
            if (!link) return false;

            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('#') || href === '' || href.startsWith('javascript:')) return false;

            // Don't intercept if target is _blank
            if (link.getAttribute('target') === '_blank') return false;

            // External link check
            if (link.href.indexOf(window.location.origin) !== 0) return false;

            // Skip if specifically opted out
            if (link.dataset.noSpa !== undefined) return false;

            // Skip folders (handeled by sidebar.js)
            if (link.parentElement.classList.contains('has-sub')) return false;

            return true;
        }

        /**
         * Handle back/forward buttons
         */
        handlePopState(e) {
            this.navigate(window.location.href, false);
        }

        /**
         * Perform the actual navigation
         */
        async navigate(url, pushState = true) {
            if (this.isNavigating) return;
            this.isNavigating = true;

            const loader = document.querySelector(this.loaderSelector);
            const container = document.querySelector(this.contentContainerSelector);

            if (loader) {
                loader.style.width = '30%';
                loader.style.opacity = '1';
            }

            if (container) {
                container.style.opacity = '0.4';
            }

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Navigation failed');

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // 1. Update Title
                document.title = doc.title;

                // 2. Update Content
                const newContent = doc.querySelector(this.contentContainerSelector);
                if (!newContent) {
                    window.location.href = url;
                    return;
                }

                // 2.5 Reset Melina state BEFORE content swap to properly cleanup modals/backdrop
                // This must happen while the old content (modals, etc.) still exists in the DOM
                if (window.Melina && typeof window.Melina.reset === 'function') {
                    window.Melina.reset();
                }

                // Perform the swap
                if (container) {
                    container.innerHTML = newContent.innerHTML;
                    container.className = newContent.className;
                }

                // 3. Update URL
                if (pushState) {
                    window.history.pushState({}, '', url);
                }

                // 4. Update Sidebar Active State
                if (window.sidebarController) {
                    window.sidebarController.currentRoute = window.location.pathname;
                    window.sidebarController.highlightActiveMenu();
                }

                // 5. Inject page-specific styles from head
                this.injectPageStyles(doc);

                // 6. Execute new scripts (only inline/page-specific)
                this.executeScripts(doc);

                // 7. Finishing touches
                window.scrollTo({ top: 0, behavior: 'auto' });

                if (container) {
                    container.style.opacity = '1';
                }

                if (loader) {
                    loader.style.width = '100%';
                    setTimeout(() => {
                        loader.style.opacity = '0';
                        setTimeout(() => loader.style.width = '0', 300);
                    }, 200);
                }

                // Trigger page load event after inline scripts execute
                setTimeout(() => {
                    document.dispatchEvent(new CustomEvent('melina:page-loaded'));
                }, 100);

                this.isNavigating = false;

            } catch (error) {
                console.error('SPA Navigation Error:', error);
                window.location.href = url;
                this.isNavigating = false;
            }
        }

        /**
         * Inject page-specific styles from the head section
         */
        injectPageStyles(doc) {
            // Remove previous SPA-injected styles
            document.querySelectorAll('style[data-spa-style]').forEach(s => s.remove());

            // Find all inline style tags in the head and body (some views output styles in body)
            const allStyles = Array.from(doc.querySelectorAll('style'));

            // Look for page-specific styles
            // This is where <?= $styles ?? '' ?> and <?= $scripts ?? '' ?> style content appears
            allStyles.forEach((oldStyle, index) => {
                const content = oldStyle.textContent;

                // Skip base/layout styles - only inject page-specific styles
                // Base styles contain these keywords, page styles don't
                const isBaseStyle = content && (
                    content.includes('Responsive Font Sizing') ||
                    content.includes('Toast notification styles') ||
                    content.includes('Progress Top Bar Styles') ||
                    content.includes('Global subtle dot pattern')
                );

                if (content && !isBaseStyle && content.trim().length > 0) {
                    const newStyle = document.createElement('style');
                    newStyle.textContent = content;
                    newStyle.setAttribute('data-spa-style', 'true');
                    document.head.appendChild(newStyle);
                }
            });
        }

        /**
         * Executes scripts from the parsed doc
         * 
         * IMPORTANT: We ONLY execute INLINE scripts (page-specific JS).
         * External scripts (libraries) are NEVER re-loaded because:
         * 1. They're already loaded from the main layout (app.php)
         * 2. Re-loading them causes "Identifier already declared" errors
         * 3. Libraries like Bootstrap, SweetAlert, Toastify are global and persist
         */
        executeScripts(doc) {
            // Clean up previous SPA inline scripts to prevent memory leaks
            document.querySelectorAll('script[data-spa-script]').forEach(s => s.remove());

            // Get ONLY inline scripts from the new page's body
            // These are the page-specific scripts inside <?= $scripts ?? '' ?>
            const inlineScripts = Array.from(doc.querySelectorAll('body script:not([src])'));

            // Filter out base layout inline scripts (modal backdrop, showToast, etc.)
            const pageSpecificScripts = inlineScripts.filter(script => {
                const content = script.textContent || '';
                // Skip layout scripts that are in every page
                const isLayoutScript = (
                    content.includes('window.showToast = function') ||
                    (content.includes('MutationObserver') && content.includes('modal-backdrop')) ||
                    (content.includes('localStorage.removeItem') && content.includes('vyzordarktheme'))
                );
                return !isLayoutScript && content.trim().length > 50; // Must have meaningful content
            });

            // Execute page-specific inline scripts
            setTimeout(() => {
                pageSpecificScripts.forEach((oldScript) => {
                    try {
                        const newScript = document.createElement('script');

                        // Wrap script content in try-catch for better error handling
                        const wrappedContent = `
                        try {
                            ${oldScript.textContent}
                        } catch (error) {
                            console.error('SPA Script execution error:', error);
                        }
                    `;

                        newScript.textContent = wrappedContent;
                        newScript.setAttribute('data-spa-script', 'inline');
                        document.body.appendChild(newScript);
                        console.log('SPA: Executed inline script');
                    } catch (error) {
                        console.error('Failed to execute script:', error);
                    }
                });
            }, 50);
        }
    }

    // Make class globally accessible
    window.SpaNavigation = SpaNavigation;

    // Global instance
    if (!window.spaNavigation) {
        window.spaNavigation = new SpaNavigation();
    }

} // End of SPA guard if block
