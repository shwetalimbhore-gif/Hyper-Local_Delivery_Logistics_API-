@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/dashboard.css') }}">
@endpush

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('admin.parcels.index') }}">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Parcels</h6>
                            <h2 class="mb-0 text-white">{{ $totalParcels ?? 0 }}</h2>
                        </div>
                        <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                    </div>
                    <small class="mt-2 d-block text-white-50">+{{ $newParcels ?? 0 }} this week</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('admin.parcels.index') }}">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 text-white-50">Delivered</h6>
                            <h2 class="mb-0 text-white">{{ $deliveredParcels ?? 0 }}</h2>
                        </div>
                        <iconify-icon icon="solar:check-circle-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                    </div>
                    <small class="mt-2 d-block text-white-50">Rate: {{ $deliveryRate ?? 0 }}%</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('admin.riders.index') }}">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">Active Riders</h6>
                            <h2 class="mb-0">{{ $activeRiders ?? 0 }}</h2>
                        </div>
                        <iconify-icon icon="solar:bicycle-line-duotone" class="fs-1"></iconify-icon>
                    </div>
                    <small class="mt-2 d-block">Total: {{ $totalRiders ?? 0 }}</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('admin.hubs.index') }}">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Hubs</h6>
                            <h2 class="mb-0 text-white">{{ $totalHubs ?? 0 }}</h2>
                        </div>
                        <iconify-icon icon="solar:warehouse-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                    </div>
                    <small class="mt-2 d-block text-white-50">Active Locations</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Recent Parcels Table -->
<div class="row mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Recent Parcels</h5>
                    <a href="{{ route('admin.parcels.create') }}" class="btn btn-primary btn-sm">
                        <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                        Create Parcel
                    </a>
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
                                <th>Sender</th>
                                <th>Receiver</th>
                                <th>Weight</th>
                                <th>Status</th>
                                <th>Rider</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentParcels ?? [] as $parcel)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $parcel->tracking_number }}</span>
                                </td>
                                <td>{{ Str::limit($parcel->sender_name, 20) }}</td>
                                <td>{{ Str::limit($parcel->receiver_name, 20) }}</td>
                                <td>{{ $parcel->weight }} kg</td>
                                <td>
                                    <span class="badge rounded-pill" style="background-color: {{ $parcel->status->color_code ?? '#6c757d' }}; color: white;">
                                        {{ $parcel->status->display_name ?? $parcel->status->name ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td>
                                    @if($parcel->assignedRider && $parcel->assignedRider->user)
                                        {{ $parcel->assignedRider->user->name }}
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>{{ $parcel->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.parcels.show', $parcel->id) }}" class="btn btn-sm btn-info" title="View">
                                            <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                                        </a>
                                        <a href="{{ route('admin.parcels.edit', $parcel->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDelete({{ $parcel->id }})">
                                            <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                        </button>
                                        <form id="delete-form-{{ $parcel->id }}" action="{{ route('admin.parcels.destroy', $parcel->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                    <p class="mt-2 text-muted">No parcels found</p>
                                    <a href="{{ route('admin.parcels.create') }}" class="btn btn-primary btn-sm">Create First Parcel</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($recentParcels) && method_exists($recentParcels, 'links'))
                    <div class="mt-3">
                        {{ $recentParcels->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Parcel Status Distribution (Simple) -->
<div class="row mt-4">
    {{-- <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Parcel Status Distribution</h5>
                <div class="mt-3">
                    @foreach($statusCounts ?? [] as $status)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold" style="color: #000 !important;">{{ $status->display_name ?? $status->name }}</span>
                                <span class="fw-bold" style="color: #000 !important;">{{ $status->count }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ ($status->count / max($totalStatusCount, 1)) * 100 }}%;
                                        background-color: {{ $status->color_code ?? '#007bff' }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div> --}}


    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.parcels.create') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <iconify-icon icon="solar:add-circle-line-duotone" class="fs-5 me-2 text-primary"></iconify-icon>
                        Create New Parcel
                    </a>
                    <a href="{{ route('admin.riders.index') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <iconify-icon icon="solar:bicycle-line-duotone" class="fs-5 me-2 text-success"></iconify-icon>
                        Manage Riders
                    </a>
                    <a href="{{ route('admin.hubs.index') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <iconify-icon icon="solar:warehouse-line-duotone" class="fs-5 me-2 text-info"></iconify-icon>
                        Manage Hubs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/dashboard.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
