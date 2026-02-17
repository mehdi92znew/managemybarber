<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::where('shop_id', auth()->user()->shop_id);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
 
        if ($request->wantsJson()) {
            return response()->json($query->latest()->take(10)->get());
        }
 
        $customers = $query->latest()->paginate(20)->withQueryString();
        
        return \Inertia\Inertia::render('Owner/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'start_date', 'end_date'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'shop_id' => auth()->user()->shop_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'notes' => $request->notes,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Customer created successfully.', 'customer' => $customer]);
        }

        return back()->with('success', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $customer->update($request->only('name', 'phone', 'notes'));

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Customer updated successfully.', 'customer' => $customer]);
        }

        return back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $customer->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Customer deleted successfully.']);
        }

        return back()->with('success', 'Customer deleted successfully.');
    }
}
