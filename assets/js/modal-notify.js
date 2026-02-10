/**
 * Modal Notification System
 * Replaces alert() with beautiful modal notifications
 */

// Prevent class re-declaration during SPA navigation
if (typeof window.ModalNotify === 'undefined') {

    class ModalNotify {
        constructor() {
            this.createModalContainer();
        }

        createModalContainer() {
            // Check if modal already exists
            if (document.getElementById('modalNotifyContainer')) return;

            const modalHTML = `
            <div class="modal fade" id="modalNotifyContainer" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title" id="modalNotifyTitle">Notification</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <div id="modalNotifyIcon" class="text-center mb-3"></div>
                            <p id="modalNotifyMessage" class="text-center mb-0"></p>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" id="modalNotifyOkBtn">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        show(message, options = {}) {
            const {
                title = 'Notification',
                type = 'info', // success, error, warning, info, question
                okText = 'OK',
                onClose = null
            } = options;

            const modal = document.getElementById('modalNotifyContainer');
            const titleEl = document.getElementById('modalNotifyTitle');
            const messageEl = document.getElementById('modalNotifyMessage');
            const iconEl = document.getElementById('modalNotifyIcon');
            const okBtn = document.getElementById('modalNotifyOkBtn');

            // Set title and message
            titleEl.textContent = title;
            messageEl.textContent = message;
            okBtn.textContent = okText;

            // Set icon based on type
            const icons = {
                success: '<i class="ri-checkbox-circle-line text-success" style="font-size: 3rem;"></i>',
                error: '<i class="ri-close-circle-line text-danger" style="font-size: 3rem;"></i>',
                warning: '<i class="ri-error-warning-line text-warning" style="font-size: 3rem;"></i>',
                info: '<i class="ri-information-line text-info" style="font-size: 3rem;"></i>',
                question: '<i class="ri-question-line text-primary" style="font-size: 3rem;"></i>'
            };

            iconEl.innerHTML = icons[type] || icons.info;

            // Show modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Handle close callback and cleanup backdrop
            const cleanupHandler = () => {
                // Remove any lingering backdrops
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                
                // Remove modal-open class from body
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                
                if (onClose) onClose();
            };

            modal.addEventListener('hidden.bs.modal', cleanupHandler, { once: true });
        }

        success(message, title = 'Success') {
            this.show(message, { type: 'success', title });
        }

        error(message, title = 'Error') {
            this.show(message, { type: 'error', title });
        }

        warning(message, title = 'Warning') {
            this.show(message, { type: 'warning', title });
        }

        info(message, title = 'Information') {
            this.show(message, { type: 'info', title });
        }

        confirm(message, options = {}) {
            const {
                title = 'Confirm',
                confirmText = 'Confirm',
                cancelText = 'Cancel',
                onConfirm = null,
                onCancel = null
            } = options;

            // Check if confirm modal already exists
            let confirmModal = document.getElementById('modalConfirmContainer');
            if (!confirmModal) {
                const confirmHTML = `
                <div class="modal fade" id="modalConfirmContainer" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title" id="modalConfirmTitle">Confirm</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-2">
                                <div class="text-center mb-3">
                                    <i class="ri-question-line text-warning" style="font-size: 3rem;"></i>
                                </div>
                                <p id="modalConfirmMessage" class="text-center mb-0"></p>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="modalConfirmCancelBtn">Cancel</button>
                                <button type="button" class="btn btn-primary" id="modalConfirmOkBtn">Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                document.body.insertAdjacentHTML('beforeend', confirmHTML);
                confirmModal = document.getElementById('modalConfirmContainer');
            }

            const titleEl = document.getElementById('modalConfirmTitle');
            const messageEl = document.getElementById('modalConfirmMessage');
            const confirmBtn = document.getElementById('modalConfirmOkBtn');
            const cancelBtn = document.getElementById('modalConfirmCancelBtn');

            titleEl.textContent = title;
            messageEl.textContent = message;
            confirmBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;

            const bsModal = new bootstrap.Modal(confirmModal);
            bsModal.show();

            // Handle confirm
            confirmBtn.onclick = () => {
                bsModal.hide();
                if (onConfirm) onConfirm();
            };

            // Handle cancel
            cancelBtn.onclick = () => {
                bsModal.hide();
                if (onCancel) onCancel();
            };
        }
    }

    // Make class globally accessible
    window.ModalNotify = ModalNotify;

    // Create global instance
    window.modalNotify = new ModalNotify();

    // Override native alert for backward compatibility
    window.alert = function (message) {
        window.modalNotify.info(message);
    };

    // Override native confirm for backward compatibility
    window.confirm = function (message) {
        return new Promise((resolve) => {
            window.modalNotify.confirm(message, {
                onConfirm: () => resolve(true),
                onCancel: () => resolve(false)
            });
        });
    };

} // End of SPA guard if block
