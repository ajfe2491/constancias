<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Editar Usuario: {{ $user->name }}</h2>

                    <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Nombre</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="input input-bordered w-full {{ $user->name === 'admin@siice.com' ? 'cursor-not-allowed opacity-70' : '' }}"
                                required {{ $user->name === 'admin@siice.com' ? 'readonly' : '' }} />
                            @if($user->name === 'admin@siice.com')
                                <label class="label">
                                    <span class="label-text-alt text-warning">Nombre reservado (Super Admin)</span>
                                </label>
                            @endif
                            @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="input input-bordered w-full" required />
                            @error('email') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password (Optional) -->
                        <div class="divider text-xs opacity-50">Cambiar Contraseña (Opcional)</div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Nueva Contraseña</span>
                            </label>
                            <input type="password" name="password" class="input input-bordered w-full" />
                            <label class="label">
                                <span class="label-text-alt opacity-60">Dejar en blanco para mantener la actual</span>
                            </label>
                            @error('password') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Confirmar Nueva Contraseña</span>
                            </label>
                            <input type="password" name="password_confirmation" class="input input-bordered w-full" />
                        </div>

                        <!-- Roles -->
                        <div class="divider">Roles</div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Asignar Roles</span>
                            </label>
                            <div class="flex flex-col gap-2">
                                @foreach($roles as $role)
                                    <label class="label cursor-pointer justify-start gap-3">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                            class="checkbox checkbox-primary" {{ $user->hasRole($role->name) ? 'checked' : '' }} />
                                        <span class="label-text">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('roles') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="card-actions justify-end mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>