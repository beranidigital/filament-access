<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use BeraniDigitalID\FilamentAccess\Facades\FilamentAccess;

class AnalyzerResult
{
    public string $file;

    /**
     * @var class-string
     */
    public string $class;

    public array $tags = [];
    /**
     * @var array<string>
     */
    public array $ability = [];
    public string $label;

    /**
     * @param  class-string  $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
        $reflection = new \ReflectionClass($class);
        $this->file = $reflection->getFileName();
        $this->label = $class;
    }

    /**
     * @return array<string>
     */
    public function permissions(): array
    {
        $permissions = [];
        foreach ($this->ability as $ability) {
            $permissions[] = FilamentAccess::determinePermissionName($ability, $this->class);
        }
        return $permissions;
    }
}
