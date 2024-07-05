<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use PhpParser\Node\Stmt;

class ModelHijacker extends BaseHijacker
{
    public static function hijack(Stmt\Class_ $sourceCode, AnalyzerResult $arg)
    {
        $existingPolicy = Gate::getPolicyFor($arg->class);
        if (!$existingPolicy) {
            // make new policy
            Artisan::call('make:policy', ['name' => $arg->label . 'Policy', '-m' => $arg->class]);
        }
        $pathToPolicy = app_path('Policies/' . $arg->label . 'Policy.php');
        $policy = file_get_contents($pathToPolicy);
        $ast = self::getParser()->parse($policy);
        $class = self::breadthFirstSearch($ast, fn ($node) => $node instanceof Stmt\Class_);
        $methods = $class->stmts;
        $methodNames = array_map(fn ($node) => $node->name->name, $methods);

    }
}
