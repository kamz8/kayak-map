<?php

namespace Database\Seeders\Dashboard;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        config(['permission.cache.store' => 'array']);
        config(['cache.default' => 'array']);

        // Create or update admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@kayak-map.test'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Kayak Map',
                'email' => 'admin@kayak-map.test',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['Super Admin']);

        $this->command->info('Super Admin user created: admin@kayak-map.test / password');
    }
}
