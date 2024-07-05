<?php

namespace BeraniDigitalID\FilamentAccess\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BeraniDigitalID\FilamentAccess\FilamentAccess
 *
 * @method static array analyzeAll
 * @method static void setNamingCallback(\Closure $namingCallback)
 * @method static string|null determinePermissionName(string $ability, mixed $arguments)
 */
class FilamentAccess extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BeraniDigitalID\FilamentAccess\FilamentAccess::class;
    }
}
