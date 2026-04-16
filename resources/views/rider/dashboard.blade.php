@extends('layouts.rider')

@section('title', 'Dashboard')

@section('content')
<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white mb-2">Welcome back, {{ Auth::user()->name }}!</h3>
                        <p class="text-white-50 mb-0">Ready for deliveries? You have {{ $activeParcels->count() }} active parcels.</p>
                    </div>
                    <div class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
                                <li><a class="dropdown-item update-status" href="#" data-status="available">Available</a></li>
                                <li><a class="dropdown-item update-status" href="#" data-status="busy">Busy</a></li>
                                <li><a class="dropdown-item update-status" href="#" data-status="offline">Offline</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Deliveries</h6>
                        <h2 class="text-white mb-0">{{ $totalDeliveries }}</h2>
                    </div>
                    <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Successful</h6>
                        <h2 class="text-white mb-0">{{ $successfulDeliveries }}</h2>
                    </div>
                    <iconify-icon icon="solar:check-circle-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Success Rate</h6>
                        <h2 class="text-white mb-0">{{ $successRate }}%</h2>
                    </div>
                    <iconify-icon icon="solar:chart-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-dark-50 mb-1">Total Earnings</h6>
                        <h2 class="text-dark mb-0">₹{{ number_format($totalEarnings, 2) }}</h2>
                    </div>
                    <iconify-icon icon="solar:wallet-money-line-duotone" class="fs-1"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Active Parcels -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Active Parcels</h6>
            </div>
            <div class="card-body">
                @if($activeParcels->count() > 0)
                    <div class="list-group">
                        @foreach($activeParcels as $parcel)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $parcel->tracking_number }}</h6>
                                    <small class="text-muted">{{ $parcel->receiver_name }} - {{ $parcel->receiver_address }}</small>
                                    <br>
                                    <span class="badge" style="background-color: {{ $parcel->status->color_code }}; color: white;">
                                        {{ $parcel->status->display_name }}
                                    </span>
                                </div>
                                <a href="{{ route('rider.parcels.index') }}" class="btn btn-sm btn-primary">View</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-muted"></iconify-icon>
                        <p class="mt-2 text-muted">No active parcels</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Deliveries -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Recent Deliveries</h6>
            </div>
            <div class="card-body">
                @if($recentDeliveries->count() > 0)
                    <div class="list-group">
                        @foreach($recentDeliveries as $parcel)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $parcel->tracking_number }}</h6>
                                    <small class="text-muted">{{ $parcel->receiver_name }} - Delivered {{ $parcel->delivered_at->diffForHumans() }}</small>
                                    <br>
                                    <span class="badge bg-success">Delivered</span>
                                </div>
                                <span class="fw-bold">₹{{ number_format($parcel->delivery_charge, 2) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-muted"></iconify-icon>
                        <p class="mt-2 text-muted">No deliveries yet</p>
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
                <h6 class="mb-0">Weekly Earnings</h6>
            </div>
            <div class="card-body">
                <canvas id="earningsChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Weekly Earnings Chart
    const ctx = document.getElementById('earningsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($weeklyEarnings->pluck('date')->map(function($date) { return date('D', strtotime($date)); })) !!},
            datasets: [{
                label: 'Earnings (₹)',
                data: {!! json_encode($weeklyEarnings->pluck('total')) !!},
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Update rider status
    $('.update-status').click(function(e) {
        e.preventDefault();
        let status = $(this).data('status');

        $.ajax({
            url: "{{ route('rider.update-status') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                status: status
            },
            success: function(response) {
                if(response.success) {
                    location.reload();
                }
            }
        });
    });
</script>
@endpush
