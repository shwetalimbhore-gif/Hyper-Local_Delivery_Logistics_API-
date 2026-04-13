@extends('admin.layout')

@section('content')

<h2>Orders</h2>

<a href="{{ route('admin.orders.create') }}" class="btn btn-primary mb-3">
    Add Order
</a>

<table class="table">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Total Amount</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    @foreach($recentOrders as $order)
    <tr>
        <td>{{ $order->id }}</td>
        <td>{{ $order->customer_name }}</td>
        <td>₹{{ $order->total_amount }}</td>
        <td>{{ $order->status }}</td>
        <td>
            <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-warning btn-sm">Edit</a>

            <a href="{{ route('admin.orders.delete', $order->id) }}" class="btn btn-danger btn-sm">Delete</a>
        </td>
    </tr>
    @endforeach

</table>

@endsection
