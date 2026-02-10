/**
 * Global Form Validation System
 * Handles real-time validation, error styling, and submit button state.
 */

// Prevent re-declaration during SPA navigation
if (typeof window.FormValidator === 'undefined') {

  const FormValidator = {
    // Configuration
    classes: {
      invalid: 'is-invalid',
      feedback: 'invalid-feedback',
      form: 'needs-validation'
    },

    // Validation patterns
    patterns: {
      email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      tel: /^[0-9]{10}$/,
      number: /^-?\d*\.?\d+$/
    },

    // Initialize all forms with 'needs-validation' class
    init() {
      document.querySelectorAll(`form.${this.classes.form}`).forEach(form => {
        this.setupForm(form);
      });

      // Also watch for dynamically added forms
      const observer = new MutationObserver((mutations) => {
        mutations.forEach(mutation => {
          mutation.addedNodes.forEach(node => {
            if (node.nodeType === 1) {
              if (node.tagName === 'FORM' && node.classList.contains(this.classes.form)) {
                this.setupForm(node);
              }
              node.querySelectorAll(`form.${this.classes.form}`).forEach(f => this.setupForm(f));
            }
          });
        });
      });

      observer.observe(document.body, { childList: true, subtree: true });
    },

    // Setup a single form
    setupForm(form) {
      const inputs = form.querySelectorAll('input, select, textarea');
      const submitBtn = form.querySelector('button[type="submit"]');

      inputs.forEach(input => {
        // Add error message container if missing
        this.ensureFeedbackElement(input);

        // Listen for changes
        const handler = () => {
          // For tel/mobile/phone fields, strip non-numeric characters
          if (input.getAttribute('type') === 'tel' || input.name === 'mobile' || input.name === 'phone') {
            input.value = input.value.replace(/\D/g, '').substring(0, 10);
          }

          this.validateField(input);
          this.updateSubmitButton(form);
        };

        input.addEventListener('input', handler);
        input.addEventListener('blur', handler);
        if (input.tagName === 'SELECT') input.addEventListener('change', handler);
      });

      // Initial check
      this.updateSubmitButton(form);

      // Prevent submission if invalid (Extra safety)
      form.addEventListener('submit', (e) => {
        if (!this.isFormValid(form)) {
          e.preventDefault();
          e.stopPropagation();
          this.validateAllFields(form);
        }
      });
    },

    // Ensure there's an element for error messages
    ensureFeedbackElement(input) {
      let feedback = input.parentNode.querySelector(`.${this.classes.feedback}`);
      if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = this.classes.feedback;
        input.parentNode.appendChild(feedback);
      }
    },

    // Validate a single field
    validateField(input) {
      let isValid = true;
      let errorMsg = '';

      const value = input.value.trim();
      const required = input.hasAttribute('required');
      const type = input.getAttribute('type');
      const minLength = input.getAttribute('minlength');
      const pattern = input.getAttribute('pattern');

      // 1. Required check
      if (required && !value) {
        isValid = false;
        errorMsg = 'This field is required.';
      }
      // 2. Email check
      else if (value && (type === 'email' || input.name === 'email')) {
        if (!this.patterns.email.test(value)) {
          isValid = false;
          errorMsg = 'Please enter a valid email address.';
        }
      }
      // 3. Tel check
      else if (value && (type === 'tel' || input.name === 'mobile' || input.name === 'phone')) {
        if (!this.patterns.tel.test(value)) {
          isValid = false;
          errorMsg = 'Please enter a valid 10-digit mobile number.';
        }
      }
      // 4. MinLength check
      else if (value && minLength && value.length < parseInt(minLength)) {
        isValid = false;
        errorMsg = `Minimum ${minLength} characters required.`;
      }
      // 5. Pattern check
      else if (value && pattern) {
        const regex = new RegExp(pattern);
        if (!regex.test(value)) {
          isValid = false;
          errorMsg = input.getAttribute('title') || 'Invalid format.';
        }
      }

      // Apply visual state
      if (isValid && value) {
        input.classList.remove(this.classes.invalid);
        input.classList.add('is-valid');
      } else if (!isValid) {
        input.classList.remove('is-valid');
        input.classList.add(this.classes.invalid);
        const feedback = input.parentNode.querySelector(`.${this.classes.feedback}`);
        if (feedback) feedback.textContent = errorMsg;
      } else {
        input.classList.remove(this.classes.invalid);
        input.classList.remove('is-valid');
      }

      return isValid;
    },

    // Check if entire form is valid
    isFormValid(form) {
      const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
      let allValid = true;

      inputs.forEach(input => {
        // A simple check for requiredness first to avoid blocking the button prematurely
        // if the user hasn't touched the field yet.
        // But the requirement says: "OK then only that button enable"
        // So we must check all required fields have values.
        if (!input.value.trim()) allValid = false;
        if (input.classList.contains(this.classes.invalid)) allValid = false;

        // Re-validate pattern/email even if not empty
        if (input.value.trim() && !this.checkFieldSilent(input)) allValid = false;
      });

      return allValid;
    },

    // Silent validation for button state
    checkFieldSilent(input) {
      const value = input.value.trim();
      const type = input.getAttribute('type');
      const minLength = input.getAttribute('minlength');

      if (input.hasAttribute('required') && !value) return false;
      if (value && type === 'email' && !this.patterns.email.test(value)) return false;
      if (value && (type === 'tel' || input.name === 'mobile' || input.name === 'phone') && !this.patterns.tel.test(value)) return false;
      if (value && minLength && value.length < parseInt(minLength)) return false;

      return true;
    },

    // Update submit button state
    updateSubmitButton(form) {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (!submitBtn) return;

      if (this.isFormValid(form)) {
        submitBtn.disabled = false;
      } else {
        submitBtn.disabled = true;
      }
    },

    // Trigger validation on all fields (e.g. on submit attempt)
    validateAllFields(form) {
      form.querySelectorAll('input, select, textarea').forEach(input => {
        this.validateField(input);
      });
    }
  };

  // Make FormValidator globally accessible
  window.FormValidator = FormValidator;

} // End of SPA guard if block

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => {
  if (window.FormValidator) {
    window.FormValidator.init();
  }
});
