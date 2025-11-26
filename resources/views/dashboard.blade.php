<x-app-layout>
    <x-slot name="header">
        {{ __('Panel de Control') }}
    </x-slot>

    <div class="space-y-8">
        <!-- Welcome Section -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-secondary p-8 text-primary-content shadow-xl">
            <div class="relative z-10">
                <h2 class="text-3xl font-bold mb-2">¡Hola, {{ Auth::user()->name }}!</h2>
                <p class="opacity-90 text-lg max-w-xl">Bienvenido al sistema de gestión de constancias. Aquí tienes un
                    resumen de la actividad reciente.</p>
                <div class="mt-6 flex gap-3">
                    <button class="btn btn-sm bg-white/20 border-0 text-white hover:bg-white/30 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Nueva Emisión
                    </button>
                    <button class="btn btn-sm bg-white/20 border-0 text-white hover:bg-white/30 backdrop-blur">
                        Ver Reportes
                    </button>
                </div>
            </div>
            <!-- Decorative circles -->
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-black/10 rounded-full blur-2xl"></div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title opacity-70">Constancias</div>
                    <div class="stat-value text-primary">256</div>
                    <div class="stat-desc">Emitidas este mes</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title opacity-70">Participantes</div>
                    <div class="stat-value text-secondary">1,200</div>
                    <div class="stat-desc">↗︎ 40 (3%)</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-title opacity-70">Eventos</div>
                    <div class="stat-value text-accent">5</div>
                    <div class="stat-desc">Activos actualmente</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title opacity-70">Pendientes</div>
                    <div class="stat-value text-info">12</div>
                    <div class="stat-desc">Requieren atención</div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activity Table -->
            <div class="lg:col-span-2 card bg-base-100 shadow-xl border border-base-200">
                <div class="card-body p-0">
                    <div class="p-6 border-b border-base-200 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Actividad Reciente</h3>
                        <button class="btn btn-ghost btn-xs">Ver todo</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <!-- head -->
                            <thead>
                                <tr>
                                    <th>Participante</th>
                                    <th>Evento</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr class="hover">
                                    <td>
                                        <div class="flex items-center space-x-3">
                                            <div class="avatar placeholder">
                                                <div
                                                    class="mask mask-squircle w-10 h-10 bg-neutral-focus text-neutral-content">
                                                    <span>JP</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold">Juan Pérez</div>
                                                <div class="text-sm opacity-50">juan@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Congreso 2024
                                        <br />
                                        <span class="badge badge-ghost badge-sm">Ponencia</span>
                                    </td>
                                    <td>
                                        <div class="badge badge-success gap-2">Enviada</div>
                                    </td>
                                    <td class="text-sm opacity-70">Hace 2 horas</td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover">
                                    <td>
                                        <div class="flex items-center space-x-3">
                                            <div class="avatar placeholder">
                                                <div
                                                    class="mask mask-squircle w-10 h-10 bg-neutral-focus text-neutral-content">
                                                    <span>ML</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold">María López</div>
                                                <div class="text-sm opacity-50">maria@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Taller de Docker
                                        <br />
                                        <span class="badge badge-ghost badge-sm">Asistente</span>
                                    </td>
                                    <td>
                                        <div class="badge badge-warning gap-2">Pendiente</div>
                                    </td>
                                    <td class="text-sm opacity-70">Hace 5 horas</td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="hover">
                                    <td>
                                        <div class="flex items-center space-x-3">
                                            <div class="avatar placeholder">
                                                <div
                                                    class="mask mask-squircle w-10 h-10 bg-neutral-focus text-neutral-content">
                                                    <span>CR</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold">Carlos Ruiz</div>
                                                <div class="text-sm opacity-50">carlos@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Congreso 2024
                                        <br />
                                        <span class="badge badge-ghost badge-sm">Evaluador</span>
                                    </td>
                                    <td>
                                        <div class="badge badge-success gap-2">Enviada</div>
                                    </td>
                                    <td class="text-sm opacity-70">Ayer</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions / Side Widgets -->
            <div class="space-y-6">
                <!-- Quick Actions Card -->
                <div class="card bg-base-100 shadow-xl border border-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-sm opacity-70 uppercase tracking-wider mb-4">Accesos Rápidos</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:bg-primary hover:text-white hover:border-primary group">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-xs font-normal">Nueva Constancia</span>
                            </button>
                            <button
                                class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:bg-secondary hover:text-white hover:border-secondary group">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span class="text-xs font-normal">Buscar</span>
                            </button>
                            <button
                                class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:bg-accent hover:text-white hover:border-accent group">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span class="text-xs font-normal">Importar CSV</span>
                            </button>
                            <button
                                class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:bg-info hover:text-white hover:border-info group">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-xs font-normal">Ajustes</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- System Info Card -->
                <div class="card bg-base-100 shadow-xl border border-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-sm opacity-70 uppercase tracking-wider mb-2">Estado del Sistema</h3>
                        <div class="flex items-center justify-between py-2 border-b border-base-200">
                            <span class="text-sm">Base de Datos</span>
                            <span class="badge badge-success badge-sm">Conectado</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-base-200">
                            <span class="text-sm">Cola de Correos</span>
                            <span class="badge badge-ghost badge-sm">0 pendientes</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm">Almacenamiento</span>
                            <div class="radial-progress text-primary text-[10px]" style="--value:45; --size:2rem;">45%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>