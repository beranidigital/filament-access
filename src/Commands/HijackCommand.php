<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use BeraniDigitalID\FilamentAccess\Analyzer\BaseAnalyzer;
use BeraniDigitalID\FilamentAccess\Task\HijackTask;
use BeraniDigitalID\FilamentAccess\Task\HijackTaskThreaded;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'filament-access:hijack',
    description: 'Hijack a class to have custom permissions'
)]
class HijackCommand extends BaseCommand
{
    public $signature = 'filament-access:hijack {pathToClass}';

    public $description = 'Hijack a class to have custom permissions';

    public function handle(): int
    {
        $res = $this->ensureGit();
        if ($res) {
            return $res;
        }
        $pathToClass = $this->argument('pathToClass');
        if (! file_exists($pathToClass)) {
            $this->error('File not found');

            return self::FAILURE;
        }
        $results = BaseAnalyzer::analyzeAll();
        $tasks = [];
        foreach ($results as $result) {
            // skip result that not in App namespace
            if (! str_starts_with($result->class, 'App\\')) {
                continue;
            }
            $tasks[] = new HijackTask($result);
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
                $worker->stack($threadedTasks[] = new HijackTaskThreaded($task));
            }

            $worker->shutdown();
            foreach ($threadedTasks as $threadedTask) {
                $results[] = $threadedTask->getResult();
            }
        }

        return self::SUCCESS;
    }
}
