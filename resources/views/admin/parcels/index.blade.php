@extends('layouts.admin')

@section('title', 'Manage Parcels')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Parcels</h5>
            <a href="{{ route('admin.parcels.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                Create New Parcel
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover" id="parcelsTable">
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
                    @foreach($parcels as $parcel)
                    <tr>
                        <td>{{ $parcel->id }}</td>
                        <td><span class="fw-bold">{{ $parcel->tracking_number }}</span></td>
                        <td>{{ $parcel->sender_name }}</small></td>
                        <td>{{ $parcel->receiver_name }}</small></td>
                        <td>{{ $parcel->weight }} kg</small></td>
                        <td>
                            @php
                                $status = $parcel->status;
                                $badgeClass = '';
                                switch($status->slug) {
                                    case 'pending': $badgeClass = 'bg-warning'; break;
                                    case 'assigned': $badgeClass = 'bg-info'; break;
                                    case 'picked-up': $badgeClass = 'bg-primary'; break;
                                    case 'out-for-delivery': $badgeClass = 'bg-secondary'; break;
                                    case 'delivered': $badgeClass = 'bg-success'; break;
                                    case 'failed-delivery': $badgeClass = 'bg-danger'; break;
                                    case 'returned-to-hub': $badgeClass = 'bg-dark'; break;
                                    default: $badgeClass = 'bg-secondary';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $status->display_name }}</span>
                         </small>
                        <td>
                            @if($parcel->assignedRider)
                                {{ $parcel->assignedRider->user->name }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                         </small>
                        <td>{{ $parcel->created_at->format('d M Y') }}</small></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.parcels.show', $parcel->id) }}" class="btn btn-sm btn-info" title="View">
                                    <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                                </a>
                                <a href="{{ route('admin.parcels.edit', $parcel->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDelete({{ $parcel->id }})">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                </button>
                                <form id="delete-form-{{ $parcel->id }}" action="{{ route('admin.parcels.destroy', $parcel->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                         </small>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
        $('#parcelsTable').DataTable({
            responsive: true,
            order: [[0, 'desc']], // Order by ID descending
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
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush
