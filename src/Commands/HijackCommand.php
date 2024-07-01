<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'filament-access:hijack',
    description: 'Hijack a class to have custom permissions'
)]
class HijackCommand extends \Illuminate\Console\Command
{
    public $signature = 'filament-access:hijack {pathToClass}';

    public $description = 'Hijack a class to have custom permissions';

    public function handle(): int
    {
        $pathToClass = $this->argument('pathToClass');
        if (! file_exists($pathToClass)) {
            $this->error('File not found');

            return self::FAILURE;
        }


        return self::SUCCESS;
    }
}
