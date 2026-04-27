/**
 * Hub Create Form Validation
 * File: public/assets/js/admin/hubs/create.js
 */

$(document).ready(function() {
    initCreateFormValidation();
});

let isSubmitting = false;

function initCreateFormValidation() {
    const form = $('#createHubForm');
    const submitBtn = form.find('button[type="submit"]');

    // Real-time validations on input and blur events
    $('#name, #code, #address').on('input blur', function() {
        validateField($(this));
    });

    $('#phone').on('input blur', function() {
        validatePhone($(this));
    });

    $('#email').on('input blur', function() {
        validateEmail($(this));
    });

    // Clear validation on focus
    $('input, textarea').on('focus', function() {
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
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Creating Hub...');

        return true;
    });
}

// Generic field validation
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
                errorMessage = 'Hub name is required';
                isValid = false;
            } else if (value.length < 2) {
                errorMessage = 'Hub name must be at least 2 characters';
                isValid = false;
            } else if (value.length > 100) {
                errorMessage = 'Hub name cannot exceed 100 characters';
                isValid = false;
            } else if (!/^[a-zA-Z0-9\s\-&,.()]+$/.test(value)) {
                errorMessage = 'Hub name contains invalid characters';
                isValid = false;
            }
            break;

        case 'code':
            if (!value) {
                errorMessage = 'Hub code is required';
                isValid = false;
            } else if (value.length < 2) {
                errorMessage = 'Hub code must be at least 2 characters';
                isValid = false;
            } else if (value.length > 20) {
                errorMessage = 'Hub code cannot exceed 20 characters';
                isValid = false;
            } else if (!/^[A-Z0-9-]+$/.test(value)) {
                errorMessage = 'Hub code can only contain uppercase letters, numbers, and hyphens';
                isValid = false;
            }
            break;

        case 'address':
            if (!value) {
                errorMessage = 'Address is required';
                isValid = false;
            } else if (value.length < 5) {
                errorMessage = 'Please enter a complete address (minimum 5 characters)';
                isValid = false;
            } else if (value.length > 500) {
                errorMessage = 'Address cannot exceed 500 characters';
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

// Phone number validation
function validatePhone(phoneField) {
    const phone = phoneField.val().trim();
    let isValid = true;
    let errorMessage = '';

    phoneField.removeClass('is-invalid is-valid');
    phoneField.next('.invalid-feedback').remove();

    if (phone && phone !== '') {
        // Indian mobile number validation (10 digits, starts with 6-9)
        const mobileRegex = /^[6-9][0-9]{9}$/;
        const landlineRegex = /^[0-9]{8,12}$/;

        if (!mobileRegex.test(phone) && !landlineRegex.test(phone)) {
            errorMessage = 'Please enter a valid 10-digit mobile number or 8-12 digit landline number';
            isValid = false;
        } else {
            isValid = true;
        }
    } else {
        isValid = true; // Phone is optional
    }

    if (!isValid) {
        phoneField.addClass('is-invalid');
        phoneField.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else if (phone && phone !== '') {
        phoneField.addClass('is-valid');
    }

    return isValid;
}

// Email validation
function validateEmail(emailField) {
    const email = emailField.val().trim();
    let isValid = true;
    let errorMessage = '';

    emailField.removeClass('is-invalid is-valid');
    emailField.next('.invalid-feedback').remove();

    if (email && email !== '') {
        // Standard email format validation
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!emailRegex.test(email)) {
            errorMessage = 'Please enter a valid email address (e.g., name@example.com)';
            isValid = false;
        } else if (email.length > 100) {
            errorMessage = 'Email address cannot exceed 100 characters';
            isValid = false;
        } else {
            isValid = true;
        }
    } else {
        isValid = true; // Email is optional
    }

    if (!isValid) {
        emailField.addClass('is-invalid');
        emailField.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else if (email && email !== '') {
        emailField.addClass('is-valid');
    }

    return isValid;
}

// Full form validation before submit
function validateForm() {
    let isValid = true;

    // Validate required fields
    if (!validateField($('#name'))) isValid = false;
    if (!validateField($('#code'))) isValid = false;
    if (!validateField($('#address'))) isValid = false;

    // Validate optional fields if they have values
    if ($('#phone').val().trim() !== '') {
        if (!validatePhone($('#phone'))) isValid = false;
    }

    if ($('#email').val().trim() !== '') {
        if (!validateEmail($('#email'))) isValid = false;
    }

    return isValid;
}

// Show validation error message
function showValidationError(message) {
    // Check if SweetAlert is available
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Validation Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK',
            timer: 3000,
            showConfirmButton: true
        });
    } else {
        // Fallback to Bootstrap alert
        const alert = $(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <strong>Validation Error!</strong><br>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(alert);

        setTimeout(() => {
            alert.fadeOut(300, () => alert.remove());
        }, 3000);
    }
}

// Check if hub code is unique (AJAX validation - optional)
function checkUniqueCode(code) {
    return $.ajax({
        url: '/admin/hubs/check-unique-code',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            code: code
        },
        async: false
    }).responseJSON;
}

// Optional: Add unique code validation on blur
$('#code').on('blur', function() {
    const code = $(this).val().trim();

    if (code && code.length >= 2) {
        $.ajax({
            url: '/admin/hubs/check-unique-code',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                code: code
            },
            success: function(response) {
                if (!response.available) {
                    const field = $('#code');
                    field.addClass('is-invalid');
                    field.next('.invalid-feedback').remove();
                    field.after('<div class="invalid-feedback">Hub code already exists. Please use a different code.</div>');
                }
            }
        });
    }
});
