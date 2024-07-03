<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use Filament\Resources\RelationManagers\RelationManager;

class FilamentResourceAnalyzer extends BaseAnalyzer
{
    public static function processAdditionalPermissions(AnalyzerResult $analyzerResult): array
    {
        return [
            'viewAny',
            'create',
            'update',
            'delete',
            'deleteAny',
            'forceDelete',
            'forceDeleteAny',
            'reorder',
            'restore',
            'restoreAny',
            'view',
        ];
    }

    /**
     *                                         {@inheritDoc}
     *
     * @throws \Exception
     */
    public static function analyze(string $class, array &$results, array &$additionalData = [], int $depth = 0, ?string $type = null): AnalyzerResult
    {
        $result = parent::analyze($class, $results, $additionalData, $depth, $type);
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
