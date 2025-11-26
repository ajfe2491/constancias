<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Editando: {{ $documentConfiguration->document_name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('document-configurations.index') }}" class="btn btn-ghost btn-sm">Volver</a>
                <button @click="document.getElementById('config-form').submit()" class="btn btn-primary btn-sm">Guardar
                    Cambios</button>
            </div>
        </div>
    </x-slot>

    <!-- Full Screen Editor Layout -->
    <div class="flex flex-col h-[calc(100vh-65px)]"
        x-data="editor('{{ route('document-configurations.preview', $documentConfiguration) }}')">

        <!-- Top Bar: Sample Variables -->
        <div
            class="bg-base-100 border-b border-base-300 p-2 flex gap-4 items-center overflow-x-auto whitespace-nowrap shrink-0 h-12">
            <div class="text-xs font-bold uppercase tracking-wider opacity-50 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                </svg>
                Variables:
            </div>
            <div class="flex gap-2 items-center">
                <template x-for="(value, key) in sampleData" :key="key">
                    <div class="join shadow-sm tooltip tooltip-bottom" :data-tip="'Usa: {' + key + '}'">
                        <button type="button"
                            @click="navigator.clipboard.writeText('{' + key + '}'); $event.target.classList.add('btn-success'); setTimeout(() => $event.target.classList.remove('btn-success'), 500)"
                            class="join-item btn btn-xs btn-ghost font-mono text-[10px] px-1 bg-base-200 border-base-300 hover:bg-base-300"
                            :title="'Click para copiar {' + key + '}'">
                            <span x-text="key"></span>
                        </button>
                        <input type="text" x-model="sampleData[key]"
                            class="join-item input input-bordered input-xs text-[10px] w-24 focus:w-40 transition-all"
                            @change="refreshPreview()" :name="'sample_data[' + key + ']'" />
                        <button type="button" @click="delete sampleData[key]; refreshPreview()"
                            class="join-item btn btn-xs btn-ghost text-error px-1">
                            &times;
                        </button>
                    </div>
                </template>

                <div class="join">
                    <input type="text" x-model="newVarKey" placeholder="Nueva..."
                        class="join-item input input-bordered input-xs text-[10px] w-20"
                        @keydown.enter.prevent="addVariable()" />
                    <button type="button" @click="addVariable()" class="join-item btn btn-primary btn-xs text-[10px]">+
                        Add</button>
                </div>

                <!-- Hint -->
                <div class="badge badge-info badge-sm gap-1 ml-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[9px]">Click en variable para copiar</span>
                </div>
            </div>
        </div>

        <div class="flex flex-1 overflow-hidden">

            <!-- Left Pane: Preview -->
            <div
                class="w-3/4 shrink-0 bg-gray-100 dark:bg-gray-900 p-4 flex flex-col border-r border-base-300 relative">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-sm uppercase tracking-wide opacity-70">Vista Previa</h3>
                    <button @click="refreshPreview()" class="btn btn-xs btn-ghost gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refrescar
                    </button>
                </div>
                <div
                    class="flex-1 bg-base-200 rounded-lg shadow-inner overflow-hidden relative flex items-center justify-center">
                    <div x-show="loading"
                        class="absolute inset-0 bg-base-100/50 flex items-center justify-center z-10 backdrop-blur-sm">
                        <span class="loading loading-spinner loading-lg text-primary"></span>
                    </div>
                    <iframe x-ref="previewFrame" class="w-full h-full border-none" src="about:blank"></iframe>
                </div>
            </div>

            <!-- Right Pane: Configuration -->
            <div class="w-1/4 shrink-0 bg-base-100 overflow-y-auto custom-scrollbar border-l border-base-200">
                <form id="config-form" method="POST"
                    action="{{ route('document-configurations.update', $documentConfiguration) }}"
                    enctype="multipart/form-data" class="p-3 space-y-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="text_elements" x-model="JSON.stringify(textElements)">

                    <!-- 1. Información Básica -->
                    <div class="collapse collapse-arrow bg-base-100 border border-base-200 rounded-md mb-1"
                        :class="{ 'collapse-open': activeSection === 'basic' }">
                        <div class="collapse-title text-xs font-bold uppercase tracking-wider flex items-center gap-2 cursor-pointer min-h-0 py-2 bg-base-50"
                            @click="activeSection = (activeSection === 'basic' ? null : 'basic')">
                            Información Básica
                        </div>
                        <div class="collapse-content text-xs p-2 pb-4">
                            <div class="form-control w-full mb-2">
                                <label class="label py-0 mb-1"><span
                                        class="label-text text-[10px] font-semibold">Nombre</span></label>
                                <input type="text" name="document_name"
                                    value="{{ old('document_name', $documentConfiguration->document_name) }}"
                                    class="input input-bordered w-full input-xs" required />
                            </div>
                            <div class="form-control w-full">
                                <label class="label py-0 mb-1"><span
                                        class="label-text text-[10px] font-semibold">Descripción</span></label>
                                <textarea name="description"
                                    class="textarea textarea-bordered h-12 text-[10px] leading-tight">{{ old('description', $documentConfiguration->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Configuración de Página -->
                    <div class="collapse collapse-arrow bg-base-100 border border-base-200 rounded-md mb-1"
                        :class="{ 'collapse-open': activeSection === 'page' }">
                        <div class="collapse-title text-xs font-bold uppercase tracking-wider flex items-center gap-2 cursor-pointer min-h-0 py-2 bg-base-50"
                            @click="activeSection = (activeSection === 'page' ? null : 'page')">
                            Configuración de Página
                        </div>
                        <div class="collapse-content text-xs p-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="form-control w-full">
                                    <label class="label py-0 mb-1"><span
                                            class="label-text text-[10px] font-semibold">Orientación</span></label>
                                    <select name="page_orientation"
                                        class="select select-bordered select-sm w-full text-xs"
                                        @change="refreshPreview()">
                                        <option value="P" {{ $documentConfiguration->page_orientation == 'P' ? 'selected' : '' }}>Vertical</option>
                                        <option value="L" {{ $documentConfiguration->page_orientation == 'L' ? 'selected' : '' }}>Horizontal</option>
                                    </select>
                                </div>
                                <div class="form-control w-full">
                                    <label class="label py-0 mb-1"><span
                                            class="label-text text-[10px] font-semibold">Tamaño</span></label>
                                    <select name="page_size" class="select select-bordered select-sm w-full text-xs"
                                        @change="refreshPreview()">
                                        <option value="Letter" {{ $documentConfiguration->page_size == 'Letter' ? 'selected' : '' }}>Carta</option>
                                        <option value="A4" {{ $documentConfiguration->page_size == 'A4' ? 'selected' : '' }}>
                                            A4</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Imagen de Fondo -->
                    <div class="collapse collapse-arrow bg-base-100 border border-base-200 rounded-md mb-1"
                        :class="{ 'collapse-open': activeSection === 'background' }">
                        <div class="collapse-title text-xs font-bold uppercase tracking-wider flex items-center gap-2 cursor-pointer min-h-0 py-2 bg-base-50"
                            @click="activeSection = (activeSection === 'background' ? null : 'background')">
                            Imagen de Fondo
                        </div>
                        <div class="collapse-content text-xs p-2 pb-4">
                            <div class="form-control w-full mb-2">
                                <input type="file" name="background_image"
                                    class="file-input file-input-bordered file-input-xs w-full text-[10px]"
                                    accept="image/*" @change="refreshPreview()" />
                                @if($documentConfiguration->background_image)
                                    <div class="mt-1 text-[9px] opacity-60 italic">Imagen actual cargada.</div>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="form-control">
                                    <label class="label py-0"><span class="label-text text-[9px] opacity-70">X
                                            (mm)</span></label>
                                    <input type="number" name="background_x"
                                        value="{{ old('background_x', $documentConfiguration->background_x ?? 0) }}"
                                        class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                        @change="refreshPreview()" />
                                </div>
                                <div class="form-control">
                                    <label class="label py-0"><span class="label-text text-[9px] opacity-70">Y
                                            (mm)</span></label>
                                    <input type="number" name="background_y"
                                        value="{{ old('background_y', $documentConfiguration->background_y ?? 0) }}"
                                        class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                        @change="refreshPreview()" />
                                </div>
                                <div class="form-control">
                                    <label class="label py-0"><span class="label-text text-[9px] opacity-70">Ancho
                                            (mm)</span></label>
                                    <input type="number" name="background_width"
                                        value="{{ old('background_width', $documentConfiguration->background_width ?? 215.9) }}"
                                        class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                        @change="refreshPreview()" />
                                </div>
                                <div class="form-control">
                                    <label class="label py-0"><span class="label-text text-[9px] opacity-70">Alto
                                            (mm)</span></label>
                                    <input type="number" name="background_height"
                                        value="{{ old('background_height', $documentConfiguration->background_height ?? 279.4) }}"
                                        class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                        @change="refreshPreview()" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Opciones -->
                    <div class="collapse collapse-arrow bg-base-100 border border-base-200 rounded-md mb-1"
                        :class="{ 'collapse-open': activeSection === 'options' }">
                        <div class="collapse-title text-xs font-bold uppercase tracking-wider flex items-center gap-2 cursor-pointer min-h-0 py-2 bg-base-50"
                            @click="activeSection = (activeSection === 'options' ? null : 'options')">
                            Opciones
                        </div>
                        <div class="collapse-content text-xs p-2">
                            <div class="flex justify-between px-1">
                                <div class="form-control">
                                    <label class="label cursor-pointer gap-2 py-0">
                                        <span class="label-text text-[10px] font-semibold">Mostrar QR</span>
                                        <input type="checkbox" name="show_qr" class="toggle toggle-primary toggle-xs" {{ $documentConfiguration->show_qr ? 'checked' : '' }}
                                            @change="refreshPreview()" />
                                    </label>
                                </div>
                                <div class="form-control">
                                    <label class="label cursor-pointer gap-2 py-0">
                                        <span class="label-text text-[10px] font-semibold">Activo</span>
                                        <input type="checkbox" name="is_active" class="toggle toggle-success toggle-xs"
                                            {{ $documentConfiguration->is_active ? 'checked' : '' }} />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- 6. Elementos de Texto (Dynamic) -->
                    <div class="collapse collapse-arrow bg-base-100 border border-base-200 rounded-md mb-1"
                        :class="{ 'collapse-open': activeSection === 'elements' }">
                        <div class="collapse-title text-xs font-bold uppercase tracking-wider flex items-center gap-2 cursor-pointer min-h-0 py-2 bg-base-50"
                            @click="activeSection = (activeSection === 'elements' ? null : 'elements')">
                            Elementos de Texto
                        </div>
                        <div class="collapse-content text-xs p-2">
                            <div class="space-y-2">
                                <template x-for="(element, index) in textElements" :key="index">
                                    <div class="border border-base-200 rounded-md p-2 bg-base-50 relative group">
                                        <button type="button" @click="removeElement(index)"
                                            class="btn btn-xs btn-circle btn-error absolute -top-1 -right-1 opacity-0 group-hover:opacity-100 transition-opacity h-4 w-4 min-h-0 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>

                                        <div class="grid grid-cols-2 gap-1 mb-1">
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">Nombre</span></label>
                                                <input type="text" x-model="element.name"
                                                    class="input input-bordered input-xs w-full h-5 text-[10px] px-1"
                                                    placeholder="Nombre" />
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">Texto</span></label>
                                                <input type="text" x-model="element.text"
                                                    class="input input-bordered input-xs w-full h-5 text-[10px] px-1"
                                                    @change="refreshPreview()" />
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-1">
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">X</span></label>
                                                <input type="number" x-model="element.x"
                                                    class="input input-bordered input-xs w-full h-5 text-[10px] px-1"
                                                    step="0.1" @change="refreshPreview()" />
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">Y</span></label>
                                                <input type="number" x-model="element.y"
                                                    class="input input-bordered input-xs w-full h-5 text-[10px] px-1"
                                                    step="0.1" @change="refreshPreview()" />
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">Width</span></label>
                                                <input type="number" x-model="element.width"
                                                    class="input input-bordered input-xs w-full h-5 text-[10px] px-1"
                                                    step="0.1" @change="refreshPreview()" />
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">Height</span></label>
                                                <input type="number" x-model="element.height"
                                                    class="input input-bordered input-xs w-full h-5 text-[10px] px-1"
                                                    step="0.1" @change="refreshPreview()" />
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">Size</span></label>
                                                <input type="number" x-model="element.font_size"
                                                    class="input input-bordered input-xs w-full h-5 text-[10px] px-1"
                                                    @change="refreshPreview()" />
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">Align</span></label>
                                                <select x-model="element.alignment"
                                                    class="select select-bordered select-sm w-full text-xs px-1"
                                                    @change="refreshPreview()">
                                                    <option value="L">L</option>
                                                    <option value="C">C</option>
                                                    <option value="R">R</option>
                                                </select>
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">Color</span></label>
                                                <input type="color" x-model="element.text_color"
                                                    class="input input-bordered input-xs w-full h-5 px-1"
                                                    @change="refreshPreview()" />
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-0"><span
                                                        class="label-text text-[9px] opacity-70">BG Color</span></label>
                                                <input type="color" x-model="element.fill_color"
                                                    class="input input-bordered input-xs w-full h-5 px-1"
                                                    @change="refreshPreview()" />
                                            </div>
                                            <div class="form-control">
                                                <label class="label cursor-pointer py-0 gap-1">
                                                    <span class="label-text text-[9px] opacity-70">Fill</span>
                                                    <input type="checkbox" x-model="element.fill"
                                                        class="toggle toggle-xs toggle-primary"
                                                        @change="refreshPreview()" />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <button type="button" @click="addElement()"
                                    class="btn btn-outline btn-primary btn-xs w-full border-dashed h-6 min-h-0 text-[10px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <script>
            function editor(previewUrl) {
                return {
                    loading: false,
                    activeSection: 'basic',
                    textElements: @json($documentConfiguration->text_elements ?? []),
                    sampleData: @json($documentConfiguration->sample_data ?? ['nombre' => 'Juan Pérez']),
                    previewUrl: previewUrl,
                    newVarKey: '',

                    init() {
                        this.refreshPreview();
                    },

                    addVariable() {
                        if (this.newVarKey && !this.sampleData.hasOwnProperty(this.newVarKey)) {
                            this.$set(this.sampleData, this.newVarKey, 'Valor de ejemplo');
                            this.newVarKey = '';
                            this.refreshPreview();
                        }
                    },

                    addElement() {
                        this.textElements.push({
                            name: 'nuevo_elemento',
                            text: 'Texto de ejemplo',
                            x: 10,
                            y: 10,
                            width: 50,
                            height: 10,
                            font_size: 12,
                            alignment: 'L',
                            text_color: '#000000',
                            fill_color: '#FFFFFF',
                            fill: false
                        });
                        this.refreshPreview();
                    },

                    removeElement(index) {
                        this.textElements.splice(index, 1);
                        this.refreshPreview();
                    },

                    async refreshPreview() {
                        this.loading = true;

                        // Gather form data manually to include dynamic text elements
                        const form = document.getElementById('config-form');
                        const formData = new FormData(form);

                        // Explicitly add text_elements and sample_data as JSON
                        formData.set('text_elements', JSON.stringify(this.textElements));
                        formData.set('sample_data', JSON.stringify(this.sampleData));
                        formData.delete('_method');

                        try {
                            const response = await fetch(this.previewUrl, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            });

                            if (response.ok) {
                                const blob = await response.blob();
                                const url = URL.createObjectURL(blob);
                                this.$refs.previewFrame.src = url;
                            } else {
                                console.error('Preview failed');
                            }
                        } catch (error) {
                            console.error('Error fetching preview:', error);
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
</x-app-layout>