<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use BeraniDigitalID\FilamentAccess\Hijacker\BaseHijacker;
use Filament\Facades\Filament;
use Filament\PanelProvider;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class BaseAnalyzer
{
    public static function processAdditionalPermissions(AnalyzerResult $analyzerResult): array
    {
        return self::giveListOfStaticMethodCanFromGivenClass($analyzerResult->class);
    }

    /**
     * @param  class-string  $class
     * @param  array<string, AnalyzerResult>  $results
     * @param  array<string, mixed>  $additionalData
     * @param  class-string|null  $type  Usually parent class
     *
     * @throw \Exception
     */
    public static function analyze(string $class, array &$results, array &$additionalData = [], int $depth = 0, ?string $type = null): AnalyzerResult
    {
        $classFQN = $class;
        if (! isset($results[$classFQN])) {
            $result = $results[$classFQN] = new AnalyzerResult($class, $type ?? $class);
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

        $result->ability = static::processAdditionalPermissions($result);

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
        RelationManager::class => FilamentRelationManagerAnalyzer::class,
        Model::class => FilamentModelAnalyzer::class,
    ];

    public static function giveListOfStaticMethodCanFromGivenClass(string $class): array
    {
        $parser = BaseHijacker::getParser();
        $staticMethodVisitor = new StaticMethodCanVisitor;
        $stmts = $parser->parse(file_get_contents((new \ReflectionClass($class))->getFileName()));
        $traverser = new \PhpParser\NodeTraverser;
        $traverser->addVisitor($staticMethodVisitor);
        $traverser->traverse($stmts);

        $cans = [];
        foreach ($staticMethodVisitor->canMethods as $method) {
            $name = $method->name->name;
            // remove can
            $name = substr($name, 3);
            $cans[] = $name;
        }

        return $cans;
    }

    /**
     * @param  class-string  $class
     * @return array<string, AnalyzerResult>
     */
    public static function startAnalyze(string $class, array &$results = [], array &$additionalData = [], int $depth = 0, ?string $type = null): array
    {

        if ($depth > 10) {
            throw new \Exception('Depth too deep');
        }
        $type = null; // enforce type to null
        if (! $type) {
            // guesswork, recommended
            $type = array_values(class_parents($class))[0] ?? null;
            // fuzzy match
            if (! isset(static::$handlers[$type])) {
                foreach (class_parents($class) as $parent) {
                    if (isset(static::$handlers[$parent])) {
                        $type = $parent;

                        // Found :D
                        break;
                    }
                }
            }
        }
        $handler = static::$handlers[$type ?? $class] ?? null;
        if ($handler) {
            $copiedAdditionalData = $additionalData;
            $handler::analyze($class, $results, $additionalData, $depth + 1, ($type ?? $class));
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

    public static array $cache = [];

    public static function traverseAndGetClass(string $path): array
    {
        $queue = scandir($path);
        $results = [];
        while ($queue && count($queue) > 0) {
            $file = array_shift($queue);
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullPath = $path . '/' . $file;
            if (is_dir($fullPath)) {
                $queue = array_merge($queue, scandir($fullPath));
            } else {
                $relativePath = str_replace($path, '', $fullPath);
                $relativePath = ltrim($relativePath, '/');
                if(str_ends_with($relativePath, '.php')){
                    $relativePath = str_replace('.php', '', $relativePath);
                    $results[] = $relativePath;
                }
            }
        }
        return $results;

    }

    /**
     * To list all panels and resources permissions
     *
     * @return array<AnalyzerResult>
     */
    public static function analyzeAll(): array
    {
        if (count(self::$cache) > 0) {
            return self::$cache;
        }
        $panels = Filament::getPanels();
        if (count($panels) > 0) {
            Log::info('Panels found');
            foreach ($panels as $panel) {
                Log::info('Panel found: ' . $panel->getId());
            }
        } else {
            Log::warning('No panels found');
        }
        $results = [];
        foreach (app()->getProviders(\Filament\PanelProvider::class) as $panel) {
            $panel = get_class($panel);
            $results = BaseAnalyzer::startAnalyze($panel, $results, type: PanelProvider::class);
        }
        $models = self::traverseAndGetClass(app_path('Models'));
        foreach ($models as $model) {
            $model = 'App\\Models\\' . $model;
            $results = BaseAnalyzer::startAnalyze($model, $results, type: Model::class);
        }
        self::$cache = $results;

        return $results;
    }
}
