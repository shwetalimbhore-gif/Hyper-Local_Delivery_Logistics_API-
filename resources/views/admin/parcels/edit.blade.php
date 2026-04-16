@extends('layouts.admin')

@section('title', 'Edit Parcel - ' . $parcel->tracking_number)

@section('content')
<div class="card">
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
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.parcels.update', $parcel->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Sender Information -->
                <div class="col-md-6">
                    <h6 class="mt-3 mb-3 text-primary">Sender Information</h6>

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

                <!-- Receiver Information -->
                <div class="col-md-6">
                    <h6 class="mt-3 mb-3 text-success">Receiver Information</h6>

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

            <!-- Parcel Details -->
            <h6 class="mt-3 mb-3 text-info">Parcel Details</h6>
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
                        <input type="number" step="0.01" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $parcel->weight) }}" required>
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Size (cm³) *</label>
                        <input type="number" step="0.01" name="size" class="form-control @error('size') is-invalid @enderror" value="{{ old('size', $parcel->size) }}" required>
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

            <!-- Delivery Information -->
            <h6 class="mt-3 mb-3 text-warning">Delivery Information</h6>
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
                        <select name="source_hub_id" class="form-control @error('source_hub_id') is-invalid @enderror" required>
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

            <!-- Assignment Information -->
            <h6 class="mt-3 mb-3">Assignment Information</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Assign Rider</label>
                        <select name="assigned_rider_id" id="riderSelect" class="form-control">
                            <option value="">-- Select Rider --</option>
                            @foreach($riders as $rider)
                                <option value="{{ $rider->id }}"
                                    data-rider-status="{{ $rider->status }}"
                                    {{ old('assigned_rider_id', $parcel->assigned_rider_id) == $rider->id ? 'selected' : '' }}>
                                    {{ $rider->user->name }} ({{ $rider->employee_id }}) - {{ ucfirst($rider->status) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Status will automatically change to "Assigned" when rider is selected</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status_id" id="statusSelect" class="form-control" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ old('status_id', $parcel->status_id) == $status->id ? 'selected' : '' }}
                                    data-status-slug="{{ $status->slug }}">
                                    {{ $status->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $parcel->notes) }}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update Parcel</button>
                <a href="{{ route('admin.parcels.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>


@endsection
