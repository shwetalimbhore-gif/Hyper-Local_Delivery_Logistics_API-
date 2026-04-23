/**
 * Rider Parcels JavaScript
 */

let currentParcelId = null;

// Show message
function showMessage(message, type) {
    let alertDiv = $('#statusMessage');
    alertDiv.removeClass('alert-info alert-success alert-danger').addClass(`alert-${type}`);
    alertDiv.html(`<iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon> ${message}`);
    alertDiv.show();
    setTimeout(() => alertDiv.fadeOut(), 3000);
}

// Update status button click handler
$(document).on('click', '.update-status-btn', function() {
    currentParcelId = $(this).data('parcel-id');
    let trackingNumber = $(this).data('tracking');
    let currentStatusName = $(this).data('current-status-name');

    $('#modalTrackingNumber').text(trackingNumber);
    $('#modalCurrentStatus').text(currentStatusName).removeClass().addClass('badge bg-secondary');
    $('#parcelId').val(currentParcelId);
    $('#statusMessage').hide();
    $('#failureReasonDiv').hide();
    $('#statusSelect').html('<option value="">Loading...</option>');

    $.ajax({
        url: `/rider/parcels/${currentParcelId}/available-statuses`,
        method: 'GET',
        success: function(response) {
            let select = $('#statusSelect');
            select.empty();
            select.append('<option value="">-- Select New Status --</option>');
            if (response.length === 0) {
                select.append('<option disabled>No status updates available</option>');
            } else {
                response.forEach(function(status) {
                    select.append(`<option value="${status.id}" data-slug="${status.slug}">${status.display_name}</option>`);
                });
            }
        },
        error: function() {
            $('#statusSelect').html('<option disabled>Error loading statuses</option>');
        }
    });
});

// Status select change handler
$('#statusSelect').change(function() {
    let selectedSlug = $(this).find('option:selected').data('slug');
    if (selectedSlug === 'failed-delivery') {
        $('#failureReasonDiv').slideDown();
    } else {
        $('#failureReasonDiv').slideUp();
    }
});

// Submit status update
$('#submitStatusUpdate').click(function() {
    let statusId = $('#statusSelect').val();
    let failureReason = $('#failureReason').val();
    let notes = $('#statusNotes').val();
    let selectedSlug = $('#statusSelect').find('option:selected').data('slug');

    if (!statusId) {
        showMessage('Please select a status', 'danger');
        return;
    }

    if (selectedSlug === 'failed-delivery' && !failureReason) {
        showMessage('Please select a failure reason', 'danger');
        return;
    }

    $('#submitStatusUpdate').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

    $.ajax({
        url: `/rider/parcels/${currentParcelId}/update-status`,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            status_id: statusId,
            failure_reason: failureReason,
            notes: notes
        },
        success: function(response) {
            if (response.success) {
                showMessage(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            showMessage(xhr.responseJSON?.error || 'Failed to update status', 'danger');
            $('#submitStatusUpdate').prop('disabled', false).html('Update Status');
        }
    });
});

// Initialize DataTable
function initParcelsDataTable() {
    if ($.fn.DataTable && $('#riderParcelsTable').length) {
        $('#riderParcelsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#riderParcelsTable').data('ajax'),
            columns: [
                { data: 'id', name: 'id' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'receiver_info', name: 'receiver_name', orderable: false },
                { data: 'address_short', name: 'receiver_address' },
                { data: 'weight', name: 'weight' },
                { data: 'status_badge', name: 'status_badge', orderable: false },
                { data: 'action', name: 'action', orderable: false }
            ],
            order: [[0, 'desc']],
            pageLength: 15
        });
    }
}

// Filter by status
$('.filter-status').click(function(e) {
    e.preventDefault();
    var status = $(this).data('status');
    window.location.href = `/rider/parcels?status=${status}`;
});

// Document Ready
$(document).ready(function() {
    initParcelsDataTable();
});
