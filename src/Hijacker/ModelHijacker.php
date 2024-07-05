<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult;
use BeraniDigitalID\FilamentAccess\Facades\FilamentAccess;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use PhpParser\Node\Stmt;

class ModelHijacker extends BaseHijacker
{
    public static function hijack(Stmt\Class_ $sourceCode, AnalyzerResult $arg)
    {
        $existingPolicy = Gate::getPolicyFor($arg->class);
        if (! $existingPolicy) {
            // make new policy
            Artisan::call('make:policy', ['name' => $arg->label . 'Policy', '-m' => $arg->class]);
        }
        $pathToPolicy = app_path('Policies/' . $arg->label . 'Policy.php');
        $policy = file_get_contents($pathToPolicy);
        $ast = self::getParser()->parse($policy);
        $class = self::breadthFirstSearch($ast, fn ($node) => $node instanceof Stmt\Class_);
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof Stmt\ClassMethod && in_array($stmt->name->name, $arg->ability)) {
                $permission = FilamentAccess::determinePermissionName($stmt->name->name, $arg->class);
                if (count($stmt->stmts) == 1 && $stmt->stmts[0] instanceof Stmt\Nop) {
                    $stmt->stmts = self::getParser()->parse('<?php return $user->can("' . $permission . '");');
                }
            }
        }
        // write back to file
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;

        file_put_contents($pathToPolicy, $prettyPrinter->prettyPrintFile($ast));
    }
}
