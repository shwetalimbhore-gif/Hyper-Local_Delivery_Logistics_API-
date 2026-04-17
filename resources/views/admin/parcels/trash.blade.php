@extends('layouts.admin')

@section('title', 'Trash - Deleted Parcels')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">
                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                Deleted Parcels (Trash)
            </h5>
            <a href="{{ route('admin.parcels.index') }}" class="btn btn-secondary">
                <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                Back to Parcels
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

        <div class="alert alert-info">
            <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
            <strong>Note:</strong> Parcels in trash are not permanently deleted. You can restore them or permanently delete them.
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="trashTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tracking #</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Status</th>
                        <th>Deleted By</th>
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parcels as $parcel)
                    <tr>
                        <td>{{ $parcel->id }}</small></td>
                        <td><span class="fw-bold">{{ $parcel->tracking_number }}</span></small></td>
                        <td>{{ $parcel->sender_name }}</small></td>
                        <td>{{ $parcel->receiver_name }}</small></td>
                        <td>
                            <span class="badge bg-danger">Deleted</span>
                         </small>
                        <td>
                            @if($parcel->deleted_by)
                                {{ $parcel->deleter->name ?? 'Unknown' }}
                            @else
                                <span class="text-muted">System</span>
                            @endif
                         </small>
                        <td>{{ $parcel->deleted_at->format('d M Y h:i A') }}</small></small>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" onclick="showRestoreModal({{ $parcel->id }}, '{{ $parcel->tracking_number }}')" title="Restore">
                                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                    Restore
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="showForceDeleteModal({{ $parcel->id }}, '{{ $parcel->tracking_number }}')" title="Permanently Delete">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                    Permanent Delete
                                </button>
                            </div>
                         </small>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                <p class="mt-3 text-muted">No deleted parcels found</p>
                                <a href="{{ route('admin.parcels.index') }}" class="btn btn-primary btn-sm">View Active Parcels</a>
                            </small>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $parcels->links() }}
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Restore Parcel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:refresh-circle-line-duotone" class="fs-1 text-success mb-3"></iconify-icon>
                <h5 class="mb-3">Restore this parcel?</h5>
                <p id="restoreMessage" class="mb-2"></p>
                <div class="alert alert-success small">
                    <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                    The parcel will be moved back to active parcels list.
                </div>
                <form id="restoreForm" method="POST">
                    @csrf
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success px-4">
                            <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                            Yes, Restore
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

<!-- Permanent Delete Confirmation Modal -->
<div class="modal fade" id="forceDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                    Permanently Delete Parcel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:danger-triangle-line-duotone" class="fs-1 text-danger mb-3"></iconify-icon>
                <h5 class="mb-3">Are you absolutely sure?</h5>
                <p id="forceDeleteMessage" class="mb-2"></p>
                <div class="alert alert-warning">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    <strong>Warning:</strong> This action cannot be undone. The parcel will be permanently deleted from the database.
                </div>
                <form id="forceDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mt-3">
                        <button type="submit" class="btn btn-danger px-4">
                            <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            Yes, Permanently Delete
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

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function showRestoreModal(id, trackingNumber) {
        $('#restoreMessage').html(`Parcel <strong>${trackingNumber}</strong> will be restored.`);
        $('#restoreForm').attr('action', `/admin/parcels/${id}/restore`);
        $('#restoreModal').modal('show');
    }

    function showForceDeleteModal(id, trackingNumber) {
        $('#forceDeleteMessage').html(`Parcel <strong>${trackingNumber}</strong> will be permanently deleted.`);
        $('#forceDeleteForm').attr('action', `/admin/parcels/${id}/force-delete`);
        $('#forceDeleteModal').modal('show');
    }
</script>
@endpush
