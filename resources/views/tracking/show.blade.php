<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tracking Results - {{ $parcel->tracking_number }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />

    <!-- Tracking Show CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/tracking-show.css') }}">
</head>
<body>
    <div class="tracking-show-wrapper">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Tracking Card -->
                    <div class="card tracking-card">
                        <div class="tracking-header text-white">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <iconify-icon icon="solar:box-line-duotone" class="fs-3 me-2"></iconify-icon>
                                    <strong>Parcel Details</strong>
                                    <p class="mb-0 mt-1 small opacity-75">Tracking Number: {{ $parcel->tracking_number }}</p>
                                </div>
                                <div class="mt-2 mt-sm-0">
                                    <span class="status-badge" style="background-color: {{ $parcel->status->color_code }}; color: white;">
                                        <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                                        {{ $parcel->status->display_name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">

                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="progress-custom">
                                    @php
                                        $progress = 10;
                                        switch($parcel->status->slug) {
                                            case 'assigned': $progress = 25; break;
                                            case 'picked-up': $progress = 50; break;
                                            case 'out-for-delivery': $progress = 75; break;
                                            case 'delivered': $progress = 100; break;
                                            case 'failed-delivery': $progress = 80; break;
                                            default: $progress = 10;
                                        }
                                    @endphp
                                    <div class="progress-bar-custom" role="progressbar" style="width: {{ $progress }}%; background-color: {{ $parcel->status->color_code }};"></div>
                                </div>
                            </div>

                            <!-- Timeline Steps -->
                            <div class="tracking-timeline">
                                <div class="timeline-steps">
                                    @php
                                        $statuses = ['pending', 'assigned', 'picked-up', 'out-for-delivery', 'delivered'];
                                        $statusLabels = ['Order Placed', 'Assigned', 'Picked Up', 'Out for Delivery', 'Delivered'];
                                        $currentStatus = $parcel->status->slug;
                                    @endphp

                                    @foreach($statuses as $index => $status)
                                        @php
                                            $stepStatus = 'pending';
                                            if ($currentStatus == $status) {
                                                $stepStatus = 'active';
                                            } elseif (array_search($currentStatus, $statuses) > $index) {
                                                $stepStatus = 'completed';
                                            }
                                        @endphp
                                        <div class="timeline-step {{ $stepStatus }}">
                                            <div class="timeline-icon">
                                                @if($stepStatus == 'completed')
                                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                                                @elseif($stepStatus == 'active')
                                                    <iconify-icon icon="solar:clock-circle-bold"></iconify-icon>
                                                @else
                                                    <iconify-icon icon="solar:circle-line-duotone"></iconify-icon>
                                                @endif
                                            </div>
                                            <div class="timeline-label">{{ $statusLabels[$index] }}</div>
                                            @if($stepStatus == 'completed' && $parcel->delivered_at && $status == 'delivered')
                                                <div class="timeline-date">{{ $parcel->delivered_at->format('d M') }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Parcel Information -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <h6 class="text-primary mb-2">
                                            <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                                            Sender Information
                                        </h6>
                                        <hr class="my-2">
                                        <p class="mb-1"><strong>Name:</strong> {{ $parcel->sender_name }}</p>
                                        <p class="mb-1"><strong>Phone:</strong> {{ $parcel->sender_phone }}</p>
                                        <p class="mb-0"><strong>Address:</strong> {{ Str::limit($parcel->sender_address, 60) }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <h6 class="text-success mb-2">
                                            <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                                            Receiver Information
                                        </h6>
                                        <hr class="my-2">
                                        <p class="mb-1"><strong>Name:</strong> {{ $parcel->receiver_name }}</p>
                                        <p class="mb-1"><strong>Phone:</strong> {{ $parcel->receiver_phone }}</p>
                                        <p class="mb-0"><strong>Address:</strong> {{ Str::limit($parcel->receiver_address, 60) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Information -->
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <div class="info-card text-center">
                                        <iconify-icon icon="solar:box-line-duotone" class="fs-3 text-primary"></iconify-icon>
                                        <h6 class="mt-2 mb-1">Weight</h6>
                                        <p class="mb-0">{{ $parcel->weight }} kg</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-card text-center">
                                        <iconify-icon icon="solar:ruler-line-duotone" class="fs-3 text-primary"></iconify-icon>
                                        <h6 class="mt-2 mb-1">Size</h6>
                                        <p class="mb-0">{{ $parcel->size }} cm³</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-card text-center">
                                        <iconify-icon icon="solar:wallet-money-line-duotone" class="fs-3 text-primary"></iconify-icon>
                                        <h6 class="mt-2 mb-1">Delivery Charge</h6>
                                        <p class="mb-0">₹{{ number_format($parcel->delivery_charge, 2) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Rider Information -->
                            @if($parcel->assignedRider)
                            <div class="info-card mt-2">
                                <h6 class="text-info mb-2">
                                    <iconify-icon icon="solar:bicycle-line-duotone"></iconify-icon>
                                    Rider Information
                                </h6>
                                <hr class="my-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Name:</strong> {{ $parcel->assignedRider->user->name }}</p>
                                        <p class="mb-1"><strong>Employee ID:</strong> {{ $parcel->assignedRider->employee_id }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Vehicle:</strong> {{ ucfirst($parcel->assignedRider->vehicle_type) }}</p>
                                        <p class="mb-0"><strong>Vehicle Number:</strong> {{ $parcel->assignedRider->vehicle_number ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Status History Timeline -->
                            <div class="status-history-list mt-4">
                                <h6 class="mb-3">
                                    <iconify-icon icon="solar:clock-circle-line-duotone"></iconify-icon>
                                    Status History
                                </h6>

                                @foreach($parcel->statusHistories as $history)
                                <div class="history-item">
                                    <div class="history-icon
                                        @if($history->status->slug == 'delivered') completed
                                        @elseif($loop->first) current
                                        @else pending
                                        @endif">
                                        @if($history->status->slug == 'delivered')
                                            <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                                        @elseif($loop->first)
                                            <iconify-icon icon="solar:clock-circle-bold"></iconify-icon>
                                        @else
                                            <iconify-icon icon="solar:circle-line-duotone"></iconify-icon>
                                        @endif
                                    </div>
                                    <div class="history-content">
                                        <div class="history-title">{{ $history->status->display_name }}</div>
                                        <div class="history-date">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                                        @if($history->notes)
                                            <div class="history-note">{{ $history->notes }}</div>
                                        @endif
                                        @if($history->updater)
                                            <div class="history-note">Updated by: {{ $history->updater->name }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- Action Buttons -->
                            <div class="text-center mt-4 pt-2">
                                <a href="{{ route('tracking.index') }}" class="btn btn-primary me-2" id="trackAnotherUrl" data-url="{{ route('tracking.index') }}">
                                    <iconify-icon icon="solar:search-line-duotone"></iconify-icon>
                                    Track Another
                                </a>
                                <a href="{{ url('/') }}" class="btn btn-secondary">
                                    <iconify-icon icon="solar:home-line-duotone"></iconify-icon>
                                    Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refresh Button -->
    <button class="btn btn-primary refresh-btn pulse" id="refreshBtn" onclick="refreshStatus()">
        <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
        Refresh Status
    </button>

    <!-- Hidden elements for JavaScript -->
    <div id="refreshError" style="display: none;"></div>
    <div id="refreshStatusUrl" data-url="{{ route('tracking.status') }}" style="display: none;"></div>
    <div id="trackingNumber" data-tracking="{{ $parcel->tracking_number }}" style="display: none;"></div>

    <!-- Scripts -->
    <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="{{ asset('assets/js/tracking-show.js') }}"></script>
</body>
</html>
