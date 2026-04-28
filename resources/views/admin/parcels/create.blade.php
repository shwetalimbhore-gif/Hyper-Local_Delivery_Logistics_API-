@extends('layouts.admin')

@section('title', 'Create New Parcel')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/parcels/create.css') }}">
@endpush

@section('content')
<div class="card parcel-create-card">
    <div class="card-body">
        <h5 class="card-title">Create New Parcel</h5>

        <form action="{{ route('admin.parcels.store') }}" method="POST" id="parcelForm">
            @csrf

            <div class="row">
                <!-- Sender Information -->
                <div class="col-md-6">
                    <div class="section-header">
                        <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                        Sender Information
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="sender_name">
                            Sender Name <span class="required-star">*</span>
                        </label>
                        <input type="text" name="sender_name" id="sender_name"
                               class="form-control @error('sender_name') is-invalid @enderror"
                               value="{{ old('sender_name') }}" required>
                        @error('sender_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="sender_phone">
                            Sender Phone <span class="required-star">*</span>
                        </label>
                        <input type="text" name="sender_phone" id="sender_phone"
                               class="form-control @error('sender_phone') is-invalid @enderror"
                               value="{{ old('sender_phone') }}" required>
                        @error('sender_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="sender_email">Sender Email</label>
                        <input type="email" name="sender_email" id="sender_email"
                               class="form-control @error('sender_email') is-invalid @enderror"
                               value="{{ old('sender_email') }}">
                        @error('sender_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="sender_address">
                            Sender Address <span class="required-star">*</span>
                        </label>
                        <textarea name="sender_address" id="sender_address"
                                  class="form-control @error('sender_address') is-invalid @enderror"
                                  rows="2" required>{{ old('sender_address') }}</textarea>
                        @error('sender_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Receiver Information -->
                <div class="col-md-6">
                    <div class="section-header">
                        <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                        Receiver Information
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="receiver_name">
                            Receiver Name <span class="required-star">*</span>
                        </label>
                        <input type="text" name="receiver_name" id="receiver_name"
                               class="form-control @error('receiver_name') is-invalid @enderror"
                               value="{{ old('receiver_name') }}" required>
                        @error('receiver_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="receiver_phone">
                            Receiver Phone <span class="required-star">*</span>
                        </label>
                        <input type="text" name="receiver_phone" id="receiver_phone"
                               class="form-control @error('receiver_phone') is-invalid @enderror"
                               value="{{ old('receiver_phone') }}" required>
                        @error('receiver_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="receiver_email">Receiver Email</label>
                        <input type="email" name="receiver_email" id="receiver_email"
                               class="form-control @error('receiver_email') is-invalid @enderror"
                               value="{{ old('receiver_email') }}">
                        @error('receiver_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="receiver_address">
                            Receiver Address <span class="required-star">*</span>
                        </label>
                        <textarea name="receiver_address" id="receiver_address"
                                  class="form-control @error('receiver_address') is-invalid @enderror"
                                  rows="2" required>{{ old('receiver_address') }}</textarea>
                        @error('receiver_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Parcel Details -->
            <div class="section-header">
                <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                Parcel Details
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="parcel_name">
                            Parcel Name <span class="required-star">*</span>
                        </label>
                        <input type="text" name="parcel_name" id="parcel_name"
                               class="form-control @error('parcel_name') is-invalid @enderror"
                               value="{{ old('parcel_name') }}" required>
                        @error('parcel_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label" for="weight">
                            Weight (kg) <span class="required-star">*</span>
                        </label>
                        <input type="number" step="0.01" name="weight" id="weight"
                               class="form-control @error('weight') is-invalid @enderror"
                               value="{{ old('weight') }}" required>
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label" for="size">
                            Size (cm³) <span class="required-star">*</span>
                        </label>
                        <input type="number" step="0.01" name="size" id="size"
                               class="form-control @error('size') is-invalid @enderror"
                               value="{{ old('size') }}" required>
                        @error('size')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="parcel_type">Parcel Type</label>
                        <select name="parcel_type" id="parcel_type" class="form-control">
                            <option value="package" {{ old('parcel_type') == 'package' ? 'selected' : '' }}>Package</option>
                            <option value="document" {{ old('parcel_type') == 'document' ? 'selected' : '' }}>Document</option>
                            <option value="fragile" {{ old('parcel_type') == 'fragile' ? 'selected' : '' }}>Fragile</option>
                            <option value="electronics" {{ old('parcel_type') == 'electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="liquid" {{ old('parcel_type') == 'liquid' ? 'selected' : '' }}>Liquid</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="parcel_description">Description</label>
                <textarea name="parcel_description" id="parcel_description"
                          class="form-control" rows="2">{{ old('parcel_description') }}</textarea>
            </div>

            <!-- Delivery Information -->
            <div class="section-header">
                <iconify-icon icon="solar:truck-line-duotone"></iconify-icon>
                Delivery Information
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="delivery_charge">
                            Delivery Charge (₹) <span class="required-star">*</span>
                        </label>
                        <input type="number" step="0.01" name="delivery_charge" id="delivery_charge"
                               class="form-control @error('delivery_charge') is-invalid @enderror"
                               value="{{ old('delivery_charge') }}" required>
                        @error('delivery_charge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control">
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="source_hub_id">
                            Source Hub <span class="required-star">*</span>
                        </label>
                        <select name="source_hub_id" id="source_hub_id"
                                class="form-control @error('source_hub_id') is-invalid @enderror" required>
                            <option value="">Select Hub</option>
                            @foreach($hubs as $hub)
                                <option value="{{ $hub->id }}" {{ old('source_hub_id') == $hub->id ? 'selected' : '' }}>
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

            <div class="mb-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                    Create Parcel
                </button>
                <button type="button" class="btn btn-success" id="autoAssignBtn"
                        data-url="{{ route('admin.parcels.find-best-rider') }}">
                    <iconify-icon icon="solar:magic-stick-3-line-duotone"></iconify-icon>
                    Auto-Assign Best Rider
                </button>
                <a href="{{ route('admin.parcels.index') }}" class="btn btn-secondary">
                    <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/parcels/create.js') }}"></script>
@endpush
