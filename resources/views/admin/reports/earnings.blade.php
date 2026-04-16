@extends('layouts.admin')

@section('title', 'Earnings Reports')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-4">Earnings Reports</h5>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.earnings') }}" class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Period</label>
                                <select name="period" class="form-select" onchange="this.form.submit()">
                                    <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Today</option>
                                    <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>This Week</option>
                                    <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>This Month</option>
                                    <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>This Year</option>
                                    <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
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
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <iconify-icon icon="solar:filter-line-duotone"></iconify-icon>
                                    Apply Filter
                                </button>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <a href="{{ route('admin.reports.earnings.export', request()->all()) }}" class="btn btn-success w-100">
                                    <iconify-icon icon="solar:export-line-duotone"></iconify-icon>
                                    Export CSV
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Earnings</h6>
                                <h2 class="text-white mb-0">₹{{ number_format($totalEarnings, 2) }}</h2>
                            </div>
                            <iconify-icon icon="solar:wallet-money-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Parcels</h6>
                                <h2 class="text-white mb-0">{{ $totalParcels }}</h2>
                            </div>
                            <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Delivered</h6>
                                <h2 class="text-white mb-0">{{ $deliveredParcels }}</h2>
                            </div>
                            <iconify-icon icon="solar:check-circle-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-dark-50 mb-1">Avg Delivery Charge</h6>
                                <h2 class="text-dark mb-0">₹{{ number_format($averageDeliveryCharge ?? 0, 2) }}</h2>
                            </div>
                            <iconify-icon icon="solar:receipt-line-duotone" class="fs-1"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Earnings Chart -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Earnings Overview</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="earningsChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Earnings by Payment Method -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">By Payment Method</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentMethodChart" style="height: 250px;"></canvas>
                        <div class="mt-3">
                            @foreach($earningsByMethod as $method)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ ucfirst($method->payment_method) }}</span>
                                <span class="fw-bold">₹{{ number_format($method->total, 2) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Earnings by Hub -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Earnings by Hub</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Hub</th>
                                        <th>Code</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($earningsByHub as $hub)
                                    <tr>
                                        <td>{{ $hub->name }}</td>
                                        <td>{{ $hub->code }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format($hub->total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    @if($earningsByHub->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performing Riders -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Top Performing Riders</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Rider</th>
                                        <th>Employee ID</th>
                                        <th>Deliveries</th>
                                        <th class="text-end">Earnings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($earningsByRider as $rider)
                                    <tr>
                                        <td>{{ $rider->name }}</td>
                                        <td>{{ $rider->employee_id }}</td>
                                        <td>{{ $rider->deliveries }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format($rider->total_earnings, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    @if($earningsByRider->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Earnings Chart
    const ctx1 = document.getElementById('earningsChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyEarnings->pluck('date')->map(function($date) { return date('d M', strtotime($date)); })) !!},
            datasets: [{
                label: 'Earnings (₹)',
                data: {!! json_encode($dailyEarnings->pluck('total')) !!},
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

    // Payment Method Chart
    const ctx2 = document.getElementById('paymentMethodChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($earningsByMethod->pluck('payment_method')->map(function($method) { return ucfirst($method); })) !!},
            datasets: [{
                data: {!! json_encode($earningsByMethod->pluck('total')) !!},
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
@endpush
