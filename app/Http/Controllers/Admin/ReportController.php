<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
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
    public function earnings(ReportFilterRequest $request)
    {
        $validated = $request->validated();

        // Get filter values with defaults
        $period = $validated['period'] ?? 'monthly';
        $startDate = $validated['start_date'] ?? now()->startOfMonth();
        $endDate = $validated['end_date'] ?? now()->endOfMonth();
        $hubId = $validated['hub_id'] ?? null;
        $riderId = $validated['rider_id'] ?? null;

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

        // Total Earnings (from delivered parcels - 70% commission)
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

         // Get daily earnings for the selected period
        $dailyEarningsQuery = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(delivered_at) as date'),
                DB::raw('COUNT(*) as deliveries'),
                DB::raw('SUM(delivery_charge) as total_charges'),
                DB::raw('SUM(delivery_charge * 0.7) as earnings')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Prepare daily earnings data for chart (fill missing dates with 0)
        $dateRange = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dateRange[$dateKey] = [
                'date' => $dateKey,
                'display_date' => $currentDate->format('d M'),
                'earnings' => 0,
                'deliveries' => 0
            ];
            $currentDate->addDay();
        }

        foreach ($dailyEarningsQuery as $earning) {
            $dateKey = $earning->date;
            if (isset($dateRange[$dateKey])) {
                $dateRange[$dateKey]['earnings'] = (float)$earning->earnings;
                $dateRange[$dateKey]['deliveries'] = (int)$earning->deliveries;
            }
        }

        // Convert to indexed arrays for chart
        $dailyEarningsData = [
            'labels' => array_values(array_column($dateRange, 'display_date')),
            'earnings' => array_values(array_column($dateRange, 'earnings')),
            'deliveries' => array_values(array_column($dateRange, 'deliveries'))
        ];

        // ========== MONTHLY EARNINGS CHART DATA ==========
        // Get monthly earnings for the current year
        $yearlyEarnings = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereYear('delivered_at', date('Y'))
            ->select(
                DB::raw('MONTH(delivered_at) as month'),
                DB::raw('COUNT(*) as deliveries'),
                DB::raw('SUM(delivery_charge) as total_charges'),
                DB::raw('SUM(delivery_charge * 0.7) as earnings')
            )
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();

        // Prepare monthly data for all 12 months
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[$i] = [
                'month' => $i,
                'month_name' => $monthNames[$i - 1],
                'earnings' => 0,
                'deliveries' => 0
            ];
        }

        foreach ($yearlyEarnings as $earning) {
            $monthlyData[$earning->month]['earnings'] = (float)$earning->earnings;
            $monthlyData[$earning->month]['deliveries'] = (int)$earning->deliveries;
        }

        $monthlyEarningsData = [
            'labels' => array_values(array_column($monthlyData, 'month_name')),
            'earnings' => array_values(array_column($monthlyData, 'earnings')),
            'deliveries' => array_values(array_column($monthlyData, 'deliveries'))
        ];

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
            'dailyEarningsData',
            'monthlyEarningsData',
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
     * Display delivery reports dashboard
     */
    public function delivery(ReportFilterRequest $request)
    {
        $validated = $request->validated();

        // Get filter values with defaults
        $period = $validated['period'] ?? 'monthly';
        $startDate = $validated['start_date'] ?? now()->startOfMonth();
        $endDate = $validated['end_date'] ?? now()->endOfMonth();
        $hubId = $validated['hub_id'] ?? null;
        $riderId = $validated['rider_id'] ?? null;

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
