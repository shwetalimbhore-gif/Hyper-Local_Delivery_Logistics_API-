<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Your Parcel - HyperLocal Delivery</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />

    <!-- Tracking CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/tracking.css') }}">
</head>
<body>
    <div class="tracking-page-wrapper">
        <div class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="card tracking-card">
                            <div class="tracking-header text-white">
                                <iconify-icon icon="solar:map-point-line-duotone" class="tracking-header-icon"></iconify-icon>
                                <h2 class="mb-2">Track Your Parcel</h2>
                                <p class="mb-0">Enter your tracking number to get real-time updates</p>
                            </div>
                            <div class="card-body tracking-form">
                                @if(session('error'))
                                    <div class="alert alert-danger tracking-alert alert-dismissible fade show" role="alert">
                                        <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <form action="{{ route('tracking.track') }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="form-label">Tracking Number</label>
                                        <div class="tracking-input-group">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <iconify-icon icon="solar:search-line-duotone"></iconify-icon>
                                                </span>
                                                <input type="text"
                                                       name="tracking_number"
                                                       class="form-control tracking-input @error('tracking_number') is-invalid @enderror"
                                                       placeholder="Enter your tracking number"
                                                       value="{{ old('tracking_number') }}"
                                                       required>
                                            </div>
                                        </div>
                                        <div class="help-text">Example: HLD20240415001</div>
                                        @error('tracking_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-track w-100 text-white">
                                        <iconify-icon icon="solar:search-line-duotone" class="me-2"></iconify-icon>
                                        Track Parcel
                                    </button>
                                </form>

                                <div class="features-section">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <iconify-icon icon="solar:clock-circle-line-duotone"></iconify-icon>
                                                </div>
                                                <div class="feature-title">Real-time Updates</div>
                                                <div class="feature-description">Instant status updates</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <iconify-icon icon="solar:map-point-line-duotone"></iconify-icon>
                                                </div>
                                                <div class="feature-title">Live Location</div>
                                                <div class="feature-description">Track in real-time</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <iconify-icon icon="solar:bell-line-duotone"></iconify-icon>
                                                </div>
                                                <div class="feature-title">SMS Alerts</div>
                                                <div class="feature-description">Delivery notifications</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tracking-footer">
                            <p class="mb-0">&copy; {{ date('Y') }} HyperLocal Delivery. All rights reserved.</p>
                            <div class="mt-2">
                                <a href="{{ url('/') }}" class="text-decoration-none me-3">Home</a>

                                <a href="{{ route('login') }}" class="text-decoration-none">Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="{{ asset('assets/js/tracking.js') }}"></script>
</body>
</html>
