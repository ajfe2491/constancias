<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $event->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('events.index') }}" class="btn btn-outline btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
                <a href="{{ route('events.edit', $event) }}" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Editar Evento
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Detalles del Evento -->
            <div class="bg-base-100 shadow-sm sm:rounded-lg border border-base-200 p-6">
                <div class="flex flex-col gap-8">
                    <!-- Top Row: Key Info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div>
                            <h3 class="text-xs font-bold opacity-50 uppercase tracking-wider mb-2">Clave</h3>
                            <p class="font-medium font-mono bg-base-200 inline-block px-3 py-1 rounded text-sm">
                                {{ $event->key }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold opacity-50 uppercase tracking-wider mb-2">Tipo</h3>
                            <p class="font-medium text-lg">{{ $event->type }}</p>
                        </div>
                        <div class="md:col-span-1">
                            <h3 class="text-xs font-bold opacity-50 uppercase tracking-wider mb-2">Fechas</h3>
                            <p class="font-medium">
                                {{ $event->start_date ? $event->start_date->format('d M Y') : 'N/A' }}
                                @if($event->end_date)
                                    <span class="text-gray-400 mx-1">|</span> {{ $event->end_date->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold opacity-50 uppercase tracking-wider mb-2">Estado</h3>
                            <div class="badge {{ $event->is_active ? 'badge-success' : 'badge-ghost' }} badge-lg">
                                {{ $event->is_active ? 'Activo' : 'Inactivo' }}
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row: Description & Meta -->
                    @if($event->description)
                        <div class="pt-6 border-t border-base-200">
                            <h3 class="text-xs font-bold opacity-50 uppercase tracking-wider mb-2">Descripción</h3>
                            <div class="flex flex-col md:flex-row justify-between items-end gap-4">
                                <p class="opacity-80 max-w-4xl">{{ $event->description }}</p>
                                <div class="text-xs opacity-50 whitespace-nowrap">
                                    Última actualización: {{ $event->updated_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex justify-end pt-2">
                            <div class="text-xs opacity-50">
                                Última actualización: {{ $event->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Plantillas de Documentos -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Plantillas de Documentos</h3>
                    <a href="{{ route('document-configurations.create', ['event_id' => $event->id]) }}"
                        class="btn btn-primary btn-sm gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva Plantilla
                    </a>
                </div>

                @if($event->documentConfigurations->isEmpty())
                    <div class="bg-base-100 rounded-lg border-2 border-dashed border-base-300 p-12 text-center">
                        <div class="opacity-50 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-lg">No hay plantillas configuradas</h3>
                        <p class="opacity-60 mb-6">Crea plantillas específicas para este evento (ej. Constancia de
                            Asistencia, Ponente, etc.)</p>
                        <a href="{{ route('document-configurations.create', ['event_id' => $event->id]) }}"
                            class="btn btn-primary btn-sm">
                            Crear Primera Plantilla
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($event->documentConfigurations as $config)
                            <div
                                class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-200 border border-base-200 group h-full">
                                <!-- Mini Preview Area -->
                                <figure class="h-32 bg-base-200 relative overflow-hidden group shrink-0">
                                    <iframe
                                        src="{{ route('document-configurations.stream-pdf', $config) }}#toolbar=0&navpanes=0&scrollbar=0&view=Fit"
                                        class="w-full h-full border-none pointer-events-none transform scale-100 origin-top-left"
                                        scrolling="no">
                                    </iframe>

                                    <!-- Overlay on hover -->
                                    <div
                                        class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center backdrop-blur-[2px] opacity-0 group-hover:opacity-100">
                                        <a href="{{ route('document-configurations.edit', $config) }}"
                                            class="btn btn-primary btn-xs shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                                            Editar
                                        </a>
                                    </div>
                                </figure>

                                <div class="card-body p-4 flex flex-col">
                                    <div class="flex justify-between items-start gap-2">
                                        <div class="flex-1 min-w-0">
                                            <div class="tooltip tooltip-bottom before:text-xs before:max-w-[200px] before:content-[attr(data-tip)]"
                                                data-tip="{{ $config->document_name }}">
                                                <h4 class="font-bold text-base truncate text-left">
                                                    {{ $config->document_name }}
                                                </h4>
                                            </div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">
                                                {{ $config->document_type }}
                                            </p>
                                        </div>
                                        <div class="dropdown dropdown-end">
                                            <label tabindex="0"
                                                class="btn btn-ghost btn-circle btn-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                                </svg>
                                            </label>
                                            <ul tabindex="0"
                                                class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                                <li><a href="{{ route('document-configurations.edit', $config) }}">Editar</a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('document-configurations.destroy', $config) }}"
                                                        method="POST" onsubmit="return confirm('¿Estás seguro?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-error">Eliminar</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <p class="text-xs text-gray-600 line-clamp-2 mt-2 h-8" title="{{ $config->description }}">
                                        {{ $config->description ?: 'Sin descripción' }}
                                    </p>

                                    <div
                                        class="card-actions justify-between items-center border-t border-base-100 pt-3 mt-auto">
                                        <div class="flex items-center text-xs text-gray-400">
                                            <div
                                                class="badge {{ $config->is_active ? 'badge-success' : 'badge-ghost' }} badge-xs gap-1 mr-2">
                                            </div>
                                            {{ $config->page_size }} ({{ $config->page_orientation }})
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>