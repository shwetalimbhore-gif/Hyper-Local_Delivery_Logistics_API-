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

    public function create()
    {
        return view('admin.orders.create');
    }

    public function store(Request $request)
    {
        die('hiii');

        // ✅ Validation
        $request->validate([
            'customer_name'   => 'required|string|max:255',
            'customer_phone'  => 'required',
            'drop_address'    => 'required',
            'total_amount'    => 'required|numeric',
            'status'          => 'required',

            // conditional validation
            'hub_id' => 'required_if:return_type,hub',
            'return_address' => 'required_if:return_type,owner',
        ]);

        // ✅ Prepare Data
        $data = $request->all();

        // ❌ REMOVE manual timestamps from form (ignore if coming)
        unset(
            $data['assigned_at'],
            $data['picked_at'],
            $data['delivered_at'],
            $data['failed_at'],
            $data['returned_at']
        );

        // ✅ Auto timestamp logic

        // When rider assigned
        if ($request->rider_id) {
            $data['assigned_at'] = now();
        }

        // Based on status
        if ($request->status == 'picked_up') {
            $data['picked_at'] = now();
        }

        if ($request->status == 'delivered') {
            $data['delivered_at'] = now();
        }

        if ($request->status == 'failed_delivered') {
            $data['failed_at'] = now();
        }

        if ($request->status == 'returned') {
            $data['returned_at'] = now();
        }

        // ✅ Create Order
        Order::create($data);

        // ✅ Redirect
        return redirect()->route('orders.index')
            ->with('success', 'Order Created Successfully 🚀');
    }

    public function assignNearestRider($orderId)
    {
        $order = Order::findOrFail($orderId);

        $latitude = $order->drop_latitude;
        $longitude = $order->drop_longitude;

        // 🔥 Find nearest rider
        $rider = Rider::selectRaw("
            *,
            (6371 * acos(
                cos(radians(?))
                * cos(radians(current_latitude))
                * cos(radians(current_longitude) - radians(?))
                + sin(radians(?))
                * sin(radians(current_latitude))
            )) AS distance
        ", [$latitude, $longitude, $latitude])
        ->where('is_available', true)
        ->orderBy('distance', 'asc')
        ->first();

        if (!$rider) {
            return back()->with('error', 'No rider available');
        }

        // Assign rider
        $order->rider_id = $rider->id;
        $order->status = 'assigned';
        $order->save();

        // Mark rider unavailable
        $rider->is_available = false;
        $rider->save();

        return back()->with('success', 'Rider assigned successfully');
    }
}
