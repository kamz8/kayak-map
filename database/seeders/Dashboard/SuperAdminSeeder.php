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
        config(['permission.cache.store' => 'array']);
        config(['cache.default' => 'array']);

        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@kayakmap.pl'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@kayakmap.pl',
                'password' => Hash::make('SuperAdmin123!'),
                'is_admin' => true,
                'is_active' => true,
            ]
        );

        // Set additional fields that are not fillable
        $superAdmin->update([
            'email_verified_at' => now(),
            'is_admin' => true,
            'is_active' => true,
        ]);

        // Assign Super Admin role
        $superAdmin->syncRoles(['Super Admin']);

        $this->command->info('Super Admin user created:');
        $this->command->info('Email: superadmin@kayakmap.pl');
        $this->command->info('Password: SuperAdmin123!');
    }
}
