@extends('layouts.admin')

@section('title', 'Parcel Details - ' . $parcel->tracking_number)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Parcel Details</h5>
                    <div>
                        <a href="{{ route('admin.parcels.edit', $parcel->id) }}" class="btn btn-warning btn-sm">
                            <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                            Edit
                        </a>
                        <a href="{{ route('admin.parcels.index') }}" class="btn btn-secondary btn-sm">
                            <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                            Back
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Tracking Info -->
                    <div class="col-md-12 mb-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Tracking Number</small>
                                        <h5 class="mb-0">{{ $parcel->tracking_number }}</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Status</small>
                                        <div>
                                            <span class="badge rounded-pill" style="background-color: {{ $parcel->status->color_code ?? '#6c757d' }}; color: white; font-size: 14px;">
                                                {{ $parcel->status->display_name ?? $parcel->status->name ?? 'Unknown' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Created Date</small>
                                        <h6 class="mb-0">{{ $parcel->created_at->format('d M Y, h:i A') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sender Information -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Sender Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="30%">Name:</th>
                                        <td>{{ $parcel->sender_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $parcel->sender_phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $parcel->sender_email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $parcel->sender_address }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Receiver Information -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Receiver Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="30%">Name:</th>
                                        <td>{{ $parcel->receiver_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $parcel->receiver_phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $parcel->receiver_email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $parcel->receiver_address }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Parcel Details -->
                    <div class="col-md-6 mt-3">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Parcel Details</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Parcel Name:</th>
                                        <td>{{ $parcel->parcel_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Type:</th>
                                        <td>{{ ucfirst($parcel->parcel_type ?? 'Package') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Weight:</th>
                                        <td>{{ $parcel->weight }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Size:</th>
                                        <td>{{ $parcel->size }} cm³</td>
                                    </tr>
                                    <tr>
                                        <th>Description:</th>
                                        <td>{{ $parcel->parcel_description ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="col-md-6 mt-3">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0">Delivery Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Source Hub:</th>
                                        <td>{{ $parcel->sourceHub->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Assigned Rider:</th>
                                        <td>
                                            @if($parcel->assignedRider && $parcel->assignedRider->user)
                                                {{ $parcel->assignedRider->user->name }}
                                                <br>
                                                <small class="text-muted">{{ $parcel->assignedRider->employee_id }}</small>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Delivery Charge:</th>
                                        <td>₹{{ number_format($parcel->delivery_charge, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Method:</th>
                                        <td>{{ ucfirst($parcel->payment_method ?? 'Cash') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Status:</th>
                                        <td>
                                            @if($parcel->payment_status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Timelines -->
                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Delivery Timeline</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Assigned At</small>
                                        <p class="mb-0">{{ $parcel->assigned_at ? $parcel->assigned_at->format('d M Y, h:i A') : 'Not assigned' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Picked Up At</small>
                                        <p class="mb-0">{{ $parcel->picked_up_at ? $parcel->picked_up_at->format('d M Y, h:i A') : 'Not picked up' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Delivered At</small>
                                        <p class="mb-0">{{ $parcel->delivered_at ? $parcel->delivered_at->format('d M Y, h:i A') : 'Not delivered' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Delivery Attempts</small>
                                        <p class="mb-0">{{ $parcel->delivery_attempts }} attempts</p>
                                    </div>
                                </div>
                                @if($parcel->failure_reason)
                                    <hr>
                                    <div class="alert alert-danger mb-0">
                                        <strong>Failure Reason:</strong> {{ $parcel->failure_reason }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status History -->
                    @if($parcel->statusHistories && $parcel->statusHistories->count() > 0)
                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0">Status History</h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    @foreach($parcel->statusHistories as $history)
                                    <div class="d-flex mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-primary p-2" style="width: 40px; height: 40px; text-align: center;">
                                                <iconify-icon icon="solar:check-circle-line-duotone" class="text-white"></iconify-icon>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">{{ $history->status->display_name ?? $history->status->name }}</h6>
                                            <p class="mb-0 text-muted small">
                                                {{ $history->created_at->format('d M Y, h:i A') }} -
                                                Updated by: {{ $history->updater->name ?? 'System' }}
                                            </p>
                                            @if($history->notes)
                                                <small class="text-muted">{{ $history->notes }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($parcel->notes)
                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Additional Notes</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $parcel->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-borderless td, .table-borderless th {
        padding: 8px 0;
    }
    .timeline {
        position: relative;
    }
    .timeline .d-flex {
        position: relative;
        padding-bottom: 20px;
    }
    .timeline .d-flex:last-child {
        padding-bottom: 0;
    }
    .timeline .d-flex:not(:last-child):before {
        content: '';
        position: absolute;
        left: 20px;
        top: 40px;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
</style>
@endpush
