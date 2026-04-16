@extends('layouts.admin')

@section('title', 'Manage Riders')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Riders</h5>
            <a href="{{ route('admin.riders.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                Add New Rider
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Hub</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Deliveries</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riders as $rider)
                    <tr>
                        <td>{{ $rider->id }}</td>
                        <td><span class="fw-bold">{{ $rider->employee_id }}</span></td>
                        <td>{{ $rider->user->name }}</td>
                        <td>{{ $rider->user->email }}</td>
                        <td>{{ $rider->user->phone }}</td>
                        <td>{{ $rider->hub->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($rider->vehicle_type) }}</span>
                        </td>
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
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-1">{{ number_format($rider->rating, 1) }}</span>
                                <iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.riders.show', $rider->id) }}" class="btn btn-sm btn-info" title="View">
                                    <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                                </a>
                                <a href="{{ route('admin.riders.edit', $rider->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="showDeleteModal({{ $rider->id }}, '{{ $rider->user->name }}', '{{ $rider->employee_id }}')">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <iconify-icon icon="solar:bicycle-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                <p class="mt-2 text-muted">No riders found</p>
                                <a href="{{ route('admin.riders.create') }}" class="btn btn-primary btn-sm">Add First Rider</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $riders->links() }}
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Centered) -->
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
                    <p class="text-muted" id="deleteRiderInfo"></p>
                    <div class="alert alert-warning">
                        <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                        <strong>Warning:</strong> This will also delete the rider's user account and all associated data.
                    </div>
                    <p class="text-danger small">⚠️ This action cannot be undone!</p>
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

@push('styles')
<style>
    .table th {
        font-weight: 600;
        color: #555;
        border-top: none;
    }
    .badge {
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 500;
    }
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }
</style>
@endpush

@push('scripts')
<script>
    function showDeleteModal(riderId, riderName, employeeId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.getElementById('deleteRiderInfo').innerHTML = `
            <strong>${riderName}</strong><br>
            <small class="text-muted">Employee ID: ${employeeId}</small>
        `;
        document.getElementById('deleteForm').action = `/admin/riders/${riderId}`;
        modal.show();
    }
</script>
@endpush
