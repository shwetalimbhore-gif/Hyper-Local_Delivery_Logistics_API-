@extends('layouts.admin')

@section('title', 'Manage Parcels')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/parcels-index.css') }}">
@endpush

@section('content')
<div class="card parcels-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Parcels</h5>
            <div>
                <a href="{{ route('admin.parcels.trash') }}" class="btn btn-secondary me-2">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Trash
                </a>
                <a href="{{ route('admin.parcels.create') }}" class="btn btn-primary">
                    <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                    Create New Parcel
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="parcelsTable" width="100%" data-ajax="{{ route('admin.parcels.data') }}">
                <thead>
                    <tr>
                        <th>ID</th>
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
                <h5 class="mb-3">Move this parcel to trash?</h5>
                <p id="softDeleteMessage" class="mb-2"></p>
                <div class="alert alert-info small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    You can restore this parcel later from the trash.
                </div>
                <form id="softDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning px-4">
                            <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            Move to Trash
                        </button>
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            <iconify-icon icon="solar:close-circle-line-duotone"></iconify-icon>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/parcels-index.js') }}"></script>
@endpush
