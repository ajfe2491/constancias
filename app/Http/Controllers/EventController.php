<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Models\ConstancyGeneralHistory;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Event::with('documentConfigurations')->latest();
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
        return view('events.index', compact('events', 'showInactive'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:20|unique:events,key',
            'type' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

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
        $event->load('documentConfigurations');
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
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Event $event)
    {
        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:20|unique:events,key,' . $event->id,
            'type' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

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
        if ($event->logo) {
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
        $event->is_active = !$event->is_active;
        $event->save();

        return back()->with('success', $event->is_active
            ? 'Evento activado correctamente.'
            : 'Evento inactivado correctamente.');
    }
}
