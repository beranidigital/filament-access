<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use Illuminate\Support\Str;
use PhpParser\Node\Stmt;

class FilamentRelationManagerHijacker extends BaseHijacker
{

    public static function hijack(Stmt\Class_ $sourceCode, \BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult $arg)
    {

        foreach ($sourceCode->stmts as $stmt) {
            if ($stmt instanceof Stmt\ClassMethod) {
                if (Str::startsWith($stmt->name->name, 'can')) {

                }
            }
        }

    }
}
