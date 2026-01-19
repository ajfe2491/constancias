<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sistema de Constancias') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .gradient-text {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="antialiased bg-gray-50 text-gray-800 h-screen flex flex-col overflow-hidden relative">

    <!-- Decoration Blobs -->
    <div
        class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob">
    </div>
    <div
        class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-purple-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000">
    </div>

    <!-- Header / Nav -->
    <header class="w-full py-6 px-8 flex justify-between items-center z-10">
        <div class="flex items-center gap-2">
            <!-- Logo Icon (SVG) -->
            <div class="bg-indigo-600 text-white p-2 rounded-lg shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-xl font-bold text-gray-900 tracking-tight">Constancias</span>
        </div>

        <nav class="flex items-center gap-4">
            @if (Route::has('login'))
                @auth
                    @can('ver dashboard')
                        <a href="{{ url('/dashboard') }}"
                            class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('profile.edit') }}"
                            class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">Mi Perfil</a>
                    @endcan

                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-sm font-medium text-gray-500 hover:text-red-500 transition-colors border-l border-gray-300 pl-4 ml-2">
                            Cerrar Sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors mr-4">Iniciar
                        Sesión</a>
                @endauth
            @endif
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center relative z-10 px-4">
        <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- Left Column: Interactions -->
            <div class="space-y-8 animate-fade-in-up">
                <div>
                    <span
                        class="inline-block py-1 px-3 rounded-full bg-indigo-50 text-indigo-600 text-xs font-semibold uppercase tracking-wider mb-4 border border-indigo-100">
                        Gestión Administrativa
                    </span>
                    <h1 class="text-5xl lg:text-6xl font-extrabold leading-tight text-gray-900 mb-6">
                        Gestión de <br>
                        <span class="gradient-text">Constancias</span> <br>
                        Simplificada.
                    </h1>
                    <p class="text-lg text-gray-600 leading-relaxed max-w-md">
                        Una plataforma centralizada para la administración de eventos, control de usuarios y la emisión
                        automatizada de documentos oficiales.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    @if (Route::has('login'))
                        @auth
                            @can('ver dashboard')
                                <a href="{{ url('/dashboard') }}"
                                    class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all duration-200 shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-1">
                                    Ir al Dashboard
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('profile.edit') }}"
                                    class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all duration-200 shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-1">
                                    Ir a Mi Perfil
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endcan
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all duration-200 shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-1">
                                Iniciar Sesión
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endauth
                    @endif
                </div>

                <!-- Features Preview (Small) -->
                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div class="flex items-center gap-3">
                        <div class="h-2 w-2 rounded-full bg-green-400"></div>
                        <span class="text-sm font-medium text-gray-500">Gestión de Eventos</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-2 w-2 rounded-full bg-blue-400"></div>
                        <span class="text-sm font-medium text-gray-500">Plantillas Dinámicas</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-2 w-2 rounded-full bg-purple-400"></div>
                        <span class="text-sm font-medium text-gray-500">Envío Masivo</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-2 w-2 rounded-full bg-pink-400"></div>
                        <span class="text-sm font-medium text-gray-500">Validación QR</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Abstract/Glass Illustration -->
            <div class="relative hidden lg:block animate-float">
                <div
                    class="relative bg-white/40 backdrop-blur-xl border border-white/50 rounded-2xl shadow-2xl p-8 transform rotate-2 hover:rotate-0 transition-transform duration-500">
                    <!-- Fake UI Mockup -->
                    <div class="flex items-center justify-between mb-6 border-b border-gray-200/50 pb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="h-2 w-20 bg-gray-200 rounded-full"></div>
                    </div>

                    <div class="space-y-4">
                        <div
                            class="h-32 bg-indigo-50/50 rounded-xl border border-indigo-100 flex items-center justify-center">
                            <span class="text-indigo-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="h-2 bg-gray-200 rounded-full w-full"></div>
                            <div class="h-2 bg-gray-200 rounded-full w-full"></div>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full w-3/4"></div>

                        <div class="pt-4 flex justify-end">
                            <div class="h-8 w-24 bg-indigo-600 rounded-lg shadow-md"></div>
                        </div>
                    </div>
                </div>

                <!-- Floating Elements -->
                <div
                    class="absolute -bottom-6 -left-6 bg-white p-4 rounded-xl shadow-xl border border-gray-100 animate-bounce-custom">
                    <div class="flex items-center gap-3">
                        <div class="bg-green-100 p-2 rounded-lg text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Estado</p>
                            <p class="text-sm font-bold text-gray-900">Enviado</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="w-full py-4 text-center z-10">
        <p class="text-xs text-gray-500">© {{ date('Y') }} Sistema de Constancias. Todos los derechos reservados.</p>
    </footer>

    <style>
        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }

            100% {
                transform: translate(0px, 0px) scale(1);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-bounce-custom {
            animation: bounce-custom 3s infinite;
        }

        @keyframes bounce-custom {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>

</html>