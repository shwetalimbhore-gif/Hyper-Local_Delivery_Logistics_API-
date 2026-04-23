/**
 * Tracking Page JavaScript
 */

// Refresh status
function refreshStatus() {
    let trackingNumber = $('#trackingNumber').val();

    $.ajax({
        url: '/api/track',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            tracking_number: trackingNumber
        },
        success: function(response) {
            if (response.success) {
                updateTrackingDisplay(response.data);
            }
        },
        error: function() {
            console.log('Failed to refresh status');
        }
    });
}

// Update tracking display
function updateTrackingDisplay(data) {
    // Update status badge
    $('#statusBadge').text(data.status.name).css('background-color', data.status.color);

    // Update timeline
    updateTimeline(data.status.slug);

    // Update last updated
    $('#lastUpdated').text(data.last_updated);
}

// Update timeline based on status
function updateTimeline(statusSlug) {
    const steps = ['pending', 'assigned', 'picked-up', 'out-for-delivery', 'delivered'];
    const currentIndex = steps.indexOf(statusSlug);

    $('.timeline-step').each(function(index) {
        $(this).removeClass('completed active');
        if (index < currentIndex) {
            $(this).addClass('completed');
        } else if (index === currentIndex) {
            $(this).addClass('active');
        }
    });
}

// Auto refresh every 30 seconds
let refreshInterval;

function startAutoRefresh() {
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(refreshStatus, 30000);
}

// Document Ready
$(document).ready(function() {
    startAutoRefresh();

    // Refresh button click
    $('.refresh-btn').click(function() {
        refreshStatus();
    });
});
