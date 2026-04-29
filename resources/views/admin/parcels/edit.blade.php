@extends('layouts.admin')

@section('title', 'Edit Parcel - ' . $parcel->tracking_number)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/parcels/edit.css') }}">
@endpush

@section('content')
<div class="card parcel-edit-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Edit Parcel: {{ $parcel->tracking_number }}</h5>
            <a href="{{ route('admin.parcels.show', $parcel->id) }}" class="btn btn-secondary btn-sm">
                <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                Back to Details
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.parcels.update', $parcel->id) }}" method="POST" id="parcelForm">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Sender Information -->
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                                Sender Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Sender Name *</label>
                                <input type="text" name="sender_name" class="form-control @error('sender_name') is-invalid @enderror" value="{{ old('sender_name', $parcel->sender_name) }}" required>
                                @error('sender_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sender Phone *</label>
                                <input type="text" name="sender_phone" class="form-control @error('sender_phone') is-invalid @enderror" value="{{ old('sender_phone', $parcel->sender_phone) }}" required>
                                @error('sender_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sender Email</label>
                                <input type="email" name="sender_email" class="form-control" value="{{ old('sender_email', $parcel->sender_email) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sender Address *</label>
                                <textarea name="sender_address" class="form-control @error('sender_address') is-invalid @enderror" rows="2" required>{{ old('sender_address', $parcel->sender_address) }}</textarea>
                                @error('sender_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Receiver Information -->
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                                Receiver Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Receiver Name *</label>
                                <input type="text" name="receiver_name" class="form-control @error('receiver_name') is-invalid @enderror" value="{{ old('receiver_name', $parcel->receiver_name) }}" required>
                                @error('receiver_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Receiver Phone *</label>
                                <input type="text" name="receiver_phone" class="form-control @error('receiver_phone') is-invalid @enderror" value="{{ old('receiver_phone', $parcel->receiver_phone) }}" required>
                                @error('receiver_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Receiver Email</label>
                                <input type="email" name="receiver_email" class="form-control" value="{{ old('receiver_email', $parcel->receiver_email) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Receiver Address *</label>
                                <textarea name="receiver_address" class="form-control @error('receiver_address') is-invalid @enderror" rows="2" required>{{ old('receiver_address', $parcel->receiver_address) }}</textarea>
                                @error('receiver_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parcel Details -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                        Parcel Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Parcel Name *</label>
                                <input type="text" name="parcel_name" class="form-control @error('parcel_name') is-invalid @enderror" value="{{ old('parcel_name', $parcel->parcel_name) }}" required>
                                @error('parcel_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Weight (kg) *</label>
                                <input type="number" step="0.01" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $parcel->weight) }}" required>
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Size (cm³) *</label>
                                <input type="number" step="0.01" name="size" id="size" class="form-control @error('size') is-invalid @enderror" value="{{ old('size', $parcel->size) }}" required>
                                @error('size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Parcel Type</label>
                                <select name="parcel_type" class="form-control">
                                    <option value="package" {{ old('parcel_type', $parcel->parcel_type) == 'package' ? 'selected' : '' }}>Package</option>
                                    <option value="document" {{ old('parcel_type', $parcel->parcel_type) == 'document' ? 'selected' : '' }}>Document</option>
                                    <option value="fragile" {{ old('parcel_type', $parcel->parcel_type) == 'fragile' ? 'selected' : '' }}>Fragile</option>
                                    <option value="electronics" {{ old('parcel_type', $parcel->parcel_type) == 'electronics' ? 'selected' : '' }}>Electronics</option>
                                    <option value="liquid" {{ old('parcel_type', $parcel->parcel_type) == 'liquid' ? 'selected' : '' }}>Liquid</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="parcel_description" class="form-control" rows="2">{{ old('parcel_description', $parcel->parcel_description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="card mb-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0">
                        <iconify-icon icon="solar:delivery-line-duotone"></iconify-icon>
                        Delivery Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Delivery Charge (₹) *</label>
                                <input type="number" step="0.01" name="delivery_charge" class="form-control @error('delivery_charge') is-invalid @enderror" value="{{ old('delivery_charge', $parcel->delivery_charge) }}" required>
                                @error('delivery_charge')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="cash" {{ old('payment_method', $parcel->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="online" {{ old('payment_method', $parcel->payment_method) == 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="card" {{ old('payment_method', $parcel->payment_method) == 'card' ? 'selected' : '' }}>Card</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Source Hub *</label>
                                <select name="source_hub_id" id="sourceHubId" class="form-control @error('source_hub_id') is-invalid @enderror" required>
                                    <option value="">Select Hub</option>
                                    @foreach($hubs as $hub)
                                        <option value="{{ $hub->id }}" {{ old('source_hub_id', $parcel->source_hub_id) == $hub->id ? 'selected' : '' }}>
                                            {{ $hub->name }} ({{ $hub->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('source_hub_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Information - FIXED SECTION -->
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <iconify-icon icon="solar:user-plus-line-duotone"></iconify-icon>
                        Assignment Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Assign Rider</label>
                                <div class="input-group">
                                    <select name="assigned_rider_id" id="riderSelect" class="form-control">
                                        <option value="">-- Select Rider --</option>
                                        @foreach($riders as $rider)
                                            <option value="{{ $rider->id }}"
                                                data-weight="{{ $rider->max_weight_capacity ?? 0 }}"
                                                data-size="{{ $rider->max_size_capacity ?? 0 }}"
                                                data-status="{{ $rider->status ?? 'unknown' }}"
                                                data-hub="{{ $rider->hub_id ?? '' }}"
                                                {{ old('assigned_rider_id', $parcel->assigned_rider_id) == $rider->id ? 'selected' : '' }}>
                                                {{ $rider->user->name ?? 'Rider #' . $rider->id }}
                                                ({{ $rider->employee_id ?? 'N/A' }}) -
                                                Max: {{ $rider->max_weight_capacity ?? 0 }}kg / {{ $rider->max_size_capacity ?? 0 }}cm³ -
                                                {{ ucfirst($rider->status ?? 'unknown') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-primary" id="autoAssignBtn"
                                            data-parcel-id="{{ $parcel->id }}"
                                            data-url="{{ route('admin.parcels.find-rider') }}"
                                            onclick="autoAssignRider()">
                                        <iconify-icon icon="solar:magic-stick-line-duotone"></iconify-icon>
                                        Auto Assign
                                    </button>
                                </div>
                                <small class="text-muted">System will find the best available rider based on weight, size, and capacity</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status *</label>
                                <select name="status_id" id="statusSelect" class="form-control @error('status_id') is-invalid @enderror" required>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}"
                                            data-status-slug="{{ $status->slug }}"
                                            {{ old('status_id', $parcel->status_id) == $status->id ? 'selected' : '' }}>
                                            {{ $status->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $parcel->notes) }}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <iconify-icon icon="solar:save-line-duotone"></iconify-icon>
                    Update Parcel
                </button>
                <a href="{{ route('admin.parcels.index') }}" class="btn btn-secondary">
                    <iconify-icon icon="solar:close-circle-line-duotone"></iconify-icon>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Auto Assign Status Message -->
<div id="autoAssignMessage" class="auto-assign-message" style="display: none;"></div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/parcels/edit.js') }}"></script>
@endpush
