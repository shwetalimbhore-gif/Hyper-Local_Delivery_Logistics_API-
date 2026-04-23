@extends('layouts.admin')

@section('title', 'Admin Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/profile.css') }}">
@endpush

@section('content')
<div class="row">
    <!-- Profile Card -->
    <div class="col-md-4 mb-4">
        <div class="card profile-card">
            <div class="card-body text-center">
                <!-- Profile Picture -->
                <div class="profile-avatar mb-3">
                    <img id="profilePreview"
                         src="{{ $admin->profile_image ? asset('storage/' . $admin->profile_image) : asset('assets/images/profile/user-1.jpg') }}"
                         alt="Profile Picture">
                    <button type="button" class="upload-btn" onclick="document.getElementById('profileImageInput').click();">
                        <iconify-icon icon="solar:camera-line-duotone"></iconify-icon>
                    </button>
                    <form id="profileImageForm" action="{{ route('admin.profile.update-picture') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        <input type="file" name="profile_image" id="profileImageInput" accept="image/*" onchange="uploadProfileImage(this)">
                    </form>
                </div>

                <h4 class="mb-1">{{ $admin->name }}</h4>
                <p class="text-muted mb-2">{{ $admin->email }}</p>
                <span class="badge bg-primary">{{ $admin->role->name ?? 'Administrator' }}</span>

                <hr class="my-3">

                <div class="row">
                    <div class="col-6">
                        <h6 class="mb-0">{{ $admin->created_at->format('d M Y') }}</h6>
                        <small class="text-muted">Joined Date</small>
                    </div>
                    <div class="col-6">
                        <h6 class="mb-0">{{ $admin->phone ?? 'N/A' }}</h6>
                        <small class="text-muted">Phone</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card stats-card">
            <div class="card-header">
                <h6 class="mb-0">Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="stats-list">
                    <div class="stats-item">
                        <span class="stats-label">Total Parcels:</span>
                        <span class="stats-value">{{ \App\Models\Parcel::count() }}</span>
                    </div>
                    <div class="stats-item">
                        <span class="stats-label">Total Riders:</span>
                        <span class="stats-value">{{ \App\Models\Rider::count() }}</span>
                    </div>
                    <div class="stats-item">
                        <span class="stats-label">Total Hubs:</span>
                        <span class="stats-value">{{ \App\Models\Hub::count() }}</span>
                    </div>
                    <div class="stats-item">
                        <span class="stats-label">Total Earnings:</span>
                        <span class="stats-value text-success">₹{{ number_format(\App\Models\Payment::where('payment_status', 'completed')->sum('amount'), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="col-md-8 mb-4">
        <div class="card form-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                    Edit Profile Information
                </h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form id="profileForm" action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $admin->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ $admin->email }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ $admin->phone }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ $admin->role->name ?? 'Admin' }}" disabled>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3">{{ $admin->address }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                        <iconify-icon icon="solar:save-line-duotone"></iconify-icon>
                        Save Changes
                    </button>
                </form>

                <div id="profileMessage" class="alert-message" style="display: none;"></div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card form-card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:lock-password-line-duotone"></iconify-icon>
                    Change Password
                </h6>
            </div>
            <div class="card-body">
                <form id="passwordForm" action="{{ route('admin.profile.change-password') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                        <iconify-icon icon="solar:lock-password-line-duotone"></iconify-icon>
                        Change Password
                    </button>
                </form>

                <div id="passwordMessage" class="alert-message" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/profile.js') }}"></script>
@endpush
