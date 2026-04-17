@extends('layouts.admin')

@section('title', 'Trash - Deleted Riders')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">
                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                Deleted Riders (Trash)
            </h5>
            <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">
                <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                Back to Riders
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
            <strong>Note:</strong> Riders in trash are not permanently deleted. You can restore them or permanently delete them.
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Hub</th>
                        <th>Deleted By</th>
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riders as $rider)
                    <tr>
                        <td>{{ $rider->id }}</small></td>
                        <td><span class="fw-bold">{{ $rider->employee_id }}</span></small></td>
                        <td>{{ $rider->user->name ?? 'N/A' }}</small></td>
                        <td>{{ $rider->user->email ?? 'N/A' }}</small></td>
                        <td>{{ $rider->user->phone ?? 'N/A' }}</small></td>
                        <td>{{ $rider->hub->name ?? 'N/A' }}</small></td>
                        <td>{{ $rider->deleter->name ?? 'System' }}</small></td>
                        <td>{{ $rider->deleted_at->format('d M Y h:i A') }}</small></small></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" onclick="showRestoreModal({{ $rider->id }}, '{{ $rider->employee_id }}')" title="Restore">
                                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                    Restore
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="showForceDeleteModal({{ $rider->id }}, '{{ $rider->employee_id }}')" title="Permanently Delete">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                    Permanent Delete
                                </button>
                            </div>
                         </small>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                <p class="mt-3 text-muted">No deleted riders found</p>
                                <a href="{{ route('admin.riders.index') }}" class="btn btn-primary btn-sm">View Active Riders</a>
                            </small>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $riders->links() }}
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Restore Rider</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <iconify-icon icon="solar:refresh-circle-line-duotone" class="fs-1 text-success mb-3"></iconify-icon>
                <h5>Restore this rider?</h5>
                <p id="restoreMessage"></p>
                <form id="restoreForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Yes, Restore</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <h5 class="modal-title">Permanently Delete Rider</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-danger mb-3"></iconify-icon>
                <h5>Are you absolutely sure?</h5>
                <p id="forceDeleteMessage"></p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <form id="forceDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Permanently Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showRestoreModal(id, employeeId) {
        $('#restoreMessage').html(`Rider <strong>${employeeId}</strong> will be restored.`);
        $('#restoreForm').attr('action', `/admin/riders/${id}/restore`);
        $('#restoreModal').modal('show');
    }

    function showForceDeleteModal(id, employeeId) {
        $('#forceDeleteMessage').html(`Rider <strong>${employeeId}</strong> will be permanently deleted.`);
        $('#forceDeleteForm').attr('action', `/admin/riders/${id}/force-delete`);
        $('#forceDeleteModal').modal('show');
    }
</script>
@endsection
