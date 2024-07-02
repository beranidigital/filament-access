<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

class FilamentWidgetAnalyzer extends BaseAnalyzer
{
    public static string $class = \Filament\Widgets\Widget::class;

    public static function processAdditionalPermissions(AnalyzerResult $analyzerResult): array
    {
        return [
            'view',
        ];
    }
}
