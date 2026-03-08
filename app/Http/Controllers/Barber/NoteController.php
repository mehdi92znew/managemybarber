<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::query()->with(['author', 'barber'])
            ->where('shop_id', '=', auth()->user()->shop_id)
            ->where(function($q) {
                $q->whereNull('barber_id')
                  ->orWhere('barber_id', '=', auth()->id());
            })
            ->latest();

        if ($request->filled('date')) {
            $query->whereDate('date', '=', $request->date);
        }
        
        if ($request->filled('type')) {
            $query->where('type', '=', $request->type);
        }

        $notes = $query->get();

        return \Inertia\Inertia::render('Barber/Notes/Index', [
            'notes' => $notes,
            'filters' => $request->only(['date', 'type'])
        ]);
    }
}
