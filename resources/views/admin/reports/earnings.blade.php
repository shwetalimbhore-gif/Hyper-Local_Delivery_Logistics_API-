@extends('layouts.admin')

@section('title', 'Earnings Reports')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/reports-earnings.css') }}">
@endpush

@section('content')
<div class="earnings-report-container">
    <div class="card report-card">
        <div class="card-body">
            <h5 class="card-title mb-4">Earnings Reports</h5>

            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card filter-card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.reports.earnings') }}" class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label">Period</label>
                                    <select name="period" class="form-select" id="periodSelect">
                                        <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Today</option>
                                        <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>This Week</option>
                                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>This Month</option>
                                        <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>This Year</option>
                                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                    </select>
                                </div>

                                <div class="col-md-2" id="startDateDiv" style="{{ $period == 'custom' ? 'display: block' : 'display: none' }}">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $startDate instanceof \DateTime ? $startDate->format('Y-m-d') : $startDate }}">
                                </div>

                                <div class="col-md-2" id="endDateDiv" style="{{ $period == 'custom' ? 'display: block' : 'display: none' }}">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $endDate instanceof \DateTime ? $endDate->format('Y-m-d') : $endDate }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Hub</label>
                                    <select name="hub_id" class="form-select">
                                        <option value="">All Hubs</option>
                                        @foreach($hubs as $hub)
                                            <option value="{{ $hub->id }}" {{ $hubId == $hub->id ? 'selected' : '' }}>{{ $hub->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Rider</label>
                                    <select name="rider_id" class="form-select">
                                        <option value="">All Riders</option>
                                        @foreach($riders as $rider)
                                            <option value="{{ $rider->id }}" {{ $riderId == $rider->id ? 'selected' : '' }}>{{ $rider->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <iconify-icon icon="solar:filter-line-duotone"></iconify-icon>
                                            Apply
                                        </button>
                                        <a href="{{ route('admin.reports.earnings.export', request()->all()) }}" class="btn btn-success">
                                            <iconify-icon icon="solar:export-line-duotone"></iconify-icon>
                                            Export
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards Row -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-primary text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Total Earnings</h6>
                                    <h2 class="text-white mb-0">₹{{ number_format($totalEarnings, 2) }}</h2>
                                    <small class="text-white-50">Rider Commission (70%)</small>
                                </div>
                                <iconify-icon icon="solar:wallet-money-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-success text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Total Deliveries</h6>
                                    <h2 class="text-white mb-0">{{ $totalDeliveries }}</h2>
                                    <small class="text-white-50">Completed Deliveries</small>
                                </div>
                                <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-info text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Total Charges</h6>
                                    <h2 class="text-white mb-0">₹{{ number_format($totalDeliveryCharges, 2) }}</h2>
                                    <small class="text-white-50">Collected from Customers</small>
                                </div>
                                <iconify-icon icon="solar:receipt-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-warning text-dark stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-dark-50 mb-1">Avg Commission</h6>
                                    <h2 class="text-dark mb-0">₹{{ number_format($averageCommission, 2) }}</h2>
                                    <small class="text-dark-50">Per Delivery</small>
                                </div>
                                <iconify-icon icon="solar:chart-line-duotone" class="fs-1"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Daily Earnings Trend</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="dailyEarningsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Payment Methods</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container-sm">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                            <div class="mt-3">
                                @foreach($earningsByMethod as $method)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ ucfirst($method->payment_method) }}</span>
                                    <span class="fw-bold earnings-amount">₹{{ number_format($method->total, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Earnings by Hub</h6>
                        </div>
                        <div class="scrollable-table-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="sticky-header">
                                        <tr>
                                            <th>Hub</th>
                                            <th>Deliveries</th>
                                            <th>Total Charges</th>
                                            <th>Commission</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($earningsByHub as $hub)
                                        <tr>
                                            <td>{{ $hub->name }} ({{ $hub->code }})</small></td>
                                            <td>{{ $hub->deliveries }}</small></td>
                                            <td>₹{{ number_format($hub->total_charges, 2) }}</small></td>
                                            <td class="earnings-amount">₹{{ number_format($hub->total_earnings, 2) }}</small></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No data available</small></td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Top Performing Riders</h6>
                        </div>
                        <div class="scrollable-table-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="sticky-header">
                                        <tr>
                                            <th>Rider</th>
                                            <th>Deliveries</th>
                                            <th>Total Charges</th>
                                            <th>Earnings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topRiders as $rider)
                                        <tr>
                                            <td>{{ $rider->name }} ({{ $rider->employee_id }})</small></td>
                                            <td>{{ $rider->deliveries }}</small></td>
                                            <td>₹{{ number_format($rider->total_charges, 2) }}</small></td>
                                            <td class="earnings-amount">₹{{ number_format($rider->total_earnings, 2) }}</small></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No data available</small></td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Monthly Earnings ({{ date('Y') }})</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="monthlyEarningsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Range Info -->
            <div class="alert alert-info-custom">
                <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                Showing data from <strong>{{ $startDate instanceof \DateTime ? $startDate->format('d M Y') : \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
                to <strong>{{ $endDate instanceof \DateTime ? $endDate->format('d M Y') : \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
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
        labels: {!! json_encode($dailyEarnings->pluck('date')->map(function($date) { return date('d M', strtotime($date)); })) !!},
        earnings: {!! json_encode($dailyEarnings->pluck('earnings')) !!},
        deliveries: {!! json_encode($dailyEarnings->pluck('deliveries')) !!}
    };

    window.monthlyEarningsData = {
        labels: [],
        values: []
    };

    @if(isset($monthlyEarnings) && $monthlyEarnings->count() > 0)
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthlyValues = Array(12).fill(0);

    @foreach($monthlyEarnings as $earning)
    monthlyValues[{{ $earning->month - 1 }}] = {{ $earning->earnings }};
    @endforeach

    window.monthlyEarningsData = {
        labels: monthNames,
        values: monthlyValues
    };
    @endif

    window.paymentMethodData = {
        labels: {!! json_encode($earningsByMethod->pluck('payment_method')->map(function($method) { return ucfirst($method); })) !!},
        values: {!! json_encode($earningsByMethod->pluck('total')) !!},
        colors: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444']
    };
</script>
<script src="{{ asset('assets/js/admin/reports-earnings.js') }}"></script>
@endpush
