<?php

namespace App\Modules\Users\Exceptions;

use Exception;

class AuthenticationException extends Exception
{
    protected $message = 'Authentication failed. Invalid credentials.';
}
