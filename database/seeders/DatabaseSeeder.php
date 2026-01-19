<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

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

        // Ensure default admin exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => '$2y$12$R9/w.7ZzZz.z/z.z.z.z.z.z.z.z.z.z.z.z.z.z.z.z.z.z.', // generic hash or use Hash::make
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
