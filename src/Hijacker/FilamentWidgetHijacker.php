<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use PhpParser\Node\Stmt;

class FilamentWidgetHijacker extends BaseHijacker
{
    public static string $templateCode = <<<'PHP'
<?php
class  FilamentPanelProviderHijacker {
    public static function canView(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('view', self::class);
    }
}
PHP;

    public static function hijack(Stmt\Class_ $sourceCode, \BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult $arg)
    {
        // find if `canView` method already exists
        $hasCanViewMethod = false;
        foreach ($sourceCode->stmts as $stmt) {
            if ($stmt instanceof Stmt\ClassMethod) {
                if ($stmt->name->name === 'canView') {
                    $hasCanViewMethod = true;

                    break;
                }
            }
        }
        if ($hasCanViewMethod) {
            return;
        }
        // add `canView` method
        $statements = self::getParser()->parse(self::$templateCode);
        if (! is_array($statements)) {
            throw new \Exception('Failed to parse template code');
        }
        $sourceCode->stmts[] = $statements[0]->stmts[0];
    }
}
