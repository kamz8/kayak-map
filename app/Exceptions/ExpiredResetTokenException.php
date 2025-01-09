<?php

namespace App\Exceptions;

use Exception;

class ExpiredResetTokenException extends Exception
{
    protected $message = 'Token resetowania hasła wygasł.';
    protected $code = 422;
}
