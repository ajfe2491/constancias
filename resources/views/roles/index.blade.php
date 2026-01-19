<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Roles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Lista de Roles</h3>
                <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Rol
                </a>
            </div>

            <div class="card bg-base-100 shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table w-full table-compact">
                        <thead>
                            <tr>
                                <th>Nombre del Rol</th>
                                <th>Permisos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr class="hover">
                                    <td class="font-bold">{{ $role->name }}</td>
                                    <td>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($role->permissions as $permission)
                                                <span class="badge badge-ghost badge-sm">{{ $permission->name }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-ghost btn-xs">
                                                Editar
                                            </a>
                                            @if($role->name !== 'Administrador' && $role->name !== 'admin')
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                    onsubmit="return confirm('¿Confirmar eliminación?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-ghost btn-xs text-error">Eliminar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>