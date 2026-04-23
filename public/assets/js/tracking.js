/**
 * Tracking Page JavaScript
 */

// Form validation before submit
function validateTrackingForm(form) {
    const trackingInput = form.querySelector('input[name="tracking_number"]');
    if (!trackingInput.value.trim()) {
        showTrackingError('Please enter a tracking number');
        trackingInput.focus();
        return false;
    }
    return true;
}

// Show error message
function showTrackingError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger tracking-alert alert-dismissible fade show';
    alertDiv.innerHTML = `
        <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const formContainer = document.querySelector('.tracking-form');
    if (formContainer) {
        formContainer.insertBefore(alertDiv, formContainer.firstChild);
    }

    setTimeout(() => {
        if (alertDiv) alertDiv.remove();
    }, 5000);
}

// Add loading state to submit button
function setButtonLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Tracking...';
    } else {
        button.disabled = false;
        button.innerHTML = '<iconify-icon icon="solar:search-line-duotone" class="me-2"></iconify-icon> Track Parcel';
    }
}

// Initialize form submission handler
function initTrackingForm() {
    const form = document.querySelector('form[action*="track"]');
    const submitBtn = form?.querySelector('button[type="submit"]');

    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateTrackingForm(this)) {
                e.preventDefault();
            } else if (submitBtn) {
                setButtonLoading(submitBtn, true);
            }
        });
    }
}

// Add autocomplete tracking number from URL parameter
function initTrackingFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const trackingNumber = urlParams.get('tracking');

    if (trackingNumber) {
        const trackingInput = document.querySelector('input[name="tracking_number"]');
        if (trackingInput) {
            trackingInput.value = trackingNumber;
            const form = document.querySelector('form[action*="track"]');
            if (form) {
                form.submit();
            }
        }
    }
}

// Document Ready
$(document).ready(function() {
    initTrackingForm();
    initTrackingFromUrl();
});
