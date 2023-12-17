<?php

namespace App\Exceptions;

use Exception;

class MoveException extends Exception
{
    protected $data;

    public function __construct($message = "", $code = 0, $data = null, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}