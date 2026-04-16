@extends('layouts.rider')

@section('title', 'My Parcels')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">My Parcels</h5>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Filter by Status
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('rider.parcels.index') }}">All</a></li>
                    @foreach($statuses as $status)
                        <li><a class="dropdown-item" href="{{ route('rider.parcels.index', ['status' => $status->slug]) }}">{{ $status->display_name }}</a></li>
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
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Receiver</th>
                        <th>Address</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parcels as $parcel)
                    <tr id="parcel-row-{{ $parcel->id }}">
                        <td><span class="fw-bold">{{ $parcel->tracking_number }}</span></td>
                        <td>
                            {{ $parcel->receiver_name }}<br>
                            <small class="text-muted">{{ $parcel->receiver_phone }}</small>
                        </small>
                        <td>{{ Str::limit($parcel->receiver_address, 50) }}</small>
                        <td>{{ $parcel->weight }} kg</small>
                        <td>
                            <span class="badge rounded-pill status-badge-{{ $parcel->id }}"
                                  style="background-color: {{ $parcel->status->color_code }}; color: {{ $parcel->status->color_code == '#ffc107' ? '#000' : '#fff' }};">
                                {{ $parcel->status->display_name }}
                            </span>
                        </small>
                        <td>
                            @php
                                $canUpdate = in_array($parcel->status->slug, ['assigned', 'picked-up', 'out-for-delivery', 'failed-delivery']);
                            @endphp
                            @if($canUpdate)
                                <button type="button" class="btn btn-sm btn-primary update-status-btn"
                                        data-parcel-id="{{ $parcel->id }}"
                                        data-tracking="{{ $parcel->tracking_number }}"
                                        data-current-status="{{ $parcel->status->slug }}"
                                        data-current-status-name="{{ $parcel->status->display_name }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateStatusModal">
                                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                    Update Status
                                </button>
                            @else
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <iconify-icon icon="solar:lock-line-duotone"></iconify-icon>
                                    No Action
                                </button>
                            @endif
                        </small>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-muted"></iconify-icon>
                            <p class="mt-2 text-muted">No parcels found</p>
                        </small>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $parcels->links() }}
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Update Parcel Status
                </h5>
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
                        <small class="text-muted">Select the next status for this delivery</small>
                    </div>

                    <div class="mb-3" id="failureReasonDiv" style="display: none;">
                        <label class="form-label fw-bold">Failure Reason <span class="text-danger">*</span></label>
                        <select name="failure_reason" id="failureReason" class="form-select">
                            <option value="">-- Select Reason --</option>
                            <option value="Wrong Address">📍 Wrong Address - Incorrect or incomplete address</option>
                            <option value="Receiver Not Available">👤 Receiver Not Available - No one at delivery location</option>
                            <option value="Phone Not Reachable">📞 Phone Not Reachable - Can't contact receiver</option>
                            <option value="Location Not Found">🗺️ Location Not Found - Rider couldn't find address</option>
                            <option value="Parcel Damaged">📦 Parcel Damaged - Package was damaged</option>
                            <option value="Refused by Receiver">❌ Refused by Receiver - Customer rejected delivery</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" id="statusNotes" class="form-control" rows="2"
                                  placeholder="Any additional information about this delivery..."></textarea>
                    </div>

                    <div id="statusMessage" class="alert" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <iconify-icon icon="solar:close-circle-line-duotone"></iconify-icon>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitStatusUpdate">
                    <span id="submitBtnText">
                        <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                        Update Status
                    </span>
                    <span id="submitBtnSpinner" class="spinner-border spinner-border-sm" style="display: none;"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentParcelId = null;

    // Load available statuses when modal opens
    $('.update-status-btn').click(function() {
        currentParcelId = $(this).data('parcel-id');
        let trackingNumber = $(this).data('tracking');
        let currentStatusName = $(this).data('current-status-name');
        let currentStatusSlug = $(this).data('current-status');

        $('#modalTrackingNumber').text(trackingNumber);
        $('#modalCurrentStatus').text(currentStatusName).removeClass().addClass('badge bg-secondary');
        $('#parcelId').val(currentParcelId);
        $('#statusMessage').hide();
        $('#failureReasonDiv').hide();

        // Reset form
        $('#statusSelect').html('<option value="">Loading statuses...</option>');
        $('#failureReason').val('');
        $('#statusNotes').val('');
        $('#submitBtnText').show();
        $('#submitBtnSpinner').hide();
        $('#submitStatusUpdate').prop('disabled', false);

        // Load available statuses
        $.ajax({
            url: `/rider/parcels/${currentParcelId}/available-statuses`,
            method: 'GET',
            success: function(response) {
                let select = $('#statusSelect');
                select.empty();

                if(response.length === 0) {
                    select.append('<option value="" disabled>No status updates available</option>');
                    $('#submitStatusUpdate').prop('disabled', true);
                } else {
                    select.append('<option value="">-- Select New Status --</option>');
                    response.forEach(function(status) {
                        select.append(`<option value="${status.id}" data-slug="${status.slug}">${status.display_name}</option>`);
                    });
                }
            },
            error: function(xhr) {
                $('#statusSelect').html('<option value="" disabled>Error loading statuses</option>');
                showMessage('Failed to load available statuses', 'danger');
            }
        });
    });

    // Show/hide failure reason based on selected status
    $('#statusSelect').change(function() {
        let selectedOption = $(this).find('option:selected');
        let selectedSlug = selectedOption.data('slug');
        let selectedText = selectedOption.text();

        if (selectedSlug === 'failed-delivery' || selectedText === 'Delivery Failed') {
            $('#failureReasonDiv').slideDown();
        } else {
            $('#failureReasonDiv').slideUp();
        }
    });

    // Submit status update
    $('#submitStatusUpdate').click(function() {
        let statusId = $('#statusSelect').val();
        let selectedOption = $('#statusSelect').find('option:selected');
        let selectedSlug = selectedOption.data('slug');
        let selectedText = selectedOption.text();
        let failureReason = $('#failureReason').val();
        let notes = $('#statusNotes').val();

        // Validate
        if (!statusId) {
            showMessage('Please select a new status', 'danger');
            $('#statusSelect').focus();
            return;
        }

        if ((selectedSlug === 'failed-delivery' || selectedText === 'Delivery Failed') && !failureReason) {
            showMessage('Please select a failure reason', 'danger');
            $('#failureReason').focus();
            return;
        }

        // Show loading
        $('#submitBtnText').hide();
        $('#submitBtnSpinner').show();
        $('#submitStatusUpdate').prop('disabled', true);
        $('#statusMessage').hide();

        // Prepare data
        let formData = {
            _token: "{{ csrf_token() }}",
            status_id: statusId,
            notes: notes
        };

        if (failureReason) {
            formData.failure_reason = failureReason;
        }

        // Submit
        $.ajax({
            url: `/rider/parcels/${currentParcelId}/update-status`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    showMessage(response.message, 'success');

                    // Update status badge in table
                    let newStatus = response.parcel.status;
                    $(`.status-badge-${currentParcelId}`)
                        .text(newStatus.display_name)
                        .css('background-color', newStatus.color_code)
                        .css('color', newStatus.color_code === '#ffc107' ? '#000' : '#fff');

                    // Disable update button if parcel is delivered or returned
                    if (newStatus.slug === 'delivered' || newStatus.slug === 'returned-to-hub') {
                        $(`.update-status-btn[data-parcel-id="${currentParcelId}"]`)
                            .replaceWith('<button class="btn btn-sm btn-secondary" disabled><iconify-icon icon="solar:lock-line-duotone"></iconify-icon> Completed</button>');
                    }

                    // Close modal after 2 seconds
                    setTimeout(function() {
                        $('#updateStatusModal').modal('hide');
                        // Optionally reload the page to refresh the list
                        // location.reload();
                    }, 2000);
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || 'Failed to update status';
                showMessage(errorMsg, 'danger');
                $('#submitBtnText').show();
                $('#submitBtnSpinner').hide();
                $('#submitStatusUpdate').prop('disabled', false);
            }
        });
    });

    function showMessage(message, type) {
        let alertDiv = $('#statusMessage');
        alertDiv.removeClass('alert-info alert-success alert-danger').addClass(`alert-${type}`);
        alertDiv.html(`<iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon> ${message}`);
        alertDiv.slideDown();

        // Auto hide after 3 seconds for success messages
        if (type === 'success') {
            setTimeout(function() {
                alertDiv.slideUp();
            }, 3000);
        }
    }
</script>
@endpush
