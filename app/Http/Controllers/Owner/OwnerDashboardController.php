<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $shopId = auth()->user()->shop_id;
        
        // 1. Date Range Handling
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();
        
        // For comparison (previous period of same duration)
        $diff = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = (clone $startDate)->subDays($diff);
        $prevEndDate = (clone $endDate)->subDays($diff);

        // 2. Metrics for Current Period
        $currentStats = $this->getStats($shopId, $startDate, $endDate);
        $prevStats = $this->getStats($shopId, $prevStartDate, $prevEndDate);

        // 3. Charts Data
        // Revenue Trend (Daily or Monthly depending on range)
        $interval = $diff > 31 ? '%Y-%m' : '%Y-%m-%d';
        $revenueTrend = Appointment::where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(start_time, '{$interval}') as label"),
                DB::raw('sum(total_price) as total')
            )
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        // Service Breakdown
        $serviceBreakdown = DB::table('appointment_services')
            ->join('appointments', 'appointment_services.appointment_id', '=', 'appointments.id')
            ->join('services', 'appointment_services.service_id', '=', 'services.id')
            ->where('appointments.shop_id', $shopId)
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.start_time', [$startDate, $endDate])
            ->select('services.name', DB::raw('count(*) as count'), DB::raw('sum(appointment_services.price_at_time) as revenue'))
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // 4. Top Performers (Barbers)
        $topBarbers = User::where('shop_id', $shopId)
            ->where('role', 'barber')
            ->withSum(['appointments as revenue' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'completed')
                      ->whereBetween('start_time', [$startDate, $endDate]);
            }], 'total_price')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // 5. Recent Activity
        $recentAppointments = Appointment::where('shop_id', $shopId)
            ->with(['barber:id,name', 'customer:id,name'])
            ->latest()
            ->take(6)
            ->get();

        return Inertia::render('Owner/Dashboard', [
            'stats' => $currentStats,
            'prevStats' => $prevStats,
            'charts' => [
                'revenueTrend' => $revenueTrend,
                'serviceBreakdown' => $serviceBreakdown,
            ],
            'topBarbers' => $topBarbers,
            'recentAppointments' => $recentAppointments,
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]
        ]);
    }

    private function getStats($shopId, $start, $end)
    {
        $revenue = Appointment::where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$start, $end])
            ->sum('total_price');

        $commission = Appointment::where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$start, $end])
            ->sum('commission_amount');

        $expenses = \App\Models\Bill::where('shop_id', $shopId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $payouts = \App\Models\BarberPayout::where('shop_id', $shopId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $appointmentsCount = Appointment::where('shop_id', $shopId)
            ->whereBetween('start_time', [$start, $end])
            ->count();

        $completedCount = Appointment::where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$start, $end])
            ->count();

        $newCustomers = Customer::where('shop_id', $shopId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'gross_revenue' => (float)$revenue,
            'shop_share' => (float)($revenue - $commission),
            'net_profit' => (float)($revenue - $commission - $expenses),
            'expenses' => (float)$expenses,
            'payouts' => (float)$payouts,
            'appointments_count' => $appointmentsCount,
            'completed_count' => $completedCount,
            'new_customers' => $newCustomers,
            'commission_liability' => (float)$commission,
        ];
    }
}
