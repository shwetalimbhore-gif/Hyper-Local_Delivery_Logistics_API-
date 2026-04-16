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
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $hubId = $request->get('hub_id');

        // Date range based on period
        switch ($period) {
            case 'daily':
                $startDate = now()->format('Y-m-d');
                $endDate = now()->format('Y-m-d');
                break;
            case 'weekly':
                $startDate = now()->startOfWeek()->format('Y-m-d');
                $endDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $startDate = now()->startOfMonth()->format('Y-m-d');
                $endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'yearly':
                $startDate = now()->startOfYear()->format('Y-m-d');
                $endDate = now()->endOfYear()->format('Y-m-d');
                break;
            case 'custom':
                // Use provided dates
                break;
        }

        // Total earnings - FIXED: Specify payments.created_at
        $totalEarnings = Payment::where('payment_status', 'completed')
            ->whereBetween('payments.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        // Earnings by payment method - FIXED: Specify payments.created_at
        $earningsByMethod = Payment::where('payment_status', 'completed')
            ->whereBetween('payments.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Earnings by hub - FIXED: Specify which created_at column
        $earningsByHub = Parcel::whereBetween('parcels.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->join('hubs', 'parcels.source_hub_id', '=', 'hubs.id')
            ->join('payments', 'parcels.id', '=', 'payments.parcel_id')
            ->where('payments.payment_status', 'completed')
            ->select('hubs.name', 'hubs.code', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('hubs.id', 'hubs.name', 'hubs.code')
            ->get();

        // Earnings by rider - FIXED: Specify table references
        $earningsByRider = Parcel::whereBetween('parcels.delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->join('riders', 'parcels.assigned_rider_id', '=', 'riders.id')
            ->join('users', 'riders.user_id', '=', 'users.id')
            ->select('users.name', 'riders.employee_id', DB::raw('COUNT(parcels.id) as deliveries'), DB::raw('SUM(parcels.delivery_charge) as total_earnings'))
            ->groupBy('riders.id', 'users.name', 'riders.employee_id')
            ->orderBy('total_earnings', 'DESC')
            ->get();

        // Daily earnings chart data - FIXED: Specify payments.created_at
        $dailyEarnings = Payment::where('payment_status', 'completed')
            ->whereBetween('payments.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(DB::raw('DATE(payments.created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Summary statistics - FIXED: Specify parcels.created_at
        $totalParcels = Parcel::whereBetween('parcels.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count();
        $deliveredParcels = Parcel::whereBetween('parcels.delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })->count();

        $averageDeliveryCharge = Parcel::whereBetween('parcels.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->avg('delivery_charge');

        // Get hubs for filter
        $hubs = Hub::where('is_active', true)->get();

        return view('admin.reports.earnings', compact(
            'totalEarnings', 'earningsByMethod', 'earningsByHub',
            'earningsByRider', 'dailyEarnings',
            'totalParcels', 'deliveredParcels', 'averageDeliveryCharge',
            'period', 'startDate', 'endDate', 'hubId', 'hubs'
        ));
    }

    /**
     * Display delivery reports dashboard
     */
    public function delivery(Request $request)
    {
        // Get filter inputs
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $hubId = $request->get('hub_id');
        $riderId = $request->get('rider_id');

        // Date range based on period
        switch ($period) {
            case 'daily':
                $startDate = now()->format('Y-m-d');
                $endDate = now()->format('Y-m-d');
                break;
            case 'weekly':
                $startDate = now()->startOfWeek()->format('Y-m-d');
                $endDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $startDate = now()->startOfMonth()->format('Y-m-d');
                $endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'yearly':
                $startDate = now()->startOfYear()->format('Y-m-d');
                $endDate = now()->endOfYear()->format('Y-m-d');
                break;
        }

        // Base query for parcels - FIXED: Specify parcels.created_at
        $parcelsQuery = Parcel::with(['status', 'sourceHub', 'assignedRider.user'])
            ->whereBetween('parcels.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($hubId) {
            $parcelsQuery->where('parcels.source_hub_id', $hubId);
        }

        if ($riderId) {
            $parcelsQuery->where('parcels.assigned_rider_id', $riderId);
        }

        $parcels = $parcelsQuery->latest('parcels.created_at')->paginate(20);

        // Status distribution - FIXED: Specify parcels.created_at
        $statusDistribution = ParcelStatus::withCount(['parcels' => function($q) use ($startDate, $endDate, $hubId, $riderId) {
            $q->whereBetween('parcels.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($hubId) {
                $q->where('parcels.source_hub_id', $hubId);
            }
            if ($riderId) {
                $q->where('parcels.assigned_rider_id', $riderId);
            }
        }])->get();

        // Delivery performance metrics - FIXED: Specify table references
        $totalParcels = $parcelsQuery->count();

        $deliveredCount = Parcel::whereBetween('parcels.delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            });

        if ($hubId) {
            $deliveredCount->where('parcels.source_hub_id', $hubId);
        }
        if ($riderId) {
            $deliveredCount->where('parcels.assigned_rider_id', $riderId);
        }
        $deliveredCount = $deliveredCount->count();

        $failedCount = Parcel::whereBetween('parcels.failed_delivery_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereHas('status', function($q) {
                $q->where('slug', 'failed_delivery');
            });

        if ($hubId) {
            $failedCount->where('parcels.source_hub_id', $hubId);
        }
        if ($riderId) {
            $failedCount->where('parcels.assigned_rider_id', $riderId);
        }
        $failedCount = $failedCount->count();

        $pendingCount = Parcel::whereBetween('parcels.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereHas('status', function($q) {
                $q->where('slug', 'pending');
            });

        if ($hubId) {
            $pendingCount->where('parcels.source_hub_id', $hubId);
        }
        if ($riderId) {
            $pendingCount->where('parcels.assigned_rider_id', $riderId);
        }
        $pendingCount = $pendingCount->count();

        $inTransitCount = Parcel::whereBetween('parcels.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereHas('status', function($q) {
                $q->whereIn('slug', ['picked_up', 'out_for_delivery']);
            });

        if ($hubId) {
            $inTransitCount->where('parcels.source_hub_id', $hubId);
        }
        if ($riderId) {
            $inTransitCount->where('parcels.assigned_rider_id', $riderId);
        }
        $inTransitCount = $inTransitCount->count();

        // Delivery success rate
        $deliveryRate = $totalParcels > 0 ? round(($deliveredCount / $totalParcels) * 100, 2) : 0;

        // Average delivery time (in minutes) - FIXED: Specify table references
        $avgDeliveryTime = Parcel::whereNotNull('parcels.delivered_at')
            ->whereNotNull('parcels.assigned_at')
            ->whereBetween('parcels.delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($hubId) {
            $avgDeliveryTime->where('parcels.source_hub_id', $hubId);
        }
        if ($riderId) {
            $avgDeliveryTime->where('parcels.assigned_rider_id', $riderId);
        }
        $avgDeliveryTime = $avgDeliveryTime->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, parcels.assigned_at, parcels.delivered_at)) as avg_time'))->first();
        $avgDeliveryTime = round($avgDeliveryTime->avg_time ?? 0);

        // Daily delivery trends - FIXED: Specify parcels.delivered_at
        $dailyDeliveries = Parcel::whereBetween('parcels.delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(DB::raw('DATE(parcels.delivered_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Rider performance ranking - FIXED: Specify table references
        $riderPerformance = Parcel::whereBetween('parcels.delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->join('riders', 'parcels.assigned_rider_id', '=', 'riders.id')
            ->join('users', 'riders.user_id', '=', 'users.id')
            ->select('users.name', 'riders.employee_id', DB::raw('COUNT(parcels.id) as deliveries'), DB::raw('AVG(TIMESTAMPDIFF(MINUTE, parcels.assigned_at, parcels.delivered_at)) as avg_delivery_time'))
            ->groupBy('riders.id', 'users.name', 'riders.employee_id')
            ->orderBy('deliveries', 'DESC')
            ->limit(10)
            ->get();

        // Hub performance - FIXED: Specify table references
        $hubPerformance = Parcel::whereBetween('parcels.delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->join('hubs', 'parcels.source_hub_id', '=', 'hubs.id')
            ->select('hubs.name', 'hubs.code', DB::raw('COUNT(parcels.id) as deliveries'))
            ->groupBy('hubs.id', 'hubs.name', 'hubs.code')
            ->orderBy('deliveries', 'DESC')
            ->get();

        // Failure reasons analysis - FIXED: Specify parcels.failed_delivery_at
        $failureReasons = Parcel::whereNotNull('parcels.failure_reason')
            ->whereBetween('parcels.failed_delivery_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('parcels.failure_reason', DB::raw('COUNT(*) as count'))
            ->groupBy('parcels.failure_reason')
            ->orderBy('count', 'DESC')
            ->get();

        // Get filters data
        $hubs = Hub::where('is_active', true)->get();
        $riders = Rider::with('user')->get();

        return view('admin.reports.delivery', compact(
            'parcels', 'statusDistribution', 'totalParcels', 'deliveredCount',
            'failedCount', 'pendingCount', 'inTransitCount', 'deliveryRate',
            'avgDeliveryTime', 'dailyDeliveries', 'riderPerformance',
            'hubPerformance', 'failureReasons', 'period', 'startDate',
            'endDate', 'hubId', 'riderId', 'hubs', 'riders'
        ));
    }

    /**
     * Export earnings report to CSV
     */
    public function exportEarnings(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $earnings = Payment::where('payment_status', 'completed')
            ->whereBetween('payments.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('parcel')
            ->get();

        $filename = "earnings_report_" . $startDate . "_to_" . $endDate . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($earnings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Tracking Number', 'Amount', 'Payment Method', 'Status']);

            foreach ($earnings as $earning) {
                fputcsv($file, [
                    $earning->created_at->format('Y-m-d H:i:s'),
                    $earning->parcel->tracking_number ?? 'N/A',
                    $earning->amount,
                    ucfirst($earning->payment_method),
                    ucfirst($earning->payment_status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export delivery report to CSV
     */
    public function exportDelivery(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $hubId = $request->get('hub_id');

        $parcels = Parcel::with(['status', 'sourceHub', 'assignedRider.user'])
            ->whereBetween('parcels.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($hubId) {
            $parcels->where('parcels.source_hub_id', $hubId);
        }

        $parcels = $parcels->get();

        $filename = "delivery_report_" . $startDate . "_to_" . $endDate . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($parcels) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Tracking Number', 'Sender', 'Receiver', 'Weight',
                'Delivery Charge', 'Status', 'Assigned Rider',
                'Created At', 'Delivered At', 'Delivery Time (mins)'
            ]);

            foreach ($parcels as $parcel) {
                $deliveryTime = null;
                if ($parcel->delivered_at && $parcel->assigned_at) {
                    $deliveryTime = $parcel->delivered_at->diffInMinutes($parcel->assigned_at);
                }

                fputcsv($file, [
                    $parcel->tracking_number,
                    $parcel->sender_name,
                    $parcel->receiver_name,
                    $parcel->weight . ' kg',
                    $parcel->delivery_charge,
                    $parcel->status->display_name ?? 'Unknown',
                    $parcel->assignedRider->user->name ?? 'Unassigned',
                    $parcel->created_at->format('Y-m-d H:i:s'),
                    $parcel->delivered_at ? $parcel->delivered_at->format('Y-m-d H:i:s') : 'Not delivered',
                    $deliveryTime ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
