<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\Notification;
use App\Models\Rider;
use App\Models\Hub;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Auth;


class DashboardController extends Controller
{
    public function index()
    {
        $totalParcels = Parcel::count();
        $deliveredParcels = Parcel::whereHas('status', function($q) {
            $q->where('slug', 'delivered');
        })->count();

        $totalRiders = Rider::count();
        $activeRiders = Rider::where('status', 'available')->count();
        $totalHubs = Hub::count();

        $newParcels = Parcel::whereDate('created_at', '>=', now()->subDays(7))->count();
        $deliveryRate = $totalParcels > 0 ? round(($deliveredParcels / $totalParcels) * 100, 2) : 0;

        $recentParcels = Parcel::with(['status', 'assignedRider.user'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalParcels', 'deliveredParcels', 'totalRiders',
            'activeRiders', 'totalHubs', 'newParcels',
            'deliveryRate', 'recentParcels'
        ));
    }

    /**
     * Fetch notifications for admin
     */
    public function fetchNotifications()
    {
        $notifications = Notification::where('user_id', \Auth::id())
            ->latest()
            ->take(20)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });

        $unreadCount = Notification::where('user_id', \Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark a single notification as read
     */
    public function markNotificationRead(Request $request)
    {
        $notification = Notification::where('id', $request->id)
            ->where('user_id', \Auth::id())
            ->first();

        if ($notification) {
            $notification->is_read = true;
            $notification->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead()
    {
        Notification::where('user_id', \Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
