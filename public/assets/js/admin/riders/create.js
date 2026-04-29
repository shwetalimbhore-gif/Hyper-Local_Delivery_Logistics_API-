/**
 * Rider Create Form Validation
 * File: public/assets/js/admin/riders/create.js
 * Pure JavaScript validations only - No Laravel validation rules
 */

$(document).ready(function() {
    initCreateFormValidation();
});

let isSubmitting = false;

function initCreateFormValidation() {
    const form = $('#riderForm');
    const submitBtn = form.find('button[type="submit"]');

    // Real-time validations on input and blur events
    $('#name, #employee_id, #vehicle_number').on('input blur', function() {
        validateField($(this));
    });

    $('#email').on('input blur', function() {
        validateEmail($(this));
    });

    $('#phone').on('input blur', function() {
        validatePhone($(this));
    });

    $('#password').on('input blur', function() {
        validatePassword($(this));
    });

    $('#hub_id, #vehicle_type').on('change', function() {
        validateSelect($(this));
    });

    $('#max_weight_capacity, #max_size_capacity').on('input blur', function() {
        validateNumberField($(this));
    });

    // Clear validation on focus
    $('input, textarea, select').on('focus', function() {
        $(this).removeClass('is-invalid is-valid');
        $(this).next('.invalid-feedback').remove();
    });

    // Form submit validation
    form.on('submit', function(e) {
        // Prevent double submission
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }

        // Validate all fields
        if (!validateForm()) {
            e.preventDefault();
            showValidationError('Please fix the errors before submitting');
            return false;
        }

        // Show loading state
        isSubmitting = true;
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Creating Rider...');

        return true;
    });
}

/**
 * Generic field validation
 */
function validateField(field) {
    const fieldName = field.attr('name');
    const value = field.val().trim();
    let isValid = true;
    let errorMessage = '';

    // Remove existing validation classes and messages
    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    // Validate based on field name
    switch(fieldName) {
        case 'name':
            if (!value) {
                errorMessage = 'Full name is required';
                isValid = false;
            } else if (value.length < 2) {
                errorMessage = 'Full name must be at least 2 characters';
                isValid = false;
            } else if (value.length > 100) {
                errorMessage = 'Full name cannot exceed 100 characters';
                isValid = false;
            } else if (!/^[a-zA-Z\s\-\.]+$/.test(value)) {
                errorMessage = 'Full name can only contain letters, spaces, hyphens, and dots';
                isValid = false;
            }
            break;

        case 'employee_id':
            if (!value) {
                errorMessage = 'Employee ID is required';
                isValid = false;
            } else if (value.length < 3) {
                errorMessage = 'Employee ID must be at least 3 characters';
                isValid = false;
            } else if (value.length > 20) {
                errorMessage = 'Employee ID cannot exceed 20 characters';
                isValid = false;
            } else if (!/^[A-Za-z0-9-]+$/.test(value)) {
                errorMessage = 'Employee ID can only contain letters, numbers, and hyphens';
                isValid = false;
            }
            break;

        case 'vehicle_number':
            if (!value) {
                errorMessage = 'Vehicle number is required';
                isValid = false;
            } else if (value.length < 4) {
                errorMessage = 'Please enter a valid vehicle number';
                isValid = false;
            } else if (value.length > 20) {
                errorMessage = 'Vehicle number cannot exceed 20 characters';
                isValid = false;
            }
            break;

        case 'address':
            if (value && value.length > 500) {
                errorMessage = 'Address cannot exceed 500 characters';
                isValid = false;
            }
            break;

        case 'vehicle_model':
            if (value && value.length > 100) {
                errorMessage = 'Vehicle model cannot exceed 100 characters';
                isValid = false;
            }
            break;

        case 'license_number':
            if (value && value.length > 50) {
                errorMessage = 'License number cannot exceed 50 characters';
                isValid = false;
            }
            break;
    }

    // Display validation feedback
    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else if (value) {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Email validation
 */
function validateEmail(emailField) {
    const email = emailField.val().trim();
    let isValid = true;
    let errorMessage = '';

    emailField.removeClass('is-invalid is-valid');
    emailField.next('.invalid-feedback').remove();

    if (!email) {
        errorMessage = 'Email address is required';
        isValid = false;
    } else {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(email)) {
            errorMessage = 'Please enter a valid email address (e.g., name@example.com)';
            isValid = false;
        } else if (email.length > 100) {
            errorMessage = 'Email address cannot exceed 100 characters';
            isValid = false;
        }
    }

    if (!isValid) {
        emailField.addClass('is-invalid');
        emailField.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        emailField.addClass('is-valid');
    }

    return isValid;
}

/**
 * Phone number validation
 */
function validatePhone(phoneField) {
    const phone = phoneField.val().trim();
    let isValid = true;
    let errorMessage = '';

    phoneField.removeClass('is-invalid is-valid');
    phoneField.next('.invalid-feedback').remove();

    if (!phone) {
        errorMessage = 'Phone number is required';
        isValid = false;
    } else {
        // Indian mobile number validation (10 digits, starts with 6-9)
        const mobileRegex = /^[6-9][0-9]{9}$/;

        if (!mobileRegex.test(phone)) {
            errorMessage = 'Please enter a valid 10-digit mobile number starting with 6-9';
            isValid = false;
        }
    }

    if (!isValid) {
        phoneField.addClass('is-invalid');
        phoneField.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        phoneField.addClass('is-valid');
    }

    return isValid;
}

/**
 * Password validation
 */
function validatePassword(passwordField) {
    const password = passwordField.val();
    let isValid = true;
    let errorMessage = '';

    passwordField.removeClass('is-invalid is-valid');
    passwordField.next('.invalid-feedback').remove();

    if (!password) {
        errorMessage = 'Password is required';
        isValid = false;
    } else if (password.length < 8) {
        errorMessage = 'Password must be at least 8 characters long';
        isValid = false;
    } else if (password.length > 255) {
        errorMessage = 'Password cannot exceed 255 characters';
        isValid = false;
    }

    if (!isValid) {
        passwordField.addClass('is-invalid');
        passwordField.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        passwordField.addClass('is-valid');
        // Show password strength indicator
        showPasswordStrength(password);
    }

    return isValid;
}

/**
 * Show password strength indicator
 */
function showPasswordStrength(password) {
    // Remove existing strength indicator
    $('.password-strength').remove();

    if (password && password.length > 0) {
        const strength = checkPasswordStrength(password);
        const strengthHtml = `
            <div class="password-strength mt-1">
                <small class="text-muted">Password strength:
                    <span class="strength-text" style="color: ${strength.color}">${strength.text}</span>
                </small>
                <div class="progress" style="height: 3px; margin-top: 2px;">
                    <div class="progress-bar ${strength.class}"
                         style="width: ${strength.percent}%; transition: width 0.3s ease;"></div>
                </div>
            </div>
        `;
        $('#password').after(strengthHtml);
    }
}

/**
 * Check password strength
 */
function checkPasswordStrength(password) {
    let strength = 0;

    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;

    const strengths = {
        1: { text: 'Weak', class: 'bg-danger', color: '#ef4444', percent: 20 },
        2: { text: 'Fair', class: 'bg-warning', color: '#f59e0b', percent: 40 },
        3: { text: 'Good', class: 'bg-info', color: '#0ea5e9', percent: 60 },
        4: { text: 'Strong', class: 'bg-primary', color: '#6366f1', percent: 80 },
        5: { text: 'Very Strong', class: 'bg-success', color: '#10b981', percent: 100 }
    };

    return strengths[strength] || strengths[1];
}

/**
 * Select field validation
 */
function validateSelect(selectField) {
    const value = selectField.val();
    let isValid = true;
    let errorMessage = '';

    selectField.removeClass('is-invalid is-valid');
    selectField.next('.invalid-feedback').remove();

    if (!value || value === '') {
        const fieldName = selectField.attr('name');
        if (fieldName === 'hub_id') {
            errorMessage = 'Please select a hub';
        } else if (fieldName === 'vehicle_type') {
            errorMessage = 'Please select a vehicle type';
        }
        isValid = false;
    }

    if (!isValid) {
        selectField.addClass('is-invalid');
        selectField.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        selectField.addClass('is-valid');
    }

    return isValid;
}

/**
 * Number field validation
 */
function validateNumberField(field) {
    const value = field.val();
    const fieldName = field.attr('name');
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (value && value !== '') {
        const numValue = parseFloat(value);
        if (isNaN(numValue)) {
            errorMessage = 'Please enter a valid number';
            isValid = false;
        } else if (numValue <= 0) {
            errorMessage = 'Value must be greater than 0';
            isValid = false;
        } else if (fieldName === 'max_weight_capacity' && numValue > 1000) {
            errorMessage = 'Maximum weight cannot exceed 1000 kg';
            isValid = false;
        } else if (fieldName === 'max_size_capacity' && numValue > 500) {
            errorMessage = 'Maximum size cannot exceed 500 cm³';
            isValid = false;
        }
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else if (value && value !== '') {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Full form validation before submit
 */
function validateForm() {
    let isValid = true;

    // Validate required fields
    if (!validateField($('#name'))) isValid = false;
    if (!validateEmail($('#email'))) isValid = false;
    if (!validatePhone($('#phone'))) isValid = false;
    if (!validateField($('#employee_id'))) isValid = false;
    if (!validateSelect($('#hub_id'))) isValid = false;
    if (!validateSelect($('#vehicle_type'))) isValid = false;
    if (!validateField($('#vehicle_number'))) isValid = false;
    if (!validatePassword($('#password'))) isValid = false;

    // Validate optional fields (only if they have values)
    if ($('#max_weight_capacity').val() && $('#max_weight_capacity').val() !== '') {
        if (!validateNumberField($('#max_weight_capacity'))) isValid = false;
    }
    if ($('#max_size_capacity').val() && $('#max_size_capacity').val() !== '') {
        if (!validateNumberField($('#max_size_capacity'))) isValid = false;
    }

    return isValid;
}

/**
 * Show validation error message
 */
function showValidationError(message) {
    // Remove existing notification
    $('.custom-notification').remove();

    const notification = $(`
        <div class="custom-notification error">
            <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
            <span>${escapeHtml(message)}</span>
        </div>
    `);

    notification.css({
        position: 'fixed',
        top: '20px',
        right: '20px',
        zIndex: 9999,
        backgroundColor: '#ef4444',
        color: 'white',
        border: 'none',
        padding: '12px 20px',
        borderRadius: '8px',
        fontSize: '14px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        animation: 'slideInRight 0.3s ease'
    });

    $('body').append(notification);

    setTimeout(() => {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Escape HTML
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

// Add animation styles if not present
if (!$('#dynamic-styles').length) {
    const style = $('<style id="dynamic-styles">')
        .text(`
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

            .custom-notification {
                z-index: 10000;
            }

            .password-strength .progress {
                background-color: #e5e7eb;
                border-radius: 10px;
                overflow: hidden;
            }

            .password-strength .progress-bar {
                transition: width 0.3s ease;
            }
        `);
    $('head').append(style);
}

// Make functions globally accessible
window.validateForm = validateForm;
