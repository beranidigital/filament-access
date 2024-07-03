<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult;
use BeraniDigitalID\FilamentAccess\Analyzer\BaseAnalyzer;
use BeraniDigitalID\FilamentAccess\Task\GenerateTask;
use BeraniDigitalID\FilamentAccess\Task\GenerateTaskThreaded;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Resource;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'filament-access:generate',
    description: 'Generate and hijack all things to have custom permissions'
)]
class GenerateCommand extends \Illuminate\Console\Command
{
    public $signature = 'filament-access:generate {--no-enum}';

    public $description = 'Generate and hijack all things to have fine grained custom permissions';

    /**
     * @param  class-string<resource>  $resourceClass
     */
    public static function resourceExplorer(string $resourceClass) {}

    public static function panelExplorer(Panel $panel): array {}

    /**
     * To list all panels and resources permissions
     *
     * @return array<AnalyzerResult>
     */
    public static function analyzeAll(): array
    {
        $results = [];
        foreach (array_values(app()->getProviders(\Filament\PanelProvider::class)) as $panel) {
            $panel = get_class($panel);
            $results = BaseAnalyzer::startAnalyze($panel, $results, type: PanelProvider::class);
        }

        return $results;
    }

    public function handle(): int
    {
        $panels = Filament::getPanels();
        if (count($panels) > 0) {
            $this->info('Panels found: ' . count($panels));
            foreach ($panels as $panel) {
                $this->info('Panel: ' . $panel->getId());
            }
        } else {
            $this->info('No panels found');
        }

        $results = self::analyzeAll();
        $tasks = [];
        foreach ($results as $result) {
            // skip result that not in App namespace
            if (! str_starts_with($result->class, 'App\\')) {
                continue;
            }
            $tasks[] = new GenerateTask($result);
        }

        $results = [];
        if (! extension_loaded('pthreads')) {
            $this->warn('pthreads extension not loaded, please install it to speed up the process');
            foreach ($tasks as $task) {
                $task->run();
                $results[] = $task->getResult();
            }
        } else {
            $worker = new \Worker();
            $worker->start();
            $threadedTasks = [];
            foreach ($tasks as $task) {
                $worker->stack($threadedTasks[] = new GenerateTaskThreaded($task));
            }

            $worker->shutdown();
            foreach ($threadedTasks as $threadedTask) {
                $results[] = $threadedTask->getResult();
            }
        }


        return self::SUCCESS;
    }
}
