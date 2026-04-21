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
            <table class="table table-hover" id="trashTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Hub</th>
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riders as $rider)
                    <tr id="rider-row-{{ $rider->id }}">
                        <td>{{ $rider->id }}</small></td>
                        <td><span class="fw-bold">{{ $rider->employee_id }}</span></small></td>
                        <td>{{ $rider->user->name ?? 'N/A' }}</small></td>
                        <td>{{ $rider->user->email ?? 'N/A' }}</small></td>
                        <td>{{ $rider->user->phone ?? 'N/A' }}</small></td>
                        <td>{{ $rider->hub->name ?? 'N/A' }}</small></td>
                        <td>{{ $rider->deleted_at ? $rider->deleted_at->format('d M Y h:i A') : 'N/A' }}</small></small></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" onclick="restoreRider({{ $rider->id }}, '{{ addslashes($rider->user->name) }}')" title="Restore">
                                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                    Restore
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="forceDeleteRider({{ $rider->id }}, '{{ $rider->employee_id }}')" title="Permanently Delete">
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

<script>
    function restoreRider(id, name) {
        if(confirm(`Restore rider "${name}"?`)) {
            document.getElementById(`restore-form-${id}`).submit();
        }
    }

    function forceDeleteRider(id, employeeId) {
        if(confirm(`Permanently delete rider ${employeeId}? This action cannot be undone.`)) {
            document.getElementById(`force-delete-form-${id}`).submit();
        }
    }
</script>

@foreach($riders as $rider)
<form id="restore-form-{{ $rider->id }}" action="{{ route('admin.riders.restore', $rider->id) }}" method="POST" style="display: none;">
    @csrf
</form>
<form id="force-delete-form-{{ $rider->id }}" action="{{ route('admin.riders.force-delete', $rider->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endsection
