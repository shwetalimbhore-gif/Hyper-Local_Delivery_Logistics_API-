/**
 * Admin Dashboard JavaScript
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
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Delivered Parcels',
                        data: window.monthlyChartData.delivered,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
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
                    backgroundColor: window.statusChartData.colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
}

// Initialize DataTable
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
            dom: 'Bfrtip',
            buttons: ['excel', 'pdf', 'print']
        });
    }
}

// Document Ready
$(document).ready(function() {
    initDashboardCharts();
    initParcelsTable();
});
