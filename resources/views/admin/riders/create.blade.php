@extends('layouts.admin')

@section('title', 'Add New Rider')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Add New Rider</h5>

        <form action="{{ route('admin.riders.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        <small class="text-muted">Minimum 8 characters</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Rider Details -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Employee ID *</label>
                        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id') }}" placeholder="e.g., RID001" required>
                        <small class="text-muted">Unique identifier for the rider</small>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hub *</label>
                        <select name="hub_id" class="form-control @error('hub_id') is-invalid @enderror" required>
                            <option value="">Select Hub</option>
                            @foreach($hubs as $hub)
                                <option value="{{ $hub->id }}" {{ old('hub_id') == $hub->id ? 'selected' : '' }}>
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
                            <option value="bike" {{ old('vehicle_type') == 'bike' ? 'selected' : '' }}>Bike</option>
                            <option value="scooter" {{ old('vehicle_type') == 'scooter' ? 'selected' : '' }}>Scooter</option>
                            <option value="bicycle" {{ old('vehicle_type') == 'bicycle' ? 'selected' : '' }}>Bicycle</option>
                            <option value="car" {{ old('vehicle_type') == 'car' ? 'selected' : '' }}>Car</option>
                            <option value="truck" {{ old('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                        </select>
                        @error('vehicle_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Number</label>
                        <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number') }}" placeholder="e.g., MH01AB1234">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Model</label>
                        <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model') }}" placeholder="e.g., Honda Shine">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Weight (kg)</label>
                                <input type="number" step="0.01" name="max_weight_capacity" class="form-control" value="{{ old('max_weight_capacity', 50) }}">
                                <small class="text-muted">Maximum weight capacity</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Size (cm³)</label>
                                <input type="number" step="0.01" name="max_size_capacity" class="form-control" value="{{ old('max_size_capacity', 100) }}">
                                <small class="text-muted">Maximum parcel size</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Create Rider</button>
                <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
