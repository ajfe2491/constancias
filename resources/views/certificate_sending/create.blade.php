<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Envío') }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto" x-data="certificateSender">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('certificate-sending.index') }}" class="btn btn-ghost gap-2 pl-0 hover:bg-transparent">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver al historial
                </a>
                <h1 class="text-2xl font-bold mt-2">Nuevo Envío de Constancias</h1>
                <p class="text-sm opacity-60">Selecciona el tipo de envío y sigue las instrucciones según la constancia.
                </p>
            </div>
            <div class="bg-base-200 rounded-xl px-4 py-3 text-sm">
                <p class="font-semibold">¿Necesitas probar una sola constancia?</p>
                <p class="opacity-70">Ahora puedes enviarla sin CSV y validar los datos en vivo.</p>
            </div>
        </div>

        <div class="flex gap-3 mb-6">
            <button type="button" class="btn" :aria-pressed="mode === 'bulk'" :class="mode === 'bulk' ? 'btn-primary' : 'btn-outline'"
                @click="setMode('bulk')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                Carga masiva
            </button>
            <button type="button" class="btn" :aria-pressed="mode === 'single'" :class="mode === 'single' ? 'btn-primary' : 'btn-outline'"
                @click="setMode('single')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Envío individual
            </button>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div x-show="mode === 'bulk'" x-cloak>
                    <div class="flex items-center w-full mb-8 relative">
                        <div class="absolute top-4 left-0 w-full h-1 bg-gray-200 -z-10"></div>
                        <div class="absolute top-4 left-0 h-1 bg-primary transition-all duration-300 -z-10"
                            :style="'width: ' + (step === 1 ? '50%' : '100%')"></div>

                        <div class="flex-1 text-center">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center text-sm font-bold transition-colors duration-300"
                                :class="step >= 1 ? 'bg-primary text-primary-content' : 'bg-gray-200 text-gray-500'">
                                1
                            </div>
                            <div class="text-sm mt-2 font-medium" :class="step >= 1 ? 'text-primary' : 'text-gray-500'">
                                Configuración</div>
                        </div>

                        <div class="flex-1 text-center">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center text-sm font-bold transition-colors duration-300"
                                :class="step >= 2 ? 'bg-primary text-primary-content' : 'bg-gray-200 text-gray-500'">
                                2
                            </div>
                            <div class="text-sm mt-2 font-medium" :class="step >= 2 ? 'text-primary' : 'text-gray-500'">
                                Cargar Datos</div>
                        </div>
                    </div>

                    <form action="{{ route('certificate-sending.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="mode" value="bulk" />

                        <div x-show="step === 1" class="space-y-6">
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-bold text-lg">1. Selecciona la Configuración del Documento</span>
                                </label>
                                <p class="text-sm opacity-70 mb-4">Elige el tipo de constancia para habilitar su plantilla y variables.</p>

                                <select name="document_configuration_id" id="document_configuration_id" class="select select-bordered w-full"
                                    required x-model="configId">
                                    <option value="" disabled selected>Selecciona una configuración...</option>
                                    @foreach($configurations as $config)
                                        <option value="{{ $config->id }}">
                                            {{ $config->document_name }} ({{ $config->event ? $config->event->name : 'Genérico' }})
                                        </option>
                                    @endforeach
                                </select>

                                <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div class="text-xs opacity-60" x-show="!configId">
                                        Selecciona una configuración para descargar su plantilla.
                                    </div>
                                    <a :href="configId ? templateUrl : '#'" class="btn btn-sm gap-2"
                                        :class="configId ? 'btn-outline' : 'btn-disabled'" :target="configId ? '_blank' : ''">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Descargar Plantilla CSV
                                    </a>
                                </div>
                                @error('document_configuration_id')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <div class="flex flex-wrap gap-2" x-show="configId">
                                <span class="badge badge-outline" x-for="field in placeholders" :key="field" x-text="field"></span>
                            </div>

                            <div class="flex justify-end mt-8">
                                <button type="button" class="btn btn-primary" @click="jumpToStep(2)" :disabled="!configId">
                                    Siguiente
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="step === 2" style="display: none;" class="space-y-6">
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-bold text-lg">2. Sube el archivo de destinatarios (CSV)</span>
                                </label>

                                <div class="alert alert-info shadow-sm mb-6 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        class="stroke-current shrink-0 w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h3 class="font-bold">Instrucciones:</h3>
                                        <ul class="list-disc list-inside mt-1 space-y-1">
                                            <li>Usa la plantilla descargada en el paso anterior.</li>
                                            <li>La columna <strong>email</strong> es obligatoria.</li>
                                            <li>Guarda el archivo como <strong>CSV delimitado por comas</strong>.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="flex items-center justify-center w-full">
                                    <label for="dropzone-file"
                                        class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                </path>
                                            </svg>
                                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Haz clic para
                                                    subir</span> o arrastra y suelta</p>
                                            <p class="text-xs text-gray-500">CSV o TXT (MAX. 10MB)</p>
                                        </div>
                                        <input id="dropzone-file" name="csv_file" type="file" class="hidden" accept=".csv,.txt" required />
                                    </label>
                                </div>
                                <div id="file-name" class="text-center text-sm font-medium text-gray-700 mt-2"></div>

                                <script>
                                    document.getElementById('dropzone-file').addEventListener('change', function (e) {
                                        var fileName = e.target.files[0].name;
                                        document.getElementById('file-name').textContent = 'Archivo seleccionado: ' + fileName;
                                    });
                                </script>

                                @error('csv_file')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" class="btn btn-ghost" @click="jumpToStep(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                    </svg>
                                    Anterior
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Iniciar Envío
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div x-show="mode === 'single'" x-cloak>
                    <div class="alert alert-success mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <span class="font-semibold">Envía una sola constancia</span>
                            <p class="text-sm opacity-70">Completa los campos variables según la configuración seleccionada y recibe retroalimentación inmediata.</p>
                        </div>
                    </div>

                    <form action="{{ route('certificate-sending.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="mode" value="single" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-bold">Configuración de constancia</span>
                                </label>
                                <select name="document_configuration_id" class="select select-bordered w-full" required x-model="configId">
                                    <option value="" disabled selected>Selecciona una configuración...</option>
                                    @foreach($configurations as $config)
                                        <option value="{{ $config->id }}">
                                            {{ $config->document_name }} ({{ $config->event ? $config->event->name : 'Genérico' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('document_configuration_id')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-bold">Correo del destinatario</span>
                                </label>
                                <input type="email" name="email" class="input input-bordered w-full" placeholder="correo@ejemplo.com" required />
                                @error('email')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>

                        <div class="bg-base-200 rounded-xl p-4" x-show="configId">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="font-semibold">Variables detectadas</p>
                                    <p class="text-sm opacity-70">Llena cada campo para personalizar la constancia seleccionada.</p>
                                </div>
                                <div class="flex flex-wrap gap-2" x-show="placeholdersWithoutEmail.length">
                                    <span class="badge badge-outline" x-for="field in placeholdersWithoutEmail" :key="field" x-text="field"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="placeholdersWithoutEmail.length">
                                <template x-for="field in placeholdersWithoutEmail" :key="field">
                                    <label class="form-control w-full">
                                        <div class="label">
                                            <span class="label-text font-medium" x-text="field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                            <span class="badge badge-ghost badge-sm" x-text="'{{ __('Requerido') }}'"></span>
                                        </div>
                                        <input type="text" class="input input-bordered" :name="`data[${field}]`"
                                            :placeholder="`Ingresa ${field}`" :value="selectedConfig?.sample_data?.[field] ?? ''" required />
                                    </label>
                                </template>
                            </div>

                            <div class="text-sm opacity-70" x-show="!placeholdersWithoutEmail.length">
                                No se encontraron variables adicionales para esta constancia.
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary" :disabled="!configId">
                                Enviar constancia
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('certificateSender', () => ({
                mode: 'bulk',
                step: 1,
                configId: '',
                configs: @json($configurationMeta),
                get templateUrl() {
                    return '{{ route('certificate-sending.template', ':id') }}'.replace(':id', this.configId);
                },
                get selectedConfig() {
                    return this.configs.find(c => c.id == this.configId);
                },
                get placeholders() {
                    return this.selectedConfig ? this.selectedConfig.placeholders : [];
                },
                get placeholdersWithoutEmail() {
                    return this.placeholders.filter(f => f !== 'email');
                },
                jumpToStep(step) {
                    if (this.configId) {
                        this.step = step;
                    }
                },
                setMode(mode) {
                    this.mode = mode;
                    this.step = 1;
                },
            }));
        });
    </script>
</x-app-layout>
