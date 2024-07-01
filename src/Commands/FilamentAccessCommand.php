<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'filament-access:command',
    description: 'My command'
)]
class FilamentAccessCommand extends Command
{
    public $signature = 'filament-access:command';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
