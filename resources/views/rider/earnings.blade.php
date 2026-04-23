@extends('layouts.rider')

@section('title', 'My Earnings')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/rider/earnings.css') }}">
@endpush

@section('content')
<div class="row">
    <!-- Earnings Cards -->
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white stats-card">
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
        <div class="card bg-success text-white stats-card">
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
        <div class="card bg-info text-white stats-card">
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
        <div class="card filter-card">
            <div class="card-body">
                <form method="GET" action="{{ route('rider.earnings') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select">
                            <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Today</option>
                            <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>This Week</option>
                            <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>This Month</option>
                            <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>

                    <div class="col-md-3" id="customDateRange" style="display: none;">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate instanceof \DateTime ? $startDate->format('Y-m-d') : $startDate }}">
                    </div>

                    <div class="col-md-3" id="customDateRangeEnd" style="display: none;">
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
        <div class="card earnings-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:chart-line-duotone"></iconify-icon>
                    Daily Earnings (Commission - 70%)
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

<!-- Period Summary -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card earnings-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:document-text-line-duotone"></iconify-icon>
                    Period Summary
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless-custom">
                    <tr>
                        <th class="summary-label">Date Range:</th>
                        <td class="summary-value">{{ $startDate instanceof \DateTime ? $startDate->format('d M Y') : \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ $endDate instanceof \DateTime ? $endDate->format('d M Y') : \Carbon\Carbon::parse($endDate)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th class="summary-label">Total Deliveries:</th>
                        <td class="summary-value">{{ $deliveriesCount }}</td>
                    </tr>
                    <tr>
                        <th class="summary-label">Total Delivery Charge:</th>
                        <td class="summary-value">₹{{ number_format($periodEarnings, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="summary-label">Your Commission (70%):</th>
                        <td class="commission-amount summary-value">₹{{ number_format($commissionEarnings, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card earnings-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:pie-chart-line-duotone"></iconify-icon>
                    Quick Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <div class="quick-stat-box">
                            <div class="quick-stat-value text-primary">{{ $deliveriesCount }}</div>
                            <div class="quick-stat-label">Deliveries</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="quick-stat-box">
                            <div class="quick-stat-value text-success">₹{{ number_format($commissionEarnings, 2) }}</div>
                            <div class="quick-stat-label">Earned</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="quick-stat-box">
                            <div class="quick-stat-value text-info">₹{{ number_format($deliveriesCount > 0 ? $commissionEarnings / $deliveriesCount : 0, 2) }}</div>
                            <div class="quick-stat-label">Avg per Delivery</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Earnings History Table -->
<div class="row">
    <div class="col-12">
        <div class="card earnings-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <iconify-icon icon="solar:history-line-duotone"></iconify-icon>
                    Earnings History
                </h6>
                <span class="badge bg-secondary">{{ $earningsHistory->total() }} Total Records</span>
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
                                    <span class="commission-amount">₹{{ number_format($parcel->delivery_charge * 0.7, 2) }}</span>
                                 </small>
                                <td>
                                    <span class="badge bg-success">Completed</span>
                                 </small>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <iconify-icon icon="solar:wallet-line-duotone"></iconify-icon>
                                        </div>
                                        <p class="empty-state-text">No earnings yet</p>
                                        <small class="text-muted">Complete deliveries to see your earnings here</small>
                                    </div>
                                 </small>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass data from PHP to JavaScript
    window.dailyEarningsData = {
        labels: {!! json_encode($dailyEarnings->pluck('date')->map(function($date) {
            return date('d M', strtotime($date));
        })) !!},
        values: {!! json_encode($dailyEarnings->pluck('total')) !!}
    };
</script>
<script src="{{ asset('assets/js/rider/earnings.js') }}"></script>
@endpush
