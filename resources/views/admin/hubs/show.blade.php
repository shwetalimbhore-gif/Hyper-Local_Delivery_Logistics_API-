@extends('layouts.admin')

@section('title', 'Hub Details - ' . $hub->name)

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Hub Details: {{ $hub->name }}</h5>
            <div>
                <a href="{{ route('admin.hubs.edit', $hub->id) }}" class="btn btn-warning btn-sm">
                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                    Edit
                </a>
                <a href="{{ route('admin.hubs.index') }}" class="btn btn-secondary btn-sm">
                    <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                    Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Hub Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr><th width="35%">Hub Name:</th><td>{{ $hub->name }}</td></tr>
                            <tr><th>Hub Code:</th><td><span class="fw-bold">{{ $hub->code }}</span></td></tr>
                            <tr><th>Manager:</th><td>{{ $hub->manager_name ?? 'N/A' }}</td></tr>
                            <tr><th>Phone:</th><td>{{ $hub->phone ?? 'N/A' }}</td></tr>
                            <tr><th>Email:</th><td>{{ $hub->email ?? 'N/A' }}</td></tr>
                            <tr><th>Address:</th><td>{{ $hub->address }}</td></tr>
                            <tr><th>Status:</th><td>
                                @if($hub->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h2 class="mb-0">{{ $riderCount ?? $hub->riders()->count() }}</h2>
                            <small class="text-muted">Riders Assigned</small>
                        </div>
                        <div class="text-center">
                            <h2 class="mb-0">{{ $parcelCount ?? $hub->sourceParcels()->count() }}</h2>
                            <small class="text-muted">Total Parcels Processed</small>
                        </div>
                    </div>
                </div>
            </div>

            @if($hub->riders && $hub->riders->count() > 0)
            <div class="col-md-12 mt-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Assigned Riders</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Vehicle</th>
                                        <th>Status</th>
                                        <th>Deliveries</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hub->riders as $rider)
                                    <tr>
                                        <td>{{ $rider->employee_id }}</td>
                                        <td>{{ $rider->user->name }}</td>
                                        <td>{{ ucfirst($rider->vehicle_type) }}</td>
                                        <td>
                                            @if($rider->status == 'available')
                                                <span class="badge bg-success">Available</span>
                                            @elseif($rider->status == 'busy')
                                                <span class="badge bg-warning">Busy</span>
                                            @else
                                                <span class="badge bg-secondary">Offline</span>
                                            @endif
                                        </td>
                                        <td>{{ $rider->total_deliveries }}</td>
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
@endsection
