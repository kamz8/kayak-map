<?php

namespace App\Exceptions;

use Exception;

class InvalidResetTokenException extends Exception
{
    protected $message = 'Nieprawidłowy lub wygasły token resetowania hasła.';
    protected $code = 422;
}
