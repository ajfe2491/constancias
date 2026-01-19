<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'ver dashboard',
            'gestionar eventos',
            'gestionar plantillas', // document-configurations
            'enviar constancias',   // certificate-sending
            'gestionar usuarios',
            'gestionar roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles and assign created permissions

        // Admin (all permissions)
        $role = Role::firstOrCreate(['name' => 'Administrador']);
        $role->givePermissionTo(Permission::all());

        // Editor (Example limited role)
        $roleEditor = Role::firstOrCreate(['name' => 'Editor']);
        $roleEditor->givePermissionTo(['ver dashboard', 'gestionar eventos', 'gestionar plantillas', 'enviar constancias']);
    }
}
