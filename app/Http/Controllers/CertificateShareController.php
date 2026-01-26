<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateShareController extends Controller
{
    public function update(Request $request, Certificate $certificate)
    {
        $history = $certificate->history;
        if (!$history || $history->user_id !== Auth::id()) {
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

        $certificate->sharedUsers()->sync($syncData);

        return back()->with('success', 'Permisos de constancia actualizados.');
    }
}
