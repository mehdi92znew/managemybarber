<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Shop::with('owner');

        if ($request->status) {
            $query->where('subscription_status', '=', $request->status);
        }

        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        $shops = $query->latest()->paginate(10);

        return Inertia::render('Admin/Shops/Index', [
            'shops' => $shops,
            'filters' => $request->only(['status', 'search'])
        ]);
    }

    public function show(Shop $shop)
    {
        $shop->load(['owner', 'users', 'services']);
        
        $stats = [
            'total_appointments' => $shop->appointments()->count(),
            'total_revenue' => (float)$shop->appointments()->where('status', '=', 'completed')->sum('total_price'),
            'total_customers' => $shop->customers()->count(),
        ];

        $logs = ActivityLog::with('user')
            ->where('shop_id', '=', $shop->id)
            ->latest()
            ->take(50)
            ->get();

        return Inertia::render('Admin/Shops/Show', [
            'shop' => $shop,
            'stats' => $stats,
            'logs' => $logs
        ]);
    }

    public function approve(Shop $shop)
    {
        $shop->update(['subscription_status' => 'trial']);
        
        ActivityLog::create([
            'shop_id' => $shop->id,
            'user_id' => auth()->id(),
            'action' => 'shop_approval',
            'description' => "Shop '{$shop->name}' was approved by Admin.",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Shop approved successfully.');
    }

    public function suspend(Shop $shop)
    {
        $shop->update(['subscription_status' => 'suspended']);
        
        ActivityLog::create([
            'shop_id' => $shop->id,
            'user_id' => auth()->id(),
            'action' => 'shop_suspension',
            'description' => "Shop '{$shop->name}' was suspended.",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Shop suspended.');
    }

    public function activate(Shop $shop)
    {
        $shop->update(['subscription_status' => 'active']);
        
        ActivityLog::create([
            'shop_id' => $shop->id,
            'user_id' => auth()->id(),
            'action' => 'shop_activation',
            'description' => "Shop '{$shop->name}' was activated.",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Shop activated.');
    }

    public function updateSubscription(Request $request, Shop $shop)
    {
        $request->validate([
            'status' => 'required|string|in:trial,active,suspended,pending',
            'ends_at' => 'nullable|date',
        ]);

        $shop->update([
            'subscription_status' => $request->status,
            'subscription_ends_at' => $request->ends_at,
        ]);

        ActivityLog::create([
            'shop_id' => $shop->id,
            'user_id' => auth()->id(),
            'action' => 'subscription_update',
            'description' => "Subscription for '{$shop->name}' updated to {$request->status}.",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Subscription updated.');
    }

    public function impersonate(Shop $shop)
    {
        $owner = $shop->owner;
        if (!$owner) {
            return back()->with('error', 'This shop has no owner.');
        }

        // Store original admin ID in session
        session(['impersonator_id' => auth()->id()]);
        
        Auth::login($owner);

        return redirect()->route('owner.dashboard')->with('success', "Now impersonating {$owner->name}");
    }

    public function leaveImpersonation()
    {
        $adminId = session('impersonator_id');
        if (!$adminId) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($adminId);
        session()->forget('impersonator_id');
        
        Auth::login($admin);

        return redirect()->route('admin.dashboard')->with('success', "Returned to Admin Dashboard");
    }
}
