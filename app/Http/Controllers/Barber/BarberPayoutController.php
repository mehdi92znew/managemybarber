<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\BarberPayout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarberPayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = BarberPayout::where('barber_id', auth()->id());

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $payouts = $query->latest('date')->latest('id')->paginate(15)->withQueryString();

        $totalEarned = (clone $query)->sum('amount');

        return Inertia::render('Barber/Payouts/Index', [
            'payouts' => $payouts,
            'totalEarned' => (float)$totalEarned,
            'filters' => $request->only(['start_date', 'end_date'])
        ]);
    }
}
