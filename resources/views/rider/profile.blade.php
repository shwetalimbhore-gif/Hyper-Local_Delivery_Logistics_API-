@extends('layouts.rider')

@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <!-- Profile Picture -->
                <div class="mb-3 position-relative">
                    <img id="profilePreview" src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : asset('assets/images/profile/user-1.jpg') }}"
                         class="rounded-circle" width="120" height="120" style="object-fit: cover; border: 3px solid #4f46e5;">
                    <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 translate-middle-y"
                            style="border-radius: 50%; width: 32px; height: 32px; padding: 0;"
                            onclick="document.getElementById('profileImageInput').click();">
                        <iconify-icon icon="solar:camera-line-duotone"></iconify-icon>
                    </button>
                    <form id="profileImageForm" action="{{ route('rider.profile.update-image') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        <input type="file" name="profile_image" id="profileImageInput" accept="image/*" onchange="this.form.submit()">
                    </form>
                </div>

                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-2">{{ $rider->employee_id }}</p>
                <div class="mb-3">
                    @if($rider->status == 'available')
                        <span class="badge bg-success">Available</span>
                    @elseif($rider->status == 'busy')
                        <span class="badge bg-warning">Busy</span>
                    @else
                        <span class="badge bg-secondary">Offline</span>
                    @endif
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <h6 class="mb-0">{{ $rider->total_deliveries }}</h6>
                        <small class="text-muted">Deliveries</small>
                    </div>
                    <div class="col-6">
                        <h6 class="mb-0">{{ number_format($rider->rating, 1) }} <iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon></h6>
                        <small class="text-muted">Rating</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Profile Information</h6>
                <button type="button" class="btn btn-sm btn-primary" id="editProfileBtn">
                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                    Edit Profile
                </button>
            </div>
            <div class="card-body">
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

                <!-- View Mode -->
                <div id="viewMode">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Full Name</label>
                            <p class="fw-bold">{{ $user->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email</label>
                            <p class="fw-bold">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Phone</label>
                            <p class="fw-bold">{{ $user->phone }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Employee ID</label>
                            <p class="fw-bold">{{ $rider->employee_id }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="text-muted small">Address</label>
                            <p class="fw-bold">{{ $user->address ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Vehicle Type</label>
                            <p class="fw-bold">{{ ucfirst($rider->vehicle_type) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Vehicle Number</label>
                            <p class="fw-bold">{{ $rider->vehicle_number ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Vehicle Model</label>
                            <p class="fw-bold">{{ $rider->vehicle_model ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Joined Date</label>
                            <p class="fw-bold">{{ $rider->joined_date ? date('d M Y', strtotime($rider->joined_date)) : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Edit Mode (Hidden by default) -->
                <form id="editMode" action="{{ route('rider.profile.update') }}" method="POST" style="display: none;">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" value="{{ $rider->employee_id }}" disabled>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $user->address) }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vehicle Number</label>
                            <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number', $rider->vehicle_number) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vehicle Model</label>
                            <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model', $rider->vehicle_model) }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle between view and edit mode
    $('#editProfileBtn').click(function() {
        $('#viewMode').hide();
        $('#editMode').show();
    });

    $('#cancelEditBtn').click(function() {
        $('#viewMode').show();
        $('#editMode').hide();
    });

    // Preview profile image before upload
    $('#profileImageInput').change(function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profilePreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
@endpush
