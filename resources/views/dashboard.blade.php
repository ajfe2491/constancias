<x-app-layout>
    <x-slot name="header">
        {{ __('Panel de Control') }}
    </x-slot>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Welcome Section -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-secondary p-8 text-primary-content shadow-xl"
                data-gsap="hero">
                <div class="relative z-10">
                    <h2 class="text-3xl font-bold mb-2">¡Hola, {{ Auth::user()->name }}!</h2>
                    <p class="opacity-90 text-lg max-w-xl">Bienvenido al <strong>SIICE</strong> (Sistema Integral de Identificación y Certificación Electrónica). Aquí tienes un resumen de la actividad reciente.</p>
                    <div class="mt-6 flex gap-3">
                        @can('enviar constancias')
                            <a href="{{ route('certificate-sending.create') }}"
                                class="btn btn-sm bg-white/20 border-0 text-white hover:bg-white/30 backdrop-blur">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Nueva Emisión
                            </a>
                        @endcan
                    </div>
                </div>
                <!-- Decorative circles -->
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-black/10 rounded-full blur-2xl"></div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6" data-gsap="stats">
                <!-- Total Constancias -->
                <div class="stats shadow bg-base-100 border border-base-200" data-gsap-item>
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
                        <div class="stat-value text-primary">{{ number_format($totalCertificates) }}</div>
                        <div class="stat-desc">Emitidas en total</div>
                    </div>
                </div>

                <!-- Tasa de Éxito Global -->
                <div class="stats shadow bg-base-100 border border-base-200" data-gsap-item>
                    <div class="stat">
                        <div class="stat-figure text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="stat-title opacity-70">Tasa de Éxito</div>
                        <div class="stat-value text-success">{{ $globalSuccessRate }}%</div>
                        <div class="stat-desc">Procesamiento Global</div>
                    </div>
                </div>

                <!-- Eventos Activos -->
                <div class="stats shadow bg-base-100 border border-base-200" data-gsap-item>
                    <div class="stat">
                        <div class="stat-figure text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="stat-title opacity-70">Eventos</div>
                        <div class="stat-value text-accent">{{ $activeEvents }}</div>
                        <div class="stat-desc">Registrados</div>
                    </div>
                </div>

                <!-- Plantillas -->
                <div class="stats shadow bg-base-100 border border-base-200" data-gsap-item>
                    <div class="stat">
                        <div class="stat-figure text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 011.414.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2">
                                </path>
                            </svg>
                        </div>
                        <div class="stat-title opacity-70">Plantillas</div>
                        <div class="stat-value text-secondary">{{ $totalTemplates }}</div>
                        <div class="stat-desc">Configuradas</div>
                    </div>
                </div>

                <!-- Usuarios -->
                <div class="stats shadow bg-base-100 border border-base-200" data-gsap-item>
                    <div class="stat">
                        <div class="stat-figure text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </div>
                        <div class="stat-title opacity-70">Usuarios</div>
                        <div class="stat-value text-info">{{ $totalUsers }}</div>
                        <div class="stat-desc">En el sistema</div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" data-gsap="charts">
                <!-- Line Chart: Monthly Trends -->
                <div class="lg:col-span-2 card bg-base-100 shadow-xl border border-base-200" data-gsap-item>
                    <div class="card-body">
                        <h3 class="font-bold text-lg mb-4">Tendencia de Envíos (Últimos 6 meses)</h3>
                        <div class="h-64">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Doughnut Chart: Top Events -->
                <div class="card bg-base-100 shadow-xl border border-base-200" data-gsap-item>
                    <div class="card-body">
                        <h3 class="font-bold text-lg mb-4">Top Eventos</h3>
                        <div class="h-64 flex items-center justify-center">
                            @if(count($eventLabels) > 0)
                                <canvas id="eventsChart"></canvas>
                            @else
                                <p class="text-sm opacity-50">Sin datos suficientes</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Analytics Section (Row 2) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" data-gsap="analytics">
                <!-- Doughnut Chart: Events by Type -->
                <div class="card bg-base-100 shadow-xl border border-base-200" data-gsap-item>
                    <div class="card-body">
                        <h3 class="font-bold text-lg mb-4">Tipos de Eventos</h3>
                        <div class="h-64 flex items-center justify-center">
                            @if(count($eventTypeLabels) > 0)
                                <canvas id="eventTypesChart"></canvas>
                            @else
                                <p class="text-sm opacity-50">Sin datos suficientes</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Failure Analysis List -->
                <div class="lg:col-span-2 card bg-base-100 shadow-xl border border-base-200" data-gsap-item>
                    <div class="card-body">
                        <h3 class="font-bold text-lg mb-4">Análisis de Errores (Recientes)</h3>
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Total Registros</th>
                                        <th>Fallidos</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($failureStats as $fail)
                                        <tr>
                                            <td>{{ $fail['date'] }}</td>
                                            <td>{{ $fail['total'] }}</td>
                                            <td class="text-error font-bold">{{ $fail['failed'] }}</td>
                                            <td>
                                                <div class="badge badge-error gap-2">
                                                    {{ round(($fail['failed'] / $fail['total']) * 100, 1) }}% Fallo
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-8 text-success">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                ¡Todo excelente! No hay errores recientes.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" data-gsap="activity">
                <!-- Recent Activity Table -->
                <div class="lg:col-span-2 card bg-base-100 shadow-xl border border-base-200" data-gsap-item>
                    <div class="card-body p-0">
                        <div class="p-6 border-b border-base-200 flex justify-between items-center">
                            <h3 class="font-bold text-lg">Últimos Envíos</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th>Plantilla</th>
                                        <th>Enviado Por</th>
                                        <th>Cantidad</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentBatches as $batch)
                                        <tr class="hover">
                                            <td>
                                                <div class="font-bold">{{ $batch->documentConfiguration->document_name ?? 'Sin nombre' }}</div>
                                                <div class="text-sm opacity-50">{{ $batch->documentConfiguration->event->name ?? 'Evento General' }}</div>
                                            </td>
                                            <td>
                                                {{ $batch->user->name ?? 'Sistema' }}
                                            </td>
                                            <td>
                                                <div class="badge badge-success gap-2">{{ $batch->procesados_exitosos }} constancias</div>
                                            </td>
                                            <td class="text-sm opacity-70">{{ $batch->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-8 text-opacity-50">
                                                No hay actividad reciente.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions / Side Widgets -->
                <div class="space-y-6" data-gsap-item>
                    <!-- Quick Actions Card -->
                    <div class="card bg-base-100 shadow-xl border border-base-200">
                        <div class="card-body">
                            <h3 class="card-title text-sm opacity-70 uppercase tracking-wider mb-4">Accesos Rápidos</h3>
                            <div class="grid grid-cols-2 gap-3">
                                @can('enviar constancias')
                                    <a href="{{ route('certificate-sending.create') }}"
                                        class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:bg-primary hover:text-white hover:border-primary group">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span class="text-xs font-normal">Nuevo Envío</span>
                                    </a>
                                @endcan

                                @can('gestionar eventos')
                                    <a href="{{ route('events.create') }}"
                                        class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:bg-accent hover:text-white hover:border-accent group">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-xs font-normal">Crear Evento</span>
                                    </a>
                                @endcan

                                @can('gestionar plantillas')
                                    <a href="{{ route('document-configurations.index') }}"
                                        class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:bg-secondary hover:text-white hover:border-secondary group">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-xs font-normal">Plantillas</span>
                                    </a>
                                @endcan

                                @can('gestionar usuarios')
                                    <a href="{{ route('users.index') }}"
                                        class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:bg-info hover:text-white hover:border-info group">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <span class="text-xs font-normal">Usuarios</span>
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.gsap) {
                const hasScrollTrigger = !!window.ScrollTrigger;
                if (hasScrollTrigger) {
                    gsap.registerPlugin(ScrollTrigger);
                }

                const animateSection = (selector, config) => {
                    const group = document.querySelector(selector);
                    if (!group) return;
                    const items = group.querySelectorAll('[data-gsap-item]');
                    if (!items.length) return;
                    const {
                        stagger = 0.12,
                        scrollStart = 'top 85%',
                        ...baseVars
                    } = config;

                    items.forEach((item, index) => {
                        const vars = {
                            ...baseVars,
                            delay: stagger ? index * stagger : 0
                        };
                        if (hasScrollTrigger) {
                            vars.scrollTrigger = {
                                trigger: item,
                                start: scrollStart,
                                toggleActions: 'play none none none'
                            };
                        }
                        gsap.from(item, vars);
                    });
                };

                const hero = document.querySelector('[data-gsap="hero"]');
                if (hero) {
                    const heroAnimation = {
                        opacity: 0,
                        y: 24,
                        scale: 0.98,
                        duration: 1.4,
                        ease: 'power4.out'
                    };
                    if (hasScrollTrigger) {
                        gsap.from(hero, {
                            ...heroAnimation,
                            scrollTrigger: {
                                trigger: hero,
                                start: 'top 85%',
                                toggleActions: 'play none none none'
                            }
                        });
                    } else {
                        gsap.from(hero, heroAnimation);
                    }
                }

                animateSection('[data-gsap="stats"]', {
                    opacity: 0,
                    y: 30,
                    scale: 0.96,
                    duration: 1.1,
                    ease: 'back.out(1.5)',
                    stagger: 0.14
                });

                animateSection('[data-gsap="charts"]', {
                    opacity: 0,
                    x: -50,
                    duration: 1.3,
                    ease: 'power3.out',
                    stagger: 0.16
                });

                animateSection('[data-gsap="analytics"]', {
                    opacity: 0,
                    x: 50,
                    rotation: 1.5,
                    duration: 1.3,
                    ease: 'power2.out',
                    stagger: 0.16
                });

                animateSection('[data-gsap="activity"]', {
                    opacity: 0,
                    y: 50,
                    skewY: 1.5,
                    duration: 1.4,
                    ease: 'expo.out',
                    stagger: 0.18
                });
            }

            // Trend Chart
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: @json($months),
                        datasets: [{
                            label: 'Constancias Emitidas',
                            data: @json($monthlyCounts),
                            borderColor: '#570df8', // primary color
                            backgroundColor: 'rgba(87, 13, 248, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Events Chart
            const eventCtx = document.getElementById('eventsChart');
            if (eventCtx) {
                new Chart(eventCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($eventLabels),
                        datasets: [{
                            data: @json($eventCounts),
                            backgroundColor: [
                                '#570df8', // primary
                                '#f000b8', // secondary
                                '#37cdbe', // accent
                                '#3d4451', // neutral
                                '#ff5724', // warning/other
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // Event Types Chart
            const eventTypesCtx = document.getElementById('eventTypesChart');
            if (eventTypesCtx) {
                new Chart(eventTypesCtx, {
                    type: 'pie', // Using Pie for variety
                    data: {
                        labels: @json($eventTypeLabels),
                        datasets: [{
                            data: @json($eventTypeCounts),
                            backgroundColor: [
                                '#37cdbe', // accent
                                '#570df8', // primary
                                '#f000b8', // secondary
                                '#3d4451', // neutral
                                '#ff5724', // warning
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>