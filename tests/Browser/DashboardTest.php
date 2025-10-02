<?php

use Laravel\Dusk\Browser;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('dashboard roles page loads correctly', function () {
    $adminUser = User::where('email', 'admin@kayakmap.pl')->first();

    if (!$adminUser) {
        $adminUser = User::factory()->create([
            'email' => 'admin@kayakmap.pl',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'is_admin' => true,
            'password' => bcrypt('password123'),
        ]);
    }

    $this->browse(function (Browser $browser) use ($adminUser) {
        $browser->loginAs($adminUser)
            ->visit('https://kayak-map.test/dashboard/roles')
            ->waitFor('.roles-index', 15)
            ->assertSee('Role systemowe')
            ->screenshot('roles-test');
    });
});
