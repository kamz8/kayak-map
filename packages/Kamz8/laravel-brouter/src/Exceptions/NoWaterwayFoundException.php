<?php

namespace Kamz\LaravelBRouter\Exceptions;

use Exception;

class NoWaterwayFoundException extends Exception
{
    protected $message = 'No waterway found in the specified area';
}
