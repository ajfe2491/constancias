<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Historial de Envíos') }}
        </h2>
    </x-slot>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Historial de Envíos</h1>
            <p class="text-sm opacity-60">Gestiona y monitorea el envío de constancias</p>
        </div>
        <a href="{{ route('certificate-sending.create') }}" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo Envío
        </a>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Total</th>
                            <th>Exitosos</th>
                            <th>Fallidos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body">
                        @forelse($history as $record)
                            <tr class="hover">
                                <td>
                                    <div class="font-bold">{{ $record->created_at?->format('d/m/Y') ?? 'N/A' }}</div>
                                    <div class="text-xs opacity-50">{{ $record->created_at?->format('H:i') ?? '' }}</div>
                                </td>
                                <td>{{ $record->user->name ?? 'Sistema' }}</td>
                                <td>{{ $record->total_registros }}</td>
                                <td class="text-success font-bold">{{ $record->procesados_exitosos }}</td>
                                <td class="text-error font-bold">{{ $record->procesados_fallidos }}</td>
                                <td>
                                    @if($record->procesados_exitosos + $record->procesados_fallidos >= $record->total_registros)
                                        <div class="badge badge-success gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Completado
                                        </div>
                                    @else
                                        <div class="badge badge-warning gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 animate-spin" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Procesando
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('certificate-sending.show', $record->id) }}"
                                        class="btn btn-ghost btn-xs">
                                        Ver Detalles
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 opacity-50">
                                    No hay envíos registrados aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $history->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function checkAndReload() {
                // Check if there are any processing spinners
                if (document.querySelector('.animate-spin')) {
                    setTimeout(function () {
                        fetch(window.location.href)
                            .then(response => response.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newBody = doc.querySelector('#history-table-body');
                                if (newBody) {
                                    document.querySelector('#history-table-body').innerHTML = newBody.innerHTML;
                                    // Continue checking
                                    checkAndReload();
                                }
                            })
                            .catch(error => console.error('Error updating history:', error));
                    }, 3000); // Check every 3 seconds
                }
            }

            checkAndReload();
        });
    </script>
</x-app-layout>