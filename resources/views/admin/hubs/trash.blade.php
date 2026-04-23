@extends('layouts.admin')

@section('title', 'Trash - Deleted Hubs')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/hubs-trash.css') }}">
@endpush

@section('content')
<div class="card trash-card">
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
                <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="alert alert-info">
            <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
            <strong>Note:</strong> Hubs in trash are not permanently deleted. You can restore them or permanently delete them.
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="trashHubsTable">
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
                    <tr id="hub-row-{{ $hub->id }}">
                        <td>{{ $hub->id }}</small></td>
                        <td><span class="fw-bold">{{ $hub->code }}</span></small></td>
                        <td>{{ $hub->name }}</small></td>
                        <td>{{ $hub->manager_name ?? 'N/A' }}</small></td>
                        <td>{{ $hub->deleted_at ? $hub->deleted_at->format('d M Y h:i A') : 'N/A' }}</small></small></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success"
                                        onclick="restoreHub({{ $hub->id }}, '{{ $hub->code }}')"
                                        title="Restore">
                                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                    Restore
                                </button>
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="forceDeleteHub({{ $hub->id }}, '{{ $hub->code }}')"
                                        title="Permanently Delete">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                    Permanent Delete
                                </button>
                            </div>
                         </small>
                    </tr>
                    @empty
                    <tr class="empty-state">
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                </div>
                                <p class="empty-state-text">No deleted hubs found</p>
                                <a href="{{ route('admin.hubs.index') }}" class="btn btn-primary btn-sm">
                                    View Active Hubs
                                </a>
                            </div>
                        </td>
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

<!-- Hidden Forms for Restore and Force Delete -->
@foreach($hubs as $hub)
<form id="restore-form-{{ $hub->id }}"
      action="{{ route('admin.hubs.restore', $hub->id) }}"
      method="POST"
      class="hidden-form">
    @csrf
</form>
<form id="force-delete-form-{{ $hub->id }}"
      action="{{ route('admin.hubs.force-delete', $hub->id) }}"
      method="POST"
      class="hidden-form">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/hubs-trash.js') }}"></script>
@endpush
