<?php

namespace App\Http\Controllers;

use App\Models\DocumentConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentConfigurationShareController extends Controller
{
    public function update(Request $request, DocumentConfiguration $documentConfiguration)
    {
        if ($documentConfiguration->user_id !== Auth::id() && !Auth::user()->isSuperAdmin()) {
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

        $documentConfiguration->sharedUsers()->sync($syncData);

        return back()->with('success', 'Permisos de la configuración actualizados.');
    }
}
