<?php

namespace App\Http\Controllers\Admin;
use App\Models\Order;
use App\Models\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $ordersCount = Order::count();

        $ridersCount = Rider::count();

        $totalRevenue = Order::where('status', 'delivered')->sum('total_amount');

        $recentOrders = Order::latest()->take(5)->get();

        return view('admin.orders.index', compact(
            'ordersCount',
            'ridersCount',
            'totalRevenue',
            'recentOrders'
        ));
    }
}
