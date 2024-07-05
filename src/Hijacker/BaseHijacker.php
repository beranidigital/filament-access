<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;

abstract class BaseHijacker
{
    abstract public static function hijack(Stmt\Class_ $sourceCode, AnalyzerResult $arg);

    /**
     * @var array<class-string, class-string>
     */
    public static array $handlers = [
        'Filament\Resources\Resource' => FilamentResourceHijacker::class,
        'Filament\PanelProvider' => FilamentPanelProviderHijacker::class,
        'Filament\Widgets\Widget' => FilamentWidgetHijacker::class,
        'Filament\Pages\Page' => FilamentPageHijacker::class,
        \Filament\Resources\RelationManagers\RelationManager::class => FilamentRelationManagerHijacker::class,
        \Illuminate\Database\Eloquent\Model::class => ModelHijacker::class,
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

    public static function breadthFirstSearch(array $ast, callable $filter): ?Stmt
    {
        $queue = $ast;
        while ($queue) {
            $node = array_shift($queue);
            if ($filter($node)) {
                return $node;
            }
            if (is_array($node)) {
                $queue = array_merge($queue, $node);
            } elseif (property_exists($node, 'stmts')) {
                $queue = array_merge($queue, $node->stmts);
            }
        }

        return null;
    }
}
