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
                <a href="{{ route('document-configurations.email-template.edit', $documentConfiguration) }}"
                    class="btn btn-outline btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 12H8m0 0l4-4m-4 4l4 4" />
                    </svg>
                    Plantilla de correo
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
        x-data="editor('{{ route('document-configurations.preview', $documentConfiguration) }}?format=jpg', '{{ route('document-configurations.update', $documentConfiguration) }}')">

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
                        :data-tip="key === 'folio' ? 'Formato: [ClaveEvento]-[Año]-[Folio]' : 'Importante: Usa {' + key + '} con llaves'">
                        <button type="button"
                            draggable="true"
                            @dragstart="startVariableDrag($event, key)"
                            @click="addTextElementAt(key, 50, 50); $event.target.classList.add('btn-success'); setTimeout(() => $event.target.classList.remove('btn-success'), 500)"
                            class="join-item btn btn-xs btn-ghost font-mono text-[10px] px-1 bg-base-200 border-base-300 hover:bg-base-300"
                            :title="'Arrastra o da click para agregar {' + key + '}'">
                            <span x-text="key"></span>
                        </button>
                        <input type="text" x-model="sampleData[key]"
                            class="join-item input input-bordered input-xs text-[10px] w-24 focus:w-40 transition-all"
                            @change="refreshPreview()" :name="'sample_data[' + key + ']'" />
                        <button type="button" @click="removeVariable(key)"
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
                    <span class="text-[9px]">Da click para agregar o arrastra. Usa llaves: {variable}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-1 overflow-hidden">

            <!-- Left Pane: Preview -->
            <div style="width: 70%;"
                class="shrink-0 bg-gray-100 dark:bg-gray-900 p-4 flex flex-col border-r border-base-300 relative">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2 pb-2 border-b border-base-300">
                    <div class="flex items-center gap-1.5">
                        <h3 class="font-bold text-xs uppercase tracking-wide !text-black dark:!text-white">Vista Previa</h3>
                    </div>
                    <div class="flex items-center flex-wrap gap-1.5 text-[10px]">
                        <!-- Drag Toggle -->
                        <label class="label cursor-pointer gap-1.5 py-0 px-0.5">
                            <span class="label-text text-[10px] font-bold !text-black dark:!text-white">Arrastrar</span>
                            <input type="checkbox" class="toggle toggle-xs toggle-primary" x-model="enableDrag" />
                        </label>
                        
                        <div class="h-3.5 w-px bg-base-300"></div>
                        
                        <!-- Undo Button -->
                        <button type="button" @click="undo()" class="btn btn-ghost btn-xs min-h-0 h-6 px-1.5 !text-black dark:!text-white font-bold hover:bg-base-200" title="Deshacer (Ctrl+Z)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l-4-4 4-4" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10h9a5 5 0 015 5v1" />
                            </svg>
                            <span class="text-[9px]">Ctrl+Z</span>
                        </button>

                        <div class="h-3.5 w-px bg-base-300"></div>

                        <!-- Redo Button -->
                        <button type="button" @click="redo()" class="btn btn-ghost btn-xs min-h-0 h-6 px-1.5 !text-black dark:!text-white font-bold hover:bg-base-200" title="Rehacer (Ctrl+Y)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 14l4-4-4-4" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 10H10a5 5 0 00-5 5v1" />
                            </svg>
                            <span class="text-[9px]">Ctrl+Y</span>
                        </button>

                        <div class="h-3.5 w-px bg-base-300"></div>

                        <!-- Delete Button -->
                        <button type="button" @click="deleteActiveElement()" class="btn btn-ghost btn-xs min-h-0 h-6 px-1.5 !text-error font-bold hover:bg-error/10" title="Eliminar (Supr)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7l1 12a1 1 0 001 1h4a1 1 0 001-1l1-12" />
                            </svg>
                            <span class="text-[9px]">Supr</span>
                        </button>

                        <div class="h-3.5 w-px bg-base-300"></div>

                        <!-- Zoom Controls Segmented Control -->
                        <div class="flex items-center join bg-base-200 border border-base-300 p-0.5 rounded">
                            <button type="button" @click="setZoom(1)" 
                                class="btn btn-ghost btn-xs join-item min-h-0 h-5 px-1.5 text-[9px] border-none font-bold"
                                :class="zoom === 1 ? '!bg-primary !text-white' : '!text-black dark:!text-white hover:bg-base-300'">
                                Ajustar
                            </button>
                            <button type="button" @click="setZoom(1.5)" 
                                class="btn btn-ghost btn-xs join-item min-h-0 h-5 px-1.5 text-[9px] border-none font-bold"
                                :class="zoom === 1.5 ? '!bg-primary !text-white' : '!text-black dark:!text-white hover:bg-base-300'">
                                1.5x
                            </button>
                            <button type="button" @click="setZoom(2)" 
                                class="btn btn-ghost btn-xs join-item min-h-0 h-5 px-1.5 text-[9px] border-none font-bold"
                                :class="zoom === 2 ? '!bg-primary !text-white' : '!text-black dark:!text-white hover:bg-base-300'">
                                2x
                            </button>
                            <button type="button" @click="setZoom(3)" 
                                class="btn btn-ghost btn-xs join-item min-h-0 h-5 px-1.5 text-[9px] border-none font-bold"
                                :class="zoom === 3 ? '!bg-primary !text-white' : '!text-black dark:!text-white hover:bg-base-300'">
                                3x
                            </button>
                        </div>

                        <div class="h-3.5 w-px bg-base-300"></div>

                        <!-- Refresh Preview -->
                        <button type="button" @click="refreshPreview(true)" class="btn btn-ghost btn-xs min-h-0 h-6 px-1.5 !text-black dark:!text-white font-bold hover:bg-base-200" title="Refrescar vista previa">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="text-[9px]">Refrescar</span>
                        </button>
                    </div>
                </div>
            <div class="flex-1 relative min-h-0 min-w-0 bg-base-200 rounded-lg shadow-inner">
                <div
                    x-ref="previewContainer"
                    @dragover.prevent="allowVariableDrop($event)"
                    @drop.prevent="handleVariableDrop($event)"
                    @mousedown="handleContainerMouseDown($event)"
                    @contextmenu="handleContextMenu($event)"
                    :class="[
                        zoom === 1 ? 'flex items-center justify-center overflow-hidden' : 'block overflow-auto p-4',
                        zoom > 1 && !isPanning ? 'cursor-grab' : '',
                        zoom > 1 && isPanning ? 'cursor-grabbing' : ''
                    ]"
                    class="absolute inset-0">
                    <div x-show="loading"
                        class="absolute inset-0 bg-base-100/50 flex items-center justify-center z-10 backdrop-blur-sm">
                        <span class="loading loading-spinner loading-lg text-primary"></span>
                    </div>
                    <div x-show="enableDrag" class="absolute inset-0 pointer-events-none z-20">
                        <div x-ref="overlay" class="absolute pointer-events-none overflow-visible"
                            :style="'width:' + overlayWidthPx + 'px; height:' + overlayHeightPx + 'px; transform: translate(' + overlayOffsetX + 'px,' + overlayOffsetY + 'px);'">
                            <template x-for="(element, index) in textElements" :key="'drag-text-' + index">
                                <div class="absolute border border-primary/60 bg-primary/10 rounded-sm cursor-move pointer-events-auto overflow-visible"
                                    :class="activeElement && activeElement.type === 'text' && activeElement.index === index ? 'ring-2 ring-primary/70' : ''"
                                    :style="elementStyle(element)"
                                    @mousedown.prevent="startDrag($event, 'text', index)"
                                    @dblclick.stop="editElementText(index)">
                                    <span class="text-[9px] text-primary px-1 py-0.5 bg-base-100/80 rounded">
                                        <span x-text="element.name || 'Texto'"></span>
                                    </span>
                                    <div class="absolute -left-1 z-20 h-3 w-3 bg-primary rounded-sm border border-white shadow pointer-events-auto"
                                        style="top: 50%; transform: translateY(-50%); cursor: ew-resize;"
                                        @mousedown.stop.prevent="startResize($event, 'text', index, 'w')"></div>
                                    <div class="absolute -right-1 z-20 h-3 w-3 bg-primary rounded-sm border border-white shadow pointer-events-auto"
                                        style="top: 50%; transform: translateY(-50%); cursor: ew-resize;"
                                        @mousedown.stop.prevent="startResize($event, 'text', index, 'e')"></div>
                                    <div class="absolute -top-1 z-20 h-3 w-3 bg-primary rounded-sm border border-white shadow pointer-events-auto"
                                        style="left: 50%; transform: translateX(-50%); cursor: ns-resize;"
                                        @mousedown.stop.prevent="startResize($event, 'text', index, 'n')"></div>
                                    <div class="absolute -bottom-1 z-20 h-3 w-3 bg-primary rounded-sm border border-white shadow pointer-events-auto"
                                        style="left: 50%; transform: translateX(-50%); cursor: ns-resize;"
                                        @mousedown.stop.prevent="startResize($event, 'text', index, 's')"></div>
                                    <div class="absolute -left-1 -top-1 h-2.5 w-2.5 bg-primary rounded-sm border border-white cursor-nwse-resize"
                                        @mousedown.prevent="startResize($event, 'text', index, 'nw')"></div>
                                    <div class="absolute -right-1 -top-1 h-2.5 w-2.5 bg-primary rounded-sm border border-white cursor-nesw-resize"
                                        @mousedown.prevent="startResize($event, 'text', index, 'ne')"></div>
                                    <div class="absolute -left-1 -bottom-1 h-2.5 w-2.5 bg-primary rounded-sm border border-white cursor-nesw-resize"
                                        @mousedown.prevent="startResize($event, 'text', index, 'sw')"></div>
                                    <div class="absolute -right-1 -bottom-1 h-2.5 w-2.5 bg-primary rounded-sm border border-white cursor-nwse-resize"
                                        @mousedown.prevent="startResize($event, 'text', index, 'se')"></div>
                                </div>
                            </template>
                            <template x-if="showQr">
                                <div class="absolute border border-secondary/70 bg-secondary/10 rounded-sm cursor-move pointer-events-auto"
                                    :style="qrStyle()"
                                    @mousedown.prevent="startDrag($event, 'qr', null)"
                                    :class="activeElement && activeElement.type === 'qr' ? 'ring-2 ring-secondary/70' : ''">
                                    <span class="text-[9px] text-secondary px-1 py-0.5 bg-base-100/80 rounded">QR</span>
                                    <div class="absolute -left-1 -top-1 h-2.5 w-2.5 bg-secondary rounded-sm border border-white cursor-nwse-resize"
                                        @mousedown.prevent="startResize($event, 'qr', null, 'nw')"></div>
                                    <div class="absolute -right-1 -top-1 h-2.5 w-2.5 bg-secondary rounded-sm border border-white cursor-nesw-resize"
                                        @mousedown.prevent="startResize($event, 'qr', null, 'ne')"></div>
                                    <div class="absolute -left-1 -bottom-1 h-2.5 w-2.5 bg-secondary rounded-sm border border-white cursor-nesw-resize"
                                        @mousedown.prevent="startResize($event, 'qr', null, 'sw')"></div>
                                    <div class="absolute -right-1 -bottom-1 h-2.5 w-2.5 bg-secondary rounded-sm border border-white cursor-nwse-resize"
                                        @mousedown.prevent="startResize($event, 'qr', null, 'se')"></div>
                                </div>
                            </template>
                            <template x-if="showFolio">
                                <div class="absolute border border-accent/70 bg-accent/10 rounded-sm cursor-move pointer-events-auto"
                                    :style="folioStyle()"
                                    @mousedown.prevent="startDrag($event, 'folio', null)"
                                    :class="activeElement && activeElement.type === 'folio' ? 'ring-2 ring-accent/70' : ''">
                                    <span class="text-[9px] text-accent px-1 py-0.5 bg-base-100/80 rounded">Folio</span>
                                    <div class="absolute -left-1 -top-1 h-2.5 w-2.5 bg-accent rounded-sm border border-white cursor-nwse-resize"
                                        @mousedown.prevent="startResize($event, 'folio', null, 'nw')"></div>
                                    <div class="absolute -right-1 -top-1 h-2.5 w-2.5 bg-accent rounded-sm border border-white cursor-nesw-resize"
                                        @mousedown.prevent="startResize($event, 'folio', null, 'ne')"></div>
                                    <div class="absolute -left-1 -bottom-1 h-2.5 w-2.5 bg-accent rounded-sm border border-white cursor-nesw-resize"
                                        @mousedown.prevent="startResize($event, 'folio', null, 'sw')"></div>
                                    <div class="absolute -right-1 -bottom-1 h-2.5 w-2.5 bg-accent rounded-sm border border-white cursor-nwse-resize"
                                        @mousedown.prevent="startResize($event, 'folio', null, 'se')"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <img x-ref="previewImage" class="max-w-full max-h-full" alt="Vista previa" @load="syncOverlayMetrics()" />
                </div>
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
                        <button type="button" @click="activeSection = 'elements'"
                            :class="activeSection === 'elements' ? 'border-primary text-primary bg-base-100' : 'border-transparent hover:bg-base-200'"
                            class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-colors shrink-0">Elementos</button>
                        <button type="button" @click="activeSection = 'options'"
                            :class="activeSection === 'options' ? 'border-primary text-primary bg-base-100' : 'border-transparent hover:bg-base-200'"
                            class="px-4 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-colors shrink-0">Opciones</button>
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
                    <input type="hidden" name="show_qr" x-bind:value="showQr ? '1' : '0'">
                    <input type="hidden" name="show_folio" x-bind:value="showFolio ? '1' : '0'">

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
                                    @change="refreshPreview()"
                                    onchange="document.getElementById('editor_custom_year_container').classList.toggle('hidden', !this.checked)" />
                                <span class="label-text text-[10px]">Prefijo Año (Ej. {{ date('Y') }}-0001)</span>
                            </label>
                        </div>
                        <div id="editor_custom_year_container" class="{{ old('folio_year_prefix', $documentConfiguration->folio_year_prefix) ? '' : 'hidden' }} px-1">
                            <input type="text" name="custom_folio_year"
                                value="{{ old('custom_folio_year', $documentConfiguration->custom_folio_year) }}"
                                class="input input-bordered input-xs w-full text-[10px]"
                                placeholder="Año manual (Ej. 2024)"
                                @change="refreshPreview()" />
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
                            <label class="label py-0 mt-1">
                                <span class="label-text-alt text-[9px] opacity-70">
                                    JPG/PNG, máx. 4 MB, hasta 8000x8000 px.
                                </span>
                            </label>
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
                                    x-model="backgroundX" :readonly="backgroundFit" :class="{'bg-base-200': backgroundFit}"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Y
                                        (mm)</span></label>
                                <input type="number" name="background_y"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="backgroundY" :readonly="backgroundFit" :class="{'bg-base-200': backgroundFit}"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Ancho
                                        (mm)</span></label>
                                <input type="number" name="background_width"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="backgroundWidth" :readonly="backgroundFit" :class="{'bg-base-200': backgroundFit}"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Alto
                                        (mm)</span></label>
                                <input type="number" name="background_height"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="backgroundHeight" :readonly="backgroundFit" :class="{'bg-base-200': backgroundFit}"
                                    @change="refreshPreview()" />
                            </div>
                        </div>
                    </div>



                    <!-- 6. Elementos de Texto (Dynamic) -->
                    <div x-show="activeSection === 'elements'" class="space-y-2">
                        <div class="text-xs font-bold uppercase tracking-wider mb-2 opacity-50 flex justify-between items-center">
                            Elementos de Texto
                            <span class="text-[9px] lowercase italic text-info">Usa {nombre} para campos dinámicos</span>
                        </div>
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
                                                class="select select-bordered select-sm w-full text-xs"
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

                                        <!-- Formatting Buttons (Bold, Italic, Underline) -->
                                        <div class="form-control col-span-2 mt-1">
                                            <label class="label py-0 mb-1"><span
                                                    class="label-text text-[9px] opacity-70">Estilo de Texto</span></label>
                                            <div class="join w-full">
                                                <button type="button" 
                                                    @click="toggleFontStyle(element, 'B')" 
                                                    class="btn btn-xs join-item flex-1 font-bold text-xs"
                                                    :class="hasFontStyle(element, 'B') ? 'btn-primary' : 'btn-outline'">
                                                    B
                                                </button>
                                                <button type="button" 
                                                    @click="toggleFontStyle(element, 'I')" 
                                                    class="btn btn-xs join-item flex-1 italic text-xs"
                                                    :class="hasFontStyle(element, 'I') ? 'btn-primary' : 'btn-outline'">
                                                    I
                                                </button>
                                                <button type="button" 
                                                    @click="toggleFontStyle(element, 'U')" 
                                                    class="btn btn-xs join-item flex-1 underline text-xs"
                                                    :class="hasFontStyle(element, 'U') ? 'btn-primary' : 'btn-outline'">
                                                    U
                                                </button>
                                            </div>
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

                    <!-- 5. Opciones -->
                    <div x-show="activeSection === 'options'" class="space-y-2">
                        <div class="text-xs font-bold uppercase tracking-wider mb-2 opacity-50">Opciones</div>
                        <div class="flex justify-between px-1 mb-2">
                            <div class="form-control">
                                <label class="label cursor-pointer gap-2 py-0">
                                    <span class="label-text text-[10px] font-semibold">Mostrar QR</span>
                                    <input type="checkbox" class="toggle toggle-xs toggle-secondary"
                                        x-model="showQr"
                                        @change="$nextTick(() => refreshPreview(true))" />
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

                        <div class="form-control w-full">
                            <label class="label py-0 mb-1"><span
                                    class="label-text text-[10px] font-semibold">Mensaje de Correo (Opcional)</span></label>
                            <textarea name="email_message"
                                class="textarea textarea-bordered h-24 text-[10px] leading-tight"
                                placeholder="Mensaje personalizado para el correo electrónico...">{{ old('email_message', $documentConfiguration->email_message) }}</textarea>
                            <label class="label py-0">
                                <span class="label-text-alt text-[9px] text-gray-500">Este mensaje aparecerá en el cuerpo del correo enviado a los participantes.</span>
                            </label>
                        </div>

                        <!-- QR Configuration Fields -->
                        <div x-show="showQr" x-transition class="grid grid-cols-2 gap-2 border-t border-base-200 pt-2">
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">QR X
                                        (mm)</span></label>
                                <input type="number" name="qr_x"
                                    value="{{ old('qr_x', $documentConfiguration->qr_x ?? 0) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="qrX"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">QR Y
                                        (mm)</span></label>
                                <input type="number" name="qr_y"
                                    value="{{ old('qr_y', $documentConfiguration->qr_y ?? 0) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="qrY"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">QR Ancho
                                        (mm)</span></label>
                                <input type="number" name="qr_width"
                                    value="{{ old('qr_width', $documentConfiguration->qr_width ?? 20) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="qrWidth"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">QR Alto
                                        (mm)</span></label>
                                <input type="number" name="qr_height"
                                    value="{{ old('qr_height', $documentConfiguration->qr_height ?? 20) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="qrHeight"
                                    @change="refreshPreview()" />
                            </div>
                        </div>

                        <!-- Folio Configuration -->
                        <div class="flex justify-between px-1 mb-2 mt-4 border-t border-base-200 pt-2">
                            <div class="form-control">
                                <label class="label cursor-pointer gap-2 py-0">
                                    <span class="label-text text-[10px] font-semibold">Mostrar Folio</span>
                                    <input type="checkbox" class="toggle toggle-xs toggle-accent"
                                        x-model="showFolio"
                                        @change="$nextTick(() => refreshPreview(true))" />
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
                                    x-model="folioX"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Folio Y
                                        (mm)</span></label>
                                <input type="number" name="folio_y"
                                    value="{{ old('folio_y', $documentConfiguration->folio_y ?? 10) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="folioY"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Ancho
                                        (mm)</span></label>
                                <input type="number" name="folio_width"
                                    value="{{ old('folio_width', $documentConfiguration->folio_width ?? 50) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="folioWidth"
                                    @change="refreshPreview()" />
                            </div>
                            <div class="form-control">
                                <label class="label py-0"><span class="label-text text-[9px] opacity-70">Alto
                                        (mm)</span></label>
                                <input type="number" name="folio_height"
                                    value="{{ old('folio_height', $documentConfiguration->folio_height ?? 10) }}"
                                    class="input input-bordered input-xs w-full text-[10px]" step="0.1"
                                    x-model="folioHeight"
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
                                    class="select select-bordered select-sm w-full text-xs"
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
            function editor(previewUrl, updateUrl) {
                return {
                    loading: false,
                    activeSection: 'basic',
                    textElements: @json($documentConfiguration->text_elements ?? []),
                    sampleData: @json($documentConfiguration->sample_data ?? ['nombre' => 'Juan Pérez']),
                    showQr: {{ ($documentConfiguration->show_qr ?? true) ? 'true' : 'false' }},
                    showFolio: {{ ($documentConfiguration->show_folio ?? true) ? 'true' : 'false' }},
                    enableLivePreview: {{ $documentConfiguration->enable_live_preview ?? true ? 'true' : 'false' }},
                    enableDrag: true,
                    overlayScale: 1,
                    overlayWidthPx: 0,
                    overlayHeightPx: 0,
                    overlayOffsetX: 0,
                    overlayOffsetY: 0,
                    dragState: null,
                    zoom: 1,
                    baseFitWidth: 0,
                    baseFitHeight: 0,
                    isPanning: false,
                    panState: null,
                    
                    // Page Settings
                    pageOrientation: '{{ $documentConfiguration->page_orientation ?? "P" }}',
                    pageSize: '{{ $documentConfiguration->page_size ?? "Letter" }}',
                    
                    // Background Settings
                    backgroundFit: {{ $documentConfiguration->background_fit ? 'true' : 'false' }},
                    backgroundX: {{ $documentConfiguration->background_x ?? 0 }},
                    backgroundY: {{ $documentConfiguration->background_y ?? 0 }},
                    backgroundWidth: {{ $documentConfiguration->background_width ?? 215.9 }},
                    backgroundHeight: {{ $documentConfiguration->background_height ?? 279.4 }},
                    qrX: {{ $documentConfiguration->qr_x ?? 0 }},
                    qrY: {{ $documentConfiguration->qr_y ?? 0 }},
                    qrWidth: {{ $documentConfiguration->qr_width ?? 20 }},
                    qrHeight: {{ $documentConfiguration->qr_height ?? 20 }},
                    folioX: {{ $documentConfiguration->folio_x ?? 10 }},
                    folioY: {{ $documentConfiguration->folio_y ?? 10 }},
                    folioWidth: {{ $documentConfiguration->folio_width ?? 50 }},
                    folioHeight: {{ $documentConfiguration->folio_height ?? 10 }},

                    activeElement: null,
                    previewUrl: previewUrl,
                    updateUrl: updateUrl,
                    newVarKey: '',

                    notifications: [],
                    undoStack: [],
                    redoStack: [],
                    setZoom(level) {
                        this.zoom = level;
                        this.$nextTick(() => {
                            this.syncOverlayMetrics();
                        });
                    },

                    handleContextMenu(event) {
                        if (this.zoom > 1) {
                            event.preventDefault();
                        }
                    },

                    handleContainerMouseDown(event) {
                        if (this.zoom > 1 && (event.button === 2 || event.button === 1)) {
                            if (event.target !== this.$refs.previewContainer && event.target !== this.$refs.previewImage) return;
                            event.preventDefault();
                            this.isPanning = true;
                            const container = this.$refs.previewContainer;
                            this.panState = {
                                startX: event.clientX,
                                startY: event.clientY,
                                startScrollLeft: container.scrollLeft,
                                startScrollTop: container.scrollTop
                            };

                            const onPanMove = (e) => {
                                if (!this.isPanning || !this.panState) return;
                                const dx = e.clientX - this.panState.startX;
                                const dy = e.clientY - this.panState.startY;
                                container.scrollLeft = this.panState.startScrollLeft - dx;
                                container.scrollTop = this.panState.startScrollTop - dy;
                            };

                            const onPanUp = () => {
                                window.removeEventListener('mousemove', onPanMove);
                                window.removeEventListener('mouseup', onPanUp);
                                this.isPanning = false;
                                this.panState = null;
                            };

                            window.addEventListener('mousemove', onPanMove);
                            window.addEventListener('mouseup', onPanUp);
                        } else if (event.target === this.$refs.previewContainer || event.target === this.$refs.previewImage) {
                            this.activeElement = null;
                        }
                    },

                    init() {
                        this.$nextTick(() => {
                            let normalized = false;

                            if (typeof this.sampleData === 'string') {
                                try {
                                    const parsed = JSON.parse(this.sampleData);
                                    if (parsed && typeof parsed === 'object') {
                                        this.sampleData = parsed;
                                        normalized = true;
                                    }
                                } catch (error) {
                                    this.sampleData = {};
                                    normalized = true;
                                }
                            }

                            if (typeof this.textElements === 'string') {
                                try {
                                    const parsed = JSON.parse(this.textElements);
                                    if (Array.isArray(parsed)) {
                                        this.textElements = parsed;
                                        normalized = true;
                                    }
                                } catch (error) {
                                    this.textElements = [];
                                    normalized = true;
                                }
                            }

                            this.refreshPreview(true);
                            this.syncOverlayMetrics();
                            window.addEventListener('resize', () => this.syncOverlayMetrics());

                            if (normalized) {
                                this.scheduleAutoSave();
                            }

                            this.$watch('pageSize', () => {
                                this.clampAllPositions();
                                this.schedulePreview(true);
                                this.syncOverlayMetrics();
                            });
                            this.$watch('pageOrientation', () => {
                                this.clampAllPositions();
                                this.schedulePreview(true);
                                this.syncOverlayMetrics();
                            });

                            const form = document.getElementById('config-form');
                            if (form) {
                                form.addEventListener('input', () => {
                                    this.schedulePreview();
                                    this.scheduleAutoSave();
                                });
                                form.addEventListener('change', () => {
                                    this.schedulePreview(true);
                                    this.scheduleAutoSave();
                                });
                            }

                            window.addEventListener('keydown', (event) => {
                                if (this.isEditingInput(event.target)) return;
                                const isMeta = event.metaKey || event.ctrlKey;
                                if (isMeta && event.key.toLowerCase() === 'z') {
                                    event.preventDefault();
                                    if (event.shiftKey) {
                                        this.redo();
                                    } else {
                                        this.undo();
                                    }
                                    return;
                                }
                                if (isMeta && event.key.toLowerCase() === 'y') {
                                    event.preventDefault();
                                    this.redo();
                                    return;
                                }
                                if (event.key === 'Delete' || event.key === 'Backspace') {
                                    this.deleteActiveElement();
                                }
                            });
                        });
                    },

                    autosaveTimeout: null,
                    previewTimeout: null,
                    autosaving: false,

                    showNotification(message, type = 'info') {
                        const id = Date.now();
                        this.notifications.push({ id, message, type });
                        setTimeout(() => {
                            this.notifications = this.notifications.filter(n => n.id !== id);
                        }, 3000);
                    },

                    setActiveElement(type, index = null) {
                        this.activeElement = { type, index };
                    },

                    deleteActiveElement() {
                        if (!this.activeElement) return;
                        if (this.activeElement.type === 'text') {
                            this.pushHistory();
                            this.textElements.splice(this.activeElement.index, 1);
                            this.activeElement = null;
                            this.refreshPreview(true);
                            this.scheduleAutoSave();
                            this.showNotification('Elemento eliminado', 'info');
                            return;
                        }
                        if (this.activeElement.type === 'qr') {
                            this.showQr = false;
                            this.activeElement = null;
                            this.refreshPreview(true);
                            this.scheduleAutoSave();
                            this.showNotification('QR desactivado', 'info');
                            return;
                        }
                        if (this.activeElement.type === 'folio') {
                            this.showFolio = false;
                            this.activeElement = null;
                            this.refreshPreview(true);
                            this.scheduleAutoSave();
                            this.showNotification('Folio desactivado', 'info');
                            return;
                        }
                    },

                    getElementWidthMm(element) {
                        const page = this.getPageDimensionsMm();
                        let widthMm = parseFloat(element.width);
                        if (!Number.isFinite(widthMm) || widthMm <= 0) {
                            const percent = parseFloat(element.auto_width_percent);
                            if (Number.isFinite(percent) && percent > 0) {
                                widthMm = page.width * (percent / 100);
                            } else {
                                widthMm = 40;
                            }
                        }
                        return Math.max(widthMm, 10);
                    },

                    getDisplayText(element) {
                        const raw = (element?.text ?? '').toString();
                        return raw.replace(/\{(\w+)\}/g, (match, key) => {
                            const value = this.sampleData?.[key];
                            return value === undefined || value === null ? match : String(value);
                        });
                    },

                    measureTextLines(text, widthPx, fontFamily, fontSizePt) {
                        const safeText = (text ?? '').toString();
                        if (widthPx <= 0) {
                            return { lines: 1, lineHeightPx: 0 };
                        }
                        if (!this._measureCanvas) {
                            this._measureCanvas = document.createElement('canvas');
                        }
                        const context = this._measureCanvas.getContext('2d');
                        const fontPx = Math.max(8, (parseFloat(fontSizePt) || 12) * 1.333);
                        context.font = `${fontPx}px ${fontFamily || 'Arial'}`;
                        const lineHeightPx = fontPx * 1.2;

                        const paragraphs = safeText.split(/\r?\n/);
                        let lines = 0;

                        paragraphs.forEach((para) => {
                            const words = para.split(' ');
                            let line = '';
                            words.forEach((word) => {
                                const testLine = line ? `${line} ${word}` : word;
                                const width = context.measureText(testLine).width;
                                if (width <= widthPx || line === '') {
                                    line = testLine;
                                } else {
                                    lines += 1;
                                    line = word;
                                }
                            });
                            lines += 1;
                        });

                        return { lines, lineHeightPx };
                    },

                    fitTextToBox(index, options = {}) {
                        if (!this.overlayScale || this.overlayScale <= 0) return;
                        const element = this.textElements[index];
                        if (!element) return;
                        const mode = options.mode || 'growBox';
                        const widthMm = this.getElementWidthMm(element);
                        const widthPx = widthMm * this.overlayScale;
                        const text = this.getDisplayText(element);
                        const fontFamily = element.font_family || 'Arial';

                        if (mode === 'fitFont') {
                            const heightMm = parseFloat(element.height) || 10;
                            const heightPx = heightMm * this.overlayScale;
                            const minSize = 6;
                            const maxSize = Math.max(parseFloat(element.font_size) || 12, 72);
                            let low = minSize;
                            let high = maxSize;
                            let best = minSize;

                            while (low <= high) {
                                const mid = Math.floor((low + high) / 2);
                                const result = this.measureTextLines(text, widthPx, fontFamily, mid);
                                const needed = Math.max(result.lines, 1) * result.lineHeightPx;
                                if (needed <= heightPx) {
                                    best = mid;
                                    low = mid + 1;
                                } else {
                                    high = mid - 1;
                                }
                            }

                            element.font_size = best;
                            const final = this.measureTextLines(text, widthPx, fontFamily, best);
                            element.multicell = final.lines > 1;
                            return;
                        }

                        const fontSize = element.font_size || 12;
                        const result = this.measureTextLines(text, widthPx, fontFamily, fontSize);
                        const heightPx = Math.max(result.lines, 1) * result.lineHeightPx;
                        const heightMm = heightPx / this.overlayScale;
                        element.height = parseFloat(Math.max(heightMm, 6).toFixed(1));
                        element.multicell = result.lines > 1;
                    },

                    isEditingInput(target) {
                        if (!target) return false;
                        const tag = target.tagName ? target.tagName.toLowerCase() : '';
                        return tag === 'input' || tag === 'textarea' || target.isContentEditable;
                    },

                    snapshotState() {
                        return {
                            textElements: JSON.parse(JSON.stringify(this.textElements)),
                            qrX: this.qrX,
                            qrY: this.qrY,
                            qrWidth: this.qrWidth,
                            qrHeight: this.qrHeight,
                            folioX: this.folioX,
                            folioY: this.folioY,
                            folioWidth: this.folioWidth,
                            folioHeight: this.folioHeight,
                        };
                    },

                    pushHistory() {
                        this.undoStack.push(this.snapshotState());
                        if (this.undoStack.length > this.historyLimit) {
                            this.undoStack.shift();
                        }
                        this.redoStack = [];
                    },

                    restoreState(state) {
                        if (!state) return;
                        this.textElements = JSON.parse(JSON.stringify(state.textElements || []));
                        this.qrX = state.qrX ?? this.qrX;
                        this.qrY = state.qrY ?? this.qrY;
                        this.qrWidth = state.qrWidth ?? this.qrWidth;
                        this.qrHeight = state.qrHeight ?? this.qrHeight;
                        this.folioX = state.folioX ?? this.folioX;
                        this.folioY = state.folioY ?? this.folioY;
                        this.folioWidth = state.folioWidth ?? this.folioWidth;
                        this.folioHeight = state.folioHeight ?? this.folioHeight;
                        this.activeElement = null;
                        this.clampAllPositions();
                        this.refreshPreview(true);
                        this.scheduleAutoSave();
                    },

                    undo() {
                        if (!this.undoStack.length) return;
                        this.redoStack.push(this.snapshotState());
                        const state = this.undoStack.pop();
                        this.restoreState(state);
                    },

                    redo() {
                        if (!this.redoStack.length) return;
                        this.undoStack.push(this.snapshotState());
                        const state = this.redoStack.pop();
                        this.restoreState(state);
                    },

                    scheduleAutoSave() {
                        if (this.autosaveTimeout) {
                            clearTimeout(this.autosaveTimeout);
                        }
                        this.autosaveTimeout = setTimeout(() => this.autoSave(), 900);
                    },

                    schedulePreview(force = false) {
                        if (!this.enableLivePreview && !force) return;
                        if (this.previewTimeout) {
                            clearTimeout(this.previewTimeout);
                        }
                        this.previewTimeout = setTimeout(() => this.refreshPreview(force), 300);
                    },

                    buildFormData() {
                        const form = document.getElementById('config-form');
                        const formData = new FormData(form);

                        formData.set('text_elements', JSON.stringify(this.textElements));
                        formData.set('sample_data', JSON.stringify(this.sampleData));
                        formData.set('page_orientation', this.pageOrientation);
                        formData.set('page_size', this.pageSize);

                        if (this.backgroundFit) {
                            formData.set('background_x', this.backgroundX);
                            formData.set('background_y', this.backgroundY);
                            formData.set('background_width', this.backgroundWidth);
                            formData.set('background_height', this.backgroundHeight);
                            formData.set('background_fit', '1');
                        }

                        // QR & Folio toggles — always send explicitly
                        formData.set('show_qr', this.showQr ? '1' : '0');
                        formData.set('show_folio', this.showFolio ? '1' : '0');

                        return formData;
                    },

                    async autoSave() {
                        if (this.autosaving) return;
                        this.autosaving = true;

                        try {
                            const formData = this.buildFormData();
                            formData.set('_method', 'PUT');

                            const response = await fetch(this.updateUrl, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (!response.ok) {
                                const data = await response.json().catch(() => null);
                                const message = data?.message || 'No se pudo guardar automáticamente';
                                this.showNotification(message, 'error');
                            }
                        } catch (error) {
                            this.showNotification('Error de conexión al guardar', 'error');
                        } finally {
                            this.autosaving = false;
                        }
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
                        this.syncOverlayMetrics();
                    },

                    getPageDimensionsMm() {
                        let width = 215.9;
                        let height = 279.4;

                        switch (this.pageSize) {
                            case 'A4':
                                width = 210.0;
                                height = 297.0;
                                break;
                            case 'Legal':
                                width = 215.9;
                                height = 355.6;
                                break;
                            case 'Letter':
                            default:
                                width = 215.9;
                                height = 279.4;
                        }

                        if (this.pageOrientation === 'L') {
                            const temp = width;
                            width = height;
                            height = temp;
                        }

                        return { width, height };
                    },

                    syncOverlayMetrics() {
                        this.$nextTick(() => {
                            const container = this.$refs.previewContainer;
                            const img = this.$refs.previewImage;
                            if (!container || !img) return;

                            if (this.zoom === 1) {
                                img.style.width = '';
                                img.style.maxWidth = '';
                                img.style.height = '';
                                img.style.maxHeight = '';
                                
                                const imgRect = img.getBoundingClientRect();
                                this.baseFitWidth = imgRect.width;
                                this.baseFitHeight = imgRect.height;
                            } else {
                                if (!this.baseFitWidth || !this.baseFitHeight) {
                                    const imgRect = img.getBoundingClientRect();
                                    this.baseFitWidth = imgRect.width || 400;
                                    this.baseFitHeight = imgRect.height || 300;
                                }
                                img.style.width = (this.baseFitWidth * this.zoom) + 'px';
                                img.style.maxWidth = 'none';
                                img.style.height = (this.baseFitHeight * this.zoom) + 'px';
                                img.style.maxHeight = 'none';
                            }

                            const rect = container.getBoundingClientRect();
                            const imgRect = img.getBoundingClientRect();
                            const page = this.getPageDimensionsMm();

                            const scale = imgRect.width / page.width;
                            if (!scale || scale <= 0) return;

                            this.overlayScale = scale;
                            this.overlayWidthPx = imgRect.width;
                            this.overlayHeightPx = imgRect.height;
                            this.overlayOffsetX = imgRect.left - rect.left + container.scrollLeft;
                            this.overlayOffsetY = imgRect.top - rect.top + container.scrollTop;
                        });
                    },

                    elementStyle(element) {
                        const page = this.getPageDimensionsMm();
                        const x = (parseFloat(element.x || 0) * this.overlayScale) || 0;
                        const y = (parseFloat(element.y || 0) * this.overlayScale) || 0;

                        let widthMm = parseFloat(element.width);
                        if (!Number.isFinite(widthMm) || widthMm <= 0) {
                            const percent = parseFloat(element.auto_width_percent);
                            if (Number.isFinite(percent) && percent > 0) {
                                widthMm = page.width * (percent / 100);
                            } else {
                                widthMm = 40;
                            }
                        }

                        let heightMm = parseFloat(element.height);
                        if (!Number.isFinite(heightMm) || heightMm <= 0) {
                            heightMm = 10;
                        }

                        widthMm = Math.max(widthMm, 10);
                        heightMm = Math.max(heightMm, 6);

                        const width = widthMm * this.overlayScale;
                        const height = heightMm * this.overlayScale;
                        return `left:${x}px; top:${y}px; width:${width}px; height:${height}px;`;
                    },

                    qrStyle() {
                        const x = (parseFloat(this.qrX || 0) * this.overlayScale) || 0;
                        const y = (parseFloat(this.qrY || 0) * this.overlayScale) || 0;
                        const width = (parseFloat(this.qrWidth || 10) * this.overlayScale) || 10;
                        const height = (parseFloat(this.qrHeight || 10) * this.overlayScale) || 10;
                        return `left:${x}px; top:${y}px; width:${width}px; height:${height}px;`;
                    },

                    folioStyle() {
                        const x = (parseFloat(this.folioX || 0) * this.overlayScale) || 0;
                        const y = (parseFloat(this.folioY || 0) * this.overlayScale) || 0;
                        const width = (parseFloat(this.folioWidth || 10) * this.overlayScale) || 10;
                        const height = (parseFloat(this.folioHeight || 6) * this.overlayScale) || 6;
                        return `left:${x}px; top:${y}px; width:${width}px; height:${height}px;`;
                    },

                    toggleFontStyle(element, styleChar) {
                        this.pushHistory();
                        let currentStyle = element.font_style || '';
                        if (currentStyle.includes(styleChar)) {
                            element.font_style = currentStyle.replace(styleChar, '');
                        } else {
                            let newStyle = currentStyle + styleChar;
                            let sortedStyle = '';
                            if (newStyle.includes('B')) sortedStyle += 'B';
                            if (newStyle.includes('I')) sortedStyle += 'I';
                            if (newStyle.includes('U')) sortedStyle += 'U';
                            element.font_style = sortedStyle;
                        }
                        this.refreshPreview(true);
                        this.scheduleAutoSave();
                    },

                    hasFontStyle(element, styleChar) {
                        return (element.font_style || '').includes(styleChar);
                    },

                    clampAllPositions() {
                        const page = this.getPageDimensionsMm();

                        const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

                        this.textElements = this.textElements.map((element) => {
                            let widthMm = parseFloat(element.width);
                            if (!Number.isFinite(widthMm) || widthMm <= 0) {
                                const percent = parseFloat(element.auto_width_percent);
                                if (Number.isFinite(percent) && percent > 0) {
                                    widthMm = page.width * (percent / 100);
                                } else {
                                    widthMm = 40;
                                }
                            }

                            let heightMm = parseFloat(element.height);
                            if (!Number.isFinite(heightMm) || heightMm <= 0) {
                                heightMm = 10;
                            }

                            const maxX = Math.max(page.width - widthMm, 0);
                            const maxY = Math.max(page.height - heightMm, 0);

                            const x = clamp(parseFloat(element.x || 0), 0, maxX);
                            const y = clamp(parseFloat(element.y || 0), 0, maxY);

                            return {
                                ...element,
                                x: parseFloat(x.toFixed(1)),
                                y: parseFloat(y.toFixed(1)),
                            };
                        });

                        const qrMaxX = Math.max(page.width - parseFloat(this.qrWidth || 0), 0);
                        const qrMaxY = Math.max(page.height - parseFloat(this.qrHeight || 0), 0);
                        this.qrX = clamp(parseFloat(this.qrX || 0), 0, qrMaxX);
                        this.qrY = clamp(parseFloat(this.qrY || 0), 0, qrMaxY);

                        const folioMaxX = Math.max(page.width - parseFloat(this.folioWidth || 0), 0);
                        const folioMaxY = Math.max(page.height - parseFloat(this.folioHeight || 0), 0);
                        this.folioX = clamp(parseFloat(this.folioX || 0), 0, folioMaxX);
                        this.folioY = clamp(parseFloat(this.folioY || 0), 0, folioMaxY);

                        this.scheduleAutoSave();
                    },

                    startDrag(event, type, index) {
                        if (!this.enableDrag) return;
                        this.setActiveElement(type, index);
                        this.pushHistory();
                        const startX = event.clientX;
                        const startY = event.clientY;
                        let originX = 0;
                        let originY = 0;

                        if (type === 'text') {
                            originX = parseFloat(this.textElements[index].x || 0);
                            originY = parseFloat(this.textElements[index].y || 0);
                        } else if (type === 'qr') {
                            originX = parseFloat(this.qrX || 0);
                            originY = parseFloat(this.qrY || 0);
                        } else if (type === 'folio') {
                            originX = parseFloat(this.folioX || 0);
                            originY = parseFloat(this.folioY || 0);
                        }

                        this.dragState = { type, index, startX, startY, originX, originY };

                        const onMove = (e) => {
                            if (!this.dragState) return;
                            const dx = (e.clientX - this.dragState.startX) / this.overlayScale;
                            const dy = (e.clientY - this.dragState.startY) / this.overlayScale;
                            const newX = parseFloat((this.dragState.originX + dx).toFixed(1));
                            const newY = parseFloat((this.dragState.originY + dy).toFixed(1));

                            if (this.dragState.type === 'text') {
                                this.textElements[this.dragState.index].x = newX;
                                this.textElements[this.dragState.index].y = newY;
                            } else if (this.dragState.type === 'qr') {
                                this.qrX = newX;
                                this.qrY = newY;
                            } else if (this.dragState.type === 'folio') {
                                this.folioX = newX;
                                this.folioY = newY;
                            }
                        };

                        const onUp = () => {
                            window.removeEventListener('mousemove', onMove);
                            window.removeEventListener('mouseup', onUp);
                            this.dragState = null;
                            this.scheduleAutoSave();
                            this.refreshPreview(true);
                        };

                        window.addEventListener('mousemove', onMove);
                        window.addEventListener('mouseup', onUp);
                    },

                    startResize(event, type, index, direction = 'se') {
                        if (!this.enableDrag) return;
                        if (event && typeof event.stopPropagation === 'function') {
                            event.stopPropagation();
                        }
                        this.setActiveElement(type, index);
                        this.pushHistory();
                        const startX = event.clientX;
                        const startY = event.clientY;

                        let originWidth = 0;
                        let originHeight = 0;
                        let originX = 0;
                        let originY = 0;

                        if (type === 'text') {
                            originWidth = parseFloat(this.textElements[index].width || 40);
                            originHeight = parseFloat(this.textElements[index].height || 10);
                            originX = parseFloat(this.textElements[index].x || 0);
                            originY = parseFloat(this.textElements[index].y || 0);
                            this.textElements[index].auto_width_percent = 0;
                        } else if (type === 'qr') {
                            originWidth = parseFloat(this.qrWidth || 20);
                            originHeight = parseFloat(this.qrHeight || 20);
                            originX = parseFloat(this.qrX || 0);
                            originY = parseFloat(this.qrY || 0);
                        } else if (type === 'folio') {
                            originWidth = parseFloat(this.folioWidth || 50);
                            originHeight = parseFloat(this.folioHeight || 10);
                            originX = parseFloat(this.folioX || 0);
                            originY = parseFloat(this.folioY || 0);
                        }

                        const minWidth = 10;
                        const minHeight = 6;

                        const onMove = (e) => {
                            const dx = (e.clientX - startX) / this.overlayScale;
                            const dy = (e.clientY - startY) / this.overlayScale;
                            let newWidth = originWidth;
                            let newHeight = originHeight;
                            let newX = originX;
                            let newY = originY;

                            if (direction.includes('e')) {
                                newWidth = originWidth + dx;
                            }
                            if (direction.includes('s')) {
                                newHeight = originHeight + dy;
                            }
                            if (direction.includes('w')) {
                                newWidth = originWidth - dx;
                                newX = originX + dx;
                            }
                            if (direction.includes('n')) {
                                newHeight = originHeight - dy;
                                newY = originY + dy;
                            }

                            newWidth = Math.max(minWidth, parseFloat(newWidth.toFixed(1)));
                            newHeight = Math.max(minHeight, parseFloat(newHeight.toFixed(1)));
                            newX = parseFloat(newX.toFixed(1));
                            newY = parseFloat(newY.toFixed(1));

                            if (type === 'text') {
                                this.textElements[index].width = newWidth;
                                this.textElements[index].height = newHeight;
                                this.textElements[index].x = newX;
                                this.textElements[index].y = newY;
                                this.fitTextToBox(index, { mode: 'fitFont' });
                            } else if (type === 'qr') {
                                this.qrWidth = newWidth;
                                this.qrHeight = newHeight;
                                this.qrX = newX;
                                this.qrY = newY;
                            } else if (type === 'folio') {
                                this.folioWidth = newWidth;
                                this.folioHeight = newHeight;
                                this.folioX = newX;
                                this.folioY = newY;
                            }
                        };

                        const onUp = () => {
                            window.removeEventListener('mousemove', onMove);
                            window.removeEventListener('mouseup', onUp);
                            this.clampAllPositions();
                            if (type === 'text' && typeof index === 'number') {
                                this.fitTextToBox(index, { mode: 'fitFont' });
                            }
                            this.scheduleAutoSave();
                            this.refreshPreview(true);
                        };

                        window.addEventListener('mousemove', onMove);
                        window.addEventListener('mouseup', onUp);
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

                    removeVariable(key) {
                        if (!confirm(`¿Estás seguro de que quieres eliminar la variable "${key}"? Esto la quitará de la lista y del documento si está siendo usada como único contenido en un elemento.`)) {
                            return;
                        }
                        this.pushHistory();
                        
                        // Eliminar de sampleData
                        const newData = { ...this.sampleData };
                        delete newData[key];
                        this.sampleData = newData;

                        // Eliminar elementos de texto que contengan EXACTAMENTE esa variable
                        const beforeCount = this.textElements.length;
                        this.textElements = this.textElements.filter(el => {
                            return el.text !== '{' + key + '}';
                        });

                        this.scheduleAutoSave();
                        
                        this.$nextTick(() => {
                            this.refreshPreview(true);
                        });
                        
                        let msg = `Variable "${key}" eliminada.`;
                        if (this.textElements.length < beforeCount) {
                            msg += ` Se quitaron ${beforeCount - this.textElements.length} elementos del documento.`;
                        }
                        this.showNotification(msg, 'info');
                    },

                    startVariableDrag(event, key) {
                        if (!event?.dataTransfer) return;
                        event.dataTransfer.setData('application/x-constancias-var', key);
                        event.dataTransfer.setData('text/plain', key);
                        event.dataTransfer.effectAllowed = 'copy';
                    },

                    allowVariableDrop(event) {
                        const types = event?.dataTransfer?.types || [];
                        if (!types || !Array.from(types).includes('application/x-constancias-var')) {
                            return;
                        }
                        event.preventDefault();
                    },

                    handleVariableDrop(event) {
                        const key = event?.dataTransfer?.getData('application/x-constancias-var');
                        if (!key) return;

                        const container = this.$refs.previewContainer;
                        if (!container) return;

                        const rect = container.getBoundingClientRect();
                        const relativeX = event.clientX - rect.left - this.overlayOffsetX;
                        const relativeY = event.clientY - rect.top - this.overlayOffsetY;
                        if (relativeX < 0 || relativeY < 0
                            || relativeX > this.overlayWidthPx
                            || relativeY > this.overlayHeightPx) {
                            return;
                        }

                        if (this.overlayScale <= 0) return;

                        const xMm = Math.max(0, relativeX / this.overlayScale);
                        const yMm = Math.max(0, relativeY / this.overlayScale);
                        this.addTextElementAt(key, xMm, yMm);
                    },

                    addTextElementAt(key, xMm, yMm) {
                        const page = this.getPageDimensionsMm();
                        const widthMm = 50;
                        const heightMm = 10;
                        const clampedX = Math.max(0, Math.min(xMm, page.width - widthMm));
                        const clampedY = Math.max(0, Math.min(yMm, page.height - heightMm));

                        this.pushHistory();
                        this.textElements.push({
                            name: key,
                            text: '{' + key + '}',
                            x: parseFloat(clampedX.toFixed(1)),
                            y: parseFloat(clampedY.toFixed(1)),
                            width: widthMm,
                            height: heightMm,
                            font_size: 12,
                            alignment: 'L',
                            text_color: '#000000',
                            fill_color: '#FFFFFF',
                            fill: false
                        });

                        this.setActiveElement('text', this.textElements.length - 1);
                        this.fitTextToBox(this.textElements.length - 1, { mode: 'growBox' });
                        this.refreshPreview(true);
                        this.scheduleAutoSave();
                        this.showNotification('Variable agregada al documento', 'success');
                    },

                    editElementText(index) {
                        const element = this.textElements[index];
                        if (!element) return;

                        const current = element.text || '';
                        const next = window.prompt('Texto del elemento:', current);
                        if (next === null) return;

                        this.pushHistory();
                        element.text = next;
                        if (!element.name || element.name === 'nuevo_elemento') {
                            element.name = next.slice(0, 24) || element.name;
                        }
                        this.fitTextToBox(index, { mode: 'growBox' });
                        this.refreshPreview(true);
                        this.scheduleAutoSave();
                    },

                    addElement() {
                        this.pushHistory();
                        this.textElements.push({
                            name: 'nuevo_elemento',
                            text: 'Texto de ejemplo',
                            x: 10,
                            y: 10,
                            width: 50,
                            height: 10,
                            font_size: 12,
                            font_family: 'Arial',
                            font_style: '',
                            alignment: 'L',
                            text_color: '#000000',
                            fill_color: '#FFFFFF',
                            fill: false
                        });
                        this.fitTextToBox(this.textElements.length - 1, { mode: 'growBox' });
                        this.refreshPreview();
                        this.showNotification('Elemento de texto agregado', 'success');
                    },

                    removeElement(index) {
                        this.pushHistory();
                        this.textElements.splice(index, 1);
                        this.refreshPreview();
                        this.showNotification('Elemento eliminado', 'info');
                    },

                    async refreshPreview(force = false) {
                        if (!this.enableLivePreview && !force) return;
                        
                        this.loading = true;

                        // Gather form data manually to include dynamic text elements
                        const formData = this.buildFormData();

                        // Explicitly set background dimensions from Alpine state to ensure they are up-to-date
                        // even if the DOM inputs haven't updated yet or are readonly
                        if (this.backgroundFit) {
                            formData.set('background_x', this.backgroundX);
                            formData.set('background_y', this.backgroundY);
                            formData.set('background_width', this.backgroundWidth);
                            formData.set('background_height', this.backgroundHeight);
                            formData.set('background_fit', '1');
                        }

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
                                if (this.$refs.previewImage) {
                                    this.$refs.previewImage.src = url;
                                }
                                this.syncOverlayMetrics();
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