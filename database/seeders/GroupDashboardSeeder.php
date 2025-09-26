<?php

namespace Database\Seeders;

use Database\Seeders\Dashboard\AdminUserSeeder;
use Database\Seeders\Dashboard\BackupSuperAdminSeeder;
use Database\Seeders\Dashboard\RolePermissionSeeder;
use Database\Seeders\Dashboard\RoleSeeder;
use Database\Seeders\Dashboard\SuperAdminSeeder;
use Illuminate\Database\Seeder;

class GroupDashboardSeeder extends seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            BackupSuperAdminSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }

}
