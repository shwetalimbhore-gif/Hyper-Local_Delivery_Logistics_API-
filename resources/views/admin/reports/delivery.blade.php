@extends('layouts.admin')

@section('title', 'Delivery Reports')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-4">Delivery Reports</h5>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.delivery') }}" class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Period</label>
                                <select name="period" class="form-select" onchange="this.form.submit()">
                                    <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Today</option>
                                    <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>This Week</option>
                                    <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>This Month</option>
                                    <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>This Year</option>
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

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
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
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Delivered</h6>
                                <h2 class="text-white mb-0">{{ $deliveredCount }}</h2>
                            </div>
                            <iconify-icon icon="solar:check-circle-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Failed</h6>
                                <h2 class="text-white mb-0">{{ $failedCount }}</h2>
                            </div>
                            <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-white-50"></iconify-icon>
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
                                <h2 class="text-white mb-0">{{ $deliveryRate }}%</h2>
                            </div>
                            <iconify-icon icon="solar:chart-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Status Distribution -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" style="height: 250px;"></canvas>
                        <div class="mt-3">
                            @foreach($statusDistribution as $status)
                            <div class="d-flex justify-content-between mb-2">
                                <span>
                                    <span class="badge" style="background-color: {{ $status->color_code }}; width: 12px; height: 12px; display: inline-block; border-radius: 50%;"></span>
                                    {{ $status->display_name }}
                                </span>
                                <span class="fw-bold">{{ $status->parcels_count }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Trends -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Delivery Trends</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="deliveryTrendsChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Rider Performance -->
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
                                        <th>Deliveries</th>
                                        <th>Avg Time (mins)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riderPerformance as $rider)
                                    <tr>
                                        <td>{{ $rider->name }} ({{ $rider->employee_id }})</small></td>
                                        <td><span class="badge bg-success">{{ $rider->deliveries }}</span></td>
                                        <td>{{ $rider->avg_delivery_time ?? 'N/A' }} mins</small></td>
                                    </tr>
                                    @endforeach
                                    @if($riderPerformance->isEmpty())
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

            <!-- Hub Performance -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Hub Performance</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Hub</th>
                                        <th>Code</th>
                                        <th>Deliveries</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hubPerformance as $hub)
                                    <tr>
                                        <td>{{ $hub->name }}</small></td>
                                        <td>{{ $hub->code }}</small></td>
                                        <td><span class="badge bg-info">{{ $hub->deliveries }}</span></td>
                                    </tr>
                                    @endforeach
                                    @if($hubPerformance->isEmpty())
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
        </div>

        <!-- Failure Reasons Analysis -->
        @if($failureReasons->isNotEmpty())
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Failure Reasons Analysis</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($failureReasons as $reason)
                            <div class="col-md-3 mb-2">
                                <div class="alert alert-warning mb-0">
                                    <strong>{{ $reason->failure_reason }}</strong>
                                    <span class="badge bg-danger float-end">{{ $reason->count }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Detailed Parcels Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Detailed Parcel List</h6>
                            <a href="{{ route('admin.reports.delivery.export', request()->all()) }}" class="btn btn-sm btn-success">
                                <iconify-icon icon="solar:export-line-duotone"></iconify-icon>
                                Export to CSV
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tracking #</th>
                                        <th>Sender</th>
                                        <th>Receiver</th>
                                        <th>Hub</th>
                                        <th>Rider</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Delivered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($parcels as $parcel)
                                    <tr>
                                        <td>{{ $parcel->tracking_number }}</small></td>
                                        <td>{{ Str::limit($parcel->sender_name, 20) }}</small></td>
                                        <td>{{ Str::limit($parcel->receiver_name, 20) }}</small></td>
                                        <td>{{ $parcel->sourceHub->name ?? 'N/A' }}</small></td>
                                        <td>{{ $parcel->assignedRider->user->name ?? 'Unassigned' }}</small></td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $parcel->status->color_code ?? '#6c757d' }}; color: white;">
                                                {{ $parcel->status->display_name ?? 'Unknown' }}
                                            </span>
                                        </small>
                                        <td>{{ $parcel->created_at->format('d M Y') }}</small></td>
                                        <td>{{ $parcel->delivered_at ? $parcel->delivered_at->format('d M Y') : '-' }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $parcels->links() }}
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
    .badge {
        padding: 5px 10px;
        font-size: 11px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Distribution Chart
    const ctx1 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusDistribution->pluck('display_name')) !!},
            datasets: [{
                data: {!! json_encode($statusDistribution->pluck('parcels_count')) !!},
                backgroundColor: {!! json_encode($statusDistribution->pluck('color_code')) !!},
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

    // Delivery Trends Chart
    const ctx2 = document.getElementById('deliveryTrendsChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyDeliveries->pluck('date')->map(function($date) { return date('d M', strtotime($date)); })) !!},
            datasets: [{
                label: 'Deliveries',
                data: {!! json_encode($dailyDeliveries->pluck('count')) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
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
</script>
@endpush
