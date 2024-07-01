<?php

namespace BeraniDigitalID\FilamentAccess\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BeraniDigitalID\FilamentAccess\FilamentAccess
 */
class FilamentAccess extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \BeraniDigitalID\FilamentAccess\FilamentAccess::class;
    }
}
