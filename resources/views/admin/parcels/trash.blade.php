@extends('layouts.admin')

@section('title', 'Parcel Trash')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/admin/parcels-trash.css') }}">
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Deleted Parcels</h5>
            <div>
                <button type="button" class="btn btn-success me-2" id="bulkRestoreBtn">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Bulk Restore
                </button>
                <button type="button" class="btn btn-danger me-2" id="bulkForceDeleteBtn">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Bulk Delete
                </button>
                <a href="{{ route('admin.parcels.index') }}" class="btn btn-secondary">
                    <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                    Back to Parcels
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="trashTable" width="100%">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>ID</th>
                        <th>Tracking #</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Deleted At</th>
                        <th width="220">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded via AJAX from DataTable -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Restore Parcel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:refresh-line-duotone" class="fs-1 text-success mb-3"></iconify-icon>
                <h5 class="mb-3">Restore this parcel?</h5>
                <p id="restoreMessage" class="mb-2"></p>
                <form id="restoreForm" method="POST">
                    @csrf
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success px-4">Yes, Restore</button>
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Force Delete Modal -->
<div class="modal fade" id="forceDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Permanently Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-danger mb-3"></iconify-icon>
                <h5 class="mb-3">Permanently delete this parcel?</h5>
                <p id="forceDeleteMessage" class="mb-2"></p>
                <div class="alert alert-warning small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    This action cannot be undone!
                </div>
                <form id="forceDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mt-3">
                        <button type="submit" class="btn btn-danger px-4">Yes, Delete Forever</button>
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/js/admin/parcels-trash.js') }}"></script>
@endpush
