@extends('layouts.admin')

@section('title', 'Manage Riders')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/riders/index.css') }}">
@endpush

@section('content')
<div class="card riders-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Riders</h5>
            <div>
                <a href="{{ route('admin.riders.trash') }}" class="btn btn-secondary me-2">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Trash
                </a>
                <a href="{{ route('admin.riders.create') }}" class="btn btn-primary">
                    <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                    Create New Rider
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="ridersTable" width="100%" data-ajax="{{ route('admin.riders.data') }}">
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
                        <th>Total Del.</th>
                        <th>Rating</th>
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
                <h5 class="mb-3">Move this rider to trash?</h5>
                <p id="softDeleteMessage" class="mb-2"></p>
                <div class="alert alert-info small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    You can restore this rider later from the trash.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
<script src="{{ asset('assets/js/admin/riders/index.js') }}"></script>
@endpush
