<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use PhpParser\Node\Stmt;

class FilamentPageHijacker extends BaseHijacker
{
    public static string $templateCode = <<<'PHP'
<?php
class  FilamentPageHijacker {
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('access', self::class);
    }
}
PHP;


    public static function hijack(Stmt\Class_ $sourceCode): void
    {
        // find if `canAccess` method already exists
        $hasCanAccessMethod = false;
        foreach ($sourceCode->stmts as $stmt) {
            if ($stmt instanceof Stmt\ClassMethod) {
                if ($stmt->name->name === 'canAccess') {
                    $hasCanAccessMethod = true;

                    break;
                }
            }
        }
        if ($hasCanAccessMethod) {
            return;
        }
        // add `canAccess` method
        $statements = self::getParser()->parse(self::$templateCode);
        if (! is_array($statements)) {
            throw new \Exception('Failed to parse template code');
        }
        $sourceCode->stmts[] = $statements[0]->stmts[0];
    }
}
