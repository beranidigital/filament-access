<?php

namespace BeraniDigitalID\FilamentAccess\Task;

if (extension_loaded('pthreads')) {

    class GenerateTaskThreaded extends \Threaded
    {
        private $result;

        private $path;

        public function __construct(string $path)
        {
            $this->path = $path;
        }

        public function run(): void
        {
            $this->result = GenerateTask::generate($this->path);
        }

        public function getResult(): array
        {
            return $this->result;
        }
    }

}
