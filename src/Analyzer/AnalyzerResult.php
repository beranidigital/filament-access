<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use BeraniDigitalID\FilamentAccess\Facades\FilamentAccess;
use Illuminate\Support\Facades\Log;

class AnalyzerResult
{
    public string $file;

    /**
     * @var class-string
     */
    public string $class;
    /**
     * @var class-string
     */
    public string $type;
    /**
     * @var array<class-string>
     */
    public array $parents;

    public array $tags = [];
    /**
     * @var array<string>
     */
    public array $ability = [];
    public string $label;

    /**
     * @param  class-string  $class
     */
    public function __construct(string $class, ?string $type = null)
    {
        $this->class = $class;
        $reflection = new \ReflectionClass($class);
        $this->file = $reflection->getFileName();
        $this->label = $class;
        $this->parents = class_parents($class) ?: [];
        $this->type = $type ?? $this->parents[0] ?? $class;
        if( $this->type ===  $this->class) {
            Log::debug('AnalyzerResult: Potential misconfigure between class and type', ['class' => $this->class, 'type' => $this->type]);
        }
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
