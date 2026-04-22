@extends('layouts.admin')

@section('title', 'Manage Parcels')

@section('content')
<div class="card">
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
            <table class="table table-hover" id="parcelsTable" width="100%">
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
<script>
$(document).ready(function() {
    $('#parcelsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.parcels.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tracking_number', name: 'tracking_number' },
            { data: 'sender_name', name: 'sender_name' },
            { data: 'receiver_name', name: 'receiver_name' },
            { data: 'weight', name: 'weight' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            { data: 'rider_name', name: 'rider_name', orderable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 15,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            zeroRecords: "No records found",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<iconify-icon icon="solar:file-text-line-duotone"></iconify-icon> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Parcels_Report'
            },
            {
                extend: 'pdf',
                text: '<iconify-icon icon="solar:file-text-line-duotone"></iconify-icon> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Parcels_Report'
            },
            {
                extend: 'print',
                text: '<iconify-icon icon="solar:printer-line-duotone"></iconify-icon> Print',
                className: 'btn btn-secondary btn-sm'
            }
        ]
    });
});

function confirmSoftDelete(id, trackingNumber) {
    $('#softDeleteMessage').html(`Parcel <strong>${trackingNumber}</strong> will be moved to trash.`);
    $('#softDeleteForm').attr('action', `/admin/parcels/${id}`);
    $('#softDeleteModal').modal('show');
}
</script>
@endpush
