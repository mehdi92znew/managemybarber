<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BarberPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BarberReportController extends Controller
{
    public function index(Request $request)
    {
        $barbers = User::where('shop_id', auth()->user()->shop_id)
            ->where('role', 'barber')
            ->get(['id', 'name']);

        $barberId = $request->barber_id;
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;

        $reportData = null;

        if ($barberId && $startDate && $endDate) {
            $appointments = Appointment::with('customer:id,name', 'services:id,name')
                ->where('barber_id', $barberId)
                ->where('status', 'completed')
                ->whereBetween('start_time', [$startDate, $endDate])
                ->get();

            $payouts = BarberPayout::where('barber_id', $barberId)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->latest()
                ->get();

            $totalServices = $appointments->sum('total_price');
            $totalCommission = $appointments->sum('commission_amount');
            $totalPayouts = $payouts->sum('amount');
            
            $reportData = [
                'appointments' => $appointments,
                'payouts' => $payouts,
                'totals' => [
                    'services' => $totalServices,
                    'commission' => $totalCommission,
                    'payouts' => $totalPayouts,
                    'balance' => $totalCommission - $totalPayouts,
                ]
            ];
        }

        return Inertia::render('Owner/Reports/BarberReport', [
            'barbers' => $barbers,
            'reportData' => $reportData,
            'filters' => [
                'barber_id' => $barberId,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]
        ]);
    }
}
