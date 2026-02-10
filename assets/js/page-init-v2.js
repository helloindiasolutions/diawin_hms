/**
 * Melina Page Initialization System
 * 
 * Provides a React-like useEffect pattern for page initialization.
 * Ensures page-specific initialization code runs both on:
 * - Initial page load (DOMContentLoaded)
 * - SPA navigation (melina:page-loaded event)
 */

(function () {
    'use strict';

    // Melina global namespace initialization
    // Melina global namespace initialization
    window.Melina = window.Melina || {};

    // Ensure state properties exist (crucial because Melina might be defined in layout without these)
    if (!Array.isArray(window.Melina._pageCallbacks)) window.Melina._pageCallbacks = [];
    if (!Array.isArray(window.Melina._cleanupCallbacks)) window.Melina._cleanupCallbacks = [];
    if (typeof window.Melina._isInitialized === 'undefined') window.Melina._isInitialized = false;
    if (typeof window.Melina._domLoaded === 'undefined') window.Melina._domLoaded = false;
    if (!window.Melina._currentPath) window.Melina._currentPath = window.location.pathname;
    if (typeof window.Melina.basePath === 'undefined') window.Melina.basePath = '';

    // Add/Update methods to ensure they are always current even if re-loaded via SPA
    Object.assign(window.Melina, {
        /**
         * Register a callback to run on page load
         */
        onPageLoad: function (callback, options = {}) {
            if (typeof callback !== 'function') {
                console.warn('Melina.onPageLoad: callback must be a function');
                return;
            }

            const callbackInfo = {
                fn: callback,
                path: options.path || null,
                once: options.once || false,
                executed: false
            };

            this._pageCallbacks.push(callbackInfo);

            // If DOM is already loaded, execute immediately
            // But skip if we are in the middle of SPA navigation (will be run by melina:page-loaded)
            if (this._domLoaded && (!window.spaNavigation || !window.spaNavigation.isNavigating)) {
                this._executeCallback(callbackInfo);
            }
        },

        /**
         * Register a cleanup callback to run when navigating away
         */
        onPageUnload: function (callback) {
            if (typeof callback === 'function') {
                this._cleanupCallbacks.push(callback);
            }
        },

        /**
         * Reset page-specific state before loading a new page
         */
        reset: function () {
            // Run cleanup callbacks
            this._cleanupCallbacks.forEach(fn => {
                try { fn(); } catch (e) { console.error('Melina: Cleanup error', e); }
            });
            this._cleanupCallbacks = [];

            // Force close all Bootstrap Modals and Offcanvases
            // We use dispose() + force removal instead of hide() because hide() is async
            // and the backdrop may persist during SPA navigation
            if (typeof bootstrap !== 'undefined') {
                // Modals - dispose and force remove
                document.querySelectorAll('.modal.show, .modal.showing').forEach(el => {
                    try {
                        const m = bootstrap.Modal.getInstance(el);
                        if (m) {
                            m.dispose();
                        }
                        // Force remove modal classes
                        el.classList.remove('show', 'showing');
                        el.style.display = 'none';
                        el.removeAttribute('aria-modal');
                        el.removeAttribute('role');
                    } catch (e) { }
                });

                // Offcanvases - dispose and force remove
                document.querySelectorAll('.offcanvas.show, .offcanvas.showing').forEach(el => {
                    try {
                        const o = bootstrap.Offcanvas.getInstance(el);
                        if (o) {
                            o.dispose();
                        }
                        el.classList.remove('show', 'showing');
                    } catch (e) { }
                });
            }

            // Force remove ALL modal backdrops (critical for SPA navigation)
            document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(el => {
                el.remove();
            });

            // Reset body state that modals/offcanvas add
            document.body.classList.remove('modal-open', 'offcanvas-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');

            this._pageCallbacks = [];
        },

        /**
         * SPA Navigation wrapper
         */
        navigate: function (url) {
            // Prepend base path if it's a relative root path and doesn't already have it
            if (url.startsWith('/') && this.basePath && !url.startsWith(this.basePath)) {
                const cleanBasePath = this.basePath.endsWith('/') ? this.basePath.slice(0, -1) : this.basePath;
                url = cleanBasePath + url;
            }

            if (window.spaNavigation && typeof window.spaNavigation.navigate === 'function') {
                window.spaNavigation.navigate(url);
            } else {
                window.location.href = url;
            }
        },

        /**
         * Execute a single callback if conditions are met
         * @private
         */
        _executeCallback: function (callbackInfo) {
            if (callbackInfo.path && !this._matchPath(callbackInfo.path)) return;
            if (callbackInfo.once && callbackInfo.executed) return;

            try {
                callbackInfo.fn();
                callbackInfo.executed = true;
            } catch (error) {
                console.error('Melina.onPageLoad callback error:', error);
            }
        },

        /**
         * Check if current path matches the specified pattern
         * @private
         */
        _matchPath: function (pattern) {
            const currentPath = window.location.pathname;
            if (pattern === currentPath) return true;
            if (pattern.endsWith('*')) {
                const prefix = pattern.slice(0, -1);
                return currentPath.startsWith(prefix);
            }
            return false;
        },

        /**
         * Run all registered callbacks for the current page
         * @private
         */
        _runCallbacks: function () {
            try {
                this._currentPath = window.location.pathname;

                // Critical Safety Check: Ensure _pageCallbacks is always an array
                if (!this._pageCallbacks || !Array.isArray(this._pageCallbacks)) {
                    // console.warn('Melina: _pageCallbacks was invalid, resetting to empty array');
                    this._pageCallbacks = [];
                }

                // Create a copy to prevent mutation issues during iteration
                const callbacks = [...this._pageCallbacks];

                for (let i = 0; i < callbacks.length; i++) {
                    const cb = callbacks[i];
                    if (cb && typeof cb === 'object') {
                        try {
                            if (!cb.once) cb.executed = false;
                            this._executeCallback(cb);
                        } catch (e) {
                            console.error('Melina: Error executing page callback', e);
                        }
                    }
                }
            } catch (globalError) {
                console.error('Melina: Critical error in _runCallbacks', globalError);
            }
        },

        /**
         * Initialize the page system
         * @private
         */
        _init: function () {
            if (this._isInitialized) return;

            const self = this;
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    self._domLoaded = true;
                    self._runCallbacks();
                });
            } else {
                self._domLoaded = true;
                self._runCallbacks();
            }

            document.addEventListener('melina:page-loaded', () => {
                self._runCallbacks();
            });

            this._interceptAddEventListener();
            this._isInitialized = true;
            console.log('Melina Page Init System Ready');
        },

        /**
         * Intercept document.addEventListener to capture DOMContentLoaded callbacks
         * @private
         */
        _interceptAddEventListener: function () {
            const self = this;
            const originalAddEventListener = Document.prototype.addEventListener;

            Document.prototype.addEventListener = function (type, listener, options) {
                if (type === 'DOMContentLoaded' && self._domLoaded) {
                    try {
                        if (typeof listener === 'function') {
                            listener({ type: 'DOMContentLoaded', target: document });
                        }
                    } catch (error) {
                        console.error('Melina: Error executing DOMContentLoaded listener:', error);
                    }
                    return;
                }
                return originalAddEventListener.call(this, type, listener, options);
            };
        }
    });

    // Auto-initialize if not already done
    if (!window.Melina._isInitialized) {
        window.Melina._init();
    }

    window.onPageLoad = function (callback, options) {
        window.Melina.onPageLoad(callback, options);
    };

    window.onPageUnload = function (callback) {
        window.Melina.onPageUnload(callback);
    };
})();
