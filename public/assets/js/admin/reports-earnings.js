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

        // Initialize on page load
        if (periodSelect.value === 'custom') {
            if (startDateDiv) startDateDiv.style.display = 'block';
            if (endDateDiv) endDateDiv.style.display = 'block';
        }
    }
}

// Initialize Daily Earnings Chart
function initDailyEarningsChart(labels, earningsData, deliveriesData) {
    const dailyCtx = document.getElementById('dailyEarningsChart');
    if (dailyCtx && labels && labels.length > 0) {
        const datasets = [
            {
                label: 'Earnings (₹)',
                data: earningsData,
                type: 'line',
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
                type: 'bar',
                backgroundColor: 'rgba(16, 185, 129, 0.5)',
                borderRadius: 4,
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
                interaction: {
                    mode: 'index',
                    intersect: false
                },
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
                                return '₹' + value.toLocaleString();
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
                        },
                        grid: {
                            drawOnChartArea: false
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
    } else if (dailyCtx) {
        dailyCtx.parentElement.innerHTML = '<div class="text-center py-5 text-muted">No earnings data available for this period</div>';
    }
}

// Initialize Monthly Earnings Chart
function initMonthlyEarningsChart(labels, earningsData) {
    const monthlyCtx = document.getElementById('monthlyEarningsChart');
    if (monthlyCtx && labels && labels.length > 0 && earningsData && earningsData.length > 0) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Earnings (₹)',
                    data: earningsData,
                    backgroundColor: '#4f46e5',
                    borderRadius: 8,
                    borderWidth: 0
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
                                return '₹' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    } else if (monthlyCtx) {
        monthlyCtx.parentElement.innerHTML = '<div class="text-center py-5 text-muted">No monthly earnings data available</div>';
    }
}

// Initialize Payment Method Chart
function initPaymentMethodChart(labels, data, colors) {
    const paymentCtx = document.getElementById('paymentMethodChart');
    if (paymentCtx && labels && labels.length > 0 && data && data.length > 0) {
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
    } else if (paymentCtx) {
        paymentCtx.parentElement.innerHTML = '<div class="text-center py-3 text-muted">No payment data available</div>';
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
        initMonthlyEarningsChart(
            window.monthlyEarningsData.labels,
            window.monthlyEarningsData.earnings
        );
    }

    if (window.paymentMethodData) {
        initPaymentMethodChart(
            window.paymentMethodData.labels,
            window.paymentMethodData.values,
            window.paymentMethodData.colors
        );
    }
}

// Document Ready
$(document).ready(function() {
    initDateRangeToggle();
    initCharts();
});
