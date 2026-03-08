<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Customer;
use App\Models\ActivityLog;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Key Metrics
        $totalShops = Shop::query()->count();
        $pendingShops = Shop::query()->where('subscription_status', '=', 'pending')->count();
        $activeShops = Shop::query()->whereIn('subscription_status', ['active', 'trial'])->count();
        
        $totalBarbers = User::query()->where('role', '=', 'barber')->count();
        $totalAppointments = Appointment::query()->count();
        $totalCustomers = Customer::query()->count();

        // 2. Platform Revenue (Sum of all completed appointments as a proxy for platform performance)
        $totalPlatformRevenue = (float)Appointment::query()->where('status', '=', 'completed')->sum('total_price');

        // 3. Shop Growth Chart (Last 6 Months)
        $shopsGrowth = Shop::select(
                DB::raw('count(*) as total'), 
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 4. Recent Activity Logs (Global)
        $recentLogs = ActivityLog::with(['shop', 'user'])->latest()->take(10)->get();

        // 5. Recent Shops
        $recentShops = Shop::with('owner')->latest()->take(5)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalShops' => $totalShops,
                'pendingShops' => $pendingShops,
                'activeShops' => $activeShops,
                'totalBarbers' => $totalBarbers,
                'totalAppointments' => $totalAppointments,
                'totalCustomers' => $totalCustomers,
                'platformRevenue' => $totalPlatformRevenue,
            ],
            'shopsGrowth' => $shopsGrowth,
            'recentLogs' => $recentLogs,
            'recentShops' => $recentShops,
        ]);
    }
}
