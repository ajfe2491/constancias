<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Evento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-base-100 overflow-hidden shadow-sm sm:rounded-lg border border-base-200 p-6">
                <form action="{{ route('events.update', $event) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Nombre -->
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Nombre del Evento</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $event->name) }}"
                            placeholder="Ej. Congreso Internacional 2024" class="input input-bordered w-full" required />
                        @error('name')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Clave -->
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Clave del Evento</span>
                        </label>
                        <input type="text" name="key" value="{{ old('key', $event->key) }}" placeholder="Ej. CON2024"
                            class="input input-bordered w-full" required maxlength="20" />
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Esta clave se utilizará como prefijo para el folio
                                de las constancias.</span>
                        </label>
                        @error('key')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Tipo -->
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Tipo de Evento</span>
                        </label>
                        <select name="type" class="select select-bordered w-full">
                            <option value="" disabled>Selecciona un tipo</option>
                            @foreach(['Congreso', 'Curso', 'Taller', 'Seminario', 'Simposio', 'Conferencia', 'Diplomado', 'Foro', 'Panel', 'Otro'] as $type)
                                <option value="{{ $type }}" {{ old('type', $event->type) == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Fechas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Inicio</span>
                            </label>
                            <input type="date" name="start_date"
                                value="{{ old('start_date', $event->start_date ? $event->start_date->format('Y-m-d') : '') }}"
                                class="input input-bordered w-full" />
                            @error('start_date')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Fin</span>
                            </label>
                            <input type="date" name="end_date"
                                value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d') : '') }}"
                                class="input input-bordered w-full" />
                            @error('end_date')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Descripción</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered h-24"
                            placeholder="Detalles adicionales...">{{ old('description', $event->description) }}</textarea>
                        @error('description')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Activo -->
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="is_active" class="checkbox checkbox-primary"
                                {{ old('is_active', $event->is_active) ? 'checked' : '' }} />
                            <span class="label-text font-bold">Evento Activo</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                        <a href="{{ route('events.show', $event) }}" class="btn btn-ghost">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Evento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
