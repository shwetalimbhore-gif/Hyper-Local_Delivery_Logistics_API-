@extends('layouts.admin')

@section('title', 'Rider Details - ' . $rider->employee_id)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/riders/show.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card rider-details-card">
            <div class="card-body">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                        Rider Details: {{ $rider->user->name }}
                    </h5>
                    <div class="btn-group">
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
                    <!-- Personal Information Column -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                                    Personal Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Full Name:</th>
                                        <td>{{ $rider->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $rider->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $rider->user->phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $rider->user->address ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Employee ID:</th>
                                        <td><span class="employee-id">{{ $rider->employee_id }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Joined Date:</th>
                                        <td>{{ $rider->joined_date ? date('d M Y', strtotime($rider->joined_date)) : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Verification Status:</th>
                                        <td>
                                            @if($rider->is_verified)
                                                <span class="badge bg-success">
                                                    <iconify-icon icon="solar:verified-check-line-duotone"></iconify-icon>
                                                    Verified
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                                                    Pending Verification
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if($rider->status == 'available')
                                                <span class="badge badge-available">
                                                    <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                                                    Available
                                                </span>
                                            @elseif($rider->status == 'busy')
                                                <span class="badge badge-busy">
                                                    <iconify-icon icon="solar:clock-circle-line-duotone"></iconify-icon>
                                                    Busy
                                                </span>
                                            @else
                                                <span class="badge badge-offline">
                                                    <iconify-icon icon="solar:power-off-line-duotone"></iconify-icon>
                                                    Offline
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Information Column -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <iconify-icon icon="solar:bicycle-line-duotone"></iconify-icon>
                                    Vehicle Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Vehicle Type:</th>
                                        <td><span class="badge badge-vehicle">{{ ucfirst($rider->vehicle_type) }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle Number:</th>
                                        <td>{{ $rider->vehicle_number ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle Model:</th>
                                        <td>{{ $rider->vehicle_model ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>License Number:</th>
                                        <td>{{ $rider->license_number ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Max Weight Capacity:</th>
                                        <td>{{ $rider->max_weight_capacity ?? 0 }} kg</small>
                                    </tr>
                                    <tr>
                                        <th>Max Size Capacity:</th>
                                        <td>{{ $rider->max_size_capacity ?? 0 }} cm³</small>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Hub Information -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <iconify-icon icon="solar:warehouse-line-duotone"></iconify-icon>
                                    Hub Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Assigned Hub:</th>
                                        <td>{{ $rider->hub->name ?? 'N/A' }}</small>
                                    </tr>
                                    <tr>
                                        <th>Hub Code:</th>
                                        <td>{{ $rider->hub->code ?? 'N/A' }}</small>
                                    </tr>
                                    <tr>
                                        <th>Hub Address:</th>
                                        <td>{{ $rider->hub->address ?? 'N/A' }}</small>
                                    </tr>
                                    <tr>
                                        <th>Hub Phone:</th>
                                        <td>{{ $rider->hub->phone ?? 'N/A' }}</small>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Statistics -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0">
                                    <iconify-icon icon="solar:chart-line-duotone"></iconify-icon>
                                    Performance Statistics
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <div class="stat-value">{{ $rider->total_deliveries ?? 0 }}</div>
                                            <div class="stat-label">Total Deliveries</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <div class="stat-value text-success">{{ $rider->successful_deliveries ?? 0 }}</div>
                                            <div class="stat-label">Successful</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <div class="stat-value text-danger">{{ $rider->failed_deliveries ?? 0 }}</div>
                                            <div class="stat-label">Failed</div>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $successRate = ($rider->total_deliveries ?? 0) > 0
                                        ? (($rider->successful_deliveries ?? 0) / ($rider->total_deliveries ?? 1)) * 100
                                        : 0;
                                @endphp

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Success Rate</small>
                                        <small>{{ number_format($successRate, 1) }}%</small>
                                    </div>
                                    <div class="progress-custom">
                                        <div class="progress-bar-custom" style="width: {{ $successRate }}%"></div>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <span class="rating-value">{{ number_format($rider->rating ?? 0, 1) }}</span>
                                        <div class="rating-stars">
                                            @php
                                                $fullStars = floor($rider->rating ?? 0);
                                                $halfStar = (($rider->rating ?? 0) - $fullStars) >= 0.5;
                                                $emptyStars = 5 - ceil($rider->rating ?? 0);
                                            @endphp
                                            @for($i = 0; $i < $fullStars; $i++)
                                                <iconify-icon icon="solar:star-bold"></iconify-icon>
                                            @endfor
                                            @if($halfStar)
                                                <iconify-icon icon="solar:star-half-bold"></iconify-icon>
                                            @endif
                                            @for($i = 0; $i < $emptyStars; $i++)
                                                <iconify-icon icon="solar:star-line-duotone"></iconify-icon>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="stat-label mt-2">Rating</div>
                                </div>

                                <div class="mt-3 pt-2 border-top">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="stat-label">Total Earnings</div>
                                            <div class="fw-bold text-primary">₹{{ number_format($rider->earnings ?? 0, 2) }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stat-label">Commission Rate</div>
                                            <div class="fw-bold">70%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Location -->
                    @if($rider->current_latitude && $rider->current_longitude)
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0">
                                    <iconify-icon icon="solar:map-point-line-duotone"></iconify-icon>
                                    Current Location
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Latitude</small>
                                        <p class="mb-0">{{ $rider->current_latitude }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Longitude</small>
                                        <p class="mb-0">{{ $rider->current_longitude }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Parcels -->
                    @if(isset($rider->assignedParcels) && $rider->assignedParcels->count() > 0)
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">
                                    <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                                    Recent Assigned Parcels (Last 10)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tracking #</th>
                                                <th>Receiver</th>
                                                <th>Status</th>
                                                <th>Weight</th>
                                                <th>Assigned At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rider->assignedParcels->take(10) as $parcel)
                                            <tr>
                                                <td>
                                                    <span class="tracking-number">{{ $parcel->tracking_number }}</span>
                                                 </small>
                                                <td>{{ $parcel->receiver_name }}</small>
                                                <td>
                                                    <span class="badge rounded-pill" style="background-color: {{ $parcel->status->color_code ?? '#6c757d' }}; color: white;">
                                                        {{ $parcel->status->display_name ?? $parcel->status->name ?? 'Unknown' }}
                                                    </span>
                                                 </small>
                                                <td>{{ $parcel->weight }} kg</small>
                                                <td>{{ $parcel->assigned_at ? $parcel->assigned_at->format('d M Y, h:i A') : 'N/A' }}</small>
                                                <td>
                                                    <a href="{{ route('admin.parcels.show', $parcel->id) }}" class="btn btn-sm btn-info" title="View Parcel">
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

                    <!-- No Parcels Message -->
                    @if((!isset($rider->assignedParcels) || $rider->assignedParcels->count() == 0))
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-body text-center py-5">
                                <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-muted mb-3"></iconify-icon>
                                <p class="text-muted mb-0">No parcels assigned yet.</p>
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
                    <div id="deleteErrorContainer" class="alert alert-danger small" style="display: none;">
                        <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                        <span id="deleteErrorMsg"></span>
                    </div>
                    <div class="alert alert-danger small">
                        <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                        <strong>Warning:</strong> This will also delete the rider's user account and all associated data.
                    </div>
                    <p class="text-warning small mb-0">⚠️ This action cannot be undone!</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <iconify-icon icon="solar:close-circle-line-duotone"></iconify-icon>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Yes, Delete Rider
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/admin/riders/show.js') }}"></script>
@endpush
