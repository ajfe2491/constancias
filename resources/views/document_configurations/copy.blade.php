<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Copiar Configuración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-base-100 overflow-hidden shadow-sm sm:rounded-lg border border-base-200 p-6">
                <form action="{{ route('document-configurations.copy.store', $documentConfiguration) }}" method="POST"
                    class="space-y-6">
                    @csrf

                    <div class="alert alert-info text-sm">
                        Se copiarán los elementos, textos, folio, QR y fondo (si existe). Aquí puedes cambiar el evento
                        y los datos principales.
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Nombre del Documento</span>
                        </label>
                        <input type="text" name="document_name" class="input input-bordered w-full" required
                            value="{{ old('document_name', $documentConfiguration->document_name . ' (Copia)') }}" />
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Evento (Opcional)</span>
                        </label>
                        <select name="event_id" class="select select-bordered w-full">
                            <option value="">-- Sin Evento (Genérico) --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected(old('event_id', $documentConfiguration->event_id) == $event->id)>
                                    {{ $event->name }} ({{ $event->key }})
                                </option>
                            @endforeach
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Puedes cambiar el evento para reutilizar la
                                configuración.</span>
                        </label>
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Tipo de Documento</span>
                        </label>
                        <select name="document_type" class="select select-bordered w-full">
                            @foreach(['constancia' => 'Constancia', 'gafete' => 'Gafete', 'carta' => 'Carta'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('document_type', $documentConfiguration->document_type) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Descripción</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered h-24"
                            placeholder="Descripción breve...">{{ old('description', $documentConfiguration->description) }}</textarea>
                    </div>

                    <div class="divider">Configuración de Folio</div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Folio Inicial</span>
                            </label>
                            <input type="number" name="folio_start" class="input input-bordered w-full" 
                                value="{{ old('folio_start', $documentConfiguration->folio_start ?? 1) }}"
                                min="1" required />
                        </div>
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Dígitos</span>
                            </label>
                            <input type="number" name="folio_digits" class="input input-bordered w-full" 
                                value="{{ old('folio_digits', $documentConfiguration->folio_digits ?? 4) }}"
                                min="1" max="20" required />
                        </div>
                        <div class="form-control w-full">
                            <label class="label cursor-pointer flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="folio_year_prefix" class="checkbox checkbox-primary" 
                                        {{ old('folio_year_prefix', $documentConfiguration->folio_year_prefix) ? 'checked' : '' }}
                                        onchange="document.getElementById('custom_year_container').classList.toggle('hidden', !this.checked)" />
                                    <span class="label-text font-bold">Prefijo Año (Ej. {{ date('Y') }}-)</span>
                                </div>
                                <div id="custom_year_container" class="{{ old('folio_year_prefix', $documentConfiguration->folio_year_prefix) ? '' : 'hidden' }} w-full mt-2">
                                    <input type="text" name="custom_folio_year" class="input input-bordered input-sm w-full" 
                                        value="{{ old('custom_folio_year', $documentConfiguration->custom_folio_year) }}"
                                        placeholder="Año manual (Ej. 2024)" />
                                    <label class="label p-0">
                                        <span class="label-text-alt text-gray-500">Si se deja vacío, se usará el año actual.</span>
                                    </label>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('document-configurations.index') }}" class="btn btn-ghost">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Copiar y Personalizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
