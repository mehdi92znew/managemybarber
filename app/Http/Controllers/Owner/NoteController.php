<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::with(['author', 'barber'])
            ->where('shop_id', '=', auth()->user()->shop_id)
            ->latest();

        if ($request->filled('barber_id')) {
            $query->where('barber_id', '=', $request->barber_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', '=', $request->date, 'and');
        }
        
        if ($request->filled('type')) {
            $query->where('type', '=', $request->type, 'and');
        }

        $notes = $query->get();
        $barbers = User::query()->where('shop_id', '=', auth()->user()->shop_id, 'and')
            ->where('role', '=', 'barber', 'and')
            ->where('is_active', '=', true, 'and')
            ->get();

        return \Inertia\Inertia::render('Owner/Notes/Index', [
            'notes' => $notes,
            'barbers' => $barbers,
            'filters' => $request->only(['barber_id', 'date', 'type'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:info,warning,danger,success',
            'barber_id' => 'nullable|exists:users,id',
            'date' => 'nullable|date',
        ]);

        if ($request->barber_id) {
            $barber = User::query()->where('id', '=', $request->barber_id, 'and')->first();
            if ($barber && $barber->shop_id !== auth()->user()->shop_id) {
                abort(403, 'Unauthorized barber selection.');
            }
        }

        Note::create([
            'shop_id' => auth()->user()->shop_id,
            'author_id' => auth()->id(),
            'barber_id' => $request->barber_id,
            'content' => $request->content,
            'type' => $request->type,
            'date' => $request->date,
            'is_active' => true,
        ]);

        return back()->with('success', 'Note created successfully.');
    }

    public function update(Request $request, Note $note)
    {
        if ($note->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:info,warning,danger,success',
            'barber_id' => 'nullable|exists:users,id',
            'date' => 'nullable|date',
        ]);

        if ($request->barber_id) {
            $barber = User::query()->where('id', '=', $request->barber_id, 'and')->first();
            if ($barber && $barber->shop_id !== auth()->user()->shop_id) {
                abort(403, 'Unauthorized barber selection.');
            }
        }

        $note->update([
            'content' => $request->content,
            'type' => $request->type,
            'barber_id' => $request->barber_id,
            'date' => $request->date,
        ]);

        return back()->with('success', 'Note updated successfully.');
    }

    public function destroy(Note $note)
    {
        if ($note->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        Note::query()->where('id', '=', $note->id, 'and')->delete();

        return back()->with('success', 'Note deleted successfully.');
    }
}
