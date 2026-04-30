@extends('layouts.rider')

@section('title', 'My Parcels')

@push('styles')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/rider/parcels.css') }}">
@endpush

@section('content')
<div class="card parcels-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">
                <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                My Parcels
            </h5>
            <div class="dropdown filter-dropdown">
                {{-- <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="statusFilterButton" data-bs-toggle="dropdown">
                    <iconify-icon icon="solar:filter-line-duotone"></iconify-icon>
                    <span id="statusFilterLabel">Filter by Status</span>
                </button> --}}
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item filter-status" href="#" data-status="">
                            <iconify-icon icon="solar:list-line-duotone"></iconify-icon>
                            All Parcels
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @foreach($statuses as $status)
                        <li>
                            <a class="dropdown-item filter-status" href="#" data-status="{{ $status->slug }}">
                                <span class="badge me-2" style="background-color: {{ $status->color_code }}; width: 10px; height: 10px; display: inline-block; border-radius: 50%;"></span>
                                {{ $status->display_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <input type="hidden" id="statusFilterValue" value="{{ $statusFilter ?? '' }}">
            <table class="table table-hover" id="riderParcelsTable" width="100%" data-ajax="{{ route('rider.parcels.data') }}">
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
                    <!-- DataTables will populate this via AJAX -->
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
                        <select name="status_id" id="statusSelect" class="form-select status-select" required>
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
                        <textarea name="notes" id="statusNotes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
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
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Update Status
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/js/rider/parcels.js') }}?v={{ filemtime(public_path('assets/js/rider/parcels.js')) }}"></script>
@endpush
