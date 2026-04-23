@extends('layouts.admin')

@section('title', 'Manage Parcels')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/parcels-datatable.css') }}">
@endpush

@section('content')
<div class="card parcels-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Parcels</h5>
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
            <table class="table table-hover" id="parcelsTable" width="100%" data-ajax="{{ route('admin.parcels.datatable') }}">
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
                    <!-- DataTables will populate this via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/parcels-datatable.js') }}"></script>
@endpush
