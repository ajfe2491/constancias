<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nueva Configuración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-base-100 overflow-hidden shadow-sm sm:rounded-lg border border-base-200 p-6">
                <form action="{{ route('document-configurations.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Nombre del Documento</span>
                        </label>
                        <input type="text" name="document_name" class="input input-bordered w-full" required
                            placeholder="Ej. Constancia General" />
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Tipo de Documento</span>
                        </label>
                        <select name="document_type" class="select select-bordered w-full">
                            <option value="constancia">Constancia</option>
                            <option value="gafete">Gafete</option>
                            <option value="carta">Carta</option>
                        </select>
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold">Descripción</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered h-24"
                            placeholder="Descripción breve..."></textarea>
                    </div>

                    <div class="divider">Configuración de Folio</div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Folio Inicial</span>
                            </label>
                            <input type="number" name="folio_start" class="input input-bordered w-full" value="1"
                                min="1" required />
                        </div>
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Dígitos</span>
                            </label>
                            <input type="number" name="folio_digits" class="input input-bordered w-full" value="4"
                                min="1" max="20" required />
                        </div>
                        <div class="form-control w-full">
                            <label class="label cursor-pointer">
                                <span class="label-text font-bold">Prefijo Año (Ej. 2025-)</span>
                                <input type="checkbox" name="folio_year_prefix" class="checkbox checkbox-primary" />
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('document-configurations.index') }}" class="btn btn-ghost">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Crear y Personalizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>