<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class BarberController extends Controller
{
    public function index()
    {
        $barbers = User::where('role', 'barber')->latest()->get();
        return \Inertia\Inertia::render('Owner/Barbers/Index', compact('barbers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $barber = User::create([
            'shop_id' => auth()->user()->shop_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'barber',
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'is_active' => true,
        ]);

        $barber->assignRole('barber');

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Barber created successfully.', 'barber' => $barber]);
        }

        return back()->with('success', 'Barber created successfully.');
    }

    public function update(Request $request, User $barber)
    {
        // Ensure the barber belongs to the authenticated shop (handled by Global Scope usually, but explicit check is good)
        if ($barber->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($barber->id)],
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $barber->update([
            'name' => $request->name,
            'email' => $request->email,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
        ]);

        if ($request->filled('password')) {
             $request->validate(['password' => 'string|min:8']);
             $barber->update(['password' => Hash::make($request->password)]);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Barber updated successfully.', 'barber' => $barber]);
        }

        return back()->with('success', 'Barber updated successfully.');
    }

    public function destroy(User $barber)
    {
        if ($barber->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $barber->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Barber deleted successfully.']);
        }

        return back()->with('success', 'Barber deleted successfully.');
    }
}
