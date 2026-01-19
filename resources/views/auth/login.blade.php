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



        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Correo electrónico -->
            <div class="form-control">
                <x-input-label for="email" value="Correo electrónico" />
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="username" class="input input-bordered w-full mt-1" />
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

                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="input input-bordered w-full mt-1" />

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