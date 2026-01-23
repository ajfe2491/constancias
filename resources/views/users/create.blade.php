<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Nuevo Usuario</h2>

                    <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Name -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Nombre</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full"
                                required autofocus />
                            <label class="label">
                                <span class="label-text-alt text-warning">"admin@siice.com" está reservado</span>
                            </label>
                            @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="input input-bordered w-full" required />
                            @error('email') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Contraseña</span>
                            </label>
                            <input type="password" name="password" class="input input-bordered w-full" required />
                            @error('password') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Confirmar Contraseña</span>
                            </label>
                            <input type="password" name="password_confirmation" class="input input-bordered w-full"
                                required />
                        </div>

                        <!-- Roles -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Asignar Roles</span>
                            </label>
                            <div class="flex flex-col gap-2">
                                @foreach($roles as $role)
                                    <label class="label cursor-pointer justify-start gap-3">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                            class="checkbox checkbox-primary" />
                                        <span class="label-text">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('roles') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="card-actions justify-end mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>