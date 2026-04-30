<x-app-layout>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeInUp 0.5s ease-out forwards; }
        .glass-card {
            background: hsl(var(--b1) / 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid hsl(var(--bc) / 0.1);
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 py-12 space-y-8">
        <!-- Header -->
        <div class="animate-fade-in">
            <h1 class="text-4xl font-extrabold tracking-tight text-base-content">
                Panel de <span class="text-primary">Configuración</span>
            </h1>
            <p class="text-lg text-base-content/60 mt-2">Administra los parámetros base y categorías globales del sistema.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-2xl rounded-2xl animate-fade-in border-0 bg-success text-success-content">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <!-- Event Types Section -->
            <div class="glass-card rounded-[2.5rem] p-8 space-y-6 animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-base-content tracking-tight">Tipos de Eventos</h2>
                        <p class="text-sm text-base-content/50">Categorías disponibles para agrupar eventos.</p>
                    </div>
                </div>

                <form action="{{ route('settings.event-types.store') }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="text" name="type" placeholder="Ej. Webinar, Congreso..." 
                        class="input input-bordered w-full rounded-2xl bg-base-200/50 focus:bg-base-100 transition-all border-base-content/10" required />
                    <button type="submit" class="btn btn-primary rounded-2xl px-8 shadow-lg shadow-primary/20">Añadir</button>
                </form>

                <div class="overflow-x-auto rounded-2xl border border-base-content/5">
                    <table class="table w-full">
                        <thead class="bg-base-200/50">
                            <tr>
                                <th class="text-[10px] font-black uppercase tracking-widest text-base-content/40">Categoría</th>
                                <th class="text-right text-[10px] font-black uppercase tracking-widest text-base-content/40">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eventTypes as $type)
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    <td class="font-bold text-base-content">{{ $type }}</td>
                                    <td class="text-right">
                                        <form action="{{ route('settings.event-types.destroy') }}" method="POST" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="type" value="{{ $type }}">
                                            <button class="btn btn-ghost btn-circle btn-xs text-error hover:bg-error/10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Document Types Section -->
            <div class="glass-card rounded-[2.5rem] p-8 space-y-6 animate-fade-in" style="animation-delay: 0.2s">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-base-content tracking-tight">Tipos de Documentos</h2>
                        <p class="text-sm text-base-content/50">Formatos y tipos de constancias soportadas.</p>
                    </div>
                </div>

                <form action="{{ route('settings.document-types.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="text" name="key" placeholder="Clave (ej. diploma)" 
                            class="input input-bordered flex-1 rounded-2xl bg-base-200/50 font-mono text-sm border-base-content/10" required />
                        <input type="text" name="label" placeholder="Etiqueta (ej. Diploma)" 
                            class="input input-bordered flex-1 rounded-2xl bg-base-200/50 border-base-content/10" required />
                        <button type="submit" class="btn btn-secondary rounded-2xl px-8 shadow-lg shadow-secondary/20">Añadir</button>
                    </div>
                </form>

                <div class="overflow-x-auto rounded-2xl border border-base-content/5">
                    <table class="table w-full">
                        <thead class="bg-base-200/50">
                            <tr>
                                <th class="text-[10px] font-black uppercase tracking-widest text-base-content/40">Clave</th>
                                <th class="text-[10px] font-black uppercase tracking-widest text-base-content/40">Etiqueta</th>
                                <th class="text-right text-[10px] font-black uppercase tracking-widest text-base-content/40">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentTypes as $key => $label)
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    <td><span class="badge badge-ghost font-mono text-[10px] px-2">{{ $key }}</span></td>
                                    <td class="font-bold text-base-content">{{ $label }}</td>
                                    <td class="text-right">
                                        <form action="{{ route('settings.document-types.destroy') }}" method="POST" onsubmit="return confirm('¿Eliminar este tipo de documento?');">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="key" value="{{ $key }}">
                                            <button class="btn btn-ghost btn-circle btn-xs text-error hover:bg-error/10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>