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
                        <h6 class="text-white-50 mb-1">Total Earnings</h6>
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
                        <h6 class="text-white-50 mb-1">This Period</h6>
                        <h2 class="text-white mb-0">₹{{ number_format($deliveryEarnings * 0.7, 2) }}</h2>
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
                        <h6 class="text-white-50 mb-1">Cash Collected</h6>
                        <h2 class="text-white mb-0">₹{{ number_format($paymentsCollected ?? 0, 2) }}</h2>
                        <small class="text-white-50">From COD deliveries</small>
                    </div>
                    <iconify-icon icon="solar:cash-out-line-duotone" class="fs-1 text-white-50"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select" onchange="this.form.submit()">
                            <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>This Week</option>
                            <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>This Month</option>
                            <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate instanceof \DateTime ? $startDate->format('Y-m-d') : $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate instanceof \DateTime ? $endDate->format('Y-m-d') : $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
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
                                <td>{{ $parcel->tracking_number }}</small></td>
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
                                    <td colspan="6" class="text-center py-4">
                                        <iconify-icon icon="solar:wallet-line-duotone" class="fs-1 text-muted"></iconify-icon>
                                        <p class="mt-2 text-muted">No earnings yet</p>
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
                borderRadius: 8
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
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Earnings (₹)'
                    }
                }
            }
        }
    });
</script>
@endpush
