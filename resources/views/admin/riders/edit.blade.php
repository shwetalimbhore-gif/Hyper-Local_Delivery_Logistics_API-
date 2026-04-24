@extends('layouts.admin')

@section('title', 'Edit Rider')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
            Edit Rider
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.riders.update', $rider->id) }}" method="POST" id="editRiderForm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <!-- Personal Information -->
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $rider->user->name ?? '') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $rider->user->email ?? '') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $rider->user->phone ?? '') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Employee ID <span class="text-danger">*</span></label>
                        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror"
                               value="{{ old('employee_id', $rider->employee_id) }}" required>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="2">{{ old('address', $rider->user->address ?? '') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Vehicle Information -->
                    <div class="mb-3">
                        <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                        <select name="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="bike" {{ old('vehicle_type', $rider->vehicle_type) == 'bike' ? 'selected' : '' }}>Bike</option>
                            <option value="scooty" {{ old('vehicle_type', $rider->vehicle_type) == 'scooty' ? 'selected' : '' }}>Scooty</option>
                            <option value="car" {{ old('vehicle_type', $rider->vehicle_type) == 'car' ? 'selected' : '' }}>Car</option>
                            <option value="van" {{ old('vehicle_type', $rider->vehicle_type) == 'van' ? 'selected' : '' }}>Van</option>
                        </select>
                        @error('vehicle_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Number <span class="text-danger">*</span></label>
                        <input type="text" name="vehicle_number" class="form-control @error('vehicle_number') is-invalid @enderror"
                               value="{{ old('vehicle_number', $rider->vehicle_number) }}" required>
                        @error('vehicle_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Model</label>
                        <input type="text" name="vehicle_model" class="form-control @error('vehicle_model') is-invalid @enderror"
                               value="{{ old('vehicle_model', $rider->vehicle_model) }}">
                        @error('vehicle_model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Hub Assignment -->
                    <div class="mb-3">
                        <label class="form-label">Hub Assignment <span class="text-danger">*</span></label>
                        <select name="hub_id" class="form-select @error('hub_id') is-invalid @enderror" required>
                            <option value="">Select Hub</option>
                            @foreach($hubs as $hub)
                                <option value="{{ $hub->id }}" {{ old('hub_id', $rider->hub_id) == $hub->id ? 'selected' : '' }}>
                                    {{ $hub->name }} ({{ $hub->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('hub_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Capacity Information -->
                    <div class="mb-3">
                        <label class="form-label">Max Weight Capacity (kg)</label>
                        <input type="number" step="0.01" name="max_weight_capacity" class="form-control"
                               value="{{ old('max_weight_capacity', $rider->max_weight_capacity) }}" placeholder="e.g., 50">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Max Size Capacity (cubic meter)</label>
                        <input type="number" step="0.01" name="max_size_capacity" class="form-control"
                               value="{{ old('max_size_capacity', $rider->max_size_capacity) }}" placeholder="e.g., 2.5">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" class="form-control @error('license_number') is-invalid @enderror"
                               value="{{ old('license_number', $rider->license_number) }}">
                        @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Rider Status -->
                    <div class="mb-3">
                        <label class="form-label">Rider Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="available" {{ old('status', $rider->status) == 'available' ? 'selected' : '' }}>
                                🟢 Available
                            </option>
                            <option value="busy" {{ old('status', $rider->status) == 'busy' ? 'selected' : '' }}>
                                🟡 Busy
                            </option>
                            <option value="offline" {{ old('status', $rider->status) == 'offline' ? 'selected' : '' }}>
                                ⚫ Offline
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Available riders can be assigned new parcels</small>
                    </div>

                    <!-- Verification -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_verified" class="form-check-input" id="isVerified"
                                   value="1" {{ old('is_verified', $rider->is_verified) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isVerified">
                                <iconify-icon icon="solar:verified-check-line-duotone"></iconify-icon>
                                Verified Rider (KYC Approved)
                            </label>
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="Leave blank to keep current password">
                        <small class="text-muted">Leave blank if you don't want to change the password</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control"
                               placeholder="Confirm new password">
                    </div>

                    <!-- Performance Stats (Read Only) -->
                    <div class="alert alert-info mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Total Deliveries</small>
                                <h6 class="mb-0">{{ $rider->total_deliveries ?? 0 }}</h6>
                            </div>
                            <div>
                                <small class="text-muted">Successful</small>
                                <h6 class="mb-0 text-success">{{ $rider->successful_deliveries ?? 0 }}</h6>
                            </div>
                            <div>
                                <small class="text-muted">Success Rate</small>
                                <h6 class="mb-0">{{ $rider->success_rate ?? 0 }}%</h6>
                            </div>
                            <div>
                                <small class="text-muted">Earnings</small>
                                <h6 class="mb-0">₹{{ number_format($rider->earnings ?? 0, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-between">
                <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">
                    <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                    Update Rider
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#editRiderForm').on('submit', function() {
        const submitBtn = $('#submitBtn');
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
        submitBtn.prop('disabled', true);
    });
});
</script>
@endpush
