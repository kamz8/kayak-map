<?php

namespace Tests\Browser;

use App\Models\User;
use Database\Seeders\Dashboard\RoleSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    /**
     * Test successful login to dashboard with valid credentials
     *
     * @return void
     */
    public function testSuccessfulDashboardLogin()
    {
        // Create Super Admin user
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@kayakmap.pl',
            'password' => bcrypt('SuperAdmin123!'),
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $superAdmin->assignRole('Super Admin');

        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard/login')
                    ->waitFor('form', 10)
                    ->assertSee('Logowanie do Panelu')

                    // Fill in login form
                    ->type('input[type="email"]', 'superadmin@kayakmap.pl')
                    ->type('input[type="password"]', 'SuperAdmin123!')

                    // Submit form
                    ->press('Zaloguj się')
                    ->pause(3000) // Wait for API call and JWT token processing

                    // Assert redirect to dashboard
                    ->waitForLocation('/dashboard', 15)
                    ->assertPathIs('/dashboard')

                    // Wait for Vue.js dashboard to load
                    ->waitFor('.dashboard-layout', 20)
                    ->pause(2000) // Allow Vue initialization

                    // Assert dashboard elements are visible
                    ->assertSee('Panel administracyjny')
                    ->assertPresent('nav') // Sidebar navigation

                    ->screenshot('dashboard-login-success');
        });
    }

    /**
     * Test login failure with invalid credentials
     *
     * @return void
     */
    public function testDashboardLoginWithInvalidCredentials()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard/login')
                    ->waitFor('form', 10)
                    ->assertSee('Logowanie do Panelu')

                    // Fill in login form with wrong credentials
                    ->type('input[type="email"]', 'wrong@example.com')
                    ->type('input[type="password"]', 'WrongPassword123!')

                    // Submit form
                    ->press('Zaloguj się')
                    ->pause(2000) // Wait for API response

                    // Assert error message is shown
                    ->waitFor('.error-message, .v-alert--error, [role="alert"]', 10)
                    ->assertSee('Nieprawidłowe dane logowania')

                    // Still on login page
                    ->assertPathIs('/dashboard/login')

                    ->screenshot('dashboard-login-invalid-credentials');
        });
    }

    /**
     * Test login with inactive user account
     *
     * @return void
     */
    public function testDashboardLoginWithInactiveAccount()
    {
        // Create inactive Super Admin user
        $inactiveUser = User::factory()->create([
            'email' => 'inactive@kayakmap.pl',
            'password' => bcrypt('Password123!'),
            'first_name' => 'Inactive',
            'last_name' => 'User',
            'is_active' => false, // Inactive account
            'email_verified_at' => now()
        ]);
        $inactiveUser->assignRole('Super Admin');

        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard/login')
                    ->waitFor('form', 10)

                    ->type('input[type="email"]', 'inactive@kayakmap.pl')
                    ->type('input[type="password"]', 'Password123!')

                    ->press('Zaloguj się')
                    ->pause(2000)

                    // Assert error about inactive account
                    ->waitFor('.error-message, .v-alert--error, [role="alert"]', 10)
                    ->assertSee('Konto nieaktywne')

                    ->assertPathIs('/dashboard/login')

                    ->screenshot('dashboard-login-inactive-account');
        });
    }

    /**
     * Test login validates required fields
     *
     * @return void
     */
    public function testDashboardLoginValidatesRequiredFields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard/login')
                    ->waitFor('form', 10)

                    // Try to submit empty form
                    ->press('Zaloguj się')
                    ->pause(1000)

                    // Assert validation errors
                    ->assertPresent('input[type="email"]:invalid')
                    ->assertPresent('input[type="password"]:invalid')

                    // Still on login page
                    ->assertPathIs('/dashboard/login')

                    ->screenshot('dashboard-login-validation-errors');
        });
    }

    /**
     * Test last_login_at and last_login_ip are updated after login
     *
     * @return void
     */
    public function testLastLoginIsUpdatedAfterDashboardLogin()
    {
        // Create Super Admin user without last_login
        $superAdmin = User::factory()->create([
            'email' => 'test@kayakmap.pl',
            'password' => bcrypt('TestAdmin123!'),
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null
        ]);
        $superAdmin->assignRole('Super Admin');

        $this->assertNull($superAdmin->last_login_at);
        $this->assertNull($superAdmin->last_login_ip);

        $this->browse(function (Browser $browser) use ($superAdmin) {
            $browser->visit('https://kayak-map.test/dashboard/login')
                    ->waitFor('form', 10)

                    ->type('input[type="email"]', 'test@kayakmap.pl')
                    ->type('input[type="password"]', 'TestAdmin123!')

                    ->press('Zaloguj się')
                    ->pause(3000)

                    ->waitForLocation('/dashboard', 15)
                    ->waitFor('.dashboard-layout', 20)

                    ->screenshot('dashboard-login-last-login-updated');
        });

        // Refresh user from database
        $superAdmin->refresh();

        // Assert last_login_at and last_login_ip are set
        $this->assertNotNull($superAdmin->last_login_at);
        $this->assertNotNull($superAdmin->last_login_ip);
    }

    /**
     * Test user without proper role cannot access dashboard
     *
     * @return void
     */
    public function testRegularUserCannotAccessDashboard()
    {
        // Create regular user without admin role
        $regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('UserPassword123!'),
            'first_name' => 'Regular',
            'last_name' => 'User',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $regularUser->assignRole('User'); // Only User role, not Admin

        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard/login')
                    ->waitFor('form', 10)

                    ->type('input[type="email"]', 'user@example.com')
                    ->type('input[type="password"]', 'UserPassword123!')

                    ->press('Zaloguj się')
                    ->pause(3000)

                    // Should show error or redirect to unauthorized page
                    ->waitFor('.error-message, .v-alert--error, [role="alert"]', 10)
                    ->assertSee('Brak uprawnień')

                    ->screenshot('dashboard-login-insufficient-permissions');
        });
    }
}
