<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::where('shop_id', auth()->user()->shop_id);

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $bills = $query->latest('date')->latest('id')->paginate(20)->withQueryString();

        return Inertia::render('Owner/Bills/Index', [
            'bills' => $bills,
            'filters' => $request->only(['start_date', 'end_date', 'type'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'type' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $bill = Bill::create([
            'shop_id' => auth()->user()->shop_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'type' => $request->type,
            'note' => $request->note,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Bill added successfully.', 'bill' => $bill]);
        }

        return back()->with('success', 'Bill added successfully.');
    }

    public function destroy(Bill $bill)
    {
        if ($bill->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $bill->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Bill deleted successfully.']);
        }

        return back()->with('success', 'Bill deleted successfully.');
    }
}
