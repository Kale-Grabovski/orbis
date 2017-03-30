<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $code = 400;

    public function __toString()
    {
        return "{$this->message}\n";
    }
}
