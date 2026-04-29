/**
 * Parcel Create Page JavaScript
 * File: public/assets/js/admin/parcels/create.js
 * Pure JavaScript validations only - No Laravel validation rules
 */

$(document).ready(function() {
    initializeFormValidation();
    initializeAutoAssign();
    initializeNumericValidation();
});

/**
 * Initialize all form validations
 */
function initializeFormValidation() {
    const form = $('#parcelForm');

    // Real-time validations on input and blur
    $('#sender_name, #receiver_name, #parcel_name').on('input blur', function() {
        validateNameField($(this));
    });

    $('#sender_phone, #receiver_phone').on('input blur', function() {
        validatePhoneField($(this));
    });

    $('#sender_email, #receiver_email').on('input blur', function() {
        validateEmailField($(this));
    });

    $('#sender_address, #receiver_address, #parcel_description').on('input blur', function() {
        validateTextField($(this));
    });

    $('#weight, #size, #delivery_charge').on('input blur', function() {
        validateNumberField($(this));
    });

    $('#source_hub_id').on('change', function() {
        validateSelectField($(this));
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
            showNotification('Please fix all errors before submitting', 'error');
            return false;
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Creating Parcel...');

        return true;
    });
}

/**
 * Validate name field
 */
function validateNameField(field) {
    const value = field.val().trim();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value) {
        errorMessage = getFieldLabel(field) + ' is required';
        isValid = false;
    } else if (value.length < 2) {
        errorMessage = getFieldLabel(field) + ' must be at least 2 characters';
        isValid = false;
    } else if (value.length > 100) {
        errorMessage = getFieldLabel(field) + ' cannot exceed 100 characters';
        isValid = false;
    } else if (!/^[a-zA-Z\s\-\.]+$/.test(value)) {
        errorMessage = getFieldLabel(field) + ' can only contain letters, spaces, hyphens, and dots';
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
 * Validate phone field
 */
function validatePhoneField(field) {
    const value = field.val().trim();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value) {
        errorMessage = getFieldLabel(field) + ' is required';
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
 * Validate email field
 */
function validateEmailField(field) {
    const value = field.val().trim();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (value && value !== '') {  // Email is optional
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(value)) {
            errorMessage = 'Please enter a valid email address (e.g., name@example.com)';
            isValid = false;
        } else if (value.length > 100) {
            errorMessage = 'Email cannot exceed 100 characters';
            isValid = false;
        } else {
            field.addClass('is-valid');
        }
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    }

    return isValid;
}

/**
 * Validate text field (address, description)
 */
function validateTextField(field) {
    const value = field.val().trim();
    const fieldName = field.attr('name');
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (fieldName === 'sender_address' || fieldName === 'receiver_address') {
        if (!value) {
            errorMessage = getFieldLabel(field) + ' is required';
            isValid = false;
        } else if (value.length < 5) {
            errorMessage = 'Please enter a complete address (minimum 5 characters)';
            isValid = false;
        } else if (value.length > 500) {
            errorMessage = 'Address cannot exceed 500 characters';
            isValid = false;
        }
    }

    // Parcel description is optional, only validate length if provided
    if (fieldName === 'parcel_description' && value && value.length > 500) {
        errorMessage = 'Description cannot exceed 500 characters';
        isValid = false;
    }

    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    } else if (value) {
        field.addClass('is-valid');
    }

    return isValid;
}

/**
 * Validate number field (weight, size, delivery_charge)
 */
function validateNumberField(field) {
    const value = field.val().trim();
    const fieldName = field.attr('name');
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value) {
        errorMessage = getFieldLabel(field) + ' is required';
        isValid = false;
    } else {
        const numValue = parseFloat(value);
        if (isNaN(numValue)) {
            errorMessage = getFieldLabel(field) + ' must be a valid number';
            isValid = false;
        } else if (numValue <= 0) {
            errorMessage = getFieldLabel(field) + ' must be greater than 0';
            isValid = false;
        } else if (fieldName === 'weight' && numValue > 1000) {
            errorMessage = 'Weight cannot exceed 1000 kg';
            isValid = false;
        } else if (fieldName === 'size' && numValue > 1000) {
            errorMessage = 'Size cannot exceed 1000 cm³';
            isValid = false;
        } else if (fieldName === 'delivery_charge' && numValue > 100000) {
            errorMessage = 'Delivery charge cannot exceed ₹1,00,000';
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
 * Validate select field
 */
function validateSelectField(field) {
    const value = field.val();
    let isValid = true;
    let errorMessage = '';

    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').remove();

    if (!value || value === '') {
        errorMessage = 'Please select a source hub';
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
 * Get field label text
 */
function getFieldLabel(field) {
    const label = $(`label[for="${field.attr('id')}"]`);
    if (label.length) {
        return label.text().replace('*', '').trim();
    }

    // Fallback based on name attribute
    const name = field.attr('name');
    const labels = {
        'sender_name': 'Sender Name',
        'sender_phone': 'Sender Phone',
        'sender_email': 'Sender Email',
        'sender_address': 'Sender Address',
        'receiver_name': 'Receiver Name',
        'receiver_phone': 'Receiver Phone',
        'receiver_email': 'Receiver Email',
        'receiver_address': 'Receiver Address',
        'parcel_name': 'Parcel Name',
        'weight': 'Weight',
        'size': 'Size',
        'delivery_charge': 'Delivery Charge',
        'source_hub_id': 'Source Hub'
    };

    return labels[name] || name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Full form validation before submit
 */
function validateForm() {
    let isValid = true;

    // Validate sender information
    if (!validateNameField($('#sender_name'))) isValid = false;
    if (!validatePhoneField($('#sender_phone'))) isValid = false;
    if ($('#sender_email').val().trim() !== '') {
        if (!validateEmailField($('#sender_email'))) isValid = false;
    }
    if (!validateTextField($('#sender_address'))) isValid = false;

    // Validate receiver information
    if (!validateNameField($('#receiver_name'))) isValid = false;
    if (!validatePhoneField($('#receiver_phone'))) isValid = false;
    if ($('#receiver_email').val().trim() !== '') {
        if (!validateEmailField($('#receiver_email'))) isValid = false;
    }
    if (!validateTextField($('#receiver_address'))) isValid = false;

    // Validate parcel details
    if (!validateNameField($('#parcel_name'))) isValid = false;
    if (!validateNumberField($('#weight'))) isValid = false;
    if (!validateNumberField($('#size'))) isValid = false;
    if (!validateNumberField($('#delivery_charge'))) isValid = false;
    if (!validateSelectField($('#source_hub_id'))) isValid = false;

    return isValid;
}

/**
 * Initialize auto-assign functionality
 */
function initializeAutoAssign() {
    $('#autoAssignBtn').click(function(e) {
        e.preventDefault();

        const weight = $('#weight').val();
        const size = $('#size').val();
        const hubId = $('#source_hub_id').val();

        // Validate required fields before auto-assign
        if (!weight) {
            showNotification('Please enter weight first', 'warning');
            $('#weight').focus();
            return;
        }

        if (!size) {
            showNotification('Please enter size first', 'warning');
            $('#size').focus();
            return;
        }

        if (!hubId) {
            showNotification('Please select source hub first', 'warning');
            $('#source_hub_id').focus();
            return;
        }

        // Show loading state
        const btn = $(this);
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Finding Rider...');
        btn.prop('disabled', true);

        $.ajax({
            url: '/admin/parcels/find-best-rider',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                weight: weight,
                size: size,
                hub_id: hubId
            },
            success: function(response) {
                if (response.success) {
                    // Create hidden input for assigned rider if not exists
                    if ($('#assigned_rider_id').length === 0) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'assigned_rider_id',
                            id: 'assigned_rider_id',
                            value: response.rider.id
                        }).appendTo('#parcelForm');
                    } else {
                        $('#assigned_rider_id').val(response.rider.id);
                    }

                    showNotification(`✓ Rider "${response.rider.name}" assigned successfully!`, 'success');

                    // Show rider info
                    const riderInfo = `
                        <div class="alert alert-success mt-2">
                            <iconify-icon icon="solar:bicycle-line-duotone"></iconify-icon>
                            <strong>Assigned Rider:</strong> ${escapeHtml(response.rider.name)}
                            (Capacity: ${response.rider.max_weight_capacity}kg)
                        </div>
                    `;

                    if ($('#riderInfo').length) {
                        $('#riderInfo').remove();
                    }
                    $('#autoAssignBtn').after(riderInfo);

                } else {
                    showNotification('❌ ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                let message = 'Error finding rider';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
}

/**
 * Initialize numeric field validation (prevent letters)
 */
function initializeNumericValidation() {
    $('input[type="number"]').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        // Allow numbers, decimal point, and control keys
        if (charCode !== 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
        }
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'success') {
    $('.custom-notification').remove();

    let bgColor = '#10b981';
    let icon = 'check-circle';

    if (type === 'error') {
        bgColor = '#ef4444';
        icon = 'danger-circle';
    } else if (type === 'warning') {
        bgColor = '#f59e0b';
        icon = 'info-circle';
    }

    const notification = $(`
        <div class="custom-notification">
            <iconify-icon icon="solar:${icon}-line-duotone"></iconify-icon>
            <span>${escapeHtml(message)}</span>
        </div>
    `);

    notification.css({
        position: 'fixed',
        top: '20px',
        right: '20px',
        zIndex: 9999,
        backgroundColor: bgColor,
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

// Add animation styles if not present
if (!$('#parcel-create-styles').length) {
    const style = $('<style id="parcel-create-styles">')
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
        `);
    $('head').append(style);
}

// Make functions globally accessible
window.validateForm = validateForm;
window.showNotification = showNotification;
