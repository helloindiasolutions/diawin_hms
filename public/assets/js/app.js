/**
 * Main Application JavaScript
 */

(function() {
    'use strict';

    // CSRF Token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // API Helper
    const API = {
        baseUrl: '/api/v1',

        async request(endpoint, options = {}) {
            const url = this.baseUrl + endpoint;
            
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            // Add auth token if exists
            const authToken = localStorage.getItem('auth_token');
            if (authToken) {
                defaultOptions.headers['Authorization'] = 'Bearer ' + authToken;
            }

            const config = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...options.headers
                }
            };

            if (config.body && typeof config.body === 'object') {
                config.body = JSON.stringify(config.body);
            }

            try {
                const response = await fetch(url, config);
                const data = await response.json();
                
                if (!response.ok) {
                    throw { status: response.status, ...data };
                }
                
                return data;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        },

        get(endpoint) {
            return this.request(endpoint, { method: 'GET' });
        },

        post(endpoint, body) {
            return this.request(endpoint, { method: 'POST', body });
        },

        put(endpoint, body) {
            return this.request(endpoint, { method: 'PUT', body });
        },

        delete(endpoint) {
            return this.request(endpoint, { method: 'DELETE' });
        }
    };

    // Form Validation Helper
    const FormValidator = {
        validate(form) {
            const inputs = form.querySelectorAll('[required], [data-validate]');
            let isValid = true;

            inputs.forEach(input => {
                if (!this.validateInput(input)) {
                    isValid = false;
                }
            });

            return isValid;
        },

        validateInput(input) {
            const value = input.value.trim();
            const rules = input.dataset.validate?.split('|') || [];
            
            // Clear previous errors
            this.clearError(input);

            // Required check
            if (input.required && !value) {
                this.showError(input, 'This field is required');
                return false;
            }

            // Custom rules
            for (const rule of rules) {
                const [ruleName, param] = rule.split(':');
                
                switch (ruleName) {
                    case 'email':
                        if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                            this.showError(input, 'Please enter a valid email');
                            return false;
                        }
                        break;
                    case 'min':
                        if (value.length < parseInt(param)) {
                            this.showError(input, `Minimum ${param} characters required`);
                            return false;
                        }
                        break;
                    case 'max':
                        if (value.length > parseInt(param)) {
                            this.showError(input, `Maximum ${param} characters allowed`);
                            return false;
                        }
                        break;
                    case 'match':
                        const matchInput = document.querySelector(`[name="${param}"]`);
                        if (matchInput && value !== matchInput.value) {
                            this.showError(input, 'Fields do not match');
                            return false;
                        }
                        break;
                }
            }

            return true;
        },

        showError(input, message) {
            input.classList.add('is-invalid');
            
            let feedback = input.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                input.parentNode.insertBefore(feedback, input.nextSibling);
            }
            feedback.textContent = message;
        },

        clearError(input) {
            input.classList.remove('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback?.classList.contains('invalid-feedback')) {
                feedback.remove();
            }
        }
    };

    // Toast Notifications
    const Toast = {
        show(message, type = 'info', duration = 3000) {
            const container = this.getContainer();
            
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <span class="toast-message">${message}</span>
                <button class="toast-close">&times;</button>
            `;

            container.appendChild(toast);

            // Close button
            toast.querySelector('.toast-close').addEventListener('click', () => {
                toast.remove();
            });

            // Auto remove
            setTimeout(() => toast.remove(), duration);
        },

        getContainer() {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
                document.body.appendChild(container);
            }
            return container;
        },

        success(message) { this.show(message, 'success'); },
        error(message) { this.show(message, 'error'); },
        warning(message) { this.show(message, 'warning'); },
        info(message) { this.show(message, 'info'); }
    };

    // Loading Spinner
    const Loading = {
        show() {
            if (document.getElementById('loading-overlay')) return;
            
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.innerHTML = '<div class="spinner"></div>';
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);display:flex;align-items:center;justify-content:center;z-index:9999;';
            document.body.appendChild(overlay);
        },

        hide() {
            document.getElementById('loading-overlay')?.remove();
        }
    };

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-validate forms
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!FormValidator.validate(form)) {
                    e.preventDefault();
                }
            });
        });

        // Auto-dismiss alerts
        document.querySelectorAll('.alert[data-dismiss]').forEach(alert => {
            setTimeout(() => alert.remove(), 5000);
        });
    });

    // Expose to global scope
    window.API = API;
    window.FormValidator = FormValidator;
    window.Toast = Toast;
    window.Loading = Loading;

})();
