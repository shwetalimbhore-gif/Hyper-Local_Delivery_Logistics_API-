/**
 * Hub Edit Page JavaScript
 * Handles toggle switch functionality and form interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all hub edit page functionalities
    initializeToggleSwitch();
    initializeFormValidation();
    initializeAutoSave();
    initializeNumericOnlyFields();
});

/**
 * Initialize Toggle Switch Functionality
 */
function initializeToggleSwitch() {
    const toggleSwitch = document.getElementById('is_active');
    const statusText = document.getElementById('statusText');

    if (toggleSwitch) {
        // Set initial status text color
        updateStatusText(toggleSwitch.checked, statusText);

        // Add change event listener
        toggleSwitch.addEventListener('change', function(e) {
            handleToggleChange(this.checked, statusText);
        });

        // Add click animation
        toggleSwitch.addEventListener('click', function(e) {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 200);
        });
    }
}

/**
 * Handle toggle switch change
 */
function handleToggleChange(isChecked, statusText) {
    // Update status text
    updateStatusText(isChecked, statusText);

    // Show confirmation dialog (optional)
    const message = isChecked ? 'Activate' : 'Deactivate';
    if (confirm(`Are you sure you want to ${message} this hub?`)) {
        // Add visual feedback
        addStatusAnimation();

        // Log the change (for debugging)
        console.log(`Hub status changed to: ${isChecked ? 'Active' : 'Inactive'}`);

        // Optional: Auto-save the status
        // autoSaveStatus(isChecked);
    } else {
        // Revert the toggle if user cancels
        toggleSwitch.checked = !isChecked;
        updateStatusText(!isChecked, statusText);
    }
}

/**
 * Update status text appearance
 */
function updateStatusText(isChecked, statusText) {
    if (statusText) {
        const statusSpan = statusText.querySelector('strong') || statusText;
        if (isChecked) {
            statusSpan.textContent = 'Active Hub';
            statusSpan.style.color = '#28a745';
            statusSpan.classList.add('status-active');
            statusSpan.classList.remove('status-inactive');
        } else {
            statusSpan.textContent = 'Inactive Hub';
            statusSpan.style.color = '#dc3545';
            statusSpan.classList.add('status-inactive');
            statusSpan.classList.remove('status-active');
        }
    }
}

/**
 * Add animation when status changes
 */
function addStatusAnimation() {
    const toggleContainer = document.querySelector('.form-check.form-switch');
    if (toggleContainer) {
        toggleContainer.classList.add('status-updated');
        setTimeout(() => {
            toggleContainer.classList.remove('status-updated');
        }, 500);
    }
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const form = document.getElementById('hubForm');

    if (form) {
        const inputs = form.querySelectorAll('input[required], textarea[required]');

        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });

        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showNotification('Please fill all required fields correctly', 'error');
            }
        });
    }
}

/**
 * Validate individual form field
 */
function validateField(field) {
    if (field.required && !field.value.trim()) {
        field.classList.add('is-invalid');
        addErrorMessage(field, `${field.previousElementSibling?.textContent || 'This field'} is required`);
        return false;
    } else {
        field.classList.remove('is-invalid');
        removeErrorMessage(field);

        // Additional validation based on field type
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                field.classList.add('is-invalid');
                addErrorMessage(field, 'Please enter a valid email address');
                return false;
            }
        }

        if (field.name === 'phone' && field.value) {
            const phoneRegex = /^[0-9+\-\s()]{10,15}$/;
            if (!phoneRegex.test(field.value)) {
                field.classList.add('is-invalid');
                addErrorMessage(field, 'Please enter a valid phone number');
                return false;
            }
        }

        return true;
    }
}

/**
 * Add error message for field
 */
function addErrorMessage(field, message) {
    let errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

/**
 * Remove error message from field
 */
function removeErrorMessage(field) {
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Validate entire form
 */
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    return isValid;
}

/**
 * Initialize auto-save functionality (optional)
 */
function initializeAutoSave() {
    let autoSaveTimer;
    const form = document.getElementById('hubForm');

    if (form && form.querySelector('input, textarea, select')) {
        const inputs = form.querySelectorAll('input:not([type="submit"]), textarea, select');

        inputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(() => {
                    autoSaveForm();
                }, 3000);
            });
        });
    }
}

/**
 * Auto-save form data to localStorage
 */
function autoSaveForm() {
    const form = document.getElementById('hubForm');
    if (form) {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        localStorage.setItem('hub_edit_draft', JSON.stringify({
            data: data,
            timestamp: new Date().getTime()
        }));

        showAutoSaveNotification();
    }
}

/**
 * Show auto-save notification
 */
function showAutoSaveNotification() {
    const notification = document.createElement('div');
    notification.className = 'alert alert-info auto-save-notification';
    notification.innerHTML = '<iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon> Draft saved automatically';
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        background: #17a2b8;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
    `;

    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 2000);
}

/**
 * Load auto-saved draft
 */
function loadAutoSaveDraft() {
    const saved = localStorage.getItem('hub_edit_draft');
    if (saved) {
        const { data, timestamp } = JSON.parse(saved);
        const hoursAgo = (Date.now() - timestamp) / (1000 * 60 * 60);

        if (hoursAgo < 24 && confirm('You have an unsaved draft. Would you like to restore it?')) {
            const form = document.getElementById('hubForm');
            if (form) {
                Object.keys(data).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field && key !== '_token' && key !== '_method') {
                        field.value = data[key];

                        // Trigger change event
                        const event = new Event('input', { bubbles: true });
                        field.dispatchEvent(event);
                    }
                });
                showNotification('Draft restored successfully', 'success');
            }
        }
    }
}

/**
 * Initialize numeric-only fields
 */
function initializeNumericOnlyFields() {
    const numericFields = document.querySelectorAll('input[type="number"]');

    numericFields.forEach(field => {
        field.addEventListener('keypress', function(e) {
            const charCode = e.which ? e.which : e.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} notification-toast`;
    notification.innerHTML = `
        <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon>
        ${message}
    `;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .auto-save-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    }
`;
document.head.appendChild(style);

// Load auto-save draft when page loads
// Uncomment the line below to enable auto-save draft loading
// loadAutoSaveDraft();
