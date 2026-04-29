/**
 * Rider Parcels Page JavaScript
 */

let currentParcelId = null;
let riderParcelsTable = null;
let selectedStatusFilter = '';

// Show message function
function showMessage(message, type) {
    let alertDiv = $('#statusMessage');
    alertDiv.removeClass('alert-info alert-success alert-danger').addClass(`alert alert-${type} alert-message`);
    alertDiv.html(`<iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon> ${message}`);
    alertDiv.show();
    setTimeout(() => alertDiv.fadeOut(), 3000);
}

// Initialize DataTable
function initParcelsDataTable() {
    if ($.fn.DataTable && $('#riderParcelsTable').length) {
        selectedStatusFilter = $('#statusFilterValue').val() || '';

        const table = $('#riderParcelsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $('#riderParcelsTable').data('ajax') || "{{ route('rider.parcels.data') }}",
                type: 'GET',
                cache: false,
                data: function(d) {
                    selectedStatusFilter = $('#statusFilterValue').val() || selectedStatusFilter || '';
                    d.status = selectedStatusFilter;
                    d.status_slug = selectedStatusFilter;
                },
                error: function(xhr) {
                    console.error('Rider parcels DataTable error:', xhr.responseText || xhr.statusText);
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'receiver_info', name: 'receiver_name', orderable: false },
                { data: 'address_short', name: 'receiver_address' },
                { data: 'weight', name: 'weight' },
                { data: 'status_badge', name: 'status_badge', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                zeroRecords: "No parcels found"
            }
        });

        riderParcelsTable = table;
        return table;
    }
    return null;
}

// Filter by status handler
function initFilterHandlers(table) {
    $(document).off('click.riderParcelFilter', '.filter-status').on('click.riderParcelFilter', '.filter-status', function(e) {
        e.preventDefault();
        const status = $(this).attr('data-status') || '';
        const label = $.trim($(this).text()) || 'All Parcels';

        selectedStatusFilter = status;
        $('#statusFilterValue').val(status);
        $('#statusFilterLabel').text(status ? label : 'All Parcels');
        updateStatusFilterUrl(status);

        // Update active state in dropdown
        $('.filter-status').removeClass('active bg-primary text-white');
        $(this).addClass('active bg-primary text-white');

        const activeTable = riderParcelsTable ||
            ($.fn.DataTable && $.fn.dataTable.isDataTable('#riderParcelsTable')
                ? $('#riderParcelsTable').DataTable()
                : table);

        if (activeTable) {
            activeTable.ajax.reload(null, true);
        } else {
            console.warn('Rider parcels DataTable is not initialized; status filter was not applied.');
        }
    });
}

function updateStatusFilterUrl(status) {
    const url = new URL(window.location.href);

    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }

    window.history.replaceState({}, '', url.toString());
}

function initInitialStatusFilter() {
    const urlStatus = new URLSearchParams(window.location.search).get('status') || '';
    const selectedStatus = $('#statusFilterValue').val() || urlStatus;
    selectedStatusFilter = selectedStatus;
    $('#statusFilterValue').val(selectedStatus);
    const matchingItem = $(`.filter-status[data-status="${selectedStatus}"]`);

    if (matchingItem.length) {
        $('.filter-status').removeClass('active bg-primary text-white');
        matchingItem.addClass('active bg-primary text-white');
        $('#statusFilterLabel').text(selectedStatus ? $.trim(matchingItem.text()) : 'All Parcels');
    }
}

// Load available statuses for modal
function loadAvailableStatuses(parcelId, callback) {
    $.ajax({
        url: `/rider/parcels/${parcelId}/available-statuses`,
        method: 'GET',
        success: function(response) {
            if (callback) callback(response);
        },
        error: function() {
            if (callback) callback([]);
        }
    });
}

// Populate status select dropdown
function populateStatusSelect(statuses) {
    const select = $('#statusSelect');
    select.empty();
    select.append('<option value="">-- Select New Status --</option>');

    if (statuses.length === 0) {
        select.append('<option disabled>No status updates available</option>');
    } else {
        statuses.forEach(function(status) {
            select.append(`<option value="${status.id}" data-slug="${status.slug}">${status.display_name}</option>`);
        });
    }
}

// Update status modal handlers
function initStatusModalHandlers() {
    // Open modal and load statuses
    $('#riderParcelsTable').on('click', '.update-status-btn', function() {
        currentParcelId = $(this).data('parcel-id');
        const trackingNumber = $(this).data('tracking');
        const currentStatusName = $(this).data('current-status-name');

        $('#modalTrackingNumber').text(trackingNumber);
        $('#modalCurrentStatus').text(currentStatusName).removeClass().addClass('badge bg-secondary');
        $('#parcelId').val(currentParcelId);
        $('#statusMessage').hide();
        $('#failureReasonDiv').hide();
        $('#statusSelect').html('<option value="">Loading...</option>');

        loadAvailableStatuses(currentParcelId, function(statuses) {
            populateStatusSelect(statuses);
        });
    });

    // Status select change - show/hide failure reason
    $('#statusSelect').off('change').on('change', function() {
        const selectedSlug = $(this).find('option:selected').data('slug');
        if (selectedSlug === 'failed-delivery') {
            $('#failureReasonDiv').slideDown();
        } else {
            $('#failureReasonDiv').slideUp();
        }
    });

    // Submit status update
    $('#submitStatusUpdate').off('click').on('click', function() {
        const statusId = $('#statusSelect').val();
        const failureReason = $('#failureReason').val();
        const notes = $('#statusNotes').val();
        const selectedSlug = $('#statusSelect').find('option:selected').data('slug');

        if (!statusId) {
            showMessage('Please select a status', 'danger');
            return;
        }

        if (selectedSlug === 'failed-delivery' && !failureReason) {
            showMessage('Please select a failure reason', 'danger');
            return;
        }

        const $btn = $('#submitStatusUpdate');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm spinner-small"></span> Updating...');

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
                    setTimeout(function() {
                        $('#updateStatusModal').modal('hide');
                        $('#riderParcelsTable').DataTable().ajax.reload(null, false);
                        $('#submitStatusUpdate').prop('disabled', false).html('Update Status');
                    }, 1500);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.error || 'Failed to update status';
                showMessage(errorMsg, 'danger');
                $btn.prop('disabled', false).html('Update Status');
            }
        });
    });
}

// Auto-hide alerts
function initAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

// Document Ready
$(document).ready(function() {
    const table = initParcelsDataTable();
    initInitialStatusFilter();
    initFilterHandlers(table);
    initStatusModalHandlers();
    initAlerts();
});
