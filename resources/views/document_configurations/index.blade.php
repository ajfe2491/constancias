<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configuraciones de Documentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Search and Actions -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6 justify-between items-center">
                <form action="{{ route('document-configurations.index') }}" method="GET"
                    class="w-full sm:w-auto flex-1 max-w-md">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar plantillas..." class="input input-bordered w-full pl-10 input-sm" />
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
                <a href="{{ route('document-configurations.create') }}"
                    class="card bg-base-100 shadow-sm hover:shadow-md border border-dashed border-base-300 hover:border-primary transition-all duration-200 group flex flex-col items-center justify-center p-6 cursor-pointer min-h-[250px]">
                    <div
                        class="w-12 h-12 rounded-full bg-base-200 group-hover:bg-primary/10 flex items-center justify-center mb-3 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-base-content/50 group-hover:text-primary transition-colors" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="font-bold text-sm group-hover:text-primary transition-colors">Nueva
                        Configuración</span>
                </a>

                @foreach($configurations as $config)
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
                                        <h2 class="font-bold text-base truncate text-left">
                                            {{ $config->document_name }}
                                        </h2>
                                    </div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">
                                        {{ $config->document_type }}
                                    </p>
                                    @if($config->event)
                                        <div class="mt-1">
                                            <span class="badge badge-primary badge-outline badge-xs gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $config->event->key }}
                                            </span>
                                            <span
                                                class="text-[10px] text-gray-400 ml-1 truncate max-w-[150px] inline-block align-bottom"
                                                title="{{ $config->event->name }}">
                                                {{ $config->event->name }}
                                            </span>
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
                                        <li><a href="{{ route('document-configurations.edit', $config) }}">Editar</a></li>
                                        <li>
                                            <form action="{{ route('document-configurations.destroy', $config) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Estás seguro de eliminar esta configuración?');">
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

                            <div class="card-actions justify-between items-center border-t border-base-100 pt-3 mt-auto">
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

            <div class="mt-6">
                {{ $configurations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>