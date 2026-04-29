/**
 * Rider Dashboard JavaScript
 */

// Initialize Weekly Earnings Chart
function initEarningsChart(labels, earningsData) {
    const ctx = document.getElementById('earningsChart');
    if (ctx && labels && labels.length > 0) {
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
    $(document).on('click', '.update-status-link', function(e) {
        e.preventDefault();

        let status = $(this).data('status');
        let $dropdownBtn = $('#statusDropdown');
        let originalText = $dropdownBtn.html();

        // Show loading state
        $dropdownBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');
        $dropdownBtn.prop('disabled', true);

        $.ajax({
            url: $('#statusUpdateUrl').data('url'),
            method: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status: status
            },
            success: function(response) {
                if (response.success) {
                    // Update button text
                    let statusText = status.charAt(0).toUpperCase() + status.slice(1);
                    let statusColor = status === 'available' ? 'text-success' : (status === 'busy' ? 'text-warning' : 'text-danger');
                    let icon = status === 'available' ? 'check-circle-line-duotone' : (status === 'busy' ? 'clock-circle-line-duotone' : 'power-off-line-duotone');

                    $dropdownBtn.html(`<iconify-icon icon="solar:${icon}" class="me-1"></iconify-icon> Status: <span class="${statusColor}">${statusText}</span>`);

                    // Show success message
                    showToast('Status updated successfully!', 'success');
                } else {
                    showToast(response.message || 'Failed to update status', 'error');
                    $dropdownBtn.html(originalText);
                }
            },
            error: function(xhr) {
                console.error('Status update error:', xhr);
                showToast('Error updating status. Please try again.', 'error');
                $dropdownBtn.html(originalText);
            },
            complete: function() {
                $dropdownBtn.prop('disabled', false);
            }
        });
    });
}

// Show toast notification
function showToast(message, type) {
    // Remove existing toast
    if ($('#statusToast').length) {
        $('#statusToast').remove();
    }

    let bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    let icon = type === 'success' ? 'check-circle-line-duotone' : 'danger-circle-line-duotone';

    let toastHtml = `
        <div id="statusToast" class="toast align-items-center text-white ${bgClass} border-0 position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 280px;" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <iconify-icon icon="solar:${icon}" class="me-2"></iconify-icon>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    $('body').append(toastHtml);
    const toast = new bootstrap.Toast(document.getElementById('statusToast'), { delay: 3000 });
    toast.show();

    setTimeout(function() {
        $('#statusToast').remove();
    }, 3000);
}

// Document Ready
$(document).ready(function() {
    // Initialize chart with data from window object
    if (window.weeklyEarningsData && window.weeklyEarningsData.labels.length > 0) {
        initEarningsChart(window.weeklyEarningsData.labels, window.weeklyEarningsData.values);
    }

    // Initialize status toggle
    initStatusToggle();
});
