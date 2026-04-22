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
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    /**
     * Display earnings report dashboard
     */
    public function earnings(Request $request)
    {
        // ... your existing earnings method code ...
    }

    public function getEarningsData(Request $request){
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $hubId = $request->get('hub_id');

        $earnings = Parcel::whereHas('status', fn($q) => $q->where('slug', 'delivered'))
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->with(['assignedRider.user', 'sourceHub'])
            ->select(['id', 'tracking_number', 'sender_name', 'receiver_name', 'delivery_charge', 'delivered_at', 'source_hub_id', 'assigned_rider_id', 'payment_method']);

        if ($hubId) {
            $earnings->where('source_hub_id', $hubId);
        }

        return DataTables::eloquent($earnings)
            ->editColumn('delivered_at', fn($row) => $row->delivered_at->format('d M Y'))
            ->editColumn('delivery_charge', fn($row) => '₹' . number_format($row->delivery_charge, 2))
            ->addColumn('commission', fn($row) => '₹' . number_format($row->delivery_charge * 0.7, 2))
            ->addColumn('rider_name', fn($row) => $row->assignedRider->user->name ?? 'N/A')
            ->addColumn('hub_name', fn($row) => $row->sourceHub->name ?? 'N/A')
            ->addColumn('status_badge', fn($row) => '<span class="badge bg-success">Delivered</span>')
            ->rawColumns(['status_badge'])
            ->toJson();
    }

    public function getDeliveryData(Request $request){
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $hubId = $request->get('hub_id');
        $statusFilter = $request->get('status');

        $parcels = Parcel::with(['status', 'assignedRider.user', 'sourceHub'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(['id', 'tracking_number', 'sender_name', 'receiver_name', 'weight', 'status_id', 'assigned_rider_id', 'source_hub_id', 'created_at', 'delivered_at']);

        if ($hubId) {
            $parcels->where('source_hub_id', $hubId);
        }

        if ($statusFilter) {
            $parcels->whereHas('status', fn($q) => $q->where('slug', $statusFilter));
        }

        return DataTables::eloquent($parcels)
            ->editColumn('weight', fn($row) => $row->weight . ' kg')
            ->editColumn('created_at', fn($row) => $row->created_at->format('d M Y'))
            ->addColumn('status_badge', fn($row) =>
                '<span class="badge" style="background-color: ' . ($row->status->color_code ?? '#6c757d') . '; color: white;">'
                . ($row->status->display_name ?? 'Unknown') . '</span>'
            )
            ->addColumn('rider_name', fn($row) => $row->assignedRider->user->name ?? 'Unassigned')
            ->addColumn('hub_name', fn($row) => $row->sourceHub->name ?? 'N/A')
            ->addColumn('delivered_date', fn($row) => $row->delivered_at ? $row->delivered_at->format('d M Y') : '-')
            ->rawColumns(['status_badge'])
            ->toJson();
    }
    /**
     * Display delivery reports dashboard
     */
    public function delivery(Request $request)
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

        // Base query for parcels
        $parcelsQuery = Parcel::with(['status', 'sourceHub', 'assignedRider.user'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($hubId) {
            $parcelsQuery->where('source_hub_id', $hubId);
        }

        if ($riderId) {
            $parcelsQuery->where('assigned_rider_id', $riderId);
        }

        $parcels = $parcelsQuery->latest('created_at')->paginate(20);

        // Status distribution
        $statusDistribution = ParcelStatus::withCount(['parcels' => function($q) use ($startDate, $endDate, $hubId, $riderId) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
            if ($hubId) {
                $q->where('source_hub_id', $hubId);
            }
            if ($riderId) {
                $q->where('assigned_rider_id', $riderId);
            }
        }])->get();

        // Delivery performance metrics
        $totalParcels = $parcelsQuery->count();

        $deliveredCount = Parcel::whereBetween('delivered_at', [$startDate, $endDate])
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            });

        if ($hubId) {
            $deliveredCount->where('source_hub_id', $hubId);
        }
        if ($riderId) {
            $deliveredCount->where('assigned_rider_id', $riderId);
        }
        $deliveredCount = $deliveredCount->count();

        $failedCount = Parcel::whereBetween('failed_delivery_at', [$startDate, $endDate])
            ->whereHas('status', function($q) {
                $q->where('slug', 'failed-delivery');
            });

        if ($hubId) {
            $failedCount->where('source_hub_id', $hubId);
        }
        if ($riderId) {
            $failedCount->where('assigned_rider_id', $riderId);
        }
        $failedCount = $failedCount->count();

        $pendingCount = Parcel::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('status', function($q) {
                $q->where('slug', 'pending');
            });

        if ($hubId) {
            $pendingCount->where('source_hub_id', $hubId);
        }
        if ($riderId) {
            $pendingCount->where('assigned_rider_id', $riderId);
        }
        $pendingCount = $pendingCount->count();

        // Delivery success rate
        $deliveryRate = $totalParcels > 0 ? round(($deliveredCount / $totalParcels) * 100, 2) : 0;

        // Average delivery time (in minutes)
        $avgDeliveryTime = Parcel::whereNotNull('delivered_at')
            ->whereNotNull('assigned_at')
            ->whereBetween('delivered_at', [$startDate, $endDate]);

        if ($hubId) {
            $avgDeliveryTime->where('source_hub_id', $hubId);
        }
        if ($riderId) {
            $avgDeliveryTime->where('assigned_rider_id', $riderId);
        }
        $avgDeliveryTime = $avgDeliveryTime->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, assigned_at, delivered_at)) as avg_time'))->first();
        $avgDeliveryTime = round($avgDeliveryTime->avg_time ?? 0);

        // Daily delivery trends
        $dailyDeliveries = Parcel::whereBetween('delivered_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(delivered_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Rider performance ranking
        $riderPerformance = Parcel::whereBetween('delivered_at', [$startDate, $endDate])
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

        // Hub performance
        $hubPerformance = Parcel::whereBetween('delivered_at', [$startDate, $endDate])
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->join('hubs', 'parcels.source_hub_id', '=', 'hubs.id')
            ->select('hubs.name', 'hubs.code', DB::raw('COUNT(parcels.id) as deliveries'))
            ->groupBy('hubs.id', 'hubs.name', 'hubs.code')
            ->orderBy('deliveries', 'DESC')
            ->get();

        // Failure reasons analysis
        $failureReasons = Parcel::whereNotNull('failure_reason')
            ->whereBetween('failed_delivery_at', [$startDate, $endDate])
            ->select('failure_reason', DB::raw('COUNT(*) as count'))
            ->groupBy('failure_reason')
            ->orderBy('count', 'DESC')
            ->get();

        // Get filters data
        $hubs = Hub::where('is_active', true)->get();
        $riders = Rider::with('user')->get();

        return view('admin.reports.delivery', compact(
            'parcels', 'statusDistribution', 'totalParcels', 'deliveredCount',
            'failedCount', 'pendingCount', 'deliveryRate',
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
        // ... your existing exportEarnings code ...
    }

    /**
     * Export delivery report to CSV
     */
    public function exportDelivery(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $hubId = $request->get('hub_id');

        $query = Parcel::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['status', 'assignedRider.user', 'sourceHub']);

        if ($hubId) {
            $query->where('source_hub_id', $hubId);
        }

        $parcels = $query->get();

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
