<?php

namespace BeraniDigitalID\FilamentAccess\Task;

if (extension_loaded('pthreads')) {

    class HijackTaskThreaded extends \Threaded
    {
        public function __construct(private readonly HijackTask $task) {}

        public function run(): void
        {
            $this->task->run();
        }

        public function getResult(): array
        {
            return $this->task->getResult();
        }
    }

}
