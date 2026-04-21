@extends('layouts.admin')

@section('title', 'Admin Profile')

@section('content')
<div class="row">
    <!-- Profile Card -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <!-- Profile Picture -->
                <div class="position-relative d-inline-block mb-3">
                    <img id="profilePreview"
                         src="{{ $admin->profile_image ? asset('storage/' . $admin->profile_image) : asset('assets/images/profile/user-1.jpg') }}"
                         class="rounded-circle"
                         width="120"
                         height="120"
                         style="object-fit: cover; border: 3px solid #4f46e5;">
                    <button type="button"
                            class="btn btn-sm btn-primary position-absolute bottom-0 end-0"
                            style="border-radius: 50%; width: 32px; height: 32px; padding: 0;"
                            onclick="document.getElementById('profileImageInput').click();">
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
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Parcels:</span>
                    <span class="fw-bold">{{ \App\Models\Parcel::count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Riders:</span>
                    <span class="fw-bold">{{ \App\Models\Rider::count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Hubs:</span>
                    <span class="fw-bold">{{ \App\Models\Hub::count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Earnings:</span>
                    <span class="fw-bold text-success">₹{{ number_format(\App\Models\Payment::where('payment_status', 'completed')->sum('amount'), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="col-md-8 mb-4">
        <div class="card">
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

                <form id="profileForm" method = "POST">
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

                <div id="profileMessage" class="mt-3" style="display: none;"></div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:lock-password-line-duotone"></iconify-icon>
                    Change Password
                </h6>
            </div>
            <div class="card-body">
                <form id="passwordForm" method = "POST">
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

                <div id="passwordMessage" class="mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Upload profile image
    function uploadProfileImage(input) {
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('profile_image', input.files[0]);

            // Preview image
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profilePreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);

            // Upload
            $.ajax({
                url: "{{ route('admin.profile.update-picture') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success) {
                        showMessage('profileMessage', 'success', response.message);
                    }
                },
                error: function(xhr) {
                    showMessage('profileMessage', 'danger', xhr.responseJSON?.message || 'Upload failed');
                }
            });
        }
    }

    // Update profile
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        $('#saveProfileBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: "{{ route('admin.profile.update') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    showMessage('profileMessage', 'success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                let message = errors ? Object.values(errors).flat().join(', ') : 'Update failed';
                showMessage('profileMessage', 'danger', message);
            },
            complete: function() {
                $('#saveProfileBtn').prop('disabled', false).html('<iconify-icon icon="solar:save-line-duotone"></iconify-icon> Save Changes');
            }
        });
    });

    // Change password
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        $('#changePasswordBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Changing...');

        $.ajax({
            url: "{{ route('admin.profile.change-password') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    showMessage('passwordMessage', 'success', response.message);
                    $('#passwordForm')[0].reset();
                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 2000);
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Password change failed';
                showMessage('passwordMessage', 'danger', message);
            },
            complete: function() {
                $('#changePasswordBtn').prop('disabled', false).html('<iconify-icon icon="solar:lock-password-line-duotone"></iconify-icon> Change Password');
            }
        });
    });

    function showMessage(elementId, type, message) {
        let alertDiv = $(`#${elementId}`);
        alertDiv.removeClass('alert-success alert-danger').addClass(`alert alert-${type}`);
        alertDiv.html(`<iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon> ${message}`);
        alertDiv.show();
        setTimeout(() => { alertDiv.fadeOut(); }, 3000);
    }
</script>
@endpush
