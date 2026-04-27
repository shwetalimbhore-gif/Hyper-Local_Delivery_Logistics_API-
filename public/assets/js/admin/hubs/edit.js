/**
 * Hub Edit Page JavaScript
 * File: public/assets/js/admin/hubs/edit.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Clear any existing draft on page load (optional)
    localStorage.removeItem('hub_edit_draft');

    // Initialize all hub edit page functionalities
    initializeToggleSwitch();
    initializeFormValidation();
    initializeAutoSave();
    initializeNumericOnlyFields();

    // Draft restore is DISABLED - no popup
});

/**
 * Initialize Toggle Switch Functionality
 */
function initializeToggleSwitch() {
    const toggleSwitch = document.getElementById('is_active');
    const statusText = document.getElementById('statusText');

    if (toggleSwitch) {
        // Set initial status text
        updateStatusText(toggleSwitch.checked, statusText);

        // Add change event listener
        toggleSwitch.addEventListener('change', function(e) {
            handleToggleChange(toggleSwitch, this.checked, statusText);
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
function handleToggleChange(toggleSwitch, isChecked, statusText) {
    updateStatusText(isChecked, statusText);
    addStatusAnimation();
    console.log(`Hub status changed to: ${isChecked ? 'Active' : 'Inactive'}`);
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
    let isValid = true;

    if (field.required && !field.value.trim()) {
        field.classList.add('is-invalid');
        addErrorMessage(field, getFieldLabel(field) + ' is required');
        isValid = false;
    } else {
        field.classList.remove('is-invalid');
        removeErrorMessage(field);

        if (isValid && field.value.trim()) {
            field.classList.add('is-valid');
        }
    }

    return isValid;
}

/**
 * Get field label text
 */
function getFieldLabel(field) {
    const label = document.querySelector(`label[for="${field.id}"]`);
    if (label) {
        return label.textContent.replace('*', '').trim();
    }
    return field.name.charAt(0).toUpperCase() + field.name.slice(1);
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
 * Initialize auto-save functionality (saves draft, but doesn't restore)
 */
function initializeAutoSave() {
    let autoSaveTimer;
    const form = document.getElementById('hubForm');

    if (form) {
        const inputs = form.querySelectorAll('input:not([type="submit"]):not([type="button"]), textarea, select');

        inputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(() => {
                    autoSaveForm();
                }, 5000); // Increased to 5 seconds
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
            if (key !== '_token' && key !== '_method') {
                data[key] = value;
            }
        });

        // Get current hub ID from URL
        const hubId = getCurrentHubId();

        localStorage.setItem('hub_edit_draft', JSON.stringify({
            data: data,
            hubId: hubId,
            timestamp: new Date().getTime()
        }));

        // Optional: Show auto-save notification (commented out to avoid clutter)
        // showAutoSaveNotification();
    }
}

/**
 * Get current hub ID from URL
 */
function getCurrentHubId() {
    const urlParts = window.location.pathname.split('/');
    const editIndex = urlParts.indexOf('edit');
    if (editIndex > 0 && urlParts[editIndex - 1]) {
        return urlParts[editIndex - 1];
    }
    return null;
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
    // Remove existing notifications
    const existing = document.querySelector('.custom-notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `custom-notification ${type}`;
    notification.innerHTML = `
        <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon>
        <span>${escapeHtml(message)}</span>
    `;

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Add CSS animations dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .status-updated {
        animation: statusPulse 0.5s ease;
    }

    @keyframes statusPulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.02); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }

    .status-active {
        color: #28a745 !important;
    }

    .status-inactive {
        color: #dc3545 !important;
    }
`;
document.head.appendChild(style);
