<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with(['shop', 'user']);

        if ($request->search) {
            $query->where('description', 'LIKE', "%{$request->search}%", 'and');
        }

        if ($request->shop_id) {
            $query->where('shop_id', '=', $request->shop_id, 'and');
        }

        if ($request->action) {
            $query->where('action', '=', $request->action, 'and');
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        return Inertia::render('Admin/Logs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'shop_id', 'action']),
            'shops' => \App\Models\Shop::query()->select('id', 'name')->get(),
            'actions' => ActivityLog::query()->distinct()->pluck('action')
        ]);
    }
}
