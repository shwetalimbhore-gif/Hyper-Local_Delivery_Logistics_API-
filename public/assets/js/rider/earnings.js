/**
 * Rider Earnings Page JavaScript
 */

// Show/hide custom date range
function initDateRangeToggle() {
    const periodSelect = document.querySelector('select[name="period"]');
    const customDateRange = document.getElementById('customDateRange');
    const customDateRangeEnd = document.getElementById('customDateRangeEnd');

    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                if (customDateRange) customDateRange.style.display = 'block';
                if (customDateRangeEnd) customDateRangeEnd.style.display = 'block';
            } else {
                if (customDateRange) customDateRange.style.display = 'none';
                if (customDateRangeEnd) customDateRangeEnd.style.display = 'none';
            }
        });

        // Initialize on page load
        if (periodSelect.value === 'custom') {
            if (customDateRange) customDateRange.style.display = 'block';
            if (customDateRangeEnd) customDateRangeEnd.style.display = 'block';
        } else {
            if (customDateRange) customDateRange.style.display = 'none';
            if (customDateRangeEnd) customDateRangeEnd.style.display = 'none';
        }
    }
}

// Initialize Daily Earnings Chart
function initEarningsChart(labels, earningsData) {
    const ctx = document.getElementById('earningsChart');
    if (ctx && labels.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Your Earnings (₹)',
                    data: earningsData,
                    backgroundColor: '#4f46e5',
                    borderRadius: 8,
                    borderWidth: 0
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
                            text: 'Date'
                        }
                    }
                }
            }
        });
    } else if (ctx) {
        // Show empty state if no data
        document.getElementById('earningsChart').parentElement.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <iconify-icon icon="solar:chart-line-duotone"></iconify-icon>
                </div>
                <p class="empty-state-text">No earnings data available for this period</p>
            </div>
        `;
    }
}

// Format currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2);
}

// Auto-hide alerts
function initAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

// Document Ready
$(document).ready(function() {
    initDateRangeToggle();
    initAlerts();

    // Initialize chart with data from window object
    if (window.dailyEarningsData) {
        initEarningsChart(window.dailyEarningsData.labels, window.dailyEarningsData.values);
    }
});
