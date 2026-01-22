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
            <div class="form-control space-y-1" x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <x-input-label for="password" value="Contraseña" />

                    @if (Route::has('password.request'))
                        <a class="text-xs link link-primary" href="{{ route('password.request') }}">
                            {{ __('¿Olvidaste tu contraseña?') }}
                        </a>
                    @endif
                </div>

                <div class="relative">
                    <input id="password" x-bind:type="show ? 'text' : 'password'" name="password" required
                        autocomplete="current-password" class="input input-bordered w-full mt-1 pr-12" />

                    <button type="button"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-gray-700 cursor-pointer transition-colors"
                        @click="show = !show">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

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