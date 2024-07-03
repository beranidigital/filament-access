<?php

namespace BeraniDigitalID\FilamentAccess;

use BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult;
use BeraniDigitalID\FilamentAccess\Commands\GenerateCommand;

class FilamentAccess
{
    protected \Closure $namingCallback;

    /**
     * @return array<AnalyzerResult>
     */
    public function analyzeAll(): array
    {
        return GenerateCommand::analyzeAll();
    }

    public function setNamingCallback(\Closure $namingCallback): void
    {
        $this->namingCallback = $namingCallback;
    }

    public function determinePermissionName(string $ability, mixed $arguments): ?string
    {
        if ($this->namingCallback) {
            return ($this->namingCallback)($ability, $arguments);
        }

        return $ability . ':' . ($arguments . '');
    }
    /**
     * @method static \BeraniDigitalID\FilamentAccess\FilamentAccess analyzeAll(): array
     * @method static \BeraniDigitalID\FilamentAccess\FilamentAccess setNamingCallback(\Closure $namingCallback): void
     * @method static \BeraniDigitalID\FilamentAccess\FilamentAccess determinePermissionName(string $ability, mixed $arguments): ?string
     */
}
