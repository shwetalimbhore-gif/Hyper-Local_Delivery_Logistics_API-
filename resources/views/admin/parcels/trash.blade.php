@extends('layouts.admin')

@section('title', 'Parcel Trash')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/parcels-trash.css') }}">
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Deleted Parcels</h5>
            <a href="{{ route('admin.parcels.index') }}" class="btn btn-secondary">
                <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                Back to Parcels
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="trashTable">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcels as $parcel)
                    <tr>
                        <td>
                            <input type="checkbox" class="parcel-checkbox" value="{{ $parcel->id }}">
                        </td>
                        <td>{{ $parcel->id }}</td>
                        <td>{{ $parcel->tracking_number }}</td>
                        <td>{{ $parcel->sender_name }}</td>
                        <td>{{ $parcel->receiver_name }}</td>
                        <td>{{ $parcel->deleted_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="showRestoreModal('{{ $parcel->id }}', '{{ $parcel->tracking_number }}')">
                                <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                Restore
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="showForceDeleteModal('{{ $parcel->id }}', '{{ $parcel->tracking_number }}')">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                Delete Forever
                            </button>
                        </td>
                    </tr>
                    @endforeach
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
<script src="{{ asset('assets/js/admin/parcels-trash.js') }}"></script>
@endpush
