/**
 * Admin Parcels Trash Page JavaScript
 */

let trashTable = null;

$(document).ready(function() {
    initAlerts();
    initTrashDataTable();
    setupEventListeners();
});

function initTrashDataTable() {
    const tableElement = $('#trashTable');

    if (!tableElement.length) return;

    // Check if DataTable is already initialized
    if ($.fn.dataTable.isDataTable(tableElement)) {
        trashTable = tableElement.DataTable();
        trashTable.destroy();
        tableElement.find('thead, tbody, tfoot').show();
    }

    trashTable = tableElement.DataTable({
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 15,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            zeroRecords: "No deleted parcels found",
            emptyTable: "Trash is empty"
        },
        processing: true,
        autoWidth: false
    });
}

function setupEventListeners() {
    // Handle restore form submission
    $('#restoreForm').off('submit').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const url = form.attr('action');
        const modalElement = document.getElementById('restoreModal');
        const modal = bootstrap.Modal.getInstance(modalElement);

        // Show loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Restoring...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Close modal
                if (modal) modal.hide();

                // Show success message
                showNotification(response.message || 'Parcel restored successfully', 'success');

                // Reload DataTable data without page refresh
                if (trashTable) {
                    trashTable.ajax.reload(null, false);
                } else {
                    // If no DataTable, reload page
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                let message = 'Error restoring parcel';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                // Still close modal
                if (modal) modal.hide();
            },
            complete: function() {
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Handle force delete form submission
    $('#forceDeleteForm').off('submit').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const url = form.attr('action');
        const modalElement = document.getElementById('forceDeleteModal');
        const modal = bootstrap.Modal.getInstance(modalElement);

        // Show loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Close modal
                if (modal) modal.hide();

                // Show success message
                showNotification(response.message || 'Parcel permanently deleted', 'success');

                // Reload DataTable data without page refresh
                if (trashTable) {
                    trashTable.ajax.reload(null, false);
                } else {
                    // If no DataTable, reload page
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                let message = 'Error deleting parcel';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                // Still close modal
                if (modal) modal.hide();
            },
            complete: function() {
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
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

function initAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

function showNotification(message, type = 'success') {
    // Remove existing notifications
    $('.custom-notification').remove();

    const notification = $(`
        <div class="custom-notification" style="
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
        ">
            <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon>
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
