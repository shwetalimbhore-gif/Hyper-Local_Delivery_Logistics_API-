@extends('layouts.admin')

@section('title', 'Trash - Deleted Hubs')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">
                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                Deleted Hubs (Trash)
            </h5>
            <a href="{{ route('admin.hubs.index') }}" class="btn btn-secondary">
                <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                Back to Hubs
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Manager</th>
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hubs as $hub)
                    <tr>
                        <td>{{ $hub->id }}</small></td>
                        <td><span class="fw-bold">{{ $hub->code }}</span></small></td>
                        <td>{{ $hub->name }}</small></td>
                        <td>{{ $hub->manager_name ?? 'N/A' }}</small></td>
                        <td>{{ $hub->deleted_at->format('d M Y h:i A') }}</small></small></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" onclick="restoreHub({{ $hub->id }}, '{{ $hub->code }}')">
                                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                    Restore
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="forceDeleteHub({{ $hub->id }}, '{{ $hub->code }}')">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                    Permanent Delete
                                </button>
                            </div>
                         </small>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                <p class="mt-3 text-muted">No deleted hubs found</p>
                            </small>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $hubs->links() }}
        </div>
    </div>
</div>

<script>
    function restoreHub(id, code) {
        if(confirm(`Restore hub ${code}?`)) {
            document.getElementById(`restore-form-${id}`).submit();
        }
    }

    function forceDeleteHub(id, code) {
        if(confirm(`Permanently delete hub ${code}? This action cannot be undone.`)) {
            document.getElementById(`force-delete-form-${id}`).submit();
        }
    }
</script>

@foreach($hubs as $hub)
<form id="restore-form-{{ $hub->id }}" action="{{ route('admin.hubs.restore', $hub->id) }}" method="POST" style="display: none;">
    @csrf
</form>
<form id="force-delete-form-{{ $hub->id }}" action="{{ route('admin.hubs.force-delete', $hub->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endsection
