<x-guest-layout>
    <!-- Estado de sesión -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="space-y-6">
        <header class="text-center space-y-1">
            <h1 class="text-2xl font-bold tracking-tight">
                Bienvenido de nuevo
            </h1>
            <p class="text-sm opacity-70">
                Inicia sesión para administrar constancias y participantes.
            </p>
        </header>

        <div class="space-y-3">
            <a
                href="{{ route('auth.google.redirect') }}"
                class="btn btn-outline btn-primary w-full gap-2 justify-center"
            >
                <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.36 13.02 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.14-3.08-.39-4.55H24v9.02h12.94c-.56 2.9-2.23 5.36-4.74 7.01l7.64 5.93C43.9 37.9 46.98 31.8 46.98 24.55z"/>
                    <path fill="#FBBC05" d="M10.54 28.03C9.96 26.3 9.64 24.47 9.64 22.55c0-1.92.32-3.75.9-5.48l-7.98-6.2C.96 14.45 0 18.11 0 22c0 3.82.94 7.44 2.59 10.64l7.95-4.61z"/>
                    <path fill="#34A853" d="M24 47.5c6.48 0 11.93-2.13 15.9-5.8l-7.64-5.93c-2.12 1.43-4.84 2.27-8.26 2.27-6.26 0-11.63-3.52-14.29-8.69l-7.98 6.2C6.51 42.62 14.62 47.5 24 47.5z"/>
                </svg>
                <span>Continuar con Google</span>
            </a>

            <div class="flex items-center gap-3 text-xs uppercase tracking-wide opacity-60">
                <div class="h-px flex-1 bg-base-200"></div>
                <span>o con correo</span>
                <div class="h-px flex-1 bg-base-200"></div>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Correo electrónico -->
            <div class="form-control">
                <x-input-label for="email" value="Correo electrónico" />
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="input input-bordered w-full mt-1"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Contraseña -->
            <div class="form-control space-y-1">
                <div class="flex items-center justify-between">
                    <x-input-label for="password" value="Contraseña" />

                    @if (Route::has('password.request'))
                        <a class="text-xs link link-primary" href="{{ route('password.request') }}">
                            {{ __('¿Olvidaste tu contraseña?') }}
                        </a>
                    @endif
                </div>

                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="input input-bordered w-full mt-1"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Recuérdame / botón -->
            <div class="flex items-center justify-between">
                <label for="remember_me" class="label cursor-pointer gap-2">
                    <input id="remember_me" type="checkbox" name="remember" class="checkbox checkbox-primary" />
                    <span class="label-text">Recuérdame</span>
                </label>

                <button type="submit" class="btn btn-primary">
                    Iniciar sesión
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
