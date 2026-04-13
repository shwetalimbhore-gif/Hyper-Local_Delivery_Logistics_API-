@extends('admin.layout')

@section('content')

<h2 class="mb-4">Add Order</h2>

<div class="card">
    <div class="card-body">

        <form>

            <!-- Customer Name -->
            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" class="form-control" placeholder="Enter customer name">
            </div>

            <!-- Amount -->
            <div class="mb-3">
                <label class="form-label">Amount (₹)</label>
                <input type="number" class="form-control" placeholder="Enter amount">
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-control">
                    <option>Pending</option>
                    <option>Out for Delivery</option>
                    <option>Delivered</option>
                </select>
            </div>

            <!-- Buttons -->
            <button type="submit" class="btn btn-success">Save Order</button>
            <a href="{{ route('admin.orders') }}" class="btn btn-secondary">Cancel</a>

        </form>

    </div>
</div>

@endsection
