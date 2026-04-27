@extends('layouts.admin')

@section('title', 'Edit Hub')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/hubs/edit.css') }}">
@endpush

@section('content')
<div class="card hub-edit-card">
    <div class="card-body">
        <h5 class="card-title">Edit Hub: {{ $hub->name }}</h5>

        <form action="{{ route('admin.hubs.update', $hub->id) }}" method="POST" id="hubForm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            Hub Name
                            <span class="required-star">*</span>
                        </label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $hub->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Hub Code
                            <span class="required-star">*</span>
                        </label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $hub->code) }}" required>
                        <small class="text-muted">Unique identifier for the hub</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Manager Name</label>
                        <input type="text" name="manager_name" class="form-control"
                               value="{{ old('manager_name', $hub->manager_name) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $hub->phone) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $hub->email) }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            Address
                            <span class="required-star">*</span>
                        </label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="4" required>{{ old('address', $hub->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                   name="is_active"
                                   class="form-check-input"
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $hub->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active" id="statusText">
                                <strong>{{ $hub->is_active ? 'Active Hub' : 'Inactive Hub' }}</strong>
                            </label>
                            <small class="text-muted d-block mt-1">Inactive hubs won't be available for new parcels</small>
                        </div>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                    Update Hub
                </button>
                <a href="{{ route('admin.hubs.index') }}" class="btn btn-secondary">
                    <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/hubs/edit.js') }}"></script>
@endpush
