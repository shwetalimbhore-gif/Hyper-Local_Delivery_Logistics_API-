@extends('layouts.rider')

@section('title', 'My Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/rider/profile.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card profile-card text-center">
            <div class="card-body">
                <!-- Profile Picture -->
                <!-- Profile Picture -->
                <div class="profile-avatar">
                    @php
                        $profileImage = Auth::user()->profile_image;
                        $defaultImage = asset('assets/images/profile/user-1.jpg');
                        $imageUrl = $profileImage ? asset('storage/' . $profileImage) : $defaultImage;
                    @endphp
                    <img id="profilePreview"
                        src="{{ $imageUrl }}"
                        alt="Profile Picture"
                        width="150"
                        height="150"
                        class="rounded-circle"
                        style="object-fit: cover; border: 3px solid #4f46e5;"
                        onerror="this.src='{{ asset('assets/images/profile/user-1.jpg') }}'">
                    <button type="button" class="upload-btn" onclick="document.getElementById('profileImageInput').click();">
                        <iconify-icon icon="solar:camera-line-duotone"></iconify-icon>
                    </button>
                    <form id="profileImageForm" action="{{ route('rider.profile.update-image') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        <input type="file" name="profile_image" id="profileImageInput" accept="image/*" onchange="uploadProfileImage(this)">
                    </form>
                </div>

                <h5 class="profile-name">{{ $user->name }}</h5>
                <p class="profile-employee-id">{{ $rider->employee_id }}</p>

                <div class="mb-3">
                    @if($rider->status == 'available')
                        <span class="badge badge-available">Available</span>
                    @elseif($rider->status == 'busy')
                        <span class="badge badge-busy">Busy</span>
                    @else
                        <span class="badge badge-offline">Offline</span>
                    @endif
                </div>

                <hr>

                <div class="stats-row">
                    <div class="stat-item">
                        <h6 class="stat-value">{{ $rider->total_deliveries }}</h6>
                        <small class="stat-label">Deliveries</small>
                    </div>
                    <div class="stat-item">
                        <h6 class="stat-value">{{ number_format($rider->rating, 1) }} <iconify-icon icon="solar:star-bold" class="rating-stars"></iconify-icon></h6>
                        <small class="stat-label">Rating</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card info-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                    Profile Information
                </h6>
                <button type="button" class="btn btn-sm btn-primary edit-profile-btn" id="editProfileBtn">
                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                    Edit Profile
                </button>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show alert-message" role="alert">
                        <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show alert-message" role="alert">
                        <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- View Mode -->
                <div id="viewMode" class="view-mode">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">{{ $user->name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $user->email }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Phone</div>
                            <div class="info-value">{{ $user->phone }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Employee ID</div>
                            <div class="info-value">{{ $rider->employee_id }}</div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="info-label">Address</div>
                            <div class="info-value">{{ $user->address ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Vehicle Type</div>
                            <div class="info-value">{{ ucfirst($rider->vehicle_type) }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Vehicle Number</div>
                            <div class="info-value">{{ $rider->vehicle_number ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Vehicle Model</div>
                            <div class="info-value">{{ $rider->vehicle_model ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Joined Date</div>
                            <div class="info-value">{{ $rider->joined_date ? date('d M Y', strtotime($rider->joined_date)) : 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Edit Mode (Hidden by default) -->
                <form id="editMode" class="edit-mode" action="{{ route('rider.profile.update') }}" method="POST">
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
                        <button type="submit" class="btn btn-primary">
                            <iconify-icon icon="solar:save-line-duotone"></iconify-icon>
                            Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelEditBtn">
                            <iconify-icon icon="solar:close-circle-line-duotone"></iconify-icon>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/rider/profile.js') }}"></script>
@endpush
