<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Editando: {{ $documentConfiguration->document_name }}
            </h2>
            <div class="flex gap-2">
                <button onclick="editor_help_modal.showModal()" class="btn btn-circle btn-ghost btn-sm"
                    title="Ayuda del Editor">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                </button>
                <a href="{{ route('document-configurations.index') }}" class="btn btn-outline btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
                <button @click="document.getElementById('config-form').submit()" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Full Screen Editor Layout -->
    <div class="flex flex-col h-[calc(100vh-65px)]"
        x-data="editor('{{ route('document-configurations.preview', $documentConfiguration) }}')">

        <!-- Top Bar: Sample Variables -->
        <div class="bg-base-100 border-b border-base-300 p-2 flex gap-4 items-center flex-wrap shrink-0 min-h-12">
            <div class="text-xs font-bold uppercase tracking-wider opacity-50 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                </svg>
                Variables:
            </div>
            <div class="flex gap-2 items-center flex-wrap">
                <template x-for="(value, key) in sampleData" :key="key">
                    <div class="join shadow-sm tooltip tooltip-bottom"
                        :data-tip="key === 'folio' ? 'Formato: [ClaveEvento]-[Año]-[Folio]' : 'Usa: {' + key + '}'">
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
            <div style="width: 70%;"
                class="shrink-0 bg-gray-100 dark:bg-gray-900 p-4 flex flex-col border-r border-base-300 relative">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-sm uppercase tracking-wide opacity-70">Vista Previa</h3>
                    <button @click="refreshPreview(true)" class="btn btn-xs btn-ghost gap-1">
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
            <div style="width: 30%;" class="shrink-0 bg-base-100 flex flex-col border-l border-base-200">
                <!-- Tabs with Scroll Controls -->
                <div x-data="{
                        showLeftArrow: false,
                        showRightArrow: false,
                        init() {
                            this.$nextTick(() => this.checkScroll());
                        },
                        checkScroll() {
                            const el = this.$refs.tabsContainer;
                            this.showLeftArrow = el.scrollLeft > 0;
                            this.showRightArrow = el.scrollLeft < (el.scrollWidth - el.clientWidth - 1);
                        },
                        scrollTabs(offset) {
                            this.$refs.tabsContainer.scrollBy({ left: offset, behavior: 'smooth' });
                        }
                    }" class="relative border-b border-base-200 bg-base-50 shrink-0 group">

                    <!-- Left Arrow -->
                    <div x-show="showLeftArrow" x-transition.opacity
                        class="absolute left-0 top-0 bottom-0 flex items-center bg-gradient-to-r from-base-50 via-base-50 to-transparent z-10 pl-1 pr-4">
                        <button type="button" @click="scrollTabs(-100)"
                            class="btn btn-xs btn-circle btn-ghost min-h-0 h-6 w-6 bg-base-100 shadow-sm border border-base-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Right Arrow -->
                    <div x-show="showRightArrow" x-transition.opacity
                        class="absolute right-0 top-0 bottom-0 flex items-center bg-gradient-to-l from-base-50 via-base-50 to-transparent z-10 pr-1 pl-4">
                        <button type="button" @click="scrollTabs(100)"
                            class="btn btn-xs btn-circle btn-ghost min-h-0 h-6 w-6 bg-base-100 shadow-sm border border-base-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Scrollable Container -->
                    <div x-ref="tabsContainer" @scroll.debounce.50ms="checkScroll()" @resize.window="checkScroll()"
                        class="flex overflow-x-auto whitespace-nowrap no-scrollbar relative">
                        <button type="button" @click="activeSection = 'basic'"
                            :class="activeSection === 'basic' ? 'border-primary text-primary bg-base-100' : 'border-transparent hover:bg-base-200'"
                            class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-colors shrink-0">Básica</button>
                        <button type="button" @click="activeSection = 'page'"
                            :class="activeSection === 'page' ? 'border-primary text-primary bg-base-100' : 'border-transparent hover:bg-base-200'"
                            class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-colors shrink-0">Página</button>
                        <button type="button" @click="activeSection = 'background'"
                            :class="activeSection === 'background' ? 'border-primary text-primary bg-base-100' : 'border-transparent hover:bg-base-200'"
                            class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-colors shrink-0">Fondo</button>
                        <button type="button" @click="activeSection = 'options'"
                            :class="activeSection === 'options' ? 'border-primary text-primary bg-base-100' : 'border-transparent hover:bg-base-200'"
                            class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-colors shrink-0">Opciones</button>
                        <button type="button" @click="activeSection = 'elements'"
                            :class="activeSection === 'elements' ? 'border-primary text-primary bg-base-100' : 'border-transparent hover:bg-base-200'"
                            class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-colors shrink-0">Elementos</button>
                        <!-- Spacer for right arrow -->
                        <div class="w-4 shrink-0"></div>
                    </div>
                </div>

                <form id="config-form" method="POST"
                    action="{{ route('document-configurations.update', $documentConfiguration) }}"
                    enctype="multipart/form-data" class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="text_elements" x-model="JSON.stringify(textElements)">

                    <!-- 1. Información Básica -->
                    <div x-show="activeSection === 'basic'" class="space-y-2">
                        <div class="text-xs font-bold uppercase tracking-wider mb-2 opacity-50 flex justify-between items-center">
                            Información Básica
                            <div class="form-control">
                                <label class="label cursor-pointer py-0 gap-2">
                                    <span class="label-text text-[10px]">Vista Previa en Vivo</span>
                                    <input type="checkbox" name="enable_live_preview" class="toggle toggle-xs toggle-primary"
                                        value="1"
                                        {{ old('enable_live_preview', $documentConfiguration->enable_live_preview ?? true) ? 'checked' : '' }}
                                        x-model="enableLivePreview" />
                                </label>
                            </div>
                        </div>

                        <div class="form-control w-full mb-2">
                            <label class="label py-0 mb-1"><span class="label-text text-xs font-semibold">Evento
                                    (Opcional)</span></label>
                            <select name="event_id" class="select select-bordered select-sm w-full text-xs"
                                @change="refreshPreview()">
                                <option value="">-- Sin Evento (Genérico) --</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ $documentConfiguration->event_id == $event->id ? 'selected' : '' }}>
                                        {{ $event->name }} ({{ $event->key }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

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

                        <div class="divider my-1 text-[10px] font-bold opacity-50">Folio</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="form-control">
                                <label class="label py-0"><span
                                        class="label-text text-[9px] opacity-70">Inicio</span></label>
                                <input type="number" name="folio_start"
                                    value="{{ old('folio_start', $documentConfiguration->folio_start ?? 1) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" min="1"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span
                                        class="label-text text-[9px] opacity-70">Dígitos</span></label>
                                <input type="number" name="folio_digits"
                                    value="{{ old('folio_digits', $documentConfiguration->folio_digits ?? 4) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" min="1" max="20"
                                    @change="refreshPreview()" />
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label cursor-pointer gap-2 py-0 justify-start">
                                <input type="checkbox" name="folio_year_prefix" value="1"
                                    class="checkbox checkbox-xs checkbox-primary" {{ old('folio_year_prefix', $documentConfiguration->folio_year_prefix) ? 'checked' : '' }}
                                    @change="refreshPreview()" />
                                <span class="label-text text-[10px]">Prefijo Año (Ej. {{ date('Y') }}-0001)</span>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Configuración de Página -->
                    <div x-show="activeSection === 'page'" class="space-y-2">
                        <div class="text-xs font-bold uppercase tracking-wider mb-2 opacity-50">Configuración de Página
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="form-control w-full">
                                <label class="label py-0 mb-1"><span
                                        class="label-text text-[10px] font-semibold">Orientación</span></label>
                                <select name="page_orientation" class="select select-bordered select-sm w-full text-xs"
                                    x-model="pageOrientation" @change="updateBackgroundDimensions(); refreshPreview()">
                                    <option value="P">Vertical</option>
                                    <option value="L">Horizontal</option>
                                </select>
                            </div>
                            <div class="form-control w-full">
                                <label class="label py-0 mb-1"><span
                                        class="label-text text-[10px] font-semibold">Tamaño</span></label>
                                <select name="page_size" class="select select-bordered select-sm w-full text-xs"
                                    x-model="pageSize" @change="updateBackgroundDimensions(); refreshPreview()">
                                    <option value="Letter">Carta</option>
                                    <option value="A4">A4</option>
                                    <option value="Legal">Oficio</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Imagen de Fondo -->
                    <div x-show="activeSection === 'background'" class="space-y-2">
                        <div class="text-xs font-bold uppercase tracking-wider mb-2 opacity-50">Imagen de Fondo</div>
                        <div class="form-control w-full mb-2">
                            <input type="file" name="background_image"
                                class="file-input file-input-bordered file-input-xs w-full text-[10px]" accept="image/*"
                                @change="refreshPreview()" />
                            @if($documentConfiguration->background_image)
                                @php
                                    $bgUrl = '';
                                    if (Str::startsWith($documentConfiguration->background_image, ['http://', 'https://'])) {
                                        $bgUrl = $documentConfiguration->background_image;
                                    } else {
                                        $bgUrl = route('document-configurations.background-image', $documentConfiguration);
                                    }
                                @endphp
                                <div class="mt-2 flex items-center gap-3 p-2 border border-base-300 rounded-lg bg-base-50">
                                    <div
                                        class="h-12 w-12 shrink-0 overflow-hidden rounded border border-base-200 bg-base-200">
                                        <img src="{{ $bgUrl }}" alt="Fondo" class="h-full w-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] font-semibold truncate"
                                            title="{{ basename($documentConfiguration->background_image) }}">
                                            {{ basename($documentConfiguration->background_image) }}
                                        </div>
                                        <div class="text-[9px] opacity-60">Imagen actual</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="form-control mb-2">
                            <label class="label cursor-pointer gap-2 py-0 justify-start">
                                <input type="checkbox" name="background_fit" value="1"
                                    class="checkbox checkbox-xs checkbox-primary" x-model="backgroundFit"
                                    @change="updateBackgroundDimensions(); refreshPreview()" />
                                <span class="label-text text-[10px]">Ajustar al documento</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">X
                                        (mm)</span></label>
                                <input type="number" name="background_x"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="backgroundX" :disabled="backgroundFit"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Y
                                        (mm)</span></label>
                                <input type="number" name="background_y"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="backgroundY" :disabled="backgroundFit"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Ancho
                                        (mm)</span></label>
                                <input type="number" name="background_width"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="backgroundWidth" :disabled="backgroundFit"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Alto
                                        (mm)</span></label>
                                <input type="number" name="background_height"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="backgroundHeight" :disabled="backgroundFit"
                                    @change="refreshPreview()" />
                            </div>
                        </div>
                    </div>



                    <!-- 5. Opciones -->
                    <div x-show="activeSection === 'options'" class="space-y-2">
                        <div class="text-xs font-bold uppercase tracking-wider mb-2 opacity-50">Opciones</div>
                        <div class="flex justify-between px-1 mb-2">
                            <div class="form-control">
                                <label class="label cursor-pointer gap-2 py-0">
                                    <span class="label-text text-[10px] font-semibold">Mostrar QR</span>
                                    <input type="checkbox" name="show_qr" value="1"
                                        class="toggle toggle-primary toggle-xs focus:outline-none" x-model="showQr"
                                        @change="refreshPreview()" />
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label cursor-pointer gap-2 py-0">
                                    <span class="label-text text-[10px] font-semibold">Activo</span>
                                    <input type="checkbox" name="is_active" value="1"
                                        class="toggle toggle-success toggle-xs focus:outline-none" {{ old('is_active', $documentConfiguration->is_active) ? 'checked' : '' }} />
                                </label>
                            </div>
                        </div>

                        <!-- QR Configuration Fields -->
                        <div x-show="document.querySelector('[name=show_qr]').checked"
                            class="grid grid-cols-2 gap-2 border-t border-base-200 pt-2">
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">QR X
                                        (mm)</span></label>
                                <input type="number" name="qr_x"
                                    value="{{ old('qr_x', $documentConfiguration->qr_x ?? 0) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">QR Y
                                        (mm)</span></label>
                                <input type="number" name="qr_y"
                                    value="{{ old('qr_y', $documentConfiguration->qr_y ?? 0) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">QR Ancho
                                        (mm)</span></label>
                                <input type="number" name="qr_width"
                                    value="{{ old('qr_width', $documentConfiguration->qr_width ?? 20) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">QR Alto
                                        (mm)</span></label>
                                <input type="number" name="qr_height"
                                    value="{{ old('qr_height', $documentConfiguration->qr_height ?? 20) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    @change="refreshPreview()" />
                            </div>
                        </div>

                        <!-- Folio Configuration -->
                        <div class="flex justify-between px-1 mb-2 mt-4 border-t border-base-200 pt-2">
                            <div class="form-control">
                                <label class="label cursor-pointer gap-2 py-0">
                                    <span class="label-text text-[10px] font-semibold">Mostrar Folio Fijo</span>
                                    <input type="checkbox" name="show_folio" value="1"
                                        class="toggle toggle-primary toggle-xs focus:outline-none" x-model="showFolio"
                                        @change="refreshPreview()" />
                                </label>
                            </div>
                        </div>

                        <div x-show="showFolio" class="grid grid-cols-2 gap-2 border-t border-base-200 pt-2">
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Folio X
                                        (mm)</span></label>
                                <input type="number" name="folio_x"
                                    value="{{ old('folio_x', $documentConfiguration->folio_x ?? 10) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Folio Y
                                        (mm)</span></label>
                                <input type="number" name="folio_y"
                                    value="{{ old('folio_y', $documentConfiguration->folio_y ?? 10) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Ancho
                                        (mm)</span></label>
                                <input type="number" name="folio_width"
                                    value="{{ old('folio_width', $documentConfiguration->folio_width ?? 50) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Alto
                                        (mm)</span></label>
                                <input type="number" name="folio_height"
                                    value="{{ old('folio_height', $documentConfiguration->folio_height ?? 10) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Tamaño
                                        Fuente</span></label>
                                <input type="number" name="folio_font_size"
                                    value="{{ old('folio_font_size', $documentConfiguration->folio_font_size ?? 12) }}"
                                    class="input input-bordered input-xs w-full text-[10px]"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span
                                        class="label-text text-[9px] opacity-70">Color</span></label>
                                <input type="color" name="folio_color"
                                    value="{{ old('folio_color', $documentConfiguration->folio_color ?? '#000000') }}"
                                    class="input input-bordered input-xs w-full h-5 px-1" @change="refreshPreview()" />
                            </div>
                            <div class="form-control col-span-2">
                                <label class="label py-0"><span
                                        class="label-text text-[9px] opacity-70">Alineación</span></label>
                                <select name="folio_alignment"
                                    class="select select-bordered select-xs w-full text-[10px] px-1 h-7 min-h-0"
                                    @change="refreshPreview()">
                                    <option value="L" {{ (old('folio_alignment', $documentConfiguration->folio_alignment) == 'L') ? 'selected' : '' }}>Izquierda
                                    </option>
                                    <option value="C" {{ (old('folio_alignment', $documentConfiguration->folio_alignment) == 'C') ? 'selected' : '' }}>Centro
                                    </option>
                                    <option value="R" {{ (old('folio_alignment', $documentConfiguration->folio_alignment) == 'R') ? 'selected' : '' }}>Derecha
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>



                    <!-- 6. Elementos de Texto (Dynamic) -->
                    <div x-show="activeSection === 'elements'" class="space-y-2">
                        <div class="text-xs font-bold uppercase tracking-wider mb-2 opacity-50">Elementos de Texto</div>
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
                                            <label class="label py-0">
                                                <span class="label-text text-[9px] opacity-70">Texto</span>
                                            </label>
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
                                            <label class="label py-0"><span class="label-text text-[9px] opacity-70">BG
                                                    Color</span></label>
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

                                        <!-- New Formatting Controls -->
                                        <div class="form-control">
                                            <label class="label py-0"><span
                                                    class="label-text text-[9px] opacity-70">Font</span></label>
                                            <select x-model="element.font_family"
                                                class="select select-bordered select-xs w-full text-[10px] px-1 h-7 min-h-0"
                                                @change="refreshPreview()">
                                                <option value="Arial">Arial</option>
                                                <option value="Times">Times</option>
                                                <option value="Courier">Courier</option>
                                            </select>
                                        </div>
                                        <div class="form-control justify-end pb-1">
                                            <label class="label cursor-pointer py-0 gap-1 justify-start">
                                                <input type="checkbox" x-model="element.uppercase"
                                                    class="checkbox checkbox-xs checkbox-primary rounded-sm"
                                                    @change="refreshPreview()" />
                                                <span class="label-text text-[9px] opacity-70">UPPERCASE</span>
                                            </label>
                                        </div>
                                        <div class="form-control col-span-2">
                                            <label class="label py-0"><span
                                                    class="label-text text-[9px] opacity-70">Auto Width
                                                    (%)</span></label>
                                            <input type="number" x-model="element.auto_width_percent"
                                                class="input input-bordered input-xs w-full h-5 text-[10px] px-1"
                                                min="0" max="100" step="1" @change="refreshPreview()"
                                                placeholder="0 = Disabled" />
                                        </div>
                                    </div>
                            </template>

                            <button type="button" @click="addElement()"
                                class="btn btn-outline btn-primary btn-xs w-full border-dashed h-6 min-h-0 text-[10px]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Agregar
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <!-- Toast Container -->
        <div class="toast toast-end toast-bottom z-50">
            <template x-for="notification in notifications" :key="notification.id">
                <div class="alert shadow-lg text-xs py-2 px-3 min-h-0" :class="{
                        'alert-info': notification.type === 'info',
                        'alert-success': notification.type === 'success',
                        'alert-error': notification.type === 'error',
                        'alert-warning': notification.type === 'warning'
                    }">
                    <span x-text="notification.message"></span>
                </div>
            </template>
        </div>

        <script>
            function editor(previewUrl) {
                return {
                    loading: false,
                    activeSection: 'basic',
                    textElements: @json($documentConfiguration->text_elements ?? []),
                    sampleData: @json($documentConfiguration->sample_data ?? ['nombre' => 'Juan Pérez']),
                    showQr: {{ $documentConfiguration->show_qr ? 'true' : 'false' }},
                    showFolio: {{ $documentConfiguration->show_folio ? 'true' : 'false' }},
                    enableLivePreview: {{ $documentConfiguration->enable_live_preview ?? true ? 'true' : 'false' }},
                    
                    // Page Settings
                    pageOrientation: '{{ $documentConfiguration->page_orientation ?? "P" }}',
                    pageSize: '{{ $documentConfiguration->page_size ?? "Letter" }}',
                    
                    // Background Settings
                    backgroundFit: {{ $documentConfiguration->background_fit ? 'true' : 'false' }},
                    backgroundX: {{ $documentConfiguration->background_x ?? 0 }},
                    backgroundY: {{ $documentConfiguration->background_y ?? 0 }},
                    backgroundWidth: {{ $documentConfiguration->background_width ?? 215.9 }},
                    backgroundHeight: {{ $documentConfiguration->background_height ?? 279.4 }},

                    activeElement: null,
                    previewUrl: previewUrl,
                    newVarKey: '',

                    notifications: [],

                    init() {
                        this.$nextTick(() => {
                            this.refreshPreview(true);
                        });
                    },

                    showNotification(message, type = 'info') {
                        const id = Date.now();
                        this.notifications.push({ id, message, type });
                        setTimeout(() => {
                            this.notifications = this.notifications.filter(n => n.id !== id);
                        }, 3000);
                    },

                    updateBackgroundDimensions() {
                        if (!this.backgroundFit) return;

                        let width, height;

                        // Dimensions in mm
                        switch (this.pageSize) {
                            case 'Letter':
                                width = 215.9;
                                height = 279.4;
                                break;
                            case 'A4':
                                width = 210.0;
                                height = 297.0;
                                break;
                            case 'Legal': // Oficio
                                width = 215.9;
                                height = 355.6;
                                break;
                            default:
                                width = 215.9;
                                height = 279.4;
                        }

                        if (this.pageOrientation === 'L') {
                            // Swap for Landscape
                            const temp = width;
                            width = height;
                            height = temp;
                        }

                        this.backgroundX = 0;
                        this.backgroundY = 0;
                        this.backgroundWidth = width.toFixed(1);
                        this.backgroundHeight = height.toFixed(1);
                    },

                    addVariable() {
                        if (this.newVarKey && !this.sampleData.hasOwnProperty(this.newVarKey)) {
                            this.sampleData[this.newVarKey] = 'Valor de ejemplo';
                            this.newVarKey = '';
                            this.refreshPreview();
                            this.showNotification('Variable agregada correctamente', 'success');
                        } else if (this.sampleData.hasOwnProperty(this.newVarKey)) {
                            this.showNotification('La variable "' + this.newVarKey + '" ya existe', 'error');
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
                        this.showNotification('Elemento de texto agregado', 'success');
                    },

                    removeElement(index) {
                        this.textElements.splice(index, 1);
                        this.refreshPreview();
                        this.showNotification('Elemento eliminado', 'info');
                    },

                    async refreshPreview(force = false) {
                        if (!this.enableLivePreview && !force) return;
                        
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
                                this.showNotification('Error al generar la vista previa', 'error');
                            }
                        } catch (error) {
                            console.error('Error fetching preview:', error);
                            this.showNotification('Error de conexión', 'error');
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>


        <style>
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>

        <!-- Editor Help Modal -->
        <!-- Editor Help Modal -->
        <dialog id="editor_help_modal" class="modal">
            <div class="modal-box w-11/12 max-w-4xl h-[600px] p-0 bg-base-100 overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="bg-base-200/50 p-4 border-b border-base-200 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-lg flex items-center gap-2 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    Guía del Editor
                </h3>
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost">✕</button>
                </form>
            </div>

            <!-- Content -->
            <div class="flex flex-1 overflow-hidden" x-data="{ activeTab: 'general' }">
                
                <!-- Sidebar -->
                <div class="w-48 bg-base-200/30 border-r border-base-200 shrink-0 overflow-y-auto">
                    <ul class="menu menu-sm p-2 gap-1">
                        <li><a @click="activeTab = 'general'" :class="{ 'active': activeTab === 'general' }">General</a></li>
                        <li><a @click="activeTab = 'variables'" :class="{ 'active': activeTab === 'variables' }">Variables</a></li>
                        <li><a @click="activeTab = 'formatting'" :class="{ 'active': activeTab === 'formatting' }">Formato de Texto</a></li>
                        <li><a @click="activeTab = 'config'" :class="{ 'active': activeTab === 'config' }">Configuración</a></li>
                        <li><a @click="activeTab = 'tips'" :class="{ 'active': activeTab === 'tips' }">Tips Pro</a></li>
                    </ul>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 overflow-y-auto p-6">
                    
                    <!-- Tab: General -->
                    <div x-show="activeTab === 'general'" class="space-y-4 animate-fade-in">
                        <h4 class="font-bold text-xl mb-4">Bienvenido al Editor</h4>
                        <p class="text-sm opacity-80">Este editor te permite diseñar constancias profesionales con precisión. Aquí tienes un resumen rápido de las áreas principales:</p>
                        
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="p-3 border border-base-200 rounded-lg">
                                <div class="font-bold text-xs uppercase opacity-50 mb-1">Área Central</div>
                                <p class="text-xs">Vista previa en tiempo real de tu documento. Lo que ves es lo que obtienes.</p>
                            </div>
                            <div class="p-3 border border-base-200 rounded-lg">
                                <div class="font-bold text-xs uppercase opacity-50 mb-1">Panel Derecho</div>
                                <p class="text-xs">Controles para editar propiedades, agregar elementos y configurar la página.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Variables -->
                    <div x-show="activeTab === 'variables'" class="space-y-4 animate-fade-in" style="display: none;">
                        <h4 class="font-bold text-xl mb-4">Variables Dinámicas</h4>
                        <p class="text-sm opacity-80 mb-4">Las variables son marcadores que se reemplazan automáticamente con la información de cada asistente.</p>
                        
                        <div class="space-y-2">
                            <div class="flex items-center gap-4 p-3 bg-base-100 border border-base-200 rounded-lg">
                                <code class="kbd kbd-sm">{nombre}</code>
                                <span class="text-sm">Nombre completo del asistente.</span>
                            </div>
                            <div class="flex items-center gap-4 p-3 bg-base-100 border border-base-200 rounded-lg">
                                <code class="kbd kbd-sm">{folio}</code>
                                <span class="text-sm">Folio único generado por el sistema.</span>
                            </div>
                            <div class="flex items-center gap-4 p-3 bg-base-100 border border-base-200 rounded-lg">
                                <code class="kbd kbd-sm">{evento}</code>
                                <span class="text-sm">Nombre del evento actual.</span>
                            </div>
                            <div class="flex items-center gap-4 p-3 bg-base-100 border border-base-200 rounded-lg">
                                <code class="kbd kbd-sm">{fecha}</code>
                                <span class="text-sm">Fecha de emisión.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Formatting -->
                    <div x-show="activeTab === 'formatting'" class="space-y-4 animate-fade-in" style="display: none;">
                        <h4 class="font-bold text-xl mb-4">Formato de Texto</h4>
                        <p class="text-sm opacity-80 mb-4">Puedes dar estilo a partes específicas de tu texto usando símbolos simples:</p>
                        
                        <div class="overflow-x-auto border border-base-200 rounded-lg">
                            <table class="table">
                                <thead class="bg-base-200/50">
                                    <tr>
                                        <th>Estilo</th>
                                        <th>Sintaxis</th>
                                        <th>Ejemplo</th>
                                        <th>Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-bold">Negrita</td>
                                        <td><code class="bg-base-200 px-1 rounded">*texto*</code></td>
                                        <td>Hola *Mundo*</td>
                                        <td>Hola <strong>Mundo</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="font-bold">Cursiva</td>
                                        <td><code class="bg-base-200 px-1 rounded">%texto%</code></td>
                                        <td>Hola %Mundo%</td>
                                        <td>Hola <em>Mundo</em></td>
                                    </tr>
                                    <tr>
                                        <td class="font-bold">Subrayado</td>
                                        <td><code class="bg-base-200 px-1 rounded">&texto&</code></td>
                                        <td>Hola &Mundo&</td>
                                        <td>Hola <span class="underline">Mundo</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info text-xs mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Puedes combinar estilos, por ejemplo: <code>*Negrita y %Cursiva%*</code></span>
                        </div>
                    </div>

                    <!-- Tab: Configuration -->
                    <div x-show="activeTab === 'config'" class="space-y-4 animate-fade-in" style="display: none;">
                        <h4 class="font-bold text-xl mb-4">Configuración Avanzada</h4>
                        
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100 rounded-box">
                            <input type="radio" name="my-accordion-2" checked="checked" /> 
                            <div class="collapse-title text-sm font-medium">
                                Folio y QR
                            </div>
                            <div class="collapse-content text-xs opacity-80"> 
                                <p>En la pestaña <strong>Opciones</strong> puedes activar el Folio Fijo y el Código QR. Estos elementos son especiales y tienen sus propios controles de posición.</p>
                            </div>
                        </div>
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100 rounded-box mt-2">
                            <input type="radio" name="my-accordion-2" /> 
                            <div class="collapse-title text-sm font-medium">
                                Imagen de Fondo
                            </div>
                            <div class="collapse-content text-xs opacity-80"> 
                                <p>Sube una imagen (JPG/PNG) en la pestaña <strong>Fondo</strong>. Usa la opción "Ajustar" para cubrir toda la página, ideal para diseños pre-impresos.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Tips -->
                    <div x-show="activeTab === 'tips'" class="space-y-4 animate-fade-in" style="display: none;">
                        <h4 class="font-bold text-xl mb-4">Tips Profesionales</h4>
                        
                        <ul class="timeline timeline-vertical timeline-compact timeline-snap-icon">
                            <li>
                                <div class="timeline-middle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-primary"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="timeline-end mb-4">
                                    <div class="font-bold text-sm">Coordenadas Precisas</div>
                                    <div class="text-xs opacity-70">Usa los campos numéricos X e Y para alinear elementos perfectamente. El punto (0,0) es la esquina superior izquierda.</div>
                                </div>
                                <hr class="bg-primary"/>
                            </li>
                            <li>
                                <hr class="bg-primary"/>
                                <div class="timeline-middle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-primary"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="timeline-end mb-4">
                                    <div class="font-bold text-sm">Copiar Variables</div>
                                    <div class="text-xs opacity-70">Haz clic en las variables de la barra superior para copiarlas al portapapeles instantáneamente.</div>
                                </div>
                                <hr class="bg-primary"/>
                            </li>
                            <li>
                                <hr class="bg-primary"/>
                                <div class="timeline-middle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-primary"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="timeline-end">
                                    <div class="font-bold text-sm">Rendimiento</div>
                                    <div class="text-xs opacity-70">Si sientes el editor lento, desactiva "Vista Previa en Vivo" en la pestaña Básica.</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </dialog>
</x-app-layout>