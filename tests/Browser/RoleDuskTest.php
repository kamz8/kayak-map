<?php

use Laravel\Dusk\Browser;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Vue.js Dashboard Tests - Laravel Dusk Best Practices
 * Following recommendations for testing Vue 3 components
 */

test('dashboard vue app loads completely', function () {
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

    $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    if (!$adminUser->hasRole($superAdminRole)) {
        $adminUser->assignRole($superAdminRole);
    }

    $this->browse(function (Browser $browser) use ($adminUser) {
        $browser->loginAs($adminUser)
            ->visit('https://kayak-map.test/dashboard')
            ->waitFor('.dashboard-layout', 20) // Extended timeout for Vue mounting
            ->pause(2000) // Allow Vue initialization
            ->assertSee('Panel administracyjny')
            ->assertPresent('nav a[href*="roles"]') // Check navigation loaded
            ->screenshot('vue-dashboard-loaded');
    });
});

test('vue roles component renders with data', function () {
    $adminUser = User::where('email', 'admin@kayakmap.pl')->first();

    $this->browse(function (Browser $browser) use ($adminUser) {
        $browser->loginAs($adminUser)
            ->visit('https://kayak-map.test/dashboard/roles')
            ->waitFor('.roles-index', 20) // Wait for Vue component mount
            ->waitFor('.v-data-table', 15) // Wait for Vuetify DataTable
            ->pause(2000) // Allow API data loading

            // Check Vue component rendered correctly
            ->assertSee('Role systemowe')
            ->assertSee('Super Admin')
            ->assertPresent('.v-data-table tbody tr') // Data loaded
            ->assertPresent('button:contains("Dodaj rolę")')
            ->screenshot('vue-roles-component-rendered');
    });
});

test('vue permission selector modal works', function () {
    $adminUser = User::where('email', 'admin@kayakmap.pl')->first();

    $this->browse(function (Browser $browser) use ($adminUser) {
        $browser->loginAs($adminUser)
            ->visit('https://kayak-map.test/dashboard/roles')
            ->waitFor('.roles-index', 20)
            ->waitFor('.v-data-table', 15)
            ->pause(2000)

            // Find and click permission management button
            // Using CSS selector that works with Vuetify
            ->waitFor('.v-data-table .v-btn[title*="Zarządzaj"], .v-data-table .v-btn[aria-label*="Zarządzaj"]', 10)
            ->click('.v-data-table .v-btn[title*="Zarządzaj"], .v-data-table .v-btn[aria-label*="Zarządzaj"]')

            // Wait for Vue modal to mount
            ->waitFor('.v-dialog.v-overlay--active', 15)
            ->waitFor('.permission-selector', 20) // Wait for our custom component
            ->pause(3000) // Allow API calls and Vue reactivity

            ->assertSee('Zarządzanie uprawnieniami')
            ->assertPresent('.permission-selector-content')
            ->assertPresent('input[placeholder*="upraw"], input[placeholder*="Szukaj"]')
            ->screenshot('vue-permission-modal-opened');
    });
});

test('vue permission search reactivity works', function () {
    $adminUser = User::where('email', 'admin@kayakmap.pl')->first();

    $this->browse(function (Browser $browser) use ($adminUser) {
        $browser->loginAs($adminUser)
            ->visit('https://kayak-map.test/dashboard/roles')
            ->waitFor('.v-data-table', 15)
            ->pause(2000)

            ->click('.v-data-table .v-btn[title*="Zarządzaj"], .v-data-table .v-btn[aria-label*="Zarządzaj"]')
            ->waitFor('.permission-selector', 20)
            ->pause(3000)

            // Test Vue reactivity in search
            ->type('input[placeholder*="upraw"], input[placeholder*="Szukaj"]', 'users')
            ->pause(1500) // Vue debounce + reactivity

            // Check if Vue filtering worked
            ->assertDontSee('Brak uprawnień pasujących')
            ->clear('input[placeholder*="upraw"], input[placeholder*="Szukaj"]')
            ->pause(1000)
            ->screenshot('vue-search-reactivity-test');
    });
});

test('vue modal close transition works', function () {
    $adminUser = User::where('email', 'admin@kayakmap.pl')->first();

    $this->browse(function (Browser $browser) use ($adminUser) {
        $browser->loginAs($adminUser)
            ->visit('https://kayak-map.test/dashboard/roles')
            ->waitFor('.v-data-table', 15)
            ->pause(2000)

            ->click('.v-data-table .v-btn[title*="Zarządzaj"], .v-data-table .v-btn[aria-label*="Zarządzaj"]')
            ->waitFor('.permission-selector', 20)
            ->pause(3000)

            // Test Vue modal close
            ->click('button:contains("Anuluj")')
            ->waitUntilMissing('.permission-selector', 15) // Wait for Vue unmount
            ->waitUntilMissing('.v-dialog.v-overlay--active', 10) // Wait for Vuetify transition
            ->pause(1000) // Transition completion

            ->assertDontSee('Zarządzanie uprawnieniami')
            ->screenshot('vue-modal-closed');
    });
});

test('vue component error handling', function () {
    $adminUser = User::where('email', 'admin@kayakmap.pl')->first();

    $this->browse(function (Browser $browser) use ($adminUser) {
        $browser->loginAs($adminUser)
            ->visit('https://kayak-map.test/dashboard/roles')
            ->waitFor('.v-data-table', 15)
            ->pause(2000)

            // Check no Vue errors in console
            ->assertMissing('.v-alert[type="error"]')
            ->assertMissing('[data-testid="vue-error"]')

            // Test export without breaking Vue
            ->click('button:contains("Eksport")')
            ->pause(3000)

            // Still on page, no Vue crashes
            ->assertSee('Role systemowe')
            ->assertPresent('.v-data-table')
            ->screenshot('vue-no-errors-after-action');
    });
});
