@extends('layouts.rider')

@section('title', 'My Parcels')

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
                    @foreach($statuses as $status)
                        <li><a class="dropdown-item filter-status" href="#" data-status="{{ $status->slug }}">{{ $status->display_name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover" id="riderParcelsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tracking #</th>
                        <th>Receiver</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcels as $parcel)
                    <tr>
                        <td>{{ $parcel->id }}</small></td>
                        <td><span class="fw-bold">{{ $parcel->tracking_number }}</span></small></td>
                        <td>{{ $parcel->receiver_name }}</small></td>
                        <td>{{ $parcel->receiver_phone }}</small></td>
                        <td>{{ Str::limit($parcel->receiver_address, 50) }}</small></td>
                        <td>{{ $parcel->weight }} kg</small></td>
                        <td>
                            @php
                                $status = $parcel->status;
                                $badgeClass = '';
                                switch($status->slug) {
                                    case 'assigned': $badgeClass = 'bg-info'; break;
                                    case 'picked-up': $badgeClass = 'bg-primary'; break;
                                    case 'out-for-delivery': $badgeClass = 'bg-secondary'; break;
                                    case 'delivered': $badgeClass = 'bg-success'; break;
                                    case 'failed-delivery': $badgeClass = 'bg-danger'; break;
                                    case 'returned-to-hub': $badgeClass = 'bg-dark'; break;
                                    default: $badgeClass = 'bg-secondary';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $status->display_name }}</span>
                         </small>
                        <td>
                            @php
                                $canUpdate = in_array($status->slug, ['assigned', 'picked-up', 'out-for-delivery', 'failed-delivery']);
                            @endphp
                            @if($canUpdate)
                                <button type="button" class="btn btn-sm btn-primary update-status-btn"
                                        data-parcel-id="{{ $parcel->id }}"
                                        data-tracking="{{ $parcel->tracking_number }}"
                                        data-current-status="{{ $status->slug }}"
                                        data-current-status-name="{{ $status->display_name }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateStatusModal">
                                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                    Update
                                </button>
                            @else
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <iconify-icon icon="solar:lock-line-duotone"></iconify-icon>
                                    No Action
                                </button>
                            @endif
                         </small>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Status Modal (same as before) -->
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
                        <select name="status_id" id="statusSelect" class="form-select form-select-lg" required>
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
                        <textarea name="notes" id="statusNotes" class="form-control" rows="2"
                                  placeholder="Any additional information..."></textarea>
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

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#riderParcelsTable').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
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
            window.location.href = "{{ route('rider.parcels.index') }}?status=" + status;
        });
    });

    // Status update logic (same as before)
    let currentParcelId = null;

    $('.update-status-btn').click(function() {
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
                response.forEach(function(status) {
                    select.append(`<option value="${status.id}" data-slug="${status.slug}">${status.display_name}</option>`);
                });
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
                        location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                showMessage(xhr.responseJSON?.error || 'Failed to update status', 'danger');
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
