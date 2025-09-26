<?php

namespace Database\Seeders\Dashboard;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@kayakmap.pl'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@kayakmap.pl',
                'password' => Hash::make('SuperAdmin123!'),
            ]
        );

        // Set additional fields that are not fillable
        $superAdmin->update([
            'email_verified_at' => now(),
        ]);

        // Assign Super Admin role
        $superAdmin->assignRole('Super Admin');

        $this->command->info('Super Admin user created:');
        $this->command->info('Email: superadmin@kayakmap.pl');
        $this->command->info('Password: SuperAdmin123!');
    }
}
