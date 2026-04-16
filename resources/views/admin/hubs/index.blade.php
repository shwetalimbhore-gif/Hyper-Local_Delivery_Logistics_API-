@extends('layouts.admin')

@section('title', 'Manage Hubs')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Hubs</h5>
            <a href="{{ route('admin.hubs.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                Add New Hub
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover" id="hubsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Manager</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Riders</th>
                        <th>Parcels</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hubs as $hub)
                    <tr>
                        <td>{{ $hub->id }}</small></td>
                        <td><span class="fw-bold">{{ $hub->code }}</span></small></td>
                        <td>{{ $hub->name }}</small></td>
                        <td>{{ $hub->manager_name ?? 'N/A' }}</small></td>
                        <td>{{ $hub->phone ?? 'N/A' }}</small></td>
                        <td>{{ $hub->email ?? 'N/A' }}</small></td>
                        <td><span class="badge bg-info">{{ $hub->riders()->count() }}</span></small></td>
                        <td><span class="badge bg-secondary">{{ $hub->sourceParcels()->count() }}</span></small></td>
                        <td>
                            @if($hub->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                         </small>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.hubs.show', $hub->id) }}" class="btn btn-sm btn-info" title="View">
                                    <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                                </a>
                                <a href="{{ route('admin.hubs.edit', $hub->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                                </a>
                                <a href="{{ route('admin.hubs.toggle-status', $hub->id) }}" class="btn btn-sm {{ $hub->is_active ? 'btn-secondary' : 'btn-success' }}" title="{{ $hub->is_active ? 'Deactivate' : 'Activate' }}">
                                    <iconify-icon icon="{{ $hub->is_active ? 'solar:power-off-line-duotone' : 'solar:power-on-line-duotone' }}"></iconify-icon>
                                </a>
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#hubsTable').DataTable({
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 15,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                zeroRecords: "No hubs found"
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', text: 'Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdf', text: 'PDF', className: 'btn btn-danger btn-sm' },
                { extend: 'print', text: 'Print', className: 'btn btn-secondary btn-sm' }
            ]
        });
    });
</script>
@endpush
