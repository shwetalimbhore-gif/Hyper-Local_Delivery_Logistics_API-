@extends('layouts.admin')

@section('title', 'Add New Hub')

@push('styles')
<!-- Include the separate CSS file -->
<link rel="stylesheet" href="{{ asset('assets/css/admin/hubs/create.css') }}">
@endpush

@section('content')
<div class="card hub-create-card">
    <div class="card-body">
        <h5 class="card-title">Add New Hub</h5>

        <form action="{{ route('admin.hubs.store') }}" method="POST" id="createHubForm">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            Hub Name
                            <span class="required-star">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Enter hub name"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Hub Code
                            <span class="required-star">*</span>
                        </label>
                        <input type="text"
                               name="code"
                               id="code"
                               class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code') }}"
                               placeholder="e.g., HUB001"
                               required>
                        <small class="form-text">Unique identifier (Uppercase letters, numbers, hyphens only)</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Manager Name</label>
                        <input type="text"
                               name="manager_name"
                               id="manager_name"
                               class="form-control"
                               value="{{ old('manager_name') }}"
                               placeholder="Enter manager name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text"
                               name="phone"
                               id="phone"
                               class="form-control"
                               value="{{ old('phone') }}"
                               placeholder="Enter phone number">
                        <small class="form-text">Optional: 10-digit mobile number</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control"
                               value="{{ old('email') }}"
                               placeholder="Enter email address">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            Address
                            <span class="required-star">*</span>
                        </label>
                        <textarea name="address"
                                  id="address"
                                  class="form-control @error('address') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Enter complete address"
                                  required>{{ old('address') }}</textarea>
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
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Hub
                            </label>
                            <small class="form-text d-block mt-1">Inactive hubs won't be available for new parcels</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                    Create Hub
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
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- jQuery Validation -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<!-- Custom JS -->
<script src="{{ asset('assets/js/admin/hubs/create.js') }}"></script>
@endpush
