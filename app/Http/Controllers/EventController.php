<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Event::with('documentConfigurations')->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $events = $query->get();
        return view('events.index', compact('events'));
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
        return view('events.show', compact('event'));
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
}
