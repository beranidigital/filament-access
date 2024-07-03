<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use Filament\Facades\Filament;
use Filament\PanelProvider;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;

class FilamentPanelProviderAnalyzer extends BaseAnalyzer
{
    public static string $class = PanelProvider::class;

    /**
     *                                      {@inheritDoc}
     */
    public static function analyze(string $class, array &$results, array &$additionalData = [], int $depth = 0, ?string $type = null): AnalyzerResult
    {
        $parentResult = parent::analyze($class, $results, $additionalData, $depth, $type);
        $additionalInfo = [];

        $panelProvider = app()->getProvider($class);
        $panel = $panelProvider->panel(\Filament\Panel::make());
        $parentResult->tags[] = $panel->getId();
        $panel = Filament::getPanel($panel->getId());
        $additionalData['currentPanel'] = $panel;
        $additionalData['currentPanelProviderClass'] = $class;
        $additionalData['tags'] = [$panel->getId()];

        foreach ($panel->getWidgets() as $widget) {
            $results = self::startAnalyze($widget, $results, $additionalData, $depth, Widget::class);
        }
        foreach ($panel->getResources() as $resource) {
            $results = self::startAnalyze($resource, $results, $additionalData, $depth, Resource::class);
        }
        foreach ($panel->getPages() as $page) {
            $results = self::startAnalyze($page, $results, $additionalData, $depth, \Filament\Pages\Page::class);
        }

        return $parentResult;
    }
}
