<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use Illuminate\Console\Command;

class FilamentAccessCommand extends Command
{
    public $signature = 'filament-access';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
