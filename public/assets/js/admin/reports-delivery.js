/**
 * Admin Delivery Reports Page JavaScript
 */

// Show/hide custom date range
function initDateRangeToggle() {
    const periodSelect = document.getElementById('periodSelect');
    const startDateDiv = document.getElementById('startDateDiv');
    const endDateDiv = document.getElementById('endDateDiv');

    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                if (startDateDiv) startDateDiv.style.display = 'block';
                if (endDateDiv) endDateDiv.style.display = 'block';
            } else {
                if (startDateDiv) startDateDiv.style.display = 'none';
                if (endDateDiv) endDateDiv.style.display = 'none';
            }
        });
    }
}

// Initialize Delivery Trends Chart
function initDeliveryTrendsChart(labels, data) {
    const trendsCtx = document.getElementById('deliveryTrendsChart');
    if (trendsCtx && labels.length > 0) {
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Deliveries',
                    data: data,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Deliveries'
                        },
                        ticks: {
                            stepSize: 1
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
    }
}

// Initialize Status Distribution Chart
function initStatusChart(labels, data, colors) {
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx && labels.length > 0) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' parcels';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Initialize all charts
function initCharts() {
    if (window.deliveryTrendsData) {
        initDeliveryTrendsChart(
            window.deliveryTrendsData.labels,
            window.deliveryTrendsData.values
        );
    }

    if (window.statusChartData) {
        initStatusChart(
            window.statusChartData.labels,
            window.statusChartData.data,
            window.statusChartData.colors
        );
    }
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
    initCharts();
    initAlerts();
});
