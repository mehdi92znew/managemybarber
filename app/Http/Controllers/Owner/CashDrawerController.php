<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\CashDrawer;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\BarberPayout;

class CashDrawerController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->user()->shop_id;
        $today = Carbon::today();

        // Get today's drawer
        $drawer = CashDrawer::where('shop_id', $shopId)
                            ->whereDate('date', $today)
                            ->first();

        // Calculate Revenue (Appointments Paid Today, considering start_time as the appointment date)
        $grossRevenue = Appointment::where('shop_id', $shopId)
                                   ->whereDate('start_time', $today)
                                   ->where('payment_status', 'paid')
                                   ->sum('total_price');

        // Calculate Expenses (Bills Today)
        $expenses = Bill::where('shop_id', $shopId)
                        ->whereDate('date', $today)
                        ->sum('amount');

        // Calculate Payouts (Barber Payouts Today)
        $payouts = BarberPayout::where('shop_id', $shopId)
                               ->whereDate('date', $today)
                               ->sum('amount');

        // Calculate Expected Net Cash in Drawer
        $startingCash = $drawer ? $drawer->starting_cash : 0;
        $netCash = $startingCash + $grossRevenue - $expenses - $payouts;

        return Inertia::render('Owner/CashDrawer/Index', [
            'drawer' => $drawer,
            'stats' => [
                'grossRevenue' => (float) $grossRevenue,
                'expenses' => (float) $expenses,
                'payouts' => (float) $payouts,
                'expectedNetCash' => (float) $netCash,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'starting_cash' => 'required|numeric|min:0'
        ]);

        $shopId = $request->user()->shop_id;
        $today = Carbon::today();

        // Ensure drawer doesn't already exist for today
        if (CashDrawer::where('shop_id', $shopId)->whereDate('date', $today)->exists()) {
            return back()->with('error', 'Cash drawer already opened for today.');
        }

        CashDrawer::create([
            'shop_id' => $shopId,
            'date' => $today,
            'starting_cash' => $request->starting_cash,
        ]);

        return back()->with('success', 'Cash drawer opened successfully.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $shopId = $request->user()->shop_id;
        $today = Carbon::today();

        $drawer = CashDrawer::where('shop_id', $shopId)
                            ->whereDate('date', $today)
                            ->firstOrFail();

        $drawer->update([
            'closing_cash' => $request->closing_cash,
            'closed_at' => now(),
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Cash drawer closed successfully.');
    }
}
