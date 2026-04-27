@extends('layouts.admin')

@section('title', 'Trashed Hubs')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/hubs/trash.css') }}">
@endpush

@section('content')
<div class="card trash-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">
                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                Trashed Hubs
            </h5>
            <div>
                <a href="{{ route('admin.hubs.index') }}" class="btn btn-primary">
                    <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                    Back to Hubs
                </a>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions" id="bulkActions">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span id="selectedCount" class="selected-count">0</span> hubs selected
                </div>
                <div>
                    <button type="button" class="btn btn-success btn-sm" id="bulkRestoreBtn">
                        <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                        Restore Selected
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkForceDeleteBtn">
                        <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                        Delete Selected Forever
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="trashTable" width="100%" data-ajax="{{ route('admin.hubs.trash-data') }}">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Manager</th>
                        <th>Deleted At</th>
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

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Restore Hub
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:refresh-circle-line-duotone" class="fs-1 text-success mb-3"></iconify-icon>
                <h5 class="mb-3">Restore this hub?</h5>
                <p id="restoreMessage" class="mb-2"></p>
                <div class="alert alert-info small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    The hub will be restored to the main list.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Restore
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Force Delete Confirmation Modal -->
<div class="modal fade" id="forceDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Permanently Delete Hub
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-danger mb-3"></iconify-icon>
                <h5 class="mb-3">Permanently delete this hub?</h5>
                <p id="forceDeleteMessage" class="mb-2"></p>
                <div class="alert alert-danger small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmForceDeleteBtn">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Permanently Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/hubs/trash.js') }}"></script>
@endpush
