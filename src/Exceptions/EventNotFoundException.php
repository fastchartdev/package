<?php

namespace Fastchartdev\Package\Exceptions;

use Exception;

class EventNotFoundException extends Exception
{
    public function __construct(string $eventName)
    {
        parent::__construct("Event '{$eventName}' not found.");
    }
}
