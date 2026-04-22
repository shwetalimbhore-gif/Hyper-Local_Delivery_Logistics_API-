@extends('layouts.rider')

@section('title', 'My Parcels - DataTable')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">My Parcels</h5>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <iconify-icon icon="solar:filter-line-duotone"></iconify-icon>
                    Filter by Status
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item filter-status" href="#" data-status="">All</a></li>
                    <li><hr class="dropdown-divider"></li>
                    @php
                        $statuses = App\Models\ParcelStatus::where('is_rider_updatable', true)
                            ->orWhereIn('slug', ['delivered', 'failed-delivery', 'returned-to-hub', 'assigned'])
                            ->orderBy('sequence_order')
                            ->get();
                    @endphp
                    @foreach($statuses as $status)
                        <li><a class="dropdown-item filter-status" href="#" data-status="{{ $status->slug }}">{{ $status->display_name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="riderParcelsTable" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tracking #</th>
                        <th>Receiver</th>
                        <th>Address</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Update Parcel Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Tracking Number:</strong>
                        <span id="modalTrackingNumber" class="fw-bold"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Current Status:</strong>
                        <span id="modalCurrentStatus" class="badge"></span>
                    </div>
                </div>

                <form id="updateStatusForm">
                    @csrf
                    <input type="hidden" name="parcel_id" id="parcelId">

                    <div class="mb-3">
                        <label class="form-label fw-bold">New Status <span class="text-danger">*</span></label>
                        <select name="status_id" id="statusSelect" class="form-select" required>
                            <option value="">-- Select New Status --</option>
                        </select>
                    </div>

                    <div class="mb-3" id="failureReasonDiv" style="display: none;">
                        <label class="form-label fw-bold">Failure Reason <span class="text-danger">*</span></label>
                        <select name="failure_reason" id="failureReason" class="form-select">
                            <option value="">-- Select Reason --</option>
                            <option value="Wrong Address">Wrong Address</option>
                            <option value="Receiver Not Available">Receiver Not Available</option>
                            <option value="Phone Not Reachable">Phone Not Reachable</option>
                            <option value="Location Not Found">Location Not Found</option>
                            <option value="Parcel Damaged">Parcel Damaged</option>
                            <option value="Refused by Receiver">Refused by Receiver</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" id="statusNotes" class="form-control" rows="2"></textarea>
                    </div>

                    <div id="statusMessage" class="alert" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitStatusUpdate">Update Status</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
<style>
    .table th {
        font-weight: 600;
        color: #555;
        border-top: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#riderParcelsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('rider.parcels.datatable') }}",
            data: function(d) {
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tracking_number', name: 'tracking_number' },
            { data: 'receiver_info', name: 'receiver_name', orderable: false },
            { data: 'address_short', name: 'receiver_address' },
            { data: 'weight', name: 'weight' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
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

    // Filter by status
    $('.filter-status').click(function(e) {
        e.preventDefault();
        var status = $(this).data('status');
        $('#statusFilter').val(status);
        table.ajax.reload();
    });
});

// Status update logic (same as before)
let currentParcelId = null;

$('#riderParcelsTable').on('click', '.update-status-btn', function() {
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
            if(response.length === 0) {
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

$('#statusSelect').change(function() {
    let selectedSlug = $(this).find('option:selected').data('slug');
    if (selectedSlug === 'failed-delivery') {
        $('#failureReasonDiv').slideDown();
    } else {
        $('#failureReasonDiv').slideUp();
    }
});

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
            _token: "{{ csrf_token() }}",
            status_id: statusId,
            failure_reason: failureReason,
            notes: notes
        },
        success: function(response) {
            if(response.success) {
                showMessage(response.message, 'success');
                setTimeout(function() {
                    $('#updateStatusModal').modal('hide');
                    $('#riderParcelsTable').DataTable().ajax.reload();
                }, 1500);
            }
        },
        error: function(xhr) {
            showMessage(xhr.responseJSON?.error || 'Failed to update status', 'danger');
            $('#submitStatusUpdate').prop('disabled', false).html('Update Status');
        }
    });
});

function showMessage(message, type) {
    let alertDiv = $('#statusMessage');
    alertDiv.removeClass('alert-info alert-success alert-danger').addClass(`alert-${type}`);
    alertDiv.html(message);
    alertDiv.show();
    setTimeout(function() { alertDiv.fadeOut(); }, 3000);
}
</script>
@endpush
