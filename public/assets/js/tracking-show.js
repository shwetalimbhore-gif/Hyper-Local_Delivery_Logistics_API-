/**
 * Tracking Results Page JavaScript
 */

let autoRefreshInterval = null;

// Refresh status function
function refreshStatus() {
    const trackingNumber = $('#trackingNumber').data('tracking');
    const refreshBtn = $('#refreshBtn');

    // Show loading state on button
    const originalText = refreshBtn.html();
    refreshBtn.html('<span class="spinner-border spinner-border-sm"></span> Refreshing...');
    refreshBtn.prop('disabled', true);

    $.ajax({
        url: $('#refreshStatusUrl').data('url') || "/api/track/status",
        method: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            tracking_number: trackingNumber
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                showRefreshError('Failed to refresh status. Please try again.');
            }
        },
        error: function() {
            showRefreshError('Network error. Please check your connection.');
        },
        complete: function() {
            refreshBtn.html(originalText);
            refreshBtn.prop('disabled', false);
        }
    });
}

// Show refresh error
function showRefreshError(message) {
    const errorDiv = $('#refreshError');
    if (errorDiv.length) {
        errorDiv.html(`
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        errorDiv.show();
        setTimeout(() => errorDiv.fadeOut(), 3000);
    } else {
        console.error(message);
    }
}

// Start auto refresh
function startAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    autoRefreshInterval = setInterval(refreshStatus, 30000);
}

// Stop auto refresh
function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Initialize tracking page
function initTrackingPage() {
    // Start auto refresh
    startAutoRefresh();

    // Stop auto refresh when page is hidden (optional - improves performance)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
}

// Track another parcel shortcut (Ctrl + T)
function initKeyboardShortcut() {
    $(document).keydown(function(e) {
        if (e.ctrlKey && e.key === 't') {
            e.preventDefault();
            window.location.href = $('#trackAnotherUrl').data('url') || "/track";
        }
    });
}

// Document Ready
$(document).ready(function() {
    initTrackingPage();
    initKeyboardShortcut();
});
