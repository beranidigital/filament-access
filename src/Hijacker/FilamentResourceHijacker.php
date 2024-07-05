<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use PhpParser\Node\Stmt;

class FilamentResourceHijacker extends BaseHijacker
{
    public static string $templateCode = <<<'PHP'
<?php
class  FilamentResourceHijacker {
    public static function can(string $action, ?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        if (static::shouldSkipAuthorization()) {
            return true;
        }

        $model = static::class;

        try {
            return \Filament\authorize($action, $record ?? $model, static::shouldCheckPolicyExistence())->allowed();
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }

   }
}
PHP;

    public static function hijack(Stmt\Class_ $sourceCode, \BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult $arg)
    {
        // find if `can` method already exists
        $hasCanMethod = false;
        foreach ($sourceCode->stmts as $stmt) {
            if ($stmt instanceof Stmt\ClassMethod) {
                if ($stmt->name->name === 'can') {
                    $hasCanMethod = true;

                    break;
                }
            }
        }
        if ($hasCanMethod) {
            //???
            // remove existing `can` method
            foreach ($sourceCode->stmts as $key => $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {
                    if ($stmt->name->name === 'can') {
                        unset($sourceCode->stmts[$key]);

                        break;
                    }
                }
            }
        }
        // add `can` method
        $statements = self::getParser()->parse(static::$templateCode);
        if (! is_array($statements)) {
            throw new \Exception('Failed to parse template code');
        }
        $sourceCode->stmts[] = $statements[0]->stmts[0];

    }
}
