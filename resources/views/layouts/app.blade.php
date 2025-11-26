<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeSwitcher()" x-init="init()">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-base-100 text-base-content">
    <div class="drawer lg:drawer-open">
        <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-base-300 bg-base-100/50 backdrop-blur sticky top-0 z-30">
                    <div class="flex items-center justify-between px-4 py-3 lg:px-6">
                        <div class="flex items-center gap-4">
                            <label for="my-drawer-2" class="btn btn-square btn-ghost lg:hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    class="inline-block w-6 h-6 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </label>
                            <div class="text-lg font-semibold">
                                {{ $header }}
                            </div>
                        </div>
                        <!-- Header Actions (Profile, etc) -->
                        <div class="flex items-center gap-2">
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-circle avatar placeholder">
                                    <div class="bg-neutral-focus text-neutral-content rounded-full w-10">
                                        <span class="text-xl">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                </label>
                                <ul tabindex="0"
                                    class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-200 rounded-box w-52">
                                    <li><a href="{{ route('profile.edit') }}">Perfil</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit">Cerrar sesión</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="p-4 lg:p-6 flex-1">
                {{ $slot }}
            </main>
        </div>
        <div class="drawer-side z-40">
            <label for="my-drawer-2" class="drawer-overlay"></label>
            @include('layouts.navigation')
        </div>
    </div>
    <!-- Toast Notifications -->
    <div x-data="{ 
        notifications: [],
        add(message, type = 'success') {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            setTimeout(() => this.remove(id), 3000);
        },
        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }" @notify.window="add($event.detail.message, $event.detail.type)" class="toast toast-end toast-bottom z-50">
        <template x-for="notification in notifications" :key="notification.id">
            <div class="alert shadow-lg transition-all duration-500 ease-in-out" :class="{
                    'alert-success': notification.type === 'success',
                    'alert-error': notification.type === 'error',
                    'alert-warning': notification.type === 'warning',
                    'alert-info': notification.type === 'info'
                }" x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">

                <svg x-show="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg"
                    class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="notification.type === 'error'" xmlns="http://www.w3.org/2000/svg"
                    class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="notification.type === 'warning'" xmlns="http://www.w3.org/2000/svg"
                    class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg x-show="notification.type === 'info'" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>

                <span x-text="notification.message"></span>
            </div>
        </template>

        <!-- Init Session Messages -->
        @if (session('success'))
            <div x-init="add('{{ session('success') }}', 'success')"></div>
        @endif
        @if (session('error'))
            <div x-init="add('{{ session('error') }}', 'error')"></div>
        @endif
        @if (session('warning'))
            <div x-init="add('{{ session('warning') }}', 'warning')"></div>
        @endif
        @if (session('info'))
            <div x-init="add('{{ session('info') }}', 'info')"></div>
        @endif
    </div>
</body>

</html>