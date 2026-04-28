/**
 * Rider Edit Page JavaScript
 * File: public/assets/js/admin/riders/edit.js
 */

$(document).ready(function() {
    initializeFormValidation();
    initializePasswordValidation();
    initializeNumericValidation();
    initializeStatusPreview();
});

/**
 * Initialize form validations
 */
function initializeFormValidation() {
    const form = $('#editRiderForm');

    // Real-time validations
    $('#name, #employee_id').on('input blur', function() {
        validateRequiredField($(this));
    });

    $('#email').on('input blur', function() {
        validateEmailField($(this));
    });

    $('#phone').on('input blur', function() {
        validatePhoneField($(this));
    });

    $('#password').on('input blur', function() {
        validatePasswordField($(this));
    });

    $('#confirm_password').on('input blur', function() {
        validateConfirmPassword($(this));
    });

    $('#hub_id, #vehicle_type, #status').on('change', function() {
        validateSelectField($(this));
    });

    $('#vehicle_number').on('input blur', function() {
        validateVehicleNumber($(this));
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
        if (!validateForm()) {
            e.preventDefault();
            showNotification('Please fix all errors before updating', 'error');
            return false;
        }

        // Check if password and confirm password match
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password && password !== confirmPassword) {
            e.preventDefault();
            showNotification('Password and Confirm Password do not match', 'error');
            return false;
        }

        // Show loading state
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Updating Rider...');

        return true;
    });
}

/**
 * Validate required field
 */
function validateRequiredField(field) {
    const value = field.val().trim();
    const fieldName = getFieldLabel(field);
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value) {
        errorMessage = fieldName + ' is required';
        isValid = false;
    } else if (field.attr('name') === 'name' && value.length < 2) {
        errorMessage = fieldName + ' must be at least 2 characters';
        isValid = false;
    } else if (field.attr('name') === 'name' && value.length > 100) {
        errorMessage = fieldName + ' cannot exceed 100 characters';
        isValid = false;
    } else if (field.attr('name') === 'employee_id' && value.length < 3) {
        errorMessage = fieldName + ' must be at least 3 characters';
        isValid = false;
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Validate email field
 */
function validateEmailField(field) {
    const value = field.val().trim();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value) {
        errorMessage = 'Email address is required';
        isValid = false;
    } else {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(value)) {
            errorMessage = 'Please enter a valid email address';
            isValid = false;
        } else if (value.length > 100) {
            errorMessage = 'Email cannot exceed 100 characters';
            isValid = false;
        }
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Validate phone field
 */
function validatePhoneField(field) {
    const value = field.val().trim();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value) {
        errorMessage = 'Phone number is required';
        isValid = false;
    } else if (!/^[6-9][0-9]{9}$/.test(value)) {
        errorMessage = 'Please enter a valid 10-digit mobile number starting with 6-9';
        isValid = false;
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Validate vehicle number
 */
function validateVehicleNumber(field) {
    const value = field.val().trim();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value) {
        errorMessage = 'Vehicle number is required';
        isValid = false;
    } else if (value.length < 4) {
        errorMessage = 'Please enter a valid vehicle number';
        isValid = false;
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Validate password field
 */
function validatePasswordField(field) {
    const value = field.val();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (value && value !== '') {
        if (value.length < 8) {
            errorMessage = 'Password must be at least 8 characters';
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
 * Validate confirm password
 */
function validateConfirmPassword(field) {
    const password = $('#password').val();
    const confirmPassword = field.val();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (password && confirmPassword && password !== confirmPassword) {
        errorMessage = 'Passwords do not match';
        isValid = false;
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else if (password && confirmPassword) {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Validate select field
 */
function validateSelectField(field) {
    const value = field.val();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value || value === '') {
        errorMessage = getFieldLabel(field) + ' is required';
        isValid = false;
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Validate number field
 */
function validateNumberField(field) {
    const value = field.val();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (value && value !== '') {
        if (isNaN(value) || parseFloat(value) <= 0) {
            errorMessage = getFieldLabel(field) + ' must be a positive number';
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
 * Get field label
 */
function getFieldLabel(field) {
    const label = $(`label[for="${field.attr('id')}"]`);
    if (label.length) {
        return label.text().replace('*', '').trim();
    }

    const name = field.attr('name');
    const labels = {
        'name': 'Full Name',
        'email': 'Email Address',
        'phone': 'Phone Number',
        'employee_id': 'Employee ID',
        'hub_id': 'Hub Assignment',
        'vehicle_type': 'Vehicle Type',
        'vehicle_number': 'Vehicle Number',
        'status': 'Rider Status'
    };

    return labels[name] || name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Full form validation
 */
function validateForm() {
    let isValid = true;

    if (!validateRequiredField($('#name'))) isValid = false;
    if (!validateEmailField($('#email'))) isValid = false;
    if (!validatePhoneField($('#phone'))) isValid = false;
    if (!validateRequiredField($('#employee_id'))) isValid = false;
    if (!validateSelectField($('#hub_id'))) isValid = false;
    if (!validateSelectField($('#vehicle_type'))) isValid = false;
    if (!validateVehicleNumber($('#vehicle_number'))) isValid = false;
    if (!validateSelectField($('#status'))) isValid = false;

    return isValid;
}

/**
 * Initialize password validation with strength meter
 */
function initializePasswordValidation() {
    const passwordField = $('#password');
    const confirmField = $('#confirm_password');

    passwordField.on('input', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);

        $('.password-strength').remove();

        if (password.length > 0) {
            const strengthHtml = `
                <div class="password-strength mt-1">
                    <small class="text-muted">Password strength:
                        <span class="strength-text">${strength.text}</span>
                    </small>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar ${strength.class}"
                             style="width: ${strength.percent}%; transition: width 0.3s ease;"></div>
                    </div>
                </div>
            `;
            $(this).after(strengthHtml);
        }
    });

    // Confirm password validation
    confirmField.on('input', function() {
        validateConfirmPassword($(this));
    });
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
        1: { text: 'Weak', class: 'bg-danger', percent: 20 },
        2: { text: 'Fair', class: 'bg-warning', percent: 40 },
        3: { text: 'Good', class: 'bg-info', percent: 60 },
        4: { text: 'Strong', class: 'bg-primary', percent: 80 },
        5: { text: 'Very Strong', class: 'bg-success', percent: 100 }
    };

    return strengths[strength] || strengths[1];
}

/**
 * Initialize numeric field validation
 */
function initializeNumericValidation() {
    $('input[type="number"]').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        if (charCode !== 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
        }
    });
}

/**
 * Initialize status preview
 */
function initializeStatusPreview() {
    const statusSelect = $('#status');
    const originalStatus = statusSelect.val();

    statusSelect.on('change', function() {
        const newStatus = $(this).val();
        const statusText = statusSelect.find('option:selected').text();

        if (newStatus !== originalStatus) {
            showNotification(`Rider status will be changed to ${statusText}`, 'info');
        }
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'success') {
    $('.custom-notification').remove();

    const notification = $(`
        <div class="custom-notification ${type}">
            <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : (type === 'error' ? 'danger-circle' : 'info-circle')}-line-duotone"></iconify-icon>
            <span>${escapeHtml(message)}</span>
        </div>
    `);

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
