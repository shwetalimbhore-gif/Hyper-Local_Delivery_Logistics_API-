@extends('layouts.admin')

@section('title', 'Manage Hubs')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/hubs.css') }}">
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Hubs</h5>
            <div>
                <a href="{{ route('admin.hubs.trash') }}" class="btn btn-secondary me-2">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Trash
                </a>
                <a href="{{ route('admin.hubs.create') }}" class="btn btn-primary">
                    <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                    Add New Hub
                </a>
            </div>
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
            <table class="table table-hover" id="hubsTable">
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
                    <tr id="hub-row-{{ $hub->id }}">
                        <td>{{ $hub->id }}</small></td>
                        <td><span class="fw-bold">{{ $hub->code }}</span></small></td>
                        <td>{{ $hub->name }}</small></td>
                        <td>{{ $hub->manager_name ?? 'N/A' }}</small></td>
                        <td>{{ $hub->phone ?? 'N/A' }}</small></td>
                        <td>{{ $hub->email ?? 'N/A' }}</small></td>
                        <td><span class="badge bg-info">{{ $hub->riders()->count() }}</span></small></td>
                        <td><span class="badge bg-secondary">{{ $hub->sourceParcels()->count() }}</span></small></td>
                        <td>
                            @if($hub->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                         </small>
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
                                <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                        onclick="showDeleteModal({{ $hub->id }}, '{{ addslashes($hub->name) }}', '{{ $hub->code }}')">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                </button>
                            </div>
                         </small>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <iconify-icon icon="solar:warehouse-line-duotone" class="fs-1 text-muted"></iconify-icon>
                            <p class="mt-3 text-muted">No hubs found</p>
                            <a href="{{ route('admin.hubs.create') }}" class="btn btn-primary btn-sm">Add First Hub</a>
                        </small>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex p-3">
                        <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-danger"></iconify-icon>
                    </div>
                </div>
                <h5 class="mb-3">Are you absolutely sure?</h5>
                <p class="text-muted mb-3">This action cannot be undone.</p>
                <div class="alert alert-warning mb-0">
                    <div id="deleteHubInfo" class="mb-2">
                        <strong>Hub: <span id="hubName"></span></strong><br>
                        <small>Code: <span id="hubCode"></span></small>
                    </div>
                    <hr class="my-2">
                    <small class="text-danger">
                        <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                        Warning: Hub can only be deleted if it has no riders or parcels assigned.
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
                        Move to Trash
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/hubs.js') }}"></script>
@endpush
