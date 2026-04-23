/**
 * Rider Dashboard JavaScript
 */

// Initialize Weekly Earnings Chart
function initEarningsChart(labels, earningsData) {
    const ctx = document.getElementById('earningsChart');
    if (ctx && labels.length > 0) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Earnings (₹)',
                    data: earningsData,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₹ ' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Earnings (₹)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value;
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Day of Week'
                        }
                    }
                }
            }
        });
    }
}

// Update rider status
function initStatusToggle() {
    $('.update-status').click(function(e) {
        e.preventDefault();
        let status = $(this).data('status');
        const $btn = $(this);

        // Show loading state
        $btn.html('<span class="spinner-border spinner-border-sm"></span> Updating...');

        $.ajax({
            url: $('#statusUpdateUrl').data('url') || "{{ route('rider.update-status') }}",
            method: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status: status
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to update status');
                }
            },
            error: function() {
                alert('Error updating status');
            },
            complete: function() {
                $btn.html(status);
            }
        });
    });
}

// Auto-refresh dashboard every 60 seconds (optional)
let autoRefreshInterval;

function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(function() {
        location.reload();
    }, 60000);
}

// Document Ready
$(document).ready(function() {
    // Initialize chart with data from window object
    if (window.weeklyEarningsData) {
        initEarningsChart(window.weeklyEarningsData.labels, window.weeklyEarningsData.values);
    }

    // Initialize status toggle
    initStatusToggle();

    // Start auto-refresh (optional - comment out if not needed)
    // startAutoRefresh();
});
