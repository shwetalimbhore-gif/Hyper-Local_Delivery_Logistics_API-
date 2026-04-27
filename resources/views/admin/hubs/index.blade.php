@extends('layouts.admin')

@section('title', 'Manage Hubs')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/hubs/index.css') }}">
@endpush

@section('content')
<div class="card hubs-card">
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
                    Create New Hub
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="hubsTable" width="100%" data-ajax="{{ route('admin.hubs.data') }}">
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
                    <!-- DataTables will populate this via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="softDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    Move to Trash
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:trash-bin-trash-line-duotone" class="fs-1 text-warning mb-3"></iconify-icon>
                <h5 class="mb-3">Move this hub to trash?</h5>
                <p id="softDeleteMessage" class="mb-2"></p>
                <div class="alert alert-info small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    You can restore this hub later from the trash.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmSoftDeleteBtn">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Move to Trash
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/hubs/index.js') }}"></script>
@endpush
