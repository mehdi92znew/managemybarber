<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        // ShopScope automatically applies
        $services = Service::latest()->paginate(20)->withQueryString();
        return \Inertia\Inertia::render('Owner/Services/Index', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_extra' => 'boolean',
        ]);

        $service = Service::create([
            'shop_id' => auth()->user()->shop_id,
            'name' => $request->name,
            'price' => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'is_extra' => $request->is_extra ?? false,
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Service created successfully.', 'service' => $service]);
        }

        return back()->with('success', 'Service created successfully.');
    }

    public function update(Request $request, Service $service)
    {
        if ($service->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_extra' => 'boolean',
        ]);

        $service->update([
            'name' => $request->name,
            'price' => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'is_extra' => $request->is_extra ?? false,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Service updated successfully.', 'service' => $service]);
        }

        return back()->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        if ($service->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $service->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Service deleted successfully.']);
        }

        return back()->with('success', 'Service deleted successfully.');
    }
}
