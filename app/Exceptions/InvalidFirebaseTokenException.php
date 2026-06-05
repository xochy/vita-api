<?php

namespace App\Exceptions;

use Exception;

class InvalidFirebaseTokenException extends Exception
{
    public function __construct($message = __('exceptions.invalid_firebase_token'))
    {
        parent::__construct($message);
    }
}
