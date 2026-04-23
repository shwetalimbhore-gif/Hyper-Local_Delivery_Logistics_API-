@extends('layouts.rider')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/rider/dashboard.css') }}">
@endpush

@section('content')
<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card welcome-card text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h3 class="text-white mb-2">Welcome back, {{ Auth::user()->name }}!</h3>
                        <p class="text-white-50 mb-0">Ready for deliveries? You have {{ $activeParcels->count() }} active parcels.</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="statusDropdown" data-bs-toggle="dropdown">
                            Status:
                            @if(Auth::user()->rider->status == 'available')
                                <span class="text-success">Available</span>
                            @elseif(Auth::user()->rider->status == 'busy')
                                <span class="text-warning">Busy</span>
                            @else
                                <span class="text-danger">Offline</span>
                            @endif
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item update-status" href="#" data-status="available">
                                    <iconify-icon icon="solar:check-circle-line-duotone" class="text-success"></iconify-icon>
                                    Available
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item update-status" href="#" data-status="busy">
                                    <iconify-icon icon="solar:clock-circle-line-duotone" class="text-warning"></iconify-icon>
                                    Busy
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item update-status" href="#" data-status="offline">
                                    <iconify-icon icon="solar:power-off-line-duotone" class="text-danger"></iconify-icon>
                                    Offline
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-primary text-white stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Deliveries</h6>
                        <h2 class="text-white mb-0">{{ $totalDeliveries }}</h2>
                    </div>
                    <iconify-icon icon="solar:box-line-duotone" class="card-icon text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-success text-white stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Successful</h6>
                        <h2 class="text-white mb-0">{{ $successfulDeliveries }}</h2>
                    </div>
                    <iconify-icon icon="solar:check-circle-line-duotone" class="card-icon text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-info text-white stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Success Rate</h6>
                        <h2 class="text-white mb-0">{{ $successRate }}%</h2>
                    </div>
                    <iconify-icon icon="solar:chart-line-duotone" class="card-icon text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-warning text-dark stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-dark-50 mb-1">Total Earnings</h6>
                        <h2 class="text-dark mb-0">₹{{ number_format($totalEarnings, 2) }}</h2>
                    </div>
                    <iconify-icon icon="solar:wallet-money-line-duotone" class="card-icon"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Active Parcels -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                    Active Parcels
                </h6>
            </div>
            <div class="card-body">
                @if($activeParcels->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($activeParcels as $parcel)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="tracking-number">{{ $parcel->tracking_number }}</div>
                                    <div class="receiver-name">{{ $parcel->receiver_name }}</div>
                                    <div class="receiver-address">{{ $parcel->receiver_address }}</div>
                                    <div class="mt-2">
                                        <span class="status-badge" style="background-color: {{ $parcel->status->color_code }}; color: white;">
                                            {{ $parcel->status->display_name }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('rider.parcels.index') }}" class="btn btn-sm btn-primary ms-3">
                                    <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                                    View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                        </div>
                        <p class="empty-state-text">No active parcels</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Deliveries -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:check-circle-line-duotone"></iconify-icon>
                    Recent Deliveries
                </h6>
            </div>
            <div class="card-body">
                @if($recentDeliveries->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentDeliveries as $parcel)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="tracking-number">{{ $parcel->tracking_number }}</div>
                                    <div class="receiver-name">{{ $parcel->receiver_name }}</div>
                                    <div class="receiver-address">{{ $parcel->delivered_at->diffForHumans() }}</div>
                                    <div class="mt-2">
                                        <span class="status-badge status-delivered">Delivered</span>
                                    </div>
                                </div>
                                <div class="earnings-amount">₹{{ number_format($parcel->delivery_charge, 2) }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                        </div>
                        <p class="empty-state-text">No deliveries yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Weekly Earnings Chart -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:chart-line-duotone"></iconify-icon>
                    Weekly Earnings
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="earningsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass data from PHP to JavaScript
    window.weeklyEarningsData = {
        labels: {!! json_encode($weeklyEarnings->pluck('date')->map(function($date) { return date('D', strtotime($date)); })) !!},
        values: {!! json_encode($weeklyEarnings->pluck('total')) !!}
    };
</script>
<script src="{{ asset('assets/js/rider/dashboard.js') }}"></script>
@endpush
