<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventShareController extends Controller
{
    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== Auth::id() && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'shared_users' => ['array'],
            'shared_users.*' => ['integer', 'exists:users,id'],
        ]);

        $sharedUsers = collect($validated['shared_users'] ?? [])
            ->reject(fn ($id) => (int) $id === Auth::id())
            ->unique()
            ->values();

        $syncData = $sharedUsers->mapWithKeys(function ($userId) {
            return [$userId => ['shared_by' => Auth::id()]];
        });

        $event->sharedUsers()->sync($syncData);

        return back()->with('success', 'Permisos del evento actualizados.');
    }
}
