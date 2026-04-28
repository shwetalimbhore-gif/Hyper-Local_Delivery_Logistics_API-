/**
 * Rider Show Page JavaScript
 * File: public/assets/js/admin/riders/show.js
 */

let currentDeleteId = null;

$(document).ready(function() {
    initializeTooltips();
    initializeProgressBar();
    initializeStatusBadge();
    setupDeleteHandler();
});

/**
 * Initialize tooltips for better UX
 */
function initializeTooltips() {
    $('.btn-warning').attr('title', 'Edit Rider');
    $('.btn-danger').attr('title', 'Delete Rider');
    $('.btn-secondary').attr('title', 'Back to List');
    $('.btn-info').attr('title', 'View Parcel Details');

    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Initialize progress bar animation
 */
function initializeProgressBar() {
    const progressBar = document.querySelector('.progress-bar-custom');
    if (progressBar) {
        const width = progressBar.style.width;
        void progressBar.offsetWidth;
    }
}

/**
 * Initialize status badge with animations
 */
function initializeStatusBadge() {
    const availableBadge = $('.badge-available');
    if (availableBadge.length) {
        availableBadge.css('animation', 'pulse 2s infinite');
    }
}

/**
 * Setup delete confirmation handler
 */
function setupDeleteHandler() {
    $('#confirmDeleteBtn').on('click', function() {
        if (!currentDeleteId) return;

        const btn = $(this);
        const originalText = btn.html();

        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/riders/${currentDeleteId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '/admin/riders';
                    });
                } else {
                    $('#deleteErrorMsg').text(response.message);
                    $('#deleteErrorContainer').show();

                    btn.html(originalText);
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                let message = 'Error deleting rider';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                $('#deleteErrorMsg').text(message);
                $('#deleteErrorContainer').show();

                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
}

/**
 * Show delete confirmation modal
 */
function showDeleteModal(riderId, riderName, riderStatus) {
    currentDeleteId = riderId;
    document.getElementById('deleteRiderName').innerHTML = `<strong>${escapeHtml(riderName)}</strong>`;
    $('#deleteErrorContainer').hide();

    // ✅ CHECK IF RIDER IS BUSY
    if (riderStatus === 'busy') {
        $('#deleteErrorMsg').text('Cannot delete rider because they are currently BUSY with active deliveries. Please wait until deliveries are completed.');
        $('#deleteErrorContainer').show();

        // Disable delete button
        $('#confirmDeleteBtn').prop('disabled', true);
        $('#confirmDeleteBtn').addClass('opacity-50');
    } else {
        // Enable delete button
        $('#confirmDeleteBtn').prop('disabled', false);
        $('#confirmDeleteBtn').removeClass('opacity-50');
    }

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

/**
 * Setup delete handler
 */
function setupDeleteHandler() {
    $('#confirmDeleteBtn').on('click', function() {
        if (!currentDeleteId) return;

        // ✅ Prevent deletion if button is disabled (busy rider)
        if ($(this).prop('disabled')) {
            showNotification('Cannot delete a busy rider', 'error');
            return;
        }

        const btn = $(this);
        const originalText = btn.html();

        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/riders/${currentDeleteId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '/admin/riders';
                    });
                } else {
                    $('#deleteErrorMsg').text(response.message);
                    $('#deleteErrorContainer').show();

                    btn.html(originalText);
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                let message = 'Error deleting rider';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                $('#deleteErrorMsg').text(message);
                $('#deleteErrorContainer').show();

                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'success') {
    $('.custom-notification').remove();

    const notification = $(`
        <div class="custom-notification ${type}">
            <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon>
            <span>${escapeHtml(message)}</span>
        </div>
    `);

    notification.css({
        position: 'fixed',
        top: '20px',
        right: '20px',
        zIndex: 9999,
        backgroundColor: type === 'success' ? '#10b981' : '#ef4444',
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

/**
 * Print rider details
 */
function printRiderDetails() {
    window.print();
}

/**
 * Copy employee ID to clipboard
 */
function copyEmployeeId(employeeId) {
    navigator.clipboard.writeText(employeeId).then(() => {
        showNotification('Employee ID copied to clipboard!', 'success');
    }).catch(() => {
        showNotification('Failed to copy employee ID', 'error');
    });
}

// Add global functions
window.showDeleteModal = showDeleteModal;
window.printRiderDetails = printRiderDetails;
window.copyEmployeeId = copyEmployeeId;

// Add animation styles dynamically
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

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.03);
            opacity: 0.9;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .custom-notification {
        z-index: 10000;
    }
`;
document.head.appendChild(style);
