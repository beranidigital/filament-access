<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(
    name: 'filament-access:generate',
    description: 'Generate and hijack all things to have custom permissions'
)]
class GenerateCommand extends Command
{
    public string $signature = 'filament-access:generate';

    public string $description = 'Generate and hijack all things to have custom permissions';

    public function handle(): int
    {
        $appPath = app_path();

        return self::SUCCESS;
    }
}
