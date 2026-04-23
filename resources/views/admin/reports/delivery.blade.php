@extends('layouts.admin')

@section('title', 'Delivery Reports')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/reports-delivery.css') }}">
@endpush

@section('content')
<div class="delivery-report-container">
    <div class="card report-card">
        <div class="card-body">
            <h5 class="card-title mb-4">Delivery Reports</h5>

            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card filter-card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.reports.delivery') }}" class="row g-3">
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
                                        <a href="{{ route('admin.reports.delivery.export', request()->all()) }}" class="btn btn-success">
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

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-primary text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Total Parcels</h6>
                                    <h2 class="text-white mb-0">{{ $totalParcels }}</h2>
                                    <small class="text-white-50">All Parcels</small>
                                </div>
                                <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-success text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Delivered</h6>
                                    <h2 class="text-white mb-0">{{ $deliveredCount }}</h2>
                                    <small class="text-white-50">Successfully Delivered</small>
                                </div>
                                <iconify-icon icon="solar:check-circle-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-danger text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between-align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Failed</h6>
                                    <h2 class="text-white mb-0">{{ $failedCount }}</h2>
                                    <small class="text-white-50">Delivery Failed</small>
                                </div>
                                <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-info text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Success Rate</h6>
                                    <h2 class="text-white mb-0">{{ $deliveryRate }}%</h2>
                                    <small class="text-white-50">Delivery Success Rate</small>
                                </div>
                                <iconify-icon icon="solar:chart-line-duotone" class="fs-1 text-white-50"></iconify-icon>
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
                            <h6 class="mb-0">Delivery Trends</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="deliveryTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Status Distribution</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container-sm">
                                <canvas id="statusChart"></canvas>
                            </div>
                            <div class="mt-3">
                                @foreach($statusDistribution as $status)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>
                                        <span class="badge me-2" style="background-color: {{ $status->color_code }}; width: 12px; height: 12px; display: inline-block; border-radius: 50%;"></span>
                                        {{ $status->display_name }}
                                    </span>
                                    <span class="fw-bold">{{ $status->parcels_count }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Tables Row -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Rider Performance</h6>
                        </div>
                        <div class="scrollable-table-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="sticky-header">
                                        <tr>
                                            <th>Rider</th>
                                            <th>Deliveries</th>
                                            <th>Avg Time (mins)</th>
                                            <th>Success Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($riderPerformance as $rider)
                                        <tr>
                                            <td>{{ $rider->name }}<br><small class="text-muted">{{ $rider->employee_id }}</small></small></td>
                                            <td><span class="badge bg-success">{{ $rider->deliveries }}</span></small></td>
                                            <td>{{ round($rider->avg_delivery_time ?? 0) }} mins</small></td>
                                            <td>
                                                @php
                                                    $successRate = $rider->deliveries > 0 ? 100 : 0;
                                                @endphp
                                                <div class="progress-custom">
                                                    <div class="progress-bar-custom bg-success" style="width: {{ $successRate }}%"></div>
                                                </div>
                                                <small>{{ $successRate }}%</small>
                                             </small>
                                        </td>
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
                            <h6 class="mb-0">Hub Performance</h6>
                        </div>
                        <div class="scrollable-table-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="sticky-header">
                                        <tr>
                                            <th>Hub</th>
                                            <th>Deliveries</th>
                                            <th>Success Rate</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hubPerformance as $hub)
                                        <tr>
                                            <td>{{ $hub->name }}<br><small class="text-muted">{{ $hub->code }}</small></small></td>
                                            <td><span class="badge bg-info">{{ $hub->deliveries }}</span></small></td>
                                            <td>
                                                @php
                                                    $hubSuccessRate = $hub->deliveries > 0 ? 100 : 0;
                                                @endphp
                                                {{ $hubSuccessRate }}%
                                             </small>
                                            <td>
                                                <div class="progress-custom">
                                                    <div class="progress-bar-custom bg-info" style="width: {{ $hubSuccessRate }}%"></div>
                                                </div>
                                             </small>
                                        </td>
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

            <!-- Failure Reasons -->
            @if(isset($failureReasons) && $failureReasons->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Failure Reasons Analysis</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($failureReasons as $reason)
                                <div class="col-md-4 col-lg-3 mb-2">
                                    <div class="failure-alert alert-warning">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $reason->failure_reason }}</span>
                                            <span class="failure-reason-count">{{ $reason->count }}</span>
                                        </div>
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
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Detailed Parcel List</h6>
                            <span class="badge bg-secondary">{{ $parcels->total() }} Total Records</span>
                        </div>
                        <div class="scrollable-table-body-large">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="sticky-header">
                                        <tr>
                                            <th>ID</th>
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
                                        @forelse($parcels as $parcel)
                                        <tr>
                                            <td>{{ $parcel->id }}</small></td>
                                            <td><span class="fw-bold">{{ $parcel->tracking_number }}</span></small></td>
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
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <iconify-icon icon="solar:box-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                                <p class="mt-2 text-muted">No parcels found</p>
                                            </small>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            {{ $parcels->links() }}
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
    window.deliveryTrendsData = {
        labels: {!! json_encode($dailyDeliveries->pluck('date')->map(function($date) { return date('d M', strtotime($date)); })) !!},
        values: {!! json_encode($dailyDeliveries->pluck('count')) !!}
    };

    window.statusChartData = {
        labels: {!! json_encode($statusDistribution->pluck('display_name')) !!},
        data: {!! json_encode($statusDistribution->pluck('parcels_count')) !!},
        colors: {!! json_encode($statusDistribution->pluck('color_code')) !!}
    };
</script>
<script src="{{ asset('assets/js/admin/reports-delivery.js') }}"></script>
@endpush
