<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use Illuminate\Support\Str;

class FilamentModelAnalyzer extends BaseAnalyzer
{
    public static function processAdditionalPermissions(AnalyzerResult $analyzerResult): array
    {
        return [
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'forceDelete',
        ];
    }

    public static function analyze(string $class, array &$results, array &$additionalData = [], int $depth = 0, ?string $type = null): AnalyzerResult
    {
        $res = parent::analyze($class, $results, $additionalData, $depth, $type);
        $res->label = Str::replace('App\\Models\\', '', $res->label);

        return $res;
    }
}
