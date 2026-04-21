@extends('layouts.rider')

@section('title', 'My Earnings')

@section('content')
<div class="row">
    <!-- Earnings Cards -->
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Earnings (All Time)</h6>
                        <h2 class="text-white mb-0">₹{{ number_format($totalEarnings, 2) }}</h2>
                    </div>
                    <iconify-icon icon="solar:wallet-money-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Period Earnings</h6>
                        <h2 class="text-white mb-0">₹{{ number_format($commissionEarnings, 2) }}</h2>
                        <small class="text-white-50">(70% commission)</small>
                    </div>
                    <iconify-icon icon="solar:calendar-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Deliveries</h6>
                        <h2 class="text-white mb-0">{{ $deliveriesCount }}</h2>
                        <small class="text-white-50">In this period</small>
                    </div>
                    <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('rider.earnings') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select" onchange="this.form.submit()">
                            <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Today</option>
                            <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>This Week</option>
                            <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>This Month</option>
                            <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="customDateRange" style="{{ $period == 'custom' ? 'display: block' : 'display: none' }}">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate instanceof \DateTime ? $startDate->format('Y-m-d') : $startDate }}">
                    </div>
                    
                    <div class="col-md-3" id="customDateRangeEnd" style="{{ $period == 'custom' ? 'display: block' : 'display: none' }}">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate instanceof \DateTime ? $endDate->format('Y-m-d') : $endDate }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <iconify-icon icon="solar:filter-line-duotone"></iconify-icon>
                            Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Earnings Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Daily Earnings (Commission - 70%)</h6>
            </div>
            <div class="card-body">
                <canvas id="earningsChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Period Summary -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Period Summary</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="50%">Date Range:</th>
                        <td>{{ $startDate instanceof \DateTime ? $startDate->format('d M Y') : \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ $endDate instanceof \DateTime ? $endDate->format('d M Y') : \Carbon\Carbon::parse($endDate)->format('d M Y') }}</small>
                    </tr>
                    <tr>
                        <th>Total Deliveries:</th>
                        <td class="fw-bold">{{ $deliveriesCount }}</td>
                    </tr>
                    <tr>
                        <th>Total Delivery Charge:</th>
                        <td>₹{{ number_format($periodEarnings, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Your Commission (70%):</th>
                        <td class="fw-bold text-success">₹{{ number_format($commissionEarnings, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h3 class="mb-0 text-primary">{{ $deliveriesCount }}</h3>
                        <small class="text-muted">Deliveries</small>
                    </div>
                    <div class="col-4">
                        <h3 class="mb-0 text-success">₹{{ number_format($commissionEarnings, 2) }}</h3>
                        <small class="text-muted">Earned</small>
                    </div>
                    <div class="col-4">
                        <h3 class="mb-0 text-info">₹{{ number_format($deliveriesCount > 0 ? $commissionEarnings / $deliveriesCount : 0, 2) }}</h3>
                        <small class="text-muted">Avg per Delivery</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Earnings History Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Earnings History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Tracking #</th>
                                <th>Receiver</th>
                                <th>Delivery Charge</th>
                                <th>Your Commission (70%)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($earningsHistory as $parcel)
                            <tr>
                                <td>{{ $parcel->delivered_at ? $parcel->delivered_at->format('d M Y') : 'N/A' }}</small></td>
                                <td><span class="fw-bold">{{ $parcel->tracking_number }}</span></small></td>
                                <td>{{ $parcel->receiver_name }}</small></td>
                                <td>₹{{ number_format($parcel->delivery_charge, 2) }}</small></td>
                                <td>
                                    <span class="fw-bold text-success">₹{{ number_format($parcel->delivery_charge * 0.7, 2) }}</span>
                                 </small>
                                <td>
                                    <span class="badge bg-success">Completed</span>
                                 </small>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <iconify-icon icon="solar:wallet-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                    <p class="mt-3 text-muted">No earnings yet</p>
                                    <small class="text-muted">Complete deliveries to see your earnings here</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $earningsHistory->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 12px 20px;
    }
    .table th {
        font-weight: 600;
        color: #555;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Show/hide custom date range
    document.querySelector('select[name="period"]').addEventListener('change', function() {
        if (this.value === 'custom') {
            document.getElementById('customDateRange').style.display = 'block';
            document.getElementById('customDateRangeEnd').style.display = 'block';
        } else {
            document.getElementById('customDateRange').style.display = 'none';
            document.getElementById('customDateRangeEnd').style.display = 'none';
        }
    });
    
    // Daily Earnings Chart
    @if($dailyEarnings->count() > 0)
    const ctx = document.getElementById('earningsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyEarnings->pluck('date')->map(function($date) { 
                return date('d M', strtotime($date)); 
            })) !!},
            datasets: [{
                label: 'Your Earnings (₹)',
                data: {!! json_encode($dailyEarnings->pluck('total')) !!},
                backgroundColor: '#4f46e5',
                borderRadius: 8,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹ ' + context.raw.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Earnings (₹)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value;
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
    @else
    document.getElementById('earningsChart').parentElement.innerHTML = '<div class="text-center py-5"><iconify-icon icon="solar:chart-line-duotone" class="fs-1 text-muted"></iconify-icon><p class="mt-3 text-muted">No earnings data available for this period</p></div>';
    @endif
</script>
@endpush