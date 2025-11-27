<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Eventos') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="eventManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">



            <!-- Search and Actions -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6 justify-between items-center">
                <form action="{{ route('events.index') }}" method="GET" class="w-full sm:w-auto flex-1 max-w-md">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar eventos..."
                            class="input input-bordered w-full pl-10 input-sm" />
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 absolute left-3 top-1/2 transform -translate-y-1/2 opacity-50" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Create New Card -->
                <button @click="openCreate()"
                    class="card bg-base-100 shadow-sm hover:shadow-md border border-dashed border-base-300 hover:border-primary transition-all duration-200 group flex flex-col items-center justify-center p-6 cursor-pointer min-h-[160px]">
                    <div
                        class="w-12 h-12 rounded-full bg-base-200 group-hover:bg-primary/10 flex items-center justify-center mb-3 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-base-content/50 group-hover:text-primary transition-colors" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="font-bold text-sm group-hover:text-primary transition-colors">Nuevo Evento</span>
                </button>

                @foreach($events as $event)
                    <div
                        class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-200 border border-base-200 group h-full">
                        <div class="card-body p-4 flex flex-col">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="tooltip tooltip-bottom before:text-xs before:max-w-[200px] before:content-[attr(data-tip)]"
                                        data-tip="{{ $event->name }}">
                                        <h2 class="font-bold text-base truncate text-left">
                                            <a href="{{ route('events.show', $event) }}"
                                                class="hover:text-primary transition-colors">
                                                {{ $event->name }}
                                            </a>
                                        </h2>
                                    </div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">
                                        <span
                                            class="font-mono bg-base-200 px-1 rounded text-base-content/70 mr-1">{{ $event->key }}</span>
                                        {{ $event->type }}
                                    </p>

                                    @if($event->documentConfigurations->count() > 0)
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach($event->documentConfigurations->groupBy('document_type') as $type => $configs)
                                                <div class="badge badge-ghost badge-xs text-[10px] gap-1 h-5">
                                                    <span class="font-bold">{{ $configs->count() }}</span> {{ ucfirst($type) }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
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
                                        class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-40 text-xs">
                                        <li><button @click='openEdit(@json($event))'>Editar</button></li>
                                        <li>
                                            <form action="{{ route('events.destroy', $event) }}" method="POST"
                                                onsubmit="return confirm('¿Estás seguro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error">Eliminar</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <p class="text-xs text-gray-600 line-clamp-2 mt-2 h-8" title="{{ $event->description }}">
                                {{ $event->description ?: 'Sin descripción' }}
                            </p>

                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-base-100 mt-auto">
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <div
                                        class="badge {{ $event->is_active ? 'badge-success' : 'badge-ghost' }} badge-xs gap-1">
                                    </div>
                                    <span>{{ $event->start_date ? $event->start_date->format('d M') : 'N/A' }}</span>
                                    @if($event->end_date)
                                        <span>- {{ $event->end_date->format('d M') }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('events.show', $event) }}"
                                    class="btn btn-ghost btn-xs text-primary">Ver</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Slide-Over Drawer -->
        <div class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true"
            x-show="slideOverOpen" style="display: none;">
            <!-- Background backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="slideOverOpen"
                x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="slideOverOpen = false"></div>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <!-- Slide-over panel -->
                        <div class="pointer-events-auto relative w-screen max-w-md" x-show="slideOverOpen"
                            x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                            x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                            <div class="flex h-full flex-col overflow-y-scroll bg-base-100 shadow-xl">
                                <div class="px-4 sm:px-6 py-6 border-b border-base-200 bg-base-200/50">
                                    <div class="flex items-start justify-between">
                                        <h2 class="text-lg font-semibold leading-6 text-base-content"
                                            id="slide-over-title" x-text="isEditing ? 'Editar Evento' : 'Nuevo Evento'">
                                            Nuevo Evento
                                        </h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button"
                                                class="rounded-md bg-base-100 text-base-content hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                                @click="slideOverOpen = false">
                                                <span class="sr-only">Cerrar panel</span>
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative mt-6 flex-1 px-4 sm:px-6">
                                    <!-- Form -->
                                    <form :action="formAction" method="POST">
                                        @csrf
                                        <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

                                        <div class="space-y-6">
                                            <!-- Nombre -->
                                            <div class="form-control w-full">
                                                <label class="label">
                                                    <span class="label-text font-bold">Nombre del Evento</span>
                                                </label>
                                                <input type="text" name="name" x-model="formData.name"
                                                    placeholder="Ej. Congreso Internacional 2024"
                                                    class="input input-bordered w-full" required />
                                            </div>

                                            <!-- Clave -->
                                            <div class="form-control w-full">
                                                <label class="label">
                                                    <span class="label-text font-bold">Clave del Evento</span>
                                                </label>
                                                <input type="text" name="key" x-model="formData.key"
                                                    placeholder="Ej. CON2024" class="input input-bordered w-full"
                                                    required maxlength="20" />
                                                <label class="label">
                                                    <span class="label-text-alt text-gray-500">Esta clave se utilizará
                                                        como prefijo para el folio de las constancias.</span>
                                                </label>
                                            </div>

                                            <!-- Tipo -->
                                            <div class="form-control w-full">
                                                <label class="label">
                                                    <span class="label-text font-bold">Tipo de Evento</span>
                                                </label>
                                                <select name="type" x-model="formData.type"
                                                    class="select select-bordered w-full">
                                                    <option value="" disabled>Selecciona un tipo</option>
                                                    <option value="Congreso">Congreso</option>
                                                    <option value="Curso">Curso</option>
                                                    <option value="Taller">Taller</option>
                                                    <option value="Seminario">Seminario</option>
                                                    <option value="Simposio">Simposio</option>
                                                    <option value="Conferencia">Conferencia</option>
                                                    <option value="Diplomado">Diplomado</option>
                                                    <option value="Foro">Foro</option>
                                                    <option value="Panel">Panel</option>
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>

                                            <!-- Fechas -->
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="form-control w-full">
                                                    <label class="label">
                                                        <span class="label-text font-bold">Inicio</span>
                                                    </label>
                                                    <input type="date" name="start_date" x-model="formData.start_date"
                                                        class="input input-bordered w-full" />
                                                </div>

                                                <div class="form-control w-full">
                                                    <label class="label">
                                                        <span class="label-text font-bold">Fin</span>
                                                    </label>
                                                    <input type="date" name="end_date" x-model="formData.end_date"
                                                        class="input input-bordered w-full" />
                                                </div>
                                            </div>

                                            <!-- Descripción -->
                                            <div class="form-control w-full">
                                                <label class="label">
                                                    <span class="label-text font-bold">Descripción</span>
                                                </label>
                                                <textarea name="description" x-model="formData.description"
                                                    class="textarea textarea-bordered h-24"
                                                    placeholder="Detalles adicionales..."></textarea>
                                            </div>

                                            <!-- Activo -->
                                            <div class="form-control">
                                                <label class="label cursor-pointer justify-start gap-4">
                                                    <input type="checkbox" name="is_active" x-model="formData.is_active"
                                                        class="checkbox checkbox-primary" />
                                                    <span class="label-text font-bold">Evento Activo</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mt-8 flex justify-end gap-3">
                                            <button type="button" class="btn btn-ghost"
                                                @click="slideOverOpen = false">Cancelar</button>
                                            <button type="submit" class="btn btn-primary"
                                                x-text="isEditing ? 'Actualizar' : 'Guardar'"></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function eventManager() {
            return {
                slideOverOpen: false,
                isEditing: false,
                formAction: '{{ route('events.store') }}',
                formData: {
                    name: '',
                    key: '',
                    type: '',
                    start_date: '',
                    end_date: '',
                    description: '',
                    is_active: true
                },

                openCreate() {
                    this.isEditing = false;
                    this.formAction = '{{ route('events.store') }}';
                    this.formData = {
                        name: '',
                        key: '',
                        type: '',
                        start_date: '',
                        end_date: '',
                        description: '',
                        is_active: true
                    };
                    this.slideOverOpen = true;
                },

                openEdit(event) {
                    this.isEditing = true;
                    // Construct update URL manually or use a named route pattern if possible, 
                    // but since we are in JS, string concatenation is easiest for now.
                    this.formAction = '/events/' + event.id;

                    // Format dates for input[type=date] (YYYY-MM-DD)
                    let startDate = event.start_date ? event.start_date.split('T')[0] : '';
                    let endDate = event.end_date ? event.end_date.split('T')[0] : '';

                    this.formData = {
                        name: event.name,
                        key: event.key,
                        type: event.type,
                        start_date: startDate,
                        end_date: endDate,
                        description: event.description,
                        is_active: !!event.is_active
                    };
                    this.slideOverOpen = true;
                }
            }
        }
    </script>
</x-app-layout>