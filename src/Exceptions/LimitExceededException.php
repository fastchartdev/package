<?php

namespace Fastchartdev\Package\Exceptions;

use Exception;

class LimitExceededException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
