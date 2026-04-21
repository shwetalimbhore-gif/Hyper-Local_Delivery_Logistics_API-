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
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
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
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
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
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
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
            
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Daily Earnings Trend</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyEarningsChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Payment Methods</h6>
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
        
        <!-- Earnings by Hub -->
        <div class="row mb-4">
            <div class="col-md-6">
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
                                        <td class="fw-bold text-success">₹{{ number_format($hub->total_earnings, 2) }}</small></td>
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
            
            <!-- Top Performing Riders -->
            <div class="col-md-6">
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
                                        <td class="fw-bold text-success">₹{{ number_format($rider->total_earnings, 2) }}</small></td>
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Monthly Earnings ({{ date('Y') }})</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyEarningsChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Date Range Info -->
        <div class="alert alert-info">
            <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
            Showing data from <strong>{{ $startDate instanceof \DateTime ? $startDate->format('d M Y') : \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> 
            to <strong>{{ $endDate instanceof \DateTime ? $endDate->format('d M Y') : \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
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
    // Show/hide custom date range
    const periodSelect = document.getElementById('periodSelect');
    const startDateDiv = document.getElementById('startDateDiv');
    const endDateDiv = document.getElementById('endDateDiv');
    
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                if (startDateDiv) startDateDiv.style.display = 'block';
                if (endDateDiv) endDateDiv.style.display = 'block';
            } else {
                if (startDateDiv) startDateDiv.style.display = 'none';
                if (endDateDiv) endDateDiv.style.display = 'none';
            }
        });
    }
    
    // Daily Earnings Chart
    @if(isset($dailyEarnings) && $dailyEarnings->count() > 0)
    const dailyCtx = document.getElementById('dailyEarningsChart');
    if (dailyCtx) {
        new Chart(dailyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyEarnings->pluck('date')->map(function($date) { return date('d M', strtotime($date)); })) !!},
                datasets: [{
                    label: 'Earnings (₹)',
                    data: {!! json_encode($dailyEarnings->pluck('earnings')) !!},
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Deliveries',
                    data: {!! json_encode($dailyEarnings->pluck('deliveries')) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
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
                    y1: {
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Number of Deliveries'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    @endif
    
    // Monthly Earnings Chart
    @if(isset($monthlyEarnings) && $monthlyEarnings->count() > 0)
    const monthlyCtx = document.getElementById('monthlyEarningsChart');
    if (monthlyCtx) {
        // Month names array
        const monthNamesJs = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        // Prepare data for all 12 months
        let monthlyLabels = [];
        let monthlyData = [];
        
        // Initialize all months with 0
        for (let i = 1; i <= 12; i++) {
            monthlyLabels.push(monthNamesJs[i - 1]);
            monthlyData.push(0);
        }
        
        // Fill in actual data
        @foreach($monthlyEarnings as $earning)
        monthlyData[{{ $earning->month - 1 }}] = {{ $earning->earnings }};
        @endforeach
        
        new Chart(monthlyCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Earnings (₹)',
                    data: monthlyData,
                    backgroundColor: '#4f46e5',
                    borderRadius: 8
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
                    }
                }
            }
        });
    }
    @endif
    
    // Payment Method Chart
    @if(isset($earningsByMethod) && $earningsByMethod->count() > 0)
    const paymentCtx = document.getElementById('paymentMethodChart');
    if (paymentCtx) {
        new Chart(paymentCtx.getContext('2d'), {
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ₹' + context.raw.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
    @endif
</script>
@endpush