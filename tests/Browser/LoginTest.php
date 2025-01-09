<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Faker\Factory;

class LoginTest extends DuskTestCase
{
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create('pl_PL');
    }

    /** @test */
    public function user_can_see_login_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://kayak-map.test/login')
                ->waitFor('.v-card', 5)
                ->assertSee('Witamy z powrotem.')
                ->assertSee('Zaloguj się i zacznij odkrywanie.');
        });
    }

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        // Tworzenie testowego użytkownika
        $email = $this->faker->email;
        $password = 'password123';

        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->visit('https://kayak-map.test/login')
                ->waitFor('.v-card', 5)
                ->type('input[type=email]', $email)
                ->type('input[type=password]', $password)
                ->press('Zaloguj się')
                ->waitForLocation('https://kayak-map.test/')
                ->assertUrlIs('https://kayak-map.test/');
        });
    }

    /** @test */
    public function login_form_shows_validation_errors()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://kayak-map.test/login')
                ->waitFor('.v-card', 5)
                ->type('input[type=email]', $this->faker->word) // nieprawidłowy email
                ->type('input[type=password]', '123') // za krótkie hasło
                ->press('Zaloguj się')
                ->waitForText('E-mail musi być poprawny')
                ->assertSee('Hasło musi mieć minimum 6 znaków');
        });
    }

    /** @test */
    public function user_can_toggle_password_visibility()
    {
        $password = $this->faker->password(8);

        $this->browse(function (Browser $browser) use ($password) {
            $browser->visit('https://kayak-map.test/login')
                ->waitFor('.v-card', 5)
                ->type('input[type=password]', $password)
                ->click('.mdi-eye')
                ->assertAttribute('input[name=password]', 'type', 'text')
                ->click('.mdi-eye-off')
                ->assertAttribute('input[name=password]', 'type', 'password');
        });
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://kayak-map.test/login')
                ->waitFor('.v-card', 5)
                ->type('input[type=email]', $this->faker->email)
                ->type('input[type=password]', $this->faker->password)
                ->press('Zaloguj się')
                ->waitForText('Błąd podczas logowania');
        });
    }

    /** @test */
    public function social_login_buttons_are_present_and_clickable()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://kayak-map.test/login')
                ->waitFor('.v-card', 5)
                ->assertPresent('button:contains("Kontynuuj z Google")')
                ->assertPresent('button:contains("Kontynuuj z Facebookiem")')
                ->assertPresent('.mdi-google')
                ->assertPresent('.mdi-facebook');
        });
    }

    /** @test */
    public function user_can_navigate_to_register()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://kayak-map.test/login')
                ->waitFor('.v-card', 5)
                ->clickLink('Zarejestruj się za darmo')
                ->assertPathIs('/register');
        });
    }
}
