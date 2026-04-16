@extends('layouts.admin')

@section('title', 'Edit Rider - ' . $rider->user->name)

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Edit Rider: {{ $rider->user->name }}</h5>
            <a href="{{ route('admin.riders.show', $rider->id) }}" class="btn btn-secondary btn-sm">
                <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                Back
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.riders.update', $rider->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6">
                    <h6 class="mt-3 mb-3 text-primary">Personal Information</h6>

                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $rider->user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $rider->user->email) }}" required readonly>
                        <small class="text-muted">Email cannot be changed</small>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $rider->user->phone) }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $rider->user->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="available" {{ old('status', $rider->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="busy" {{ old('status', $rider->status) == 'busy' ? 'selected' : '' }}>Busy</option>
                            <option value="offline" {{ old('status', $rider->status) == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Rider Details -->
                <div class="col-md-6">
                    <h6 class="mt-3 mb-3 text-success">Rider Details</h6>

                    <div class="mb-3">
                        <label class="form-label">Employee ID *</label>
                        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id', $rider->employee_id) }}" required>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hub *</label>
                        <select name="hub_id" class="form-control @error('hub_id') is-invalid @enderror" required>
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

                    <div class="mb-3">
                        <label class="form-label">Vehicle Type *</label>
                        <select name="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror" required>
                            <option value="bike" {{ old('vehicle_type', $rider->vehicle_type) == 'bike' ? 'selected' : '' }}>Bike</option>
                            <option value="scooter" {{ old('vehicle_type', $rider->vehicle_type) == 'scooter' ? 'selected' : '' }}>Scooter</option>
                            <option value="bicycle" {{ old('vehicle_type', $rider->vehicle_type) == 'bicycle' ? 'selected' : '' }}>Bicycle</option>
                            <option value="car" {{ old('vehicle_type', $rider->vehicle_type) == 'car' ? 'selected' : '' }}>Car</option>
                            <option value="truck" {{ old('vehicle_type', $rider->vehicle_type) == 'truck' ? 'selected' : '' }}>Truck</option>
                        </select>
                        @error('vehicle_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Number</label>
                        <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number', $rider->vehicle_number) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Model</label>
                        <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model', $rider->vehicle_model) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $rider->license_number) }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Weight Capacity (kg)</label>
                                <input type="number" step="0.01" name="max_weight_capacity" class="form-control" value="{{ old('max_weight_capacity', $rider->max_weight_capacity) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Size Capacity (cm³)</label>
                                <input type="number" step="0.01" name="max_size_capacity" class="form-control" value="{{ old('max_size_capacity', $rider->max_size_capacity) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update Rider</button>
                <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
