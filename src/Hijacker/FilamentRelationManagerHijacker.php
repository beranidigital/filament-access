<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

// basically the same method to hijack
class FilamentRelationManagerHijacker extends FilamentResourceHijacker
{
    public static string $templateCode = <<<'PHP'
<?php
class  FilamentRelationManagerHijacker {
    public function can(string $action, ?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        return parent::can($action, $record ?? static::class);
    }
}

PHP;
}
