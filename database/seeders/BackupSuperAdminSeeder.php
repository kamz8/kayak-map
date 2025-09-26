<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BackupSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Utwórz backup Super Admin - zabezpieczenie przed utratą kontroli
        $backupSuperAdmin = User::firstOrCreate(
            ['email' => 'backup.superadmin@kayakmap.pl'],
            [
                'first_name' => 'Backup',
                'last_name' => 'Super Admin',
                'email' => 'backup.superadmin@kayakmap.pl',
                'password' => Hash::make('BackupSuperAdmin2024!'),
            ]
        );

        $backupSuperAdmin->update([
            'email_verified_at' => now(),
        ]);

        // Przypisz rolę Super Admin
        $backupSuperAdmin->assignRole('Super Admin');

        // Utwórz dodatkowego Admin dla bezpieczeństwa
        $backupAdmin = User::firstOrCreate(
            ['email' => 'backup.admin@kayakmap.pl'],
            [
                'first_name' => 'Backup',
                'last_name' => 'Admin',
                'email' => 'backup.admin@kayakmap.pl',
                'password' => Hash::make('BackupAdmin2024!'),

            ]
        );

        $backupAdmin->update([
            'email_verified_at' => now(),
        ]);

        // Przypisz rolę Admin
        $backupAdmin->assignRole('Admin');

        $this->command->info('Backup administrators created:');
        $this->command->info('Backup Super Admin: backup.superadmin@kayakmap.pl / BackupSuperAdmin2024!');
        $this->command->info('Backup Admin: backup.admin@kayakmap.pl / BackupAdmin2024!');
        $this->command->warn('WAŻNE: Zapisz te dane w bezpiecznym miejscu!');
    }
}
