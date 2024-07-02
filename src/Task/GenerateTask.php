<?php

namespace BeraniDigitalID\FilamentAccess\Task;

class GenerateTask
{
    private $result;
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function run(): void
    {
        $this->result = self::generate($this->path);
    }

    public static function generate(string $path): array
    {
        $sourceCode = file_get_contents($path);
        $hijackedSourceCode = \BeraniDigitalID\FilamentAccess\Hijacker\BaseHijacker::handleSourceCode($sourceCode);

        return [

            'success' => true,
        ];
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
