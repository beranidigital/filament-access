<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

class FilamentResourceHijacker extends BaseHijacker
{
    public static string $templateCode = <<<'PHP'
    public static function getModel(): string
    {
        // if class name is ModelResource, return the class name
        $modelName = null;
        if(property_exists(static::class, 'model')) {
            $modelName = static::model;
        }
        $expectedClassName = $modelName . 'Resource';
        if (static::class === $expectedClassName) {
            return $modelName;
        }
        return self::class;
    }
PHP;


    public static function hijack(string $sourceCode): string
    {
        return $sourceCode;
    }
}
