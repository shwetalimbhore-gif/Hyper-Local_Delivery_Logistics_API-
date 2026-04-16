@extends('layouts.admin')

@section('title', 'Manage Hubs')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Hubs</h5>
            <a href="{{ route('admin.hubs.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                Add New Hub
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Manager</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Riders</th>
                        <th>Parcels</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hubs as $hub)
                    <tr>
                        <td>{{ $hub->id }}</td>
                        <td><span class="fw-bold">{{ $hub->code }}</span></td>
                        <td>{{ $hub->name }}</td>
                        <td>{{ $hub->manager_name ?? 'N/A' }}</td>
                        <td>{{ $hub->phone ?? 'N/A' }}</td>
                        <td>{{ $hub->email ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $hub->riders()->count() }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $hub->sourceParcels()->count() }}</span>
                        </td>
                        <td>
                            @if($hub->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.hubs.show', $hub->id) }}" class="btn btn-sm btn-info" title="View">
                                    <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                                </a>
                                <a href="{{ route('admin.hubs.edit', $hub->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                                </a>
                                <a href="{{ route('admin.hubs.toggle-status', $hub->id) }}" class="btn btn-sm {{ $hub->is_active ? 'btn-secondary' : 'btn-success' }}" title="{{ $hub->is_active ? 'Deactivate' : 'Activate' }}">
                                    <iconify-icon icon="{{ $hub->is_active ? 'solar:power-off-line-duotone' : 'solar:power-on-line-duotone' }}"></iconify-icon>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="showDeleteModal({{ $hub->id }}, '{{ $hub->name }}', '{{ $hub->code }}')" {{ $hub->riders()->count() > 0 || $hub->sourceParcels()->count() > 0 ? 'disabled' : '' }}>
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <iconify-icon icon="solar:warehouse-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                <p class="mt-2 text-muted">No hubs found</p>
                                <a href="{{ route('admin.hubs.create') }}" class="btn btn-primary btn-sm">Add First Hub</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $hubs->links() }}
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
                    Delete Hub
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex p-3 mb-3">
                        <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-danger"></iconify-icon>
                    </div>
                </div>
                <h5 class="mb-3">Are you absolutely sure?</h5>
                <p class="text-muted mb-2">This action <strong>cannot</strong> be undone.</p>
                <div class="alert alert-warning mt-3">
                    <div id="deleteHubInfo" class="mb-2">
                        <strong>Hub: <span id="hubName"></span></strong><br>
                        <small>Code: <span id="hubCode"></span></small>
                    </div>
                    <hr class="my-2">
                    <small class="text-danger">
                        <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                        Deleting this hub will permanently remove it from the system.
                    </small>
                </div>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <iconify-icon icon="solar:close-circle-line-duotone"></iconify-icon>
                    Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">
                        <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                        Yes, Delete Hub
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

    /* Center modal properly */
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }

    /* Animation for modal */
    .modal.fade .modal-dialog {
        transform: scale(0.8);
        transition: transform 0.2s ease-in-out;
    }

    .modal.show .modal-dialog {
        transform: scale(1);
    }

    /* Alert styling */
    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffecb5;
        color: #856404;
    }
</style>
@endpush

@push('scripts')
<script>
    function showDeleteModal(hubId, hubName, hubCode) {
        // Get modal element
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));

        // Set hub info in modal
        document.getElementById('hubName').innerHTML = hubName;
        document.getElementById('hubCode').innerHTML = hubCode;

        // Set form action
        document.getElementById('deleteForm').action = `/admin/hubs/${hubId}`;

        // Show modal
        modal.show();
    }
</script>
@endpush
