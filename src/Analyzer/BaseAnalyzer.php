<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use Filament\PanelProvider;
use Filament\Resources\Resource;

abstract class BaseAnalyzer
{
    /**
     * @param  class-string  $class
     * @param  array<string, AnalyzerResult>  $results
     * @throw \Exception
     */
    public static function analyze(string $class, array &$results, array &$additionalData = [], int $depth = 0): AnalyzerResult
    {
        $classFQN = $class;
        if (! isset($results[$classFQN])) {
            $result = $results[$classFQN] = new AnalyzerResult($class);
            if (isset($additionalData['tags'])) {
                $result->tags = $additionalData['tags'];
            }
        }
        $result = $results[$classFQN];

        // try to get navigation group
        try {
            if (method_exists($class, 'getNavigationGroup')) {
                $group = $class::getNavigationGroup();
                if ($group) {
                    $result->tags[] = $group;
                }

            }
        } catch (\Exception $exception) {
        }

        // try invoke label
        try {
            if (method_exists($class, 'getNavigationLabel')) {
                $label = $class::getNavigationLabel();
                if ($label) {
                    $result->label = $label;
                }
            }
        } catch (\BadMethodCallException $exception) {
        }

        return $result;
    }

    /**
     * @var array<class-string, class-string>
     */
    public static array $handlers = [
        \Filament\Widgets\Widget::class => FilamentWidgetAnalyzer::class,
        \Filament\Pages\Page::class => FilamentPageAnalyzer::class,
        Resource::class => FilamentResourceAnalyzer::class,
        PanelProvider::class => FilamentPanelProviderAnalyzer::class,
    ];

    /**
     * @param  class-string  $class
     * @return array<string, AnalyzerResult>
     */
    public static function startAnalyze(string $class, array &$results = [], array &$additionalData = [], int $depth = 0, ?string $type = null): array
    {

        if ($depth > 10) {
            throw new \Exception('Depth too deep');
        }
        $handler = static::$handlers[$type ?? $class] ?? null;
        if ($handler) {
            $copiedAdditionalData = $additionalData;
            $handler::analyze($class, $results, $additionalData, $depth + 1);
            $additionalData = $copiedAdditionalData; // restore additional data as stack unwinds
        } else {
            echo 'No handler for ' . ($type ?? $class) . "\n";
        }

        return $results;
    }

    public static function getHandlers(): array
    {
        return self::$handlers;
    }
}
