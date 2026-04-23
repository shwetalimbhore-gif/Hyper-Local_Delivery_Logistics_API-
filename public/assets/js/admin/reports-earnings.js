/**
 * Admin Earnings Reports Page JavaScript
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

// Initialize Daily Earnings Chart
function initDailyEarningsChart(labels, earningsData, deliveriesData) {
    const dailyCtx = document.getElementById('dailyEarningsChart');
    if (dailyCtx && labels.length > 0) {
        const datasets = [
            {
                label: 'Earnings (₹)',
                data: earningsData,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }
        ];

        if (deliveriesData && deliveriesData.length > 0) {
            datasets.push({
                label: 'Deliveries',
                data: deliveriesData,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            });
        }

        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
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
                                const label = context.dataset.label || '';
                                const value = context.raw;
                                if (label.includes('Earnings')) {
                                    return label + ': ₹' + value.toFixed(2);
                                }
                                return label + ': ' + value;
                            }
                        }
                    }
                },
                scales: {
                    y: {
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
                    y1: {
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Number of Deliveries'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
}

// Initialize Monthly Earnings Chart
function initMonthlyEarningsChart(monthlyData) {
    const monthlyCtx = document.getElementById('monthlyEarningsChart');
    if (monthlyCtx && monthlyData.labels.length > 0) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Earnings (₹)',
                    data: monthlyData.values,
                    backgroundColor: '#4f46e5',
                    borderRadius: 8
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
                    }
                }
            }
        });
    }
}

// Initialize Payment Method Chart
function initPaymentMethodChart(labels, data, colors) {
    const paymentCtx = document.getElementById('paymentMethodChart');
    if (paymentCtx && labels.length > 0) {
        new Chart(paymentCtx, {
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
                                return context.label + ': ₹' + context.raw.toFixed(2);
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
    if (window.dailyEarningsData) {
        initDailyEarningsChart(
            window.dailyEarningsData.labels,
            window.dailyEarningsData.earnings,
            window.dailyEarningsData.deliveries
        );
    }

    if (window.monthlyEarningsData) {
        initMonthlyEarningsChart(window.monthlyEarningsData);
    }

    if (window.paymentMethodData) {
        initPaymentMethodChart(
            window.paymentMethodData.labels,
            window.paymentMethodData.values,
            window.paymentMethodData.colors
        );
    }
}

// Auto-hide alerts
function initAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

// Format currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2);
}

// Document Ready
$(document).ready(function() {
    initDateRangeToggle();
    initCharts();
    initAlerts();
});
