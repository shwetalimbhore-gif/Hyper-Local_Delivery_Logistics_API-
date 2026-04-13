@extends('admin.layout')

@section('content')

<h2 class="mb-4">Dashboard</h2>

<!--first row -->

<div class="row">

    <!-- Orders -->
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Total Orders</h5>
            <h3>{{ $ordersCount }}</h3>
        </div>
    </div>

    <!-- Riders -->
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Total Riders</h5>
            <h3>{{ $ridersCount }}</h3>
        </div>
    </div>

    <!-- Revenue -->
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Total Revenue</h5>
            <h3>{{ $totalRevenue }}</h3>
        </div>
    </div>

</div>

<!-- second row  (Table)-->

<div class="card mt-4">
    <div class="card-body">
        <h5>Recent Orders</h5>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($recentOrders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->customer_name}}</td>
                    <td>₹{{ $order->total_amount }}</td>
                    <td>{{ $order->status }}</td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>

<!-- Charts -->
<div class="card mt-4">
    <div class="card-body">
        <h5>Sales Chart</h5>
        <div id="chart"></div>
    </div>
</div>

{{-- Activate Charts --}}
<script>
var options = {
    chart: {
        type: 'line'
    },
    series: [{
        name: 'Orders',
        data: @json($recentOrders->pluck('total_amount'))
    }],
    xaxis: {
        categories: @json($recentOrders->pluck('created_at'))
    }
};

new ApexCharts(document.querySelector("#chart"), options).render();
</script>

@endsection


