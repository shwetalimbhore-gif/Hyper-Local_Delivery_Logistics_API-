@extends('layouts.admin')

@section('title', 'Hub Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/hubs/show.css') }}">
@endpush

@section('content')
<div class="row">
    <!-- Hub Information Card -->
    <div class="col-md-6">
        <div class="card info-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    Hub Information
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Hub Name</th>
                        <td>{{ $hub->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Hub Code</th>
                        <td><span class="fw-bold">{{ $hub->code ?? 'N/A' }}</span></td>
                    </tr>
                    <tr>
                        <th>Manager Name</th>
                        <td>{{ $hub->manager_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $hub->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $hub->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $hub->address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if(($hub->is_active ?? false) == true)
                                <span class="badge bg-success">
                                    <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                                    Active
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <iconify-icon icon="solar:close-circle-line-duotone"></iconify-icon>
                                    Inactive
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $hub->created_at ? $hub->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $hub->updated_at ? $hub->updated_at->format('d M Y, h:i A') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="col-md-6">
        <div class="card info-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:chart-line-duotone"></iconify-icon>
                    Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="stat-number text-primary">{{ $riderCount ?? $hub->riders()->count() ?? 0 }}</div>
                        <div class="stat-label">
                            <iconify-icon icon="solar:bicycle-line-duotone"></iconify-icon>
                            Riders Assigned
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-number text-success">{{ $parcelCount ?? $hub->sourceParcels()->count() ?? 0 }}</div>
                        <div class="stat-label">
                            <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                            Total Parcels Processed
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Riders List -->
        @if(isset($hub->riders) && $hub->riders && $hub->riders->count() > 0)
        <div class="card info-card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:users-group-line-duotone"></iconify-icon>
                    Assigned Riders ({{ $hub->riders->count() }})
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hub->riders as $rider)
                            <tr>
                                <td>{{ $rider->employee_id ?? 'N/A' }}</td>
                                <td>{{ $rider->user->name ?? 'N/A' }}</td>
                                <td>{{ ucfirst($rider->vehicle_type ?? 'N/A') }}</td>
                                <td>
                                    @php
                                        $status = $rider->status ?? 'offline';
                                    @endphp
                                    @if($status == 'available')
                                        <span class="badge bg-success">
                                            <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                                            Available
                                        </span>
                                    @elseif($status == 'busy')
                                        <span class="badge bg-warning">
                                            <iconify-icon icon="solar:clock-circle-line-duotone"></iconify-icon>
                                            Busy
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <iconify-icon icon="solar:power-off-line-duotone"></iconify-icon>
                                            Offline
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- No Riders Message -->
        @if((!isset($hub->riders) || $hub->riders->count() == 0))
        <div class="card info-card mt-3">
            <div class="card-body empty-state">
                <iconify-icon icon="solar:users-group-line-duotone"></iconify-icon>
                <p>No riders assigned to this hub yet.</p>
                <a href="{{ route('admin.riders.create') }}?hub_id={{ $hub->id }}" class="btn btn-primary btn-sm">
                    <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                    Assign Rider
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-3">
    <div class="col-12">
        <div class="action-buttons">
            <a href="{{ route('admin.hubs.index') }}" class="btn btn-secondary">
                <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                Back to Hubs
            </a>
            <a href="{{ route('admin.hubs.edit', $hub->id) }}" class="btn btn-warning">
                <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                Edit Hub
            </a>
            @if(($hub->is_active ?? false) == true)
                <a href="{{ route('admin.hubs.toggle-status', $hub->id) }}" class="btn btn-secondary">
                    <iconify-icon icon="solar:power-off-line-duotone"></iconify-icon>
                    Deactivate Hub
                </a>
            @else
                <a href="{{ route('admin.hubs.toggle-status', $hub->id) }}" class="btn btn-success">
                    <iconify-icon icon="solar:power-on-line-duotone"></iconify-icon>
                    Activate Hub
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
