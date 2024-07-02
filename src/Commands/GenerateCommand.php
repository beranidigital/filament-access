<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use BeraniDigitalID\FilamentAccess\Analyzer\BaseAnalyzer;
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
     * @param class-string<Resource> $resourceClass
     */
    public static function resourceExplorer(string $resourceClass) {

    }

    public static function panelExplorer(Panel $panel): array {}

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

        $results = [];
        foreach (array_values(app()->getProviders(\Filament\PanelProvider::class)) as $panel) {
            $panel = get_class($panel);
            $results = BaseAnalyzer::startAnalyze($panel, $results, type: PanelProvider::class);
        }


        return self::SUCCESS;
    }
}
