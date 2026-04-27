/**
 * Hub Index Page JavaScript
 * File: public/assets/js/admin/hubs/index.js
 */

let hubsTable = null;
let deleteHubId = null;

$(document).ready(function() {
    initializeDataTable();
    setupEventListeners();
});

/**
 * Initialize DataTable
 */
function initializeDataTable() {
    const tableElement = $('#hubsTable');

    if (!tableElement.length) return;

    // Destroy existing DataTable if any
    if ($.fn.DataTable && $.fn.dataTable.isDataTable(tableElement)) {
        hubsTable = tableElement.DataTable();
        hubsTable.destroy();
        tableElement.find('thead, tbody, tfoot').show();
    }

    hubsTable = tableElement.DataTable({
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
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'manager_name', name: 'manager_name' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'riders_count', name: 'riders_count', orderable: false, searchable: false },
            { data: 'parcels_count', name: 'parcels_count', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            searchPlaceholder: "Search hubs...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No hubs found",
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
    window.hubsDataTable = hubsTable;
}

/**
 * Setup all event listeners
 */
function setupEventListeners() {
    // Soft delete button click
    $(document).on('click', '.soft-delete-btn', function(e) {
        e.preventDefault();

        const button = $(this);
        deleteHubId = button.data('id');
        let hubCode = button.data('code');
        let hubName = button.data('name');

        $('#softDeleteMessage').html(`Are you sure you want to move hub <strong>${hubCode}</strong> (${hubName}) to trash?`);

        const modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
        modal.show();
    });

    // Confirm delete button
    $('#confirmSoftDeleteBtn').off('click').on('click', function() {
        if (!deleteHubId) return;

        const btn = $(this);
        const originalText = btn.html();

        // Show loading state
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Moving...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/hubs/${deleteHubId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    if (hubsTable) {
                        hubsTable.ajax.reload(null, false);
                    }
                } else {
                    showNotification(response.message, 'error');
                }

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
                modal.hide();
                deleteHubId = null;
            },
            error: function(xhr) {
                let message = 'Error deleting hub';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
                modal.hide();
                deleteHubId = null;
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

    // Toggle status with AJAX
    $(document).on('click', '.toggle-status-btn', function(e) {
        e.preventDefault();

        const button = $(this);
        const url = button.attr('href');
        const isActive = button.data('active');

        // Confirmation dialog
        if (!confirm(`Are you sure you want to ${isActive ? 'deactivate' : 'activate'} this hub?`)) {
            return false;
        }

        // Show loading state on button
        const originalHtml = button.html();
        button.html('<span class="spinner-border spinner-border-sm"></span>');
        button.prop('disabled', true);

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    if (hubsTable) {
                        hubsTable.ajax.reload(null, false);
                    }
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function(xhr) {
                let message = 'Error toggling status';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            },
            complete: function() {
                button.html(originalHtml);
                button.prop('disabled', false);
            }
        });
    });

    // View button click (optional analytics)
    $(document).on('click', '.view-hub-btn', function(e) {
        const hubId = $(this).data('id');
        console.log('Viewing hub:', hubId);
        // Add analytics or logging here
    });

    // Edit button click (optional analytics)
    $(document).on('click', '.edit-hub-btn', function(e) {
        const hubId = $(this).data('id');
        console.log('Editing hub:', hubId);
        // Add analytics or logging here
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

    // Auto remove after 5 seconds
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
 * Refresh DataTable manually (useful for external calls)
 */
function refreshHubsTable() {
    if (hubsTable) {
        hubsTable.ajax.reload(null, false);
    }
}

// Make functions globally accessible
window.refreshHubsTable = refreshHubsTable;
window.showNotification = showNotification;
