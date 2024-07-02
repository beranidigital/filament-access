<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

class FilamentPageAnalyzer extends BaseAnalyzer
{
    public static string $class = \Filament\Pages\Page::class;

    public static function processAdditionalPermissions(AnalyzerResult $analyzerResult): array
    {
        return [
            'access'
        ];
    }
}
