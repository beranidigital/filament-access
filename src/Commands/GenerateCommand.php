<?php

namespace BeraniDigitalID\FilamentAccess\Commands;

use BeraniDigitalID\FilamentAccess\Analyzer\BaseAnalyzer;
use BeraniDigitalID\FilamentAccess\Hijacker\BaseHijacker;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'filament-access:generate',
    description: 'Generate and hijack all things to have custom permissions'
)]
class GenerateCommand extends \Illuminate\Console\Command
{
    public $signature = 'filament-access:generate';

    public $description = 'Generate and hijack all things to have fine grained custom permissions';

    public static string $template = <<<'PHP'
<?php
namespace NAMESPACENAME;

enum PERMISSIONS: string {
// add your permissions here

}

PHP;

    public function handle(): int
    {
        $results = BaseAnalyzer::analyzeAll();
        $enumPath = config('filament-access.enums_path');
        $classPath = Str::beforeLast($enumPath, '.php');
        $classPath = Str::replace('/', '\\', $classPath);
        $className = class_basename($classPath);
        $namespace = Str::beforeLast($classPath, '\\' . $className);
        $permissions = [];
        foreach ($results as $result) {
            $permissions = array_merge($permissions, $result->permissions());
        }
        $permissions = array_unique($permissions);
        $permissionsPhpEnum = [];
        foreach ($permissions as $permission) {
            $alphanumericOnly = preg_replace('/[^a-zA-Z0-9]/', '_', $permission);
            // remove double or more underscore
            $alphanumericOnly = preg_replace('/_+/', '_', $alphanumericOnly);
            $permissionsPhpEnum[] = "   case $alphanumericOnly = '$permission';";
        }
        $stringBuilder = self::$template;
        $stringBuilder = str_replace('PERMISSIONS', $className, $stringBuilder);
        $stringBuilder = str_replace('NAMESPACENAME', $namespace, $stringBuilder);
        $stringBuilder = str_replace('// add your permissions here', implode("\n", $permissionsPhpEnum), $stringBuilder);

        $enumPath = Str::replace('App', 'app', $enumPath);
        $enumPath = base_path($enumPath);
        if (file_exists($enumPath)) {
            $read = file_get_contents($enumPath);
            $stringBuilder = self::mergeEnumsSourceCode($stringBuilder, $read);
        }
        file_put_contents($enumPath, $stringBuilder);

        return self::SUCCESS;
    }

    public static function mergeEnumsSourceCode(string $source, string $target): string
    {
        $parser = BaseHijacker::getParser();
        $ast = $parser->parse($source);
        $targetAst = $parser->parse($target);

        $sourceClazz = BaseHijacker::breadthFirstSearch($ast, fn ($node) => $node instanceof Enum_);
        $targetClazz = BaseHijacker::breadthFirstSearch($targetAst, fn ($node) => $node instanceof Enum_);

        if (! $sourceClazz) {
            throw new \Exception('Source code');
        }
        if (! $targetClazz) {
            throw new \Exception('Target code');
        }
        $existingEnum = [];
        // do not replace existing enum
        foreach ($sourceClazz->stmts as $stmt) {
            if ($stmt instanceof EnumCase) {
                $existingEnum[] = $stmt->name->name;
            }
        }

        foreach ($targetClazz->stmts as $stmt) {
            if ($stmt instanceof EnumCase) {
                if (! in_array($stmt->name->name, $existingEnum)) {
                    $sourceClazz->stmts[] = $stmt;
                }
            }
        }

        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        $newSourceCode = $prettyPrinter->prettyPrintFile($ast);

        return $newSourceCode;
    }
}
