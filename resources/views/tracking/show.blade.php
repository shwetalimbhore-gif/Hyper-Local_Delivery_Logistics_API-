<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tracking Results - {{ $parcel->tracking_number }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
    <style>
        .tracking-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }
        .tracking-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 15px 0 0;
            padding: 20px 25px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
            transition: all 0.3s;
        }
        .info-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Timeline Styles */
        .tracking-timeline {
            position: relative;
            padding: 30px 0 20px 0;
        }

        .timeline-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-steps:before {
            content: '';
            position: absolute;
            top: 30px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e9ecef;
            z-index: 1;
        }

        .timeline-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .timeline-icon {
            width: 60px;
            height: 60px;
            background: white;
            border: 3px solid #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            background: white;
            transition: all 0.3s;
        }

        .timeline-step.completed .timeline-icon {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }

        .timeline-step.active .timeline-icon {
            border-color: #764ba2;
            background: #764ba2;
            color: white;
            box-shadow: 0 0 0 5px rgba(118, 75, 162, 0.2);
        }

        .timeline-step.pending .timeline-icon {
            border-color: #e9ecef;
            background: white;
            color: #adb5bd;
        }

        .timeline-label {
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }

        .timeline-step.completed .timeline-label {
            color: #28a745;
        }

        .timeline-step.active .timeline-label {
            color: #764ba2;
        }

        .timeline-step.pending .timeline-label {
            color: #adb5bd;
        }

        .timeline-date {
            font-size: 10px;
            color: #6c757d;
            margin-top: 4px;
        }

        /* Status History List */
        .status-history-list {
            margin-top: 30px;
        }

        .history-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
            position: relative;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .history-icon.pending {
            background: #fff3cd;
            color: #ffc107;
        }

        .history-icon.completed {
            background: #d4edda;
            color: #28a745;
        }

        .history-icon.current {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .history-content {
            flex: 1;
        }

        .history-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .history-date {
            font-size: 11px;
            color: #6c757d;
        }

        .history-note {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .refresh-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1000;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
        }
        .pulse {
            animation: pulse 1.5s infinite;
        }
        .page-wrapper {
            background: #f4f6f9;
            min-height: 100vh;
        }
        .progress-custom {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
        }
        .progress-bar {
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper">
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
                                        $progress = 0;
                                        switch($parcel->status->slug) {
                                            case 'pending': $progress = 10; break;
                                            case 'assigned': $progress = 25; break;
                                            case 'picked-up': $progress = 50; break;
                                            case 'out-for-delivery': $progress = 75; break;
                                            case 'delivered': $progress = 100; break;
                                            case 'failed-delivery': $progress = 80; break;
                                            default: $progress = 10;
                                        }
                                    @endphp
                                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%; background-color: {{ $parcel->status->color_code }};"></div>
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
                                <a href="{{ route('tracking.index') }}" class="btn btn-primary me-2">
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
    <button class="btn btn-primary refresh-btn pulse" onclick="refreshStatus()">
        <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
        Refresh Status
    </button>

    <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

    <script>
        function refreshStatus() {
            let trackingNumber = "{{ $parcel->tracking_number }}";

            $.ajax({
                url: "{{ route('tracking.status') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    tracking_number: trackingNumber
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    console.log('Failed to refresh status');
                }
            });
        }

        // Auto refresh every 30 seconds
        setInterval(refreshStatus, 30000);
    </script>
</body>
</html>
