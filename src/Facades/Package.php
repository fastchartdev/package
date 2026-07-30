<?php

namespace Fastchartdev\Package\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Fastchartdev\Package\Package
 */
class Package extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Fastchartdev\Package\Package::class;
    }
}
