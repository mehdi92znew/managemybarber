<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class BarberDashboardController extends Controller
{
    public function index()
    {
        $barberId = auth()->id();

        // 1. Key Metrics
        $todayAppointments = Appointment::where('barber_id', $barberId)
            ->whereDate('start_time', today())
            ->count();
            
        $upcomingCount = Appointment::where('barber_id', $barberId)
            ->where('start_time', '>', now())
            ->count();

        $monthCommission = Appointment::where('barber_id', $barberId)
            ->where('status', 'completed')
            ->whereMonth('start_time', now()->month)
            ->sum('commission_amount');

        $totalCommission = Appointment::where('barber_id', $barberId)
            ->where('status', 'completed')
            ->sum('commission_amount');

        // 2. Commission Chart Data (Daily for current month)
        $commissionData = Appointment::select(
                DB::raw('sum(commission_amount) as total'), 
                DB::raw("DATE_FORMAT(start_time, '%Y-%m-%d') as day")
            )
            ->where('barber_id', $barberId)
            ->where('status', 'completed')
            ->whereMonth('start_time', now()->month)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // 3. Upcoming List
        $appointments = Appointment::with('customer')
            ->where('barber_id', $barberId)
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return \Inertia\Inertia::render('Barber/Dashboard', [
            'stats' => [
                'todayAppointments' => $todayAppointments,
                'upcomingCount' => $upcomingCount,
                'monthCommission' => $monthCommission,
                'totalCommission' => $totalCommission,
            ],
            'commissionChart' => $commissionData,
            'appointments' => $appointments
        ]);
    }
}
