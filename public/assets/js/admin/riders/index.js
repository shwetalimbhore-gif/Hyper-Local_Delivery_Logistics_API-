/**
 * Rider Index Page JavaScript
 * File: public/assets/js/admin/riders/index.js
 */

let ridersTable = null;
let deleteRiderId = null;

$(document).ready(function() {
    initializeDataTable();
    setupEventListeners();
});

/**
 * Initialize DataTable
 */
function initializeDataTable() {
    const tableElement = $('#ridersTable');

    if (!tableElement.length) return;

    // Destroy existing DataTable if any
    if ($.fn.DataTable && $.fn.dataTable.isDataTable(tableElement)) {
        ridersTable = tableElement.DataTable();
        ridersTable.destroy();
        tableElement.find('thead, tbody, tfoot').show();
    }

    ridersTable = tableElement.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: tableElement.data('ajax'),
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error);
                showNotification('Error loading data', 'error');
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'employee_id', name: 'employee_id' },
            { data: 'full_name', name: 'user.name' },
            { data: 'email', name: 'user.email' },
            { data: 'phone', name: 'user.phone' },
            { data: 'hub_name', name: 'hub.name' },
            { data: 'vehicle_badge', name: 'vehicle_type', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'total_deliveries', name: 'total_deliveries' },
            { data: 'rating_display', name: 'rating', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            searchPlaceholder: "Search riders...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No riders found",
            paginate: {
                first: '<iconify-icon icon="solar:double-alt-arrow-left-line-duotone"></iconify-icon>',
                last: '<iconify-icon icon="solar:double-alt-arrow-right-line-duotone"></iconify-icon>',
                next: '<iconify-icon icon="solar:alt-arrow-right-line-duotone"></iconify-icon>',
                previous: '<iconify-icon icon="solar:alt-arrow-left-line-duotone"></iconify-icon>'
            }
        },
        drawCallback: function() {
            // Refresh icons after table redraw
            if (typeof iconify !== 'undefined') {
                iconify.scan();
            }
        }
    });

    // Make DataTable accessible globally
    window.ridersDataTable = ridersTable;
}

/**
 * Setup all event listeners
 */
function setupEventListeners() {
    // Soft delete button click - CHECK STATUS
    $(document).on('click', '.soft-delete-btn', function(e) {
        e.preventDefault();

        const button = $(this);
        const riderId = button.data('id');
        const riderName = button.data('name');
        const employeeId = button.data('employee-id');
        const riderStatus = button.data('status'); // Add this data attribute

        // ✅ CHECK IF RIDER IS BUSY
        if (riderStatus === 'busy') {
            Swal.fire({
                title: 'Cannot Delete',
                text: 'This rider is currently BUSY with active deliveries. Please wait until deliveries are completed.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        deleteRiderId = riderId;
        $('#softDeleteMessage').html(`Are you sure you want to move rider <strong>${escapeHtml(riderName)}</strong> (${escapeHtml(employeeId)}) to trash?`);

        const modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
        modal.show();
    });

    // Confirm delete button
    $('#confirmSoftDeleteBtn').off('click').on('click', function() {
        if (!deleteRiderId) return;

        const btn = $(this);
        const originalText = btn.html();

        // Show loading state
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Moving...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/riders/${deleteRiderId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');

                    // Reload DataTable
                    if (ridersTable) {
                        ridersTable.ajax.reload(null, false);
                    }
                } else {
                    showNotification(response.message, 'error');
                }

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
                modal.hide();
                deleteRiderId = null;
            },
            error: function(xhr) {
                let message = 'Error deleting rider';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
                modal.hide();
                deleteRiderId = null;
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

    // Modal close event - reset
    $('#softDeleteModal').on('hidden.bs.modal', function() {
        deleteRiderId = null;
        $('#softDeleteMessage').html('');
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'success') {
    // Remove existing notifications
    $('.custom-notification').remove();

    const notification = $(`
        <div class="custom-notification ${type}">
            <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon>
            <span>${escapeHtml(message)}</span>
        </div>
    `);

    $('body').append(notification);

    setTimeout(() => {
        notification.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Refresh DataTable manually
 */
function refreshRidersTable() {
    if (ridersTable) {
        ridersTable.ajax.reload(null, false);
    }
}

/**
 * Export table to CSV
 */
function exportToCSV() {
    if (ridersTable) {
        const data = ridersTable.rows().data().toArray();
        const headers = ['ID', 'Employee ID', 'Name', 'Email', 'Phone', 'Hub', 'Vehicle', 'Status', 'Total Deliveries', 'Rating'];

        let csv = headers.join(',') + '\n';

        data.forEach(row => {
            csv += `${row.id},${row.employee_id},"${row.full_name}",${row.email},${row.phone},"${row.hub_name}",${row.vehicle_type},${row.status},${row.total_deliveries},${row.rating}\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'riders_export.csv';
        a.click();
        window.URL.revokeObjectURL(url);

        showNotification('Export completed successfully', 'success');
    }
}

// Make functions globally accessible
window.refreshRidersTable = refreshRidersTable;
window.exportToCSV = exportToCSV;
window.showNotification = showNotification;
