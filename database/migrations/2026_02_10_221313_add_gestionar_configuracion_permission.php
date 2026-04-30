<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the permission
        $permission = Permission::firstOrCreate(['name' => 'gestionar configuracion']);

        // Assign to Administrator role
        $role = Role::where('name', 'Administrador')->first();
        if ($role) {
            $role->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the permission
        $permission = Permission::where('name', 'gestionar configuracion')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
