<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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

<body class="font-sans antialiased bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <div class="min-h-screen flex flex-col bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
        <!-- Header (Static, full width) -->
        <div class="w-full px-6 pt-6 relative z-30"
            style="position: sticky; top: 0; z-index: 9999;">
            <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-application-logo class="w-16 h-16 drop-shadow-md" />
                </div>

                <a  class="flex flex-col items-center group text-center">
                    <span
                        class="block text-5xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-primary via-secondary to-accent drop-shadow-sm pb-1">
                        SIICE
                    </span>
                    <div class="h-1 w-24 mx-auto bg-gradient-to-r from-transparent via-base-content/20 to-transparent my-1">
                    </div>
                    <span class="block text-xs font-bold text-base-content/70 uppercase tracking-[0.2em] leading-relaxed">
                        Sistema Integral de<br>Identificación y Certificación
                    </span>
                </a>

                <button type="button" data-theme-toggle onclick="window.__toggleTheme && window.__toggleTheme()"
                    class="inline-flex items-center px-3 py-2 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 shadow-sm transition-colors cursor-pointer pointer-events-auto select-none"
                    style="cursor: pointer; pointer-events: auto; position: relative; z-index: 9999;">
                    <span data-theme-label="dark">Modo oscuro</span>
                    <span data-theme-label="light" class="hidden">Modo claro</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col justify-center items-center">
            <div class="w-full sm:max-w-5xl lg:max-w-6xl px-6">
                {{ $slot }}
            </div>
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
        @if ($errors->any() && !session('error'))
            <div x-init="add({{ json_encode($errors->first()) }}, 'error')"></div>
        @endif
    </div>
    <script>
        (() => {
            const root = document.documentElement;

            const updateLabels = (isDark) => {
                document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
                    const darkLabel = btn.querySelector('[data-theme-label="dark"]');
                    const lightLabel = btn.querySelector('[data-theme-label="light"]');
                    if (!darkLabel || !lightLabel) return;
                    darkLabel.classList.toggle('hidden', isDark);
                    lightLabel.classList.toggle('hidden', !isDark);
                });
            };

            const setTheme = (isDark) => {
                root.classList.toggle('dark', isDark);
                root.setAttribute('data-theme', isDark ? 'dark' : 'light');
                updateLabels(isDark);
                window.localStorage.setItem('theme', isDark ? 'dark' : 'light');
            };

            const storedTheme = window.localStorage.getItem('theme');
            const prefersDark = window.matchMedia &&
                window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (storedTheme === 'dark' || storedTheme === 'light') {
                setTheme(storedTheme === 'dark');
            } else {
                setTheme(prefersDark);
            }

            window.__toggleTheme = () => {
                setTheme(!root.classList.contains('dark'));
            };

            const findToggleFromEvent = (event) => {
                const direct = event.target.closest && event.target.closest('[data-theme-toggle]');
                if (direct) return direct;
                const toggles = Array.from(document.querySelectorAll('[data-theme-toggle]'));
                if (!toggles.length) return null;
                const { clientX, clientY } = event;
                return toggles.find((btn) => {
                    const rect = btn.getBoundingClientRect();
                    return clientX >= rect.left && clientX <= rect.right &&
                        clientY >= rect.top && clientY <= rect.bottom;
                }) || null;
            };

            document.addEventListener('click', (event) => {
                const button = findToggleFromEvent(event);
                if (!button) return;
                event.preventDefault();
                window.__toggleTheme();
            }, true);
        })();
    </script>
</body>

</html>
