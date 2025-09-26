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
        // Create or update admin user
        User::updateOrCreate(
            ['email' => 'admin@kayak-map.test'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Kayak Map',
                'email' => 'admin@kayak-map.test',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created: admin@kayak-map.test / password');
    }
}
