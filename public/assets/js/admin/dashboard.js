/**
 * Admin Dashboard JavaScript - Enhanced with Eye-Relaxing Visuals
 * All original functionality preserved
 */

// Initialize dashboard charts
function initDashboardCharts() {
    // Monthly Trends Chart
    const monthlyCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyCtx && window.monthlyChartData) {
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: window.monthlyChartData.labels,
                datasets: [
                    {
                        label: 'Total Parcels',
                        data: window.monthlyChartData.total,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Delivered Parcels',
                        data: window.monthlyChartData.delivered,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        cornerRadius: 8,
                        bodyFont: { size: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    line: { tension: 0.4 }
                }
            }
        });
    }

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx && window.statusChartData) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: window.statusChartData.labels,
                datasets: [{
                    data: window.statusChartData.data,
                    backgroundColor: window.statusChartData.colors || [
                        '#06d6a0', '#ffd166', '#4cc9f0', '#ef476f', '#4361ee', '#7209b7'
                    ],
                    borderWidth: 0,
                    hoverOffset: 15,
                    cutout: '65%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 11, weight: '500' },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const dataset = data.datasets[0];
                                        const value = dataset.data[i];
                                        const total = dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);

                                        return {
                                            text: `${label} (${percentage}%)`,
                                            fillStyle: dataset.backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                layout: {
                    padding: 10
                }
            }
        });
    }
}

// Initialize DataTable with enhanced styling
function initParcelsTable() {
    if ($.fn.DataTable && $('#parcelsTable').length) {
        $('#parcelsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#parcelsTable').data('ajax'),
            columns: [
                { data: 'id', name: 'id' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'sender_name', name: 'sender_name' },
                { data: 'receiver_name', name: 'receiver_name' },
                { data: 'weight', name: 'weight' },
                { data: 'status_badge', name: 'status_badge', orderable: false },
                { data: 'rider_name', name: 'rider_name', orderable: false },
                { data: 'created_date', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false }
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'excel',
                    text: '<iconify-icon icon="solar:document-text-line-duotone"></iconify-icon> Excel',
                    className: 'btn btn-sm btn-success me-1',
                    titleAttr: 'Export to Excel'
                },
                {
                    extend: 'pdf',
                    text: '<iconify-icon icon="solar:document-text-line-duotone"></iconify-icon> PDF',
                    className: 'btn btn-sm btn-danger me-1',
                    titleAttr: 'Export to PDF'
                },
                {
                    extend: 'print',
                    text: '<iconify-icon icon="solar:printer-line-duotone"></iconify-icon> Print',
                    className: 'btn btn-sm btn-secondary',
                    titleAttr: 'Print Table'
                }
            ],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                search: '<iconify-icon icon="solar:magnifer-line-duotone"></iconify-icon>',
                searchPlaceholder: 'Search parcels...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                infoFiltered: '(filtered from _MAX_ total entries)'
            },
            initComplete: function() {
                // Add animation to table rows
                $('.dataTable tbody tr').css('opacity', '0').each(function(index) {
                    $(this).delay(index * 50).animate({ opacity: 1 }, 200);
                });

                // Style the search input
                $('.dataTables_filter input').addClass('form-control form-control-sm').css({
                    'padding': '0.375rem 0.75rem',
                    'border-radius': '8px',
                    'border': '1px solid #e2e8f0',
                    'transition': 'all 0.3s ease'
                });

                // Style the length select
                $('.dataTables_length select').addClass('form-select form-select-sm').css({
                    'border-radius': '8px',
                    'border': '1px solid #e2e8f0'
                });
            },
            drawCallback: function() {
                // Add hover effect to rows
                $('.dataTable tbody tr').hover(
                    function() { $(this).css('background', 'linear-gradient(90deg, #f8fafc 0%, #ffffff 100%)'); },
                    function() { $(this).css('background', ''); }
                );
            }
        });
    }
}

// Document Ready with enhanced animations
$(document).ready(function() {
    // Initialize all charts and tables (original functionality preserved)
    initDashboardCharts();
    initParcelsTable();

    // ADDITIONAL ENHANCEMENTS (Non-breaking, visual only)

    // Add fade-in animation to main content
    $('.dashboard-wrapper, .container-fluid, .row').first().css('opacity', '0').animate({ opacity: 1 }, 500);

    // Animate stat cards on scroll
    const statCards = $('.card');
    const animateCards = function() {
        statCards.each(function(index) {
            const card = $(this);
            const cardPosition = card.offset().top;
            const windowHeight = $(window).height();

            if (cardPosition < $(window).scrollTop() + windowHeight - 100) {
                if (!card.hasClass('animated')) {
                    card.addClass('animated');
                    card.css('opacity', '0').delay(index * 100).animate({ opacity: 1 }, 400);
                }
            }
        });
    };

    // Initially hide cards and animate them
    statCards.css('opacity', '0');
    animateCards();

    // Animate on scroll
    $(window).on('scroll', function() {
        animateCards();
    });

    // Add hover effect to all buttons with smooth transition
    $('.btn').css('transition', 'all 0.3s ease');

    // Enhance table rows with smooth hover effect
    $('#parcelsTable tbody').on('mouseenter', 'tr', function() {
        $(this).css({
            'transform': 'scale(1.01)',
            'box-shadow': '0 2px 8px rgba(0,0,0,0.05)',
            'transition': 'all 0.2s ease'
        });
    }).on('mouseleave', 'tr', function() {
        $(this).css({
            'transform': 'scale(1)',
            'box-shadow': 'none'
        });
    });

    // Add tooltip functionality to action buttons
    $('.btn-info, .btn-warning, .btn-danger').each(function() {
        const title = $(this).attr('title') ||
                     ($(this).hasClass('btn-info') ? 'View' :
                      $(this).hasClass('btn-warning') ? 'Edit' : 'Delete');
        $(this).attr('title', title);

        if (typeof bootstrap !== 'undefined') {
            new bootstrap.Tooltip(this, { placement: 'top', trigger: 'hover' });
        }
    });

    // Smooth number counting for dashboard stats (if any)
    const stats = $('.stat-number, .card h2');
    stats.each(function() {
        const $this = $(this);
        const finalValue = parseInt($this.text());
        if (!isNaN(finalValue)) {
            let currentValue = 0;
            const duration = 1000;
            const step = finalValue / (duration / 16);

            const updateCounter = setInterval(function() {
                currentValue += step;
                if (currentValue >= finalValue) {
                    $this.text(finalValue);
                    clearInterval(updateCounter);
                } else {
                    $this.text(Math.floor(currentValue));
                }
            }, 16);
        }
    });

    // Add loading indicator for DataTable
    $(document).ajaxStart(function() {
        $('#parcelsTable').css('opacity', '0.5');
    }).ajaxStop(function() {
        $('#parcelsTable').css('opacity', '1');
    });

    // Auto-refresh dashboard data every 2 minutes (optional)
    let autoRefreshInterval;
    const autoRefresh = $('#parcelsTable').data('auto-refresh');
    if (autoRefresh && autoRefresh === true) {
        autoRefreshInterval = setInterval(function() {
            const table = $('#parcelsTable').DataTable();
            if (table) {
                table.ajax.reload(null, false);
            }
        }, 120000); // 2 minutes
    }

    // Cleanup interval on page unload
    $(window).on('beforeunload', function() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    });

    // Add keyboard shortcuts (Ctrl+R to refresh, Ctrl+P to print)
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            location.reload();
        }
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });

    console.log('Admin Dashboard initialized successfully');
});
