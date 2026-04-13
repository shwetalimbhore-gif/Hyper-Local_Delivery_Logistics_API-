<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rider;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {

        $ordersCount = Order::count();

        $ridersCount = Rider::count();

        $totalRevenue = Order::where('status' , 'delivered')->sum('total_amount');

        $recentOrders = Order::latest()->take(5)->get();

        return view('admin.dashboard' , compact(
            'ordersCount',
            'ridersCount',
            'totalRevenue',
            'recentOrders',
        ));

    }
}
