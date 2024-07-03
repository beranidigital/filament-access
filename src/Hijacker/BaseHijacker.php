<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;

abstract class BaseHijacker
{
    abstract public static function hijack(Stmt\Class_ $sourceCode);

    /**
     * @var array<class-string, class-string>
     */
    public static array $handlers = [
        'Filament\Resources\Resource' => FilamentResourceHijacker::class,
    ];

    protected static ?Parser $parser = null;

    public static function getParser(): Parser
    {
        if (! self::$parser) {
            self::$parser = (new ParserFactory())->createForHostVersion();
        }

        return self::$parser;
    }

    public static function handleSourceCode(AnalyzerResult $arg): ?string
    {
        $parser = self::getParser();
        $sourceCode = file_get_contents($arg->file);

        $ast = $parser->parse($sourceCode);
        if (! $ast) {
            throw new \Exception('Source code has no AST: ' . $sourceCode);
        }
        $dumper = new \PhpParser\NodeDumper();
        $hashBefore = hash('sha256', $dumper->dump($ast));

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new HijackerVisitor($arg));
        $ast = $traverser->traverse($ast);

        $hashAfter = hash('sha256', $dumper->dump($ast));
        if ($hashBefore === $hashAfter) {
            return null;
        }
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        $newSourceCode = $prettyPrinter->prettyPrintFile($ast);

        return $newSourceCode;
    }
}
