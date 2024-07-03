<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use Illuminate\Console\Command;

class BaseCommand extends Command
{
    public function ensureGit(): int
    {
        if (! file_exists(base_path('.git'))) {
            $confirm = $this->confirm('No git repository found, its recommended to have one. Continue operation?', false);
            if (! $confirm) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
