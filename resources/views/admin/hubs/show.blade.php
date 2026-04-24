@extends('layouts.admin')

@section('title', 'Hub Details')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Hub Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="35%">Hub Name</th>
                        <td>{{ $hub->name ?? 'N/A' }}</small>
                    </tr>
                    <tr>
                        <th>Hub Code</th>
                        <td><span class="fw-bold">{{ $hub->code ?? 'N/A' }}</span></small>
                    </tr>
                    <tr>
                        <th>Manager Name</th>
                        <td>{{ $hub->manager_name ?? 'N/A' }}</small>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $hub->phone ?? 'N/A' }}</small>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $hub->email ?? 'N/A' }}</small>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $hub->address ?? 'N/A' }}</small>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if(isset($hub->is_active) && $hub->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                         </small>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $hub->created_at ? $hub->created_at->format('d M Y h:i A') : 'N/A' }}</small>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $hub->updated_at ? $hub->updated_at->format('d M Y h:i A') : 'N/A' }}</small>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h2 class="mb-0 text-primary">{{ $riderCount ?? $hub->riders()->count() ?? 0 }}</h2>
                        <small class="text-muted">Riders Assigned</small>
                    </div>
                    <div class="col-6">
                        <h2 class="mb-0 text-success">{{ $parcelCount ?? $hub->sourceParcels()->count() ?? 0 }}</h2>
                        <small class="text-muted">Total Parcels Processed</small>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($hub->riders) && $hub->riders && $hub->riders->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hub->riders as $rider)
                            <tr>
                                <td>{{ $rider->employee_id ?? 'N/A' }}</small>
                                <td>{{ $rider->user->name ?? 'N/A' }}</small>
                                <td>{{ ucfirst($rider->vehicle_type ?? 'N/A') }}</small>
                                <td>
                                    @if(isset($rider->status) && $rider->status == 'available')
                                        <span class="badge bg-success">Available</span>
                                    @elseif(isset($rider->status) && $rider->status == 'busy')
                                        <span class="badge bg-warning">Busy</span>
                                    @else
                                        <span class="badge bg-secondary">Offline</span>
                                    @endif
                                 </small>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <a href="{{ route('admin.hubs.index') }}" class="btn btn-secondary">
            <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
            Back to Hubs
        </a>
        <a href="{{ route('admin.hubs.edit', $hub->id) }}" class="btn btn-warning">
            <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
            Edit Hub
        </a>
    </div>
</div>
@endsection
