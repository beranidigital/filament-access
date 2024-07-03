<?php

namespace BeraniDigitalID\FilamentAccess\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BeraniDigitalID\FilamentAccess\FilamentAccess
 * @method static \BeraniDigitalID\FilamentAccess\FilamentAccess analyzeAll(): array
 * @method static \BeraniDigitalID\FilamentAccess\FilamentAccess setNamingCallback(\Closure $namingCallback): void
 * @method static \BeraniDigitalID\FilamentAccess\FilamentAccess determinePermissionName(string $ability, mixed $arguments): ?string
 */
class FilamentAccess extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return \BeraniDigitalID\FilamentAccess\FilamentAccess::class;
    }
}
