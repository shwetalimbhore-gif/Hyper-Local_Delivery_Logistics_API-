<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\Rider;
use App\Models\Payment;
use App\Models\Hub;
use App\Models\ParcelStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display earnings report dashboard
     */
    public function earnings(Request $request)
    {
        // Get filter inputs
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $hubId = $request->get('hub_id');
        $riderId = $request->get('rider_id');
        
        // Set date range based on period
        if ($period == 'custom' && $startDate && $endDate) {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        } else {
            switch ($period) {
                case 'daily':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'weekly':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    break;
                case 'monthly':
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                    break;
                case 'yearly':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    break;
                default:
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
            }
        }
        
        // Base query for delivered parcels
        $deliveredQuery = Parcel::whereHas('status', function($q) {
            $q->where('slug', 'delivered');
        });
        
        // Apply date filter
        $deliveredQuery->whereBetween('delivered_at', [$startDate, $endDate]);
        
        // Apply hub filter
        if ($hubId) {
            $deliveredQuery->where('source_hub_id', $hubId);
        }
        
        // Apply rider filter
        if ($riderId) {
            $deliveredQuery->where('assigned_rider_id', $riderId);
        }
        
        // Total Earnings (from delivered parcels)
        $totalEarnings = $deliveredQuery->sum(DB::raw('delivery_charge * 0.7'));
        
        // Total Delivery Charges collected
        $totalDeliveryCharges = $deliveredQuery->sum('delivery_charge');
        
        // Total number of deliveries
        $totalDeliveries = $deliveredQuery->count();
        
        // Average delivery charge
        $averageDeliveryCharge = $totalDeliveries > 0 ? $totalDeliveryCharges / $totalDeliveries : 0;
        
        // Average rider commission per delivery
        $averageCommission = $totalDeliveries > 0 ? $totalEarnings / $totalDeliveries : 0;
        
        // Earnings by Payment Method
        $earningsByMethod = Payment::where('payment_status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();
        
        // Earnings by Hub
        $earningsByHub = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->join('hubs', 'parcels.source_hub_id', '=', 'hubs.id')
            ->select('hubs.name', 'hubs.code', DB::raw('COUNT(parcels.id) as deliveries'), DB::raw('SUM(parcels.delivery_charge) as total_charges'), DB::raw('SUM(parcels.delivery_charge * 0.7) as total_earnings'))
            ->groupBy('hubs.id', 'hubs.name', 'hubs.code')
            ->get();
        
        // Top Performing Riders
        $topRiders = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->join('riders', 'parcels.assigned_rider_id', '=', 'riders.id')
            ->join('users', 'riders.user_id', '=', 'users.id')
            ->select('users.name', 'riders.employee_id', DB::raw('COUNT(parcels.id) as deliveries'), DB::raw('SUM(parcels.delivery_charge) as total_charges'), DB::raw('SUM(parcels.delivery_charge * 0.7) as total_earnings'))
            ->groupBy('riders.id', 'users.name', 'riders.employee_id')
            ->orderBy('total_earnings', 'DESC')
            ->limit(10)
            ->get();
        
        // Daily earnings chart data
        $dailyEarnings = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(delivered_at) as date'), DB::raw('COUNT(*) as deliveries'), DB::raw('SUM(delivery_charge * 0.7) as earnings'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
        
        // Monthly earnings chart (for the year)
        $monthlyEarnings = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereYear('delivered_at', now()->year)
            ->select(DB::raw('MONTH(delivered_at) as month'), DB::raw('COUNT(*) as deliveries'), DB::raw('SUM(delivery_charge * 0.7) as earnings'))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();
        
        // Earnings trend (last 12 months)
        $trendData = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->where('delivered_at', '>=', now()->subMonths(12))
            ->select(DB::raw('DATE_FORMAT(delivered_at, "%Y-%m") as month'), DB::raw('SUM(delivery_charge * 0.7) as earnings'), DB::raw('COUNT(*) as deliveries'))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();
        
        // Get filters data
        $hubs = Hub::where('is_active', true)->get();
        $riders = Rider::with('user')->get();
        
        return view('admin.reports.earnings', compact(
            'totalEarnings',
            'totalDeliveryCharges',
            'totalDeliveries',
            'averageDeliveryCharge',
            'averageCommission',
            'earningsByMethod',
            'earningsByHub',
            'topRiders',
            'dailyEarnings',
            'monthlyEarnings',
            'trendData',
            'period',
            'startDate',
            'endDate',
            'hubId',
            'riderId',
            'hubs',
            'riders'
        ));
    }
    
    /**
     * Export earnings report to CSV
     */
    public function exportEarnings(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $hubId = $request->get('hub_id');
        
        $query = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['assignedRider.user', 'sourceHub']);
        
        if ($hubId) {
            $query->where('source_hub_id', $hubId);
        }
        
        $parcels = $query->get();
        
        $filename = "earnings_report_" . $startDate . "_to_" . $endDate . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($parcels) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date', 'Tracking Number', 'Sender', 'Receiver', 
                'Delivery Charge', 'Rider Commission (70%)', 'Rider Name', 
                'Hub', 'Payment Method'
            ]);
            
            foreach ($parcels as $parcel) {
                fputcsv($file, [
                    $parcel->delivered_at->format('Y-m-d'),
                    $parcel->tracking_number,
                    $parcel->sender_name,
                    $parcel->receiver_name,
                    $parcel->delivery_charge,
                    $parcel->delivery_charge * 0.7,
                    $parcel->assignedRider->user->name ?? 'N/A',
                    $parcel->sourceHub->name ?? 'N/A',
                    $parcel->payment_method ?? 'cash',
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}