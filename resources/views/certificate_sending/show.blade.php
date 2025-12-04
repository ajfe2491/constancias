<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Envío') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <a href="{{ route('certificate-sending.index') }}" class="btn btn-ghost gap-2 pl-0 hover:bg-transparent">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver al historial
        </a>
        <div class="flex justify-between items-end mt-2">
            <div>
                <h1 class="text-2xl font-bold">Detalles del Envío #{{ $history->id }}</h1>
                <p class="text-sm opacity-60">Iniciado el {{ $history->created_at?->format('d/m/Y H:i') ?? 'N/A' }} por
                    {{ $history->user->name ?? 'Sistema' }}
                </p>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stats shadow bg-base-100">
            <div class="stat">
                <div class="stat-title">Total Registros</div>
                <div class="stat-value">{{ $history->total_registros }}</div>
                <div class="stat-desc">Filas en el CSV</div>
            </div>
        </div>

        <div class="stats shadow bg-base-100">
            <div class="stat">
                <div class="stat-title">Enviados Exitosamente</div>
                <div class="stat-value text-success">{{ $history->procesados_exitosos }}</div>
                <div class="stat-desc">Correos entregados</div>
            </div>
        </div>

        <div class="stats shadow bg-base-100">
            <div class="stat">
                <div class="stat-title">Fallidos</div>
                <div class="stat-value text-error">{{ $history->procesados_fallidos }}</div>
                <div class="stat-desc">Errores de envío</div>
            </div>
        </div>
    </div>

    @if(!empty($history->errores))
        <div class="card bg-base-100 shadow-xl border border-error/20">
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
                            @foreach($history->errores as $error)
                                <tr>
                                    <td class="font-mono">{{ $error['email'] ?? 'N/A' }}</td>
                                    <td class="text-error">{{ $error['error'] ?? 'Unknown error' }}</td>
                                    <td class="opacity-60">{{ $error['time'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>