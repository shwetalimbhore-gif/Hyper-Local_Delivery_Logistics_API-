@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                        My Profile
                    </h5>
                </div>
                <div class="card-body">
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

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                            Please fix the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Profile Image Column -->
                        <div class="col-md-4">
                            <div class="text-center mb-4">
                                <div class="profile-image-container mb-3">
                                    @if(Auth::user()->profile_image)
                                        <img src="{{ Storage::url(Auth::user()->profile_image) }}"
                                             alt="Profile Image"
                                             class="rounded-circle img-fluid"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <div class="default-avatar rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto"
                                             style="width: 150px; height: 150px;">
                                            <span class="display-1 text-white">
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <form action="{{ url('/profile/update-picture') }}" method="POST" enctype="multipart/form-data" id="uploadImageForm">
                                    @csrf
                                    <label class="btn btn-outline-primary btn-sm">
                                        <iconify-icon icon="solar:camera-line-duotone"></iconify-icon>
                                        Change Photo
                                        <input type="file" name="profile_image" class="d-none" accept="image/*" onchange="this.form.submit()">
                                    </label>
                                </form>
                                <small class="text-muted d-block mt-2">JPG, PNG, GIF (Max 2MB)</small>
                            </div>
                        </div>

                        <!-- Profile Information Column -->
                        <div class="col-md-8">
                            <form action="{{ url('/profile/update') }}" method="POST">
                                @csrf

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', Auth::user()->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email', Auth::user()->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                               value="{{ old('phone', Auth::user()->phone ?? '9876543210') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" value="Admin" disabled>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', Auth::user()->address ?? 'Admin Office, Main Hub, Andheri East, Mumbai') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Member Since</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->created_at->format('F d, Y') }}" disabled>
                                </div>

                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                        <iconify-icon icon="solar:lock-password-line-duotone"></iconify-icon>
                                        Change Password
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <iconify-icon icon="solar:save-line-duotone"></iconify-icon>
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:lock-password-line-duotone"></iconify-icon>
                    Change Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/profile/change-password') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .default-avatar {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    }

    .profile-image-container img {
        border: 3px solid #198754;
        padding: 3px;
    }
</style>
@endpush
