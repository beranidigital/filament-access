<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;

class FilamentResourceAnalyzer extends BaseAnalyzer
{
    /**
     * @param  class-string<resource>  $class
     */
    public static function analyze(string $class, array &$results, array &$additionalData = [], int $depth = 0): AnalyzerResult
    {
        $result = parent::analyze($class, $results, $additionalData, $depth);
        $additionalData['tags'] = $result->tags;
        $result->tags[] = $result->label;

        foreach ($class::getRelations() as $relation) {
            if (! is_string($relation)) {
                continue;
            }
            $results = self::startAnalyze($relation, $results, $additionalData, $depth, RelationManager::class);
        }

        foreach ($class::getWidgets() as $widget) {
            $results = self::startAnalyze($widget, $results, $additionalData, $depth, \Filament\Widgets\Widget::class);
        }

        return $result;
    }
}