<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $adminRole = Role::where('name', 'Administrador')->first();

        $adminEmail = 'admin@siice.com';
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminEmail,
                'password' => Hash::make($adminEmail),
            ],
        );

        if ($adminRole) {
            $admin->assignRole($adminRole);

            // Also assign to the first ever user (ID 1), just in case it's different in Dev
            $firstUser = User::find(1);
            if ($firstUser && !$firstUser->hasRole($adminRole)) {
                $firstUser->assignRole($adminRole);
            }
        }
    }
}
