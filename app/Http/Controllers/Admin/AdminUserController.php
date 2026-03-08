<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('shop');

        if ($request->role) {
            $query->where('role', '=', $request->role);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate(20);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['role', 'search'])
        ]);
    }

    public function toggleBlock(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot block yourself.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'user_unblocked' : 'user_blocked';
        $status = $user->is_active ? 'unblocked' : 'blocked';

        ActivityLog::create([
            'shop_id' => $user->shop_id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => "User '{$user->name}' ({$user->email}) was {$status}.",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "User has been {$status}.");
    }
}
