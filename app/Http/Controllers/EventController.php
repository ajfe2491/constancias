<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Models\ConstancyGeneralHistory;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Event::with('documentConfigurations', 'sharedUsers')
            ->withCount('sharedUsers')
            ->visibleTo(Auth::user())
            ->latest();
        $showInactive = $request->boolean('show_inactive');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if (!$showInactive) {
            $query->where('is_active', true);
        }

        $events = $query->get();
        $shareableUsers = \App\Models\User::whereNull('deleted_at')
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
        return view('events.index', compact('events', 'showInactive', 'shareableUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:20|unique:events,key',
            'type' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|mimetypes:image/jpeg,image/png|max:2048|dimensions:max_width=6000,max_height=6000',
        ];
        $messages = [
            'logo.image' => 'El logotipo debe ser una imagen válida.',
            'logo.mimes' => 'El logotipo debe ser JPG o PNG.',
            'logo.mimetypes' => 'El logotipo debe ser JPG o PNG.',
            'logo.max' => 'El logotipo no debe superar 2 MB.',
            'logo.dimensions' => 'El logotipo no debe exceder 6000x6000 px.',
        ];
        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active');
        $validated['user_id'] = Auth::id();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('event_logos', 'public');
        }

        \App\Models\Event::create($validated);

        return redirect()->route('events.index')
            ->with('success', 'Evento creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Event $event)
    {
        if (!$event->canBeViewedBy(Auth::user())) {
            abort(403);
        }

        $event->load(['documentConfigurations' => function ($query) {
            $query->withCount('sharedUsers')->with('sharedUsers');
        }]);
        $isOwner = $event->user_id === Auth::id() || Auth::user()->isSuperAdmin();
        $configIds = $event->documentConfigurations->pluck('id');
        $batchBase = ConstancyGeneralHistory::whereIn('document_configuration_id', $configIds);
        $lastBatch = (clone $batchBase)->latest()->first();

        $eventDurationDays = null;
        if ($event->start_date && $event->end_date) {
            $eventDurationDays = $event->start_date->diffInDays($event->end_date) + 1;
        }

        return view('events.show', [
            'event' => $event,
            'totalTemplates' => $event->documentConfigurations->count(),
            'totalCertificates' => Certificate::where('event_id', $event->id)->count(),
            'totalBatches' => (clone $batchBase)->count(),
            'totalSuccessful' => (clone $batchBase)->sum('procesados_exitosos'),
            'lastBatchAt' => $lastBatch?->created_at,
            'eventDurationDays' => $eventDurationDays,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Event $event)
    {
        $this->ensureOwner($event);
        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Event $event)
    {
        $this->ensureOwner($event);
        $rules = [
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:20|unique:events,key,' . $event->id,
            'type' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|mimetypes:image/jpeg,image/png|max:2048|dimensions:max_width=6000,max_height=6000',
        ];
        $messages = [
            'logo.image' => 'El logotipo debe ser una imagen válida.',
            'logo.mimes' => 'El logotipo debe ser JPG o PNG.',
            'logo.mimetypes' => 'El logotipo debe ser JPG o PNG.',
            'logo.max' => 'El logotipo no debe superar 2 MB.',
            'logo.dimensions' => 'El logotipo no debe exceder 6000x6000 px.',
        ];
        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('logo')) {
            if ($event->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($event->logo);
            }
            $validated['logo'] = $request->file('logo')->store('event_logos', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.index')
            ->with('success', 'Evento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Event $event)
    {
        $this->ensureOwner($event);
        if ($event->logo && method_exists($event, 'isForceDeleting') && $event->isForceDeleting()) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->logo);
        }
        $event->delete();
        return redirect()->route('events.index')
            ->with('success', 'Evento eliminado exitosamente.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(\App\Models\Event $event)
    {
        $this->ensureOwner($event);
        $event->is_active = !$event->is_active;
        $event->save();

        return back()->with('success', $event->is_active
            ? 'Evento activado correctamente.'
            : 'Evento inactivado correctamente.');
    }

    private function ensureOwner(\App\Models\Event $event): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && $event->user_id !== $user->id)) {
            abort(403);
        }
    }
}
