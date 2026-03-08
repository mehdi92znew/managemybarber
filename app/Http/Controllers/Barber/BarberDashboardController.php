<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarberDashboardController extends Controller
{
    public function index(Request $request)
    {
        $barberId = auth()->id();
        
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

        // 1. Next Appointment
        $nextAppointment = Appointment::with(['customer', 'services'])
            ->where('barber_id', $barberId)
            ->where('start_time', '>=', now())
            ->whereDate('start_time', today())
            ->orderBy('start_time')
            ->first();

        // 2. Today's Full Schedule
        $todaySchedule = Appointment::with(['customer', 'services'])
            ->where('barber_id', $barberId)
            ->whereDate('start_time', today())
            ->orderBy('start_time')
            ->get();

        // 3. Stats
        $todayCommission = Appointment::where('barber_id', $barberId)
            ->where('status', 'completed')
            ->whereDate('start_time', today())
            ->sum('commission_amount');

        $weekCommission = Appointment::where('barber_id', $barberId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('commission_amount');

        $filteredCommission = Appointment::where('barber_id', $barberId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->sum('commission_amount');

        $completedCount = Appointment::where('barber_id', $barberId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->count();

        // 4. Commission Chart Data
        $diff = $startDate->diffInDays($endDate) + 1;
        $interval = $diff > 31 ? '%Y-%m' : '%Y-%m-%d';
        
        $commissionData = Appointment::select(
                DB::raw('sum(commission_amount) as total'), 
                DB::raw("DATE_FORMAT(start_time, '{$interval}') as day")
            )
            ->where('barber_id', $barberId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return \Inertia\Inertia::render('Barber/Dashboard', [
            'stats' => [
                'todayCommission' => $todayCommission,
                'weekCommission' => $weekCommission,
                'filteredCommission' => $filteredCommission,
                'completedCount' => $completedCount,
            ],
            'nextAppointment' => $nextAppointment,
            'todaySchedule' => $todaySchedule,
            'commissionChart' => $commissionData,
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]
        ]);
    }
}
