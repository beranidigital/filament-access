<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use PhpParser\Node\Stmt;
use PhpParser\Parser;
use PhpParser\ParserFactory;

abstract class BaseHijacker
{
    abstract public static function hijack(Stmt $sourceCode): string;

    public static array $handlers = [
        'Filament\Resources\Resource' => FilamentResourceHijacker::class,
    ];

    protected static ?Parser $parser = null;

    public static function handleSourceCode(string $sourceCode): string
    {
        if (! self::$parser) {
            self::$parser = (new ParserFactory())->createForHostVersion();
        }

        $ast = self::$parser->parse($sourceCode);
        if (! $ast) {
            throw new \Exception('Source code has no AST');
        }
        $className = null;
        foreach ($ast as $node) {
            if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                $className = $node->name->name;

                break;
            }
        }
        if (! $className) {
            throw new \Exception('Source code has no class');
        }
        if (array_key_exists($className, self::$handlers)) {
            return self::$handlers[$className]::hijack($sourceCode);
        } else {
            return $sourceCode;
        }
    }
}
