@extends('layouts.admin')

@section('title', 'Manage Riders')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Riders</h5>
            <a href="{{ route('admin.riders.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                Add New Rider
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
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
                        <td>{{ $rider->id }}</td>
                        <td><span class="fw-bold">{{ $rider->employee_id }}</span></td>
                        <td>{{ $rider->user->name }}</small></td>
                        <td>{{ $rider->user->email }}</small></td>
                        <td>{{ $rider->user->phone }}</small></td>
                        <td>{{ $rider->hub->name ?? 'N/A' }}</small></td>
                        <td><span class="badge bg-info">{{ ucfirst($rider->vehicle_type) }}</span></small></td>
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
                                <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="showDeleteModal({{ $rider->id }}, '{{ $rider->user->name }}', '{{ $rider->employee_id }}')">
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-danger mb-3"></iconify-icon>
                <h5>Are you sure?</h5>
                <p id="deleteRiderInfo"></p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

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
                zeroRecords: "No riders found"
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', text: 'Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdf', text: 'PDF', className: 'btn btn-danger btn-sm' },
                { extend: 'print', text: 'Print', className: 'btn btn-secondary btn-sm' }
            ]
        });
    });

    function showDeleteModal(id, name, employeeId) {
        $('#deleteRiderInfo').html(`<strong>${name}</strong><br><small>Employee ID: ${employeeId}</small>`);
        $('#deleteForm').attr('action', `/admin/riders/${id}`);
        $('#deleteModal').modal('show');
    }
</script>
@endpush
