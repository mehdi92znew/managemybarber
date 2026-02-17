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

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Key Metrics
        $totalShops = Shop::count();
        $totalBarbers = User::where('role', 'barber')->count();
        $totalAppointments = Appointment::count();
        $totalCustomers = Customer::count();

        // 2. Shop Growth Chart (Last 6 Months)
        $shopsGrowth = Shop::select(
                DB::raw('count(*) as total'), 
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 3. Recent Shops
        $recentShops = Shop::with('owner')->latest()->take(5)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalShops' => $totalShops,
                'totalBarbers' => $totalBarbers,
                'totalAppointments' => $totalAppointments,
                'totalCustomers' => $totalCustomers,
            ],
            'shopsGrowth' => $shopsGrowth,
            'recentShops' => $recentShops,
        ]);
    }
}
