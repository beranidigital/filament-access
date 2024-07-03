<?php

namespace BeraniDigitalID\FilamentAccess\Task;

use BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult;

class GenerateTask
{
    private $result;

    public function __construct(private readonly AnalyzerResult $arg) {}

    public function run(): void
    {
        $this->result = self::generate($this->arg);
    }

    public static function generate(AnalyzerResult $res): array
    {
        $sourceCode = file_get_contents($res->file);
        $hijackedSourceCode = \BeraniDigitalID\FilamentAccess\Hijacker\BaseHijacker::handleSourceCode($res);
        if ($hijackedSourceCode) {
            file_put_contents($res->file, $hijackedSourceCode);
            echo 'Injected code to ' . $res->file . "\n";
        }

        return [

            'success' => true,
        ];
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
