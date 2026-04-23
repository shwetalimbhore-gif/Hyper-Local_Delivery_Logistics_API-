/**
 * Admin Parcels Trash Page JavaScript
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Trash page loaded - Initializing DataTable');
    initTrashDataTable();
    setupEventListeners();
});

function initTrashDataTable() {
    const tableElement = $('#trashTable');

    // Check if table exists
    if (!tableElement.length) {
        console.error('Table #trashTable not found');
        return;
    }

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable(tableElement)) {
        console.log('Destroying existing DataTable');
        tableElement.DataTable().destroy();
        tableElement.find('thead, tbody, tfoot').show();
    }

    console.log('Initializing new DataTable');

    // Initialize DataTable
    window.trashTable = tableElement.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/admin/parcels/trash-data',
            type: 'GET',
            dataType: 'json',
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                showNotification('Error loading trash data. Please refresh the page.', 'error');
            }
        },
        columns: [
            {
                data: 'checkbox',
                name: 'checkbox',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return data || '';
                }
            },
            { data: 'id', name: 'id' },
            { data: 'tracking_number', name: 'tracking_number' },
            { data: 'sender_name', name: 'sender_name' },
            { data: 'receiver_name', name: 'receiver_name' },
            { data: 'deleted_at', name: 'deleted_at' },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return data || '';
                }
            }
        ],
        order: [[1, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100, -1], [10, 15, 25, 50, 100, "All"]],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            searchPlaceholder: "Search deleted parcels...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No deleted parcels found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching deleted parcels found",
            emptyTable: "Trash is empty",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        drawCallback: function() {
            console.log('Table redrawn');
            $('#selectAll').prop('checked', false);
        }
    });
}

function setupEventListeners() {
    // Select all checkbox
    $('#selectAll').off('change').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.parcel-checkbox').prop('checked', isChecked);
    });

    // Restore form submission
    $('#restoreForm').off('submit').on('submit', function(e) {
        e.preventDefault();

        const url = $(this).attr('action');
        const modal = bootstrap.Modal.getInstance(document.getElementById('restoreModal'));

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                if (modal) modal.hide();
                showNotification(response.message || 'Parcel restored successfully', 'success');
                if (window.trashTable) {
                    window.trashTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                if (modal) modal.hide();
                let message = 'Error restoring parcel';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });

    // Force delete form submission
    $('#forceDeleteForm').off('submit').on('submit', function(e) {
        e.preventDefault();

        const url = $(this).attr('action');
        const modal = bootstrap.Modal.getInstance(document.getElementById('forceDeleteModal'));

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'DELETE'
            },
            dataType: 'json',
            success: function(response) {
                if (modal) modal.hide();
                showNotification(response.message || 'Parcel permanently deleted', 'success');
                if (window.trashTable) {
                    window.trashTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                if (modal) modal.hide();
                let message = 'Error deleting parcel';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });

    // Bulk Restore
    $('#bulkRestoreBtn').off('click').on('click', function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            showNotification('Please select at least one parcel to restore', 'warning');
            return;
        }

        if (confirm(`Are you sure you want to restore ${selectedIds.length} parcel(s)?`)) {
            bulkAction('restore', selectedIds);
        }
    });

    // Bulk Force Delete
    $('#bulkForceDeleteBtn').off('click').on('click', function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            showNotification('Please select at least one parcel to delete', 'warning');
            return;
        }

        if (confirm(`Are you sure you want to permanently delete ${selectedIds.length} parcel(s)? This cannot be undone!`)) {
            bulkAction('force-delete', selectedIds);
        }
    });
}

function getSelectedIds() {
    const ids = [];
    $('.parcel-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function bulkAction(action, ids) {
    const url = `/admin/parcels/bulk-${action}`;

    showNotification('Processing...', 'info');

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            ids: ids,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function(response) {
            showNotification(response.message, 'success');
            if (window.trashTable) {
                window.trashTable.ajax.reload(null, false);
            }
        },
        error: function(xhr) {
            showNotification('Error performing bulk action', 'error');
        }
    });
}

function showRestoreModal(id, trackingNumber) {
    $('#restoreMessage').html(`Are you sure you want to restore parcel <strong>${trackingNumber}</strong>?`);
    $('#restoreForm').attr('action', `/admin/parcels/${id}/restore`);
    const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
    modal.show();
}

function showForceDeleteModal(id, trackingNumber) {
    $('#forceDeleteMessage').html(`Are you sure you want to permanently delete parcel <strong>${trackingNumber}</strong>? This action cannot be undone.`);
    $('#forceDeleteForm').attr('action', `/admin/parcels/${id}/force-delete`);
    const modal = new bootstrap.Modal(document.getElementById('forceDeleteModal'));
    modal.show();
}

function showNotification(message, type = 'success') {
    $('.custom-notification').remove();

    const bgColor = type === 'success' ? '#28a745' : (type === 'error' ? '#dc3545' : (type === 'warning' ? '#ffc107' : '#17a2b8'));
    const textColor = type === 'warning' ? '#000' : '#fff';

    const notification = $(`
        <div class="custom-notification" style="
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: ${bgColor};
            color: ${textColor};
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
        ">
            <span>${message}</span>
        </div>
    `);

    $('body').append(notification);

    setTimeout(function() {
        notification.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}
