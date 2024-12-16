<?php

declare(strict_types=1);

return [
    'failed' => 'Błędny login lub hasło.',
    'password' => 'Hasło jest nieprawidłowe.',
    'throttle' => 'Za dużo nieudanych prób logowania. Proszę spróbować za :seconds sekund.',

    // Dodane komunikaty dla rejestracji
    'email_taken' => 'Ten adres email jest już zajęty.',
    'password_requirements' => 'Hasło musi zawierać wielką literę, małą literę, cyfrę oraz znak specjalny.',
    'registration_disabled' => 'Rejestracja jest obecnie wyłączona.',
    'too_many_attempts' => 'Zbyt wiele prób rejestracji. Spróbuj ponownie za :minutes minutes.',

    // Dodane komunikaty dla walidacji
    'invalid_credentials' => 'Nieprawidłowe dane logowania.',
    'account_inactive' => 'Konto jest nieaktywne.',
    'verification_required' => 'Proszę zweryfikować adres email.',
    'email_verification_sent' => 'Link weryfikacyjny został wysłany na podany adres email.',
    'password_reset_sent' => 'Link do resetowania hasła został wysłany na podany adres email.',
    'password_reset_invalid' => 'Nieprawidłowy lub wygasły token resetowania hasła.',
    'password_reset_success' => 'Hasło zostało pomyślnie zmienione.',
    'login_success' => 'Logowanie pomyślne.',
    'logout_success' => 'Wylogowanie pomyślne.',
    'session_expired' => 'Sesja wygasła. Proszę zalogować się ponownie.',
];
