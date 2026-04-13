@extends('admin.layout')

@section('content')

<h2>Edit Order</h2>

<form method="POST" action="{{ route('admin.orders.update', $order->id) }}">
    @csrf

    <input type="text" name="customer_name" value="{{ $order->customer_name }}" class="form-control mb-2">

    <input type="number" name="amount" value="{{ $order->total_amount }}" class="form-control mb-2">

    <select name="status" class="form-control mb-2">
        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
    </select>

    <button class="btn btn-primary">Update</button>

</form>

@endsection
