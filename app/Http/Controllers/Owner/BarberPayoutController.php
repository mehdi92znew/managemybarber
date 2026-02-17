<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BarberPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarberPayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = BarberPayout::where('shop_id', auth()->user()->shop_id)
            ->with('barber:id,name');

        if ($request->filled('barber_id')) {
            $query->where('barber_id', $request->barber_id);
        }

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $payouts = $query->latest('date')->latest('id')->paginate(20)->withQueryString();
        
        $barbers = User::where('shop_id', auth()->user()->shop_id)
            ->where('role', 'barber')
            ->get(['id', 'name']);

        return Inertia::render('Owner/BarberPayouts/Index', [
            'payouts' => $payouts,
            'barbers' => $barbers,
            'filters' => $request->only(['barber_id', 'start_date', 'end_date'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        // Ensure the barber belongs to the same shop
        $barber = User::where('id', $request->barber_id)
            ->where('shop_id', auth()->user()->shop_id)
            ->firstOrFail();

        $payout = BarberPayout::create([
            'shop_id' => auth()->user()->shop_id,
            'barber_id' => $request->barber_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'note' => $request->note,
        ]);

        if ($request->wantsJson()) {
            $payout->load('barber:id,name');
            return response()->json(['message' => 'Payout recorded successfully.', 'payout' => $payout]);
        }

        return back()->with('success', 'Payout recorded successfully.');
    }

    public function destroy(BarberPayout $barberPayout)
    {
        if ($barberPayout->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $barberPayout->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Payout deleted successfully.']);
        }

        return back()->with('success', 'Payout deleted successfully.');
    }
}
