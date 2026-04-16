@extends('layouts.admin')

@section('title', 'Create New Parcel')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Create New Parcel</h5>

        <form action="{{ route('admin.parcels.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Sender Information -->
                <div class="col-md-6">
                    <h6 class="mt-3 mb-3">Sender Information</h6>

                    <div class="mb-3">
                        <label class="form-label">Sender Name *</label>
                        <input type="text" name="sender_name" class="form-control @error('sender_name') is-invalid @enderror" value="{{ old('sender_name') }}" required>
                        @error('sender_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sender Phone *</label>
                        <input type="text" name="sender_phone" class="form-control @error('sender_phone') is-invalid @enderror" value="{{ old('sender_phone') }}" required>
                        @error('sender_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sender Email</label>
                        <input type="email" name="sender_email" class="form-control @error('sender_email') is-invalid @enderror" value="{{ old('sender_email') }}">
                        @error('sender_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sender Address *</label>
                        <textarea name="sender_address" class="form-control @error('sender_address') is-invalid @enderror" rows="2" required>{{ old('sender_address') }}</textarea>
                        @error('sender_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Receiver Information -->
                <div class="col-md-6">
                    <h6 class="mt-3 mb-3">Receiver Information</h6>

                    <div class="mb-3">
                        <label class="form-label">Receiver Name *</label>
                        <input type="text" name="receiver_name" class="form-control @error('receiver_name') is-invalid @enderror" value="{{ old('receiver_name') }}" required>
                        @error('receiver_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Receiver Phone *</label>
                        <input type="text" name="receiver_phone" class="form-control @error('receiver_phone') is-invalid @enderror" value="{{ old('receiver_phone') }}" required>
                        @error('receiver_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Receiver Email</label>
                        <input type="email" name="receiver_email" class="form-control @error('receiver_email') is-invalid @enderror" value="{{ old('receiver_email') }}">
                        @error('receiver_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Receiver Address *</label>
                        <textarea name="receiver_address" class="form-control @error('receiver_address') is-invalid @enderror" rows="2" required>{{ old('receiver_address') }}</textarea>
                        @error('receiver_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Parcel Details -->
            <h6 class="mt-3 mb-3">Parcel Details</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Parcel Name *</label>
                        <input type="text" name="parcel_name" class="form-control @error('parcel_name') is-invalid @enderror" value="{{ old('parcel_name') }}" required>
                        @error('parcel_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Weight (kg) *</label>
                        <input type="number" step="0.01" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight') }}" required>
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Size (cm³) *</label>
                        <input type="number" step="0.01" name="size" class="form-control @error('size') is-invalid @enderror" value="{{ old('size') }}" required>
                        @error('size')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Parcel Type</label>
                        <select name="parcel_type" class="form-control">
                            <option value="package">Package</option>
                            <option value="document">Document</option>
                            <option value="fragile">Fragile</option>
                            <option value="electronics">Electronics</option>
                            <option value="liquid">Liquid</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="parcel_description" class="form-control" rows="2">{{ old('parcel_description') }}</textarea>
            </div>

            <!-- Delivery Charges -->
            <h6 class="mt-3 mb-3">Delivery Information</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Delivery Charge (₹) *</label>
                        <input type="number" step="0.01" name="delivery_charge" class="form-control @error('delivery_charge') is-invalid @enderror" value="{{ old('delivery_charge') }}" required>
                        @error('delivery_charge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="online">Online</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Source Hub *</label>
                        <select name="source_hub_id" class="form-control @error('source_hub_id') is-invalid @enderror" required>
                            <option value="">Select Hub</option>
                            @foreach($hubs as $hub)
                                <option value="{{ $hub->id }}" {{ old('source_hub_id') == $hub->id ? 'selected' : '' }}>{{ $hub->name }}</option>
                            @endforeach
                        </select>
                        @error('source_hub_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Create Parcel</button>
                <a href="{{ route('admin.parcels.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#riderSelect').change(function() {
                let riderId = $(this).val();

                if (riderId) {
                    showMessage('Status will be automatically set to "Assigned" when you create this parcel.', 'info');
                }
            });

            function showMessage(message, type) {
                let alertDiv = $('#autoStatusMessage');
                if (alertDiv.length === 0) {
                    $('.card-body').prepend(`<div id="autoStatusMessage" class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
                } else {
                    alertDiv.removeClass('alert-info alert-success').addClass(`alert-${type}`).html(`${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`);
                }

                setTimeout(function() {
                    $('#autoStatusMessage').fadeOut();
                }, 3000);
            }
        });
    </script>
@endpush
@endsection
