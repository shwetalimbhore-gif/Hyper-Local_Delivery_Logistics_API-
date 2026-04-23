/**
 * Parcels Index Page JavaScript
 */

let parcelsTable = null;

$(document).ready(function() {
    initializeDataTable();
    setupEventListeners();
});

function initializeDataTable() {
    const tableElement = $('#parcelsTable');

    if (!tableElement.length) return;

    // Check if DataTable is already initialized
    if ($.fn.dataTable.isDataTable(tableElement)) {
        parcelsTable = tableElement.DataTable();
        parcelsTable.destroy();
        tableElement.find('thead, tbody, tfoot').show();
    }

    parcelsTable = tableElement.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: tableElement.data('ajax'),
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error);
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tracking_number', name: 'tracking_number' },
            { data: 'sender_name', name: 'sender_name' },
            { data: 'receiver_name', name: 'receiver_name' },
            { data: 'weight', name: 'weight' },
            { data: 'status_html', name: 'status', orderable: false, searchable: false },
            { data: 'rider_name', name: 'rider_name', orderable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true,
        processing: true,
        serverSide: true
    });
}

function setupEventListeners() {
    // Use event delegation for dynamic elements
    $(document).on('click', '.soft-delete-btn', function(e) {
        e.preventDefault();
        const button = $(this);
        const parcelId = button.data('id');
        const trackingNumber = button.data('tracking');

        // Set modal content
        $('#softDeleteMessage').html(`Are you sure you want to move parcel <strong>${trackingNumber}</strong> to trash?`);
        $('#softDeleteForm').attr('action', `/admin/parcels/${parcelId}`);

        // Store reference to button for callback
        $('#softDeleteModal').data('button', button);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
        modal.show();
    });

    // Handle soft delete form submission
    $('#softDeleteForm').off('submit').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const url = form.attr('action');
        const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
        const button = $('#softDeleteModal').data('button');

        // Show loading state on modal button
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Moving...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'DELETE'
            },
            success: function(response) {
                // Close modal
                if (modal) modal.hide();

                // Show success message
                showNotification(response.message || 'Parcel moved to trash successfully', 'success');

                // Reload DataTable
                if (parcelsTable) {
                    parcelsTable.ajax.reload(null, false);
                } else {
                    location.reload();
                }
            },
            error: function(xhr) {
                let message = 'Error moving parcel to trash';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                // Close modal on error too
                if (modal) modal.hide();
            },
            complete: function() {
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                $('#softDeleteModal').removeData('button');
            }
        });
    });
}

function showNotification(message, type = 'success') {
    // Remove existing notifications
    $('.custom-notification').remove();

    const notification = $(`
        <div class="custom-notification alert alert-${type === 'success' ? 'success' : 'danger'}">
            <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon>
            <span>${message}</span>
        </div>
    `);

    notification.css({
        position: 'fixed',
        top: '20px',
        right: '20px',
        zIndex: 9999,
        backgroundColor: type === 'success' ? '#28a745' : '#dc3545',
        color: 'white',
        border: 'none',
        padding: '12px 20px',
        borderRadius: '8px',
        fontSize: '14px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        display: 'flex',
        alignItems: 'center',
        gap: '10px'
    });

    $('body').append(notification);

    setTimeout(() => {
        notification.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}
