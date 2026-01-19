<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Rol') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Nuevo Rol</h2>

                    <form action="{{ route('roles.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Name -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Nombre del Rol</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full"
                                required autofocus placeholder="Ej: Editor, Visor" />
                            @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Permissions -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-bold">Asignar Permisos</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($permissions as $permission)
                                    <label
                                        class="label cursor-pointer justify-start gap-3 border border-base-200 rounded p-2 hover:bg-base-200">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                            class="checkbox checkbox-sm checkbox-primary" />
                                        <span class="label-text text-sm">{{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('permissions') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="card-actions justify-end mt-4">
                            <a href="{{ route('roles.index') }}" class="btn btn-ghost">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Rol</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>