@extends('layouts.admin')

@section('title', 'Parcels - DataTable')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Parcels (Server-side DataTable)</h5>
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
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#parcelsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.parcels.datatable') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tracking_number', name: 'tracking_number' },
            { data: 'sender_name', name: 'sender_name' },
            { data: 'receiver_name', name: 'receiver_name' },
            { data: 'weight', name: 'weight' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            { data: 'rider_name', name: 'rider_name', orderable: false },
            { data: 'created_date', name: 'created_at' },
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

function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this parcel?')) {
        window.location.href = '/admin/parcels/' + id;
    }
}
</script>
@endpush
