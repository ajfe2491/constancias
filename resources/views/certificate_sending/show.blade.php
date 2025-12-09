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

    <div x-data="sendingStatus(@json($statusPayload))" class="space-y-6">
        <div class="mb-2">
            <a href="{{ route('certificate-sending.index') }}" class="btn btn-ghost gap-2 pl-0 hover:bg-transparent">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver al historial
            </a>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end mt-2 gap-3">
                <div>
                    <h1 class="text-2xl font-bold">Detalles del Envío #{{ $history->id }}</h1>
                    <p class="text-sm opacity-60">Iniciado el {{ $history->created_at?->format('d/m/Y H:i') ?? 'N/A' }} por
                        {{ $history->user->name ?? 'Sistema' }}
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <div class="badge" :class="completed ? 'badge-success gap-2' : 'badge-warning gap-2'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                :class="completed ? '' : 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span x-text="completed ? 'Completado' : 'En proceso'"></span>
                        </div>
                        <span class="text-xs opacity-70" x-text="lastUpdatedLabel"></span>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if($history->csv_file_path)
                        <a href="{{ asset('storage/' . $history->csv_file_path) }}" target="_blank"
                            class="btn btn-outline btn-sm gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar CSV
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-sm font-semibold mb-2">Progreso del envío</p>
                        <progress class="progress progress-primary w-full" :value="processed" :max="total"></progress>
                        <div class="flex justify-between text-xs opacity-70 mt-2">
                            <span x-text="`${progress}% completado`"></span>
                            <span x-text="`${processed}/${total} procesados`"></span>
                        </div>
                    </div>
                    <div class="text-center md:w-48">
                        <p class="text-sm opacity-70">Estado</p>
                        <p class="text-xl font-bold" x-text="completed ? 'Listo' : 'Enviando...'"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="stats shadow bg-base-100">
                <div class="stat">
                    <div class="stat-title">Total Registros</div>
                    <div class="stat-value" x-text="total">{{ $history->total_registros }}</div>
                    <div class="stat-desc">Filas en el CSV o formulario</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100">
                <div class="stat">
                    <div class="stat-title">Enviados Exitosamente</div>
                    <div class="stat-value text-success" x-text="success">{{ $history->procesados_exitosos }}</div>
                    <div class="stat-desc">Correos entregados</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100">
                <div class="stat">
                    <div class="stat-title">Fallidos</div>
                    <div class="stat-value text-error" x-text="failed">{{ $history->procesados_fallidos }}</div>
                    <div class="stat-desc">Errores de envío</div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl border border-error/20" x-show="errors.length">
            <div class="card-body">
                <h2 class="card-title text-error text-lg mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Registro de Errores
                </h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full text-sm">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Error</th>
                                <th>Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="error in errors" :key="error.email + error.time">
                                <tr>
                                    <td class="font-mono" x-text="error.email ?? 'N/A'"></td>
                                    <td class="text-error" x-text="error.error ?? 'Error desconocido'"></td>
                                    <td class="opacity-60" x-text="error.time ?? ''"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sendingStatus', (initial) => ({
                id: initial.id,
                total: initial.total,
                success: initial.success,
                failed: initial.failed,
                errors: initial.errors ?? [],
                updatedAt: initial.updated_at,
                statusUrl: '{{ route('certificate-sending.status', ['history' => $history->id]) }}',
                statusUrl: '{{ route('certificate-sending.status', $history) }}',
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
