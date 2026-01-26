<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Envío') }}
        </h2>
    </x-slot>

    @php
        $statusPayload = [
            'id' => $history->id,
            'total' => $history->total_registros,
            'success' => $history->procesados_exitosos,
            'failed' => $history->procesados_fallidos,
            'errors' => $history->errores ?? [],
            'updated_at' => $history->updated_at?->toDateTimeString(),
        ];
    @endphp

    <div x-data='sendingStatus(@json($statusPayload))' class="space-y-6">
        @if(!$isOwner)
            <div class="alert alert-info text-sm">
                Acceso compartido: solo puedes ver las constancias que te compartieron.
            </div>
        @endif
        <div class="mb-2">
            <a href="{{ route('certificate-sending.index') }}" class="btn btn-ghost gap-2 pl-0 hover:bg-transparent">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver al historial
            </a>

            <div class="flex flex-col md:flex-row justify-between items-start gap-4 mt-2">
                <div>
                    <h1 class="text-2xl font-bold">Detalles del Envío #{{ $history->id }}</h1>
                    <div class="flex flex-col gap-1 mt-1 text-sm opacity-70">
                        <p>Iniciado el {{ $history->created_at?->format('d/m/Y H:i') ?? 'N/A' }} por
                            {{ $history->user->name ?? 'Sistema' }}
                        </p>
                        @if($history->documentConfiguration)
                            <p class="font-semibold text-primary">
                                Evento: {{ $history->documentConfiguration->event->name ?? 'Sin Evento' }}
                                <span class="mx-2">|</span>
                                Constancia: {{ $history->documentConfiguration->document_name }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="badge badge-lg badge-outline gap-2">
                        @if($history->csv_file_path)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Envío Masivo
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Envío Individual
                        @endif
                    </div>

                    <div class="badge badge-lg" :class="completed ? 'badge-success gap-2' : 'badge-warning gap-2'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="completed ? '' : 'animate-spin'"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="completed ? 'Completado' : 'En proceso'"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Status & Stats -->
            <div class="lg:col-span-2 space-y-6">
                @if($isOwner)
                    <!-- Progress Card -->
                    <div class="card bg-base-100 shadow-md">
                        <div class="card-body">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="card-title text-sm">Progreso</h3>
                                <span class="text-xs opacity-70" x-text="lastUpdatedLabel"></span>
                            </div>
                            <progress class="progress progress-primary w-full h-3" :value="processed"
                                :max="total"></progress>
                            <div class="flex justify-between text-sm mt-2 font-medium">
                                <span x-text="`${progress}%`"></span>
                                <span x-text="`${processed} de ${total} registros`"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="stats shadow bg-base-100 border-l-4 border-blue-500">
                            <div class="stat px-4 py-3">
                                <div class="stat-title text-xs uppercase font-bold tracking-wider opacity-70">Total</div>
                                <div class="stat-value text-2xl font-extrabold text-base-content" x-text="total"></div>
                                <div class="stat-desc text-xs mt-1 opacity-70">Registros procesados</div>
                            </div>
                        </div>

                        <div class="stats shadow bg-base-100 border-l-4 border-green-500">
                            <div class="stat px-4 py-3">
                                <div class="stat-title text-xs uppercase font-bold tracking-wider opacity-70">Exitosos</div>
                                <div class="stat-value text-2xl font-extrabold text-success" x-text="success"></div>
                                <div class="stat-desc text-xs mt-1 opacity-70">Correos enviados</div>
                            </div>
                        </div>

                        <div class="stats shadow bg-base-100 border-l-4 border-red-500">
                            <div class="stat px-4 py-3">
                                <div class="stat-title text-xs uppercase font-bold tracking-wider opacity-70">Fallidos</div>
                                <div class="stat-value text-2xl font-extrabold text-error" x-text="failed"></div>
                                <div class="stat-desc text-xs mt-1 opacity-70">Errores encontrados</div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($history->csv_file_path)
                        <div class="card bg-base-100 shadow-sm border border-base-200">
                            <div class="card-body p-4 flex-row items-center gap-4">
                                <div class="p-3 bg-base-200 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-70" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold">Archivo de Origen</h3>
                                    <p class="text-xs opacity-70">CSV utilizado para este envío</p>
                                </div>
                                <a href="{{ route('certificate-sending.csv', $history) }}"
                                    class="btn btn-primary btn-sm gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Descargar CSV
                                </a>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Certificates List -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <h3 class="card-title text-sm">Constancias generadas</h3>
                            <span class="text-xs opacity-70">{{ $certificates->total() }} registros</span>
                        </div>
                        <p class="text-xs opacity-60 mb-3">
                            Para no sobrecargar, las previsualizaciones se cargan bajo demanda.
                        </p>

                        @if ($certificates->isEmpty())
                            <div class="text-sm opacity-60">Aún no hay constancias generadas.</div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="table table-zebra table-compact w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th>Folio</th>
                                            <th>Correo</th>
                                            <th>UUID</th>
                                            <th>Compartida</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($certificates as $certificate)
                                            <tr>
                                                <td class="font-mono">{{ $certificate->folio ?? 'N/A' }}</td>
                                                <td>{{ $certificate->recipient_email ?? 'N/A' }}</td>
                                                <td class="text-xs opacity-60">{{ $certificate->uuid }}</td>
                                                <td>
                                                    @if($isOwner)
                                                        @if($certificate->shared_users_count > 0)
                                                            <span class="badge badge-success badge-xs">Compartida</span>
                                                        @else
                                                            <span class="badge badge-ghost badge-xs">Privada</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-info badge-xs">Compartida contigo</span>
                                                    @endif
                                                </td>
                                                <td class="space-x-2">
                                                    <a href="{{ route('certificates.verify', $certificate->uuid) }}"
                                                        target="_blank" class="btn btn-ghost btn-xs">Verificar</a>
                                                    @if($isOwner)
                                                        <button type="button" class="btn btn-outline btn-xs"
                                                            onclick="document.getElementById('resend-modal-{{ $certificate->id }}').showModal()">
                                                            Reenviar
                                                        </button>
                                                        <button type="button" class="btn btn-ghost btn-xs"
                                                            onclick="document.getElementById('share-modal-{{ $certificate->id }}').showModal()">
                                                            Compartir
                                                        </button>
                                                    @endif
                                                    <dialog id="resend-modal-{{ $certificate->id }}" class="modal">
                                                        <div class="modal-box">
                                                            <h3 class="font-bold text-lg mb-2">Reenviar constancia</h3>
                                                            <p class="text-sm opacity-70 mb-4">
                                                                Ingresa el correo correcto para reenviar la constancia.
                                                            </p>
                                                            <form method="POST"
                                                                action="{{ route('certificates.resend', $certificate) }}"
                                                                class="space-y-4">
                                                                @csrf
                                                                <div class="form-control">
                                                                    <label class="label">
                                                                        <span class="label-text">Correo electrónico</span>
                                                                    </label>
                                                                    <input type="email" name="email"
                                                                        value="{{ $certificate->recipient_email }}"
                                                                        class="input input-bordered w-full" required />
                                                                </div>
                                                                @php
                                                                    $extraData = collect($certificate->recipient_data ?? [])
                                                                        ->except(['email', 'folio', 'folio_number', 'qr_path'])
                                                                        ->filter(fn($value) => $value !== null && $value !== '');
                                                                @endphp
                                                                @if ($extraData->isNotEmpty())
                                                                    <div class="divider text-xs">Variables</div>
                                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                                        @foreach ($extraData as $key => $value)
                                                                            <div class="form-control">
                                                                                <label class="label">
                                                                                    <span class="label-text">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                                                                                </label>
                                                                                <input type="text" name="data[{{ $key }}]"
                                                                                    value="{{ $value }}"
                                                                                    class="input input-bordered w-full" />
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                                <div class="flex justify-end gap-2">
                                                                    <button type="button" class="btn btn-ghost"
                                                                        onclick="document.getElementById('resend-modal-{{ $certificate->id }}').close()">
                                                                        Cancelar
                                                                    </button>
                                                                    <button type="submit" class="btn btn-primary">Reenviar</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </dialog>
                                                    @if($isOwner)
                                                        <dialog id="share-modal-{{ $certificate->id }}" class="modal">
                                                            <div class="modal-box">
                                                                <h3 class="font-bold text-lg mb-2">Compartir constancia</h3>
                                                                <p class="text-sm opacity-70 mb-4">
                                                                    Selecciona los usuarios que podrán ver esta constancia.
                                                                </p>
                                                                <form method="POST"
                                                                    action="{{ route('certificates.share', $certificate) }}"
                                                                    class="space-y-4">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="max-h-64 overflow-y-auto space-y-2 border border-base-200 rounded-lg p-3">
                                                                        @forelse($shareableUsers as $user)
                                                                            <label class="flex items-center gap-2 text-sm">
                                                                                <input type="checkbox" name="shared_users[]"
                                                                                    value="{{ $user->id }}"
                                                                                    class="checkbox checkbox-primary checkbox-xs"
                                                                                    {{ $certificate->sharedUsers->contains($user->id) ? 'checked' : '' }} />
                                                                                <span>{{ $user->name }}</span>
                                                                            </label>
                                                                        @empty
                                                                            <div class="text-xs opacity-60">
                                                                                No hay usuarios disponibles para compartir.
                                                                            </div>
                                                                        @endforelse
                                                                    </div>
                                                                    <div class="flex justify-end gap-2">
                                                                        <button type="button" class="btn btn-ghost"
                                                                            onclick="document.getElementById('share-modal-{{ $certificate->id }}').close()">
                                                                            Cancelar
                                                                        </button>
                                                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </dialog>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                {{ $certificates->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($isOwner)
                    <!-- Error Log -->
                    <div class="card bg-base-100 shadow-xl border border-error/20" x-show="errors.length"
                        style="display: none;">
                        <div class="card-body">
                            <h2 class="card-title text-error text-lg mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Registro de Errores
                            </h2>
                            <div class="overflow-x-auto max-h-60">
                                <table class="table table-zebra table-compact w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Descripci&oacute;n del Error</th>
                                            <th>Hora</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="error in errors" :key="error.email + error.time">
                                            <tr>
                                                <td class="font-mono font-bold" x-text="error.email ?? 'N/A'"></td>
                                                <td class="text-error" x-text="error.error ?? 'Error desconocido'"></td>
                                                <td class="opacity-60 whitespace-nowrap" x-text="error.time ?? ''"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Document Preview -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-lg h-full">
                    <div class="card-body p-0 flex flex-col h-full">
                        <div class="p-4 border-b">
                            <h3 class="font-bold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Previsualizaci&oacute;n
                            </h3>
                            <p class="text-xs opacity-70">Diseño base de la constancia a enviar</p>
                        </div>
                        <div class="bg-base-200 flex-1 flex items-center justify-center relative min-h-[400px]">
                            @if($isOwner && $history->documentConfiguration)
                                <iframe
                                    src="{{ route('document-configurations.stream-pdf', $history->documentConfiguration) }}#toolbar=0&navpanes=0&scrollbar=0&view=Fit"
                                    class="absolute inset-0 w-full h-full" frameborder="0"></iframe>
                            @else
                                <div class="text-center p-6 opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p>Vista previa disponible solo para el creador</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sendingStatus', (initial) => ({
                id: initial.id,
                total: Number(initial.total_registros || initial.total || 0),
                success: Number(initial.procesados_exitosos || initial.success || 0),
                failed: Number(initial.procesados_fallidos || initial.failed || 0),
                errors: initial.errores || initial.errors || [],
                updatedAt: initial.updated_at,
                statusUrl: '{{ route('certificate-sending.status', $history->id) }}',
                poller: null,
                get processed() {
                    return (this.success || 0) + (this.failed || 0);
                },
                get progress() {
                    return this.total > 0 ? Math.min(100, Math.round((this.processed / this.total) * 100)) : 0;
                },
                get completed() {
                    return this.processed >= this.total && this.total > 0;
                },
                get lastUpdatedLabel() {
                    return this.updatedAt ? `Actualizado: ${this.updatedAt}` : '';
                },
                init() {
                    this.startPolling();
                },
                startPolling() {
                    if (this.completed) {
                        return;
                    }

                    this.poller = setInterval(() => this.fetchStatus(), 3000);
                },
                async fetchStatus() {
                    try {
                        const response = await fetch(this.statusUrl);
                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();
                        this.success = data.success;
                        this.failed = data.failed;
                        this.total = data.total;
                        this.errors = data.errors ?? [];
                        this.updatedAt = data.updated_at ?? this.updatedAt;

                        if (this.completed && this.poller) {
                            clearInterval(this.poller);
                        }
                    } catch (error) {
                        console.error('No se pudo actualizar el estado', error);
                    }
                }
            }));
        });
    </script>
</x-app-layout>