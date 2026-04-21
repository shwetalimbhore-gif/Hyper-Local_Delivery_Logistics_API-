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
            <table class="table table-hover" id="ridersTable">
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
                    @foreach($riders as $rider)
                    <tr>
                        <td>{{ $rider->id }}</small></td>
                        <td><span class="fw-bold">{{ $rider->employee_id }}</span></small></td>
                        <td>{{ $rider->user->name }}</small></td>
                        <td>{{ $rider->user->email }}</small></td>
                        <td>{{ $rider->user->phone }}</small></td>
                        <td>{{ $rider->hub->name ?? 'N/A' }}</small></td>
                        <td>
                            @php
                                $vehicleBadge = '';
                                switch($rider->vehicle_type) {
                                    case 'bike': $vehicleBadge = 'bg-primary'; break;
                                    case 'scooter': $vehicleBadge = 'bg-info'; break;
                                    case 'bicycle': $vehicleBadge = 'bg-success'; break;
                                    case 'car': $vehicleBadge = 'bg-warning'; break;
                                    case 'truck': $vehicleBadge = 'bg-danger'; break;
                                    default: $vehicleBadge = 'bg-secondary';
                                }
                            @endphp
                            <span class="badge {{ $vehicleBadge }}">{{ ucfirst($rider->vehicle_type) }}</span>
                         </small>
                        <td>
                            @if($rider->status == 'available')
                                <span class="badge bg-success">Available</span>
                            @elseif($rider->status == 'busy')
                                <span class="badge bg-warning">Busy</span>
                            @else
                                <span class="badge bg-secondary">Offline</span>
                            @endif
                         </small>
                        <td>{{ $rider->total_deliveries }}</small></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-1">{{ number_format($rider->rating, 1) }}</span>
                                <iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon>
                            </div>
                         </small>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.riders.show', $rider->id) }}" class="btn btn-sm btn-info" title="View">
                                    <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                                </a>
                                <a href="{{ route('admin.riders.edit', $rider->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Move to Trash" onclick="confirmSoftDelete({{ $rider->id }}, '{{ addslashes($rider->user->name) }}', '{{ $rider->employee_id }}')">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                </button>
                            </div>
                         </small>
                    </tr>
                    @endforeach
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
            responsive: true,
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
