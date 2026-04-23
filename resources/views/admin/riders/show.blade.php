@extends('layouts.admin')

@section('title', 'Rider Details - ' . $rider->employee_id)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/riders-show.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card rider-details-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Rider Details: {{ $rider->user->name }}</h5>
                    <div>
                        <a href="{{ route('admin.riders.edit', $rider->id) }}" class="btn btn-warning btn-sm">
                            <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                            Edit
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" onclick="showDeleteModal({{ $rider->id }}, '{{ $rider->user->name }}')">
                            <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            Delete
                        </button>
                        <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary btn-sm">
                            <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                            Back
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr><th width="35%">Full Name:</th><td>{{ $rider->user->name }}</small></tr>
                                    <tr><th>Email:</th><td>{{ $rider->user->email }}</small></tr>
                                    <tr><th>Phone:</th><td>{{ $rider->user->phone }}</small></tr>
                                    <tr><th>Address:</th><td>{{ $rider->user->address ?? 'N/A' }}</small></tr>
                                    <tr><th>Employee ID:</th><td><span class="employee-id">{{ $rider->employee_id }}</span></small></tr>
                                    <tr><th>Joined Date:</th><td>{{ $rider->joined_date ? date('d M Y', strtotime($rider->joined_date)) : 'N/A' }}</small></tr>
                                    <tr><th>Status:</th>
                                        <td>
                                            @if($rider->status == 'available')
                                                <span class="badge badge-available">Available</span>
                                            @elseif($rider->status == 'busy')
                                                <span class="badge badge-busy">Busy</span>
                                            @else
                                                <span class="badge badge-offline">Offline</span>
                                            @endif
                                         </small>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Information -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Vehicle Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr><th width="35%">Vehicle Type:</th><td><span class="badge badge-vehicle">{{ ucfirst($rider->vehicle_type) }}</span></small></tr>
                                    <tr><th>Vehicle Number:</th><td>{{ $rider->vehicle_number ?? 'N/A' }}</small></tr>
                                    <tr><th>Vehicle Model:</th><td>{{ $rider->vehicle_model ?? 'N/A' }}</small></td>
                                    <tr><th>License Number:</th><td>{{ $rider->license_number ?? 'N/A' }}</small></tr>
                                    <tr><th>Max Weight Capacity:</th><td>{{ $rider->max_weight_capacity }} kg</small></tr>
                                    <tr><th>Max Size Capacity:</th><td>{{ $rider->max_size_capacity }} cm³</small></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Hub Information -->
                    <div class="col-md-6 mt-3">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Hub Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr><th width="35%">Assigned Hub:</th><td>{{ $rider->hub->name ?? 'N/A' }}</small></tr>
                                    <tr><th>Hub Code:</th><td>{{ $rider->hub->code ?? 'N/A' }}</small></tr>
                                    <tr><th>Hub Address:</th><td>{{ $rider->hub->address ?? 'N/A' }}</small></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Statistics -->
                    <div class="col-md-6 mt-3">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0">Performance Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <div class="stat-value">{{ $rider->total_deliveries }}</div>
                                            <div class="stat-label">Total Deliveries</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <div class="stat-value text-success">{{ $rider->successful_deliveries }}</div>
                                            <div class="stat-label">Successful</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <div class="stat-value text-danger">{{ $rider->failed_deliveries }}</div>
                                            <div class="stat-label">Failed</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress-custom mt-3">
                                    @php
                                        $successRate = $rider->total_deliveries > 0 ? ($rider->successful_deliveries / $rider->total_deliveries) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar-custom" style="width: {{ $successRate }}%"></div>
                                </div>
                                <div class="text-center mt-3">
                                    <span class="rating-value">{{ number_format($rider->rating, 1) }}</span>
                                    <span class="rating-stars">
                                        <iconify-icon icon="solar:star-bold"></iconify-icon>
                                    </span>
                                    <div class="stat-label">Rating</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Parcels -->
                    @if($rider->assignedParcels && $rider->assignedParcels->count() > 0)
                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Recent Assigned Parcels</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tracking #</th>
                                                <th>Receiver</th>
                                                <th>Status</th>
                                                <th>Assigned At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rider->assignedParcels->take(10) as $parcel)
                                            <tr>
                                                <td>{{ $parcel->tracking_number }}</small></td>
                                                <td>{{ $parcel->receiver_name }}</small></td>
                                                <td>
                                                    <span class="badge rounded-pill" style="background-color: {{ $parcel->status->color_code ?? '#6c757d' }}; color: white;">
                                                        {{ $parcel->status->display_name ?? $parcel->status->name ?? 'Unknown' }}
                                                    </span>
                                                 </small>
                                                <td>{{ $parcel->assigned_at ? $parcel->assigned_at->format('d M Y') : 'N/A' }}</small></tr>
                                                <td>
                                                    <a href="{{ route('admin.parcels.show', $parcel->id) }}" class="btn btn-sm btn-info">
                                                        <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                                                    </a>
                                                 </small>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-danger mb-3"></iconify-icon>
                    <h5 class="mb-3">Are you sure you want to delete this rider?</h5>
                    <p class="text-muted" id="deleteRiderName"></p>
                    <p class="text-danger small">⚠️ This will also delete the rider's user account and all associated data.</p>
                    <p class="text-warning small">This action cannot be undone!</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <iconify-icon icon="solar:close-circle-line-duotone"></iconify-icon>
                    Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                        Yes, Delete Rider
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/riders-show.js') }}"></script>
@endpush
