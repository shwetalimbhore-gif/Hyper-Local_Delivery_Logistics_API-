<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Your Parcel - HyperLocal Delivery</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
    <style>
        .tracking-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: none;
        }
        .tracking-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 15px 0 0;
            padding: 30px;
        }
        .tracking-input {
            border-radius: 10px;
            padding: 12px 20px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        .tracking-input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }
        .btn-track {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-track:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
        }
        .feature-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .feature-item:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
        }
        .feature-icon {
            font-size: 35px;
            color: #764ba2;
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #6c757d;
        }
        .page-wrapper {
            min-height: 100vh;
            background: #f4f6f9;
        }
    </style>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper">
        <div class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="card tracking-card">
                            <div class="tracking-header text-white text-center">
                                <iconify-icon icon="solar:map-point-line-duotone" class="fs-1 mb-2"></iconify-icon>
                                <h2 class="mb-2">Track Your Parcel</h2>
                                <p class="mb-0 opacity-75">Enter your tracking number to get real-time updates</p>
                            </div>
                            <div class="card-body p-4">
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <form action="{{ route('tracking.track') }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Tracking Number</label>
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
                                        <small class="text-muted">Example: HLD20240415001</small>
                                        @error('tracking_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-track w-100 text-white">
                                        <iconify-icon icon="solar:search-line-duotone" class="me-2"></iconify-icon>
                                        Track Parcel
                                    </button>
                                </form>

                                <div class="features mt-4 pt-3">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <iconify-icon icon="solar:clock-circle-line-duotone"></iconify-icon>
                                                </div>
                                                <h6 class="mb-1">Real-time Updates</h6>
                                                <small class="text-muted">Instant status updates</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <iconify-icon icon="solar:map-point-line-duotone"></iconify-icon>
                                                </div>
                                                <h6 class="mb-1">Live Location</h6>
                                                <small class="text-muted">Track in real-time</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="feature-item">
                                                <div class="feature-icon">
                                                    <iconify-icon icon="solar:bell-line-duotone"></iconify-icon>
                                                </div>
                                                <h6 class="mb-1">SMS Alerts</h6>
                                                <small class="text-muted">Delivery notifications</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="footer">
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

    <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>
</html>
