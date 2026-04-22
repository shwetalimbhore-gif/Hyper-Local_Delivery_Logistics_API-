@extends('layouts.admin')

@section('title', 'Manage Riders')

@section('content')
<div class="card">
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
                    Add New Rider
                </a>
            </div>
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

        <div class="table-responsive">
            <table class="table table-hover" id="ridersTable" width="100%">
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
                        <th>Deliveries</th>
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

<!-- Soft Delete Confirmation Modal -->
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

@push('styles')
<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 15px;
    }
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
$(document).ready(function() {
    $('#ridersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.riders.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'employee_id', name: 'employee_id' },
            { data: 'full_name', name: 'user.name' },
            { data: 'email', name: 'user.email' },
            { data: 'phone', name: 'user.phone' },
            { data: 'hub_name', name: 'hub.name' },
            { data: 'vehicle_badge', name: 'vehicle_type', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'total_deliveries', name: 'total_deliveries' },
            { data: 'rating_display', name: 'rating', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
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
                title: 'Riders_Report'
            },
            {
                extend: 'pdf',
                text: '<iconify-icon icon="solar:file-text-line-duotone"></iconify-icon> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Riders_Report'
            },
            {
                extend: 'print',
                text: '<iconify-icon icon="solar:printer-line-duotone"></iconify-icon> Print',
                className: 'btn btn-secondary btn-sm'
            }
        ]
    });
});

function confirmSoftDelete(id, name, employeeId) {
    $('#softDeleteMessage').html(`Rider <strong>${name}</strong> (${employeeId}) will be moved to trash.`);
    $('#softDeleteForm').attr('action', `/admin/riders/${id}`);
    $('#softDeleteModal').modal('show');
}
</script>
@endpush
