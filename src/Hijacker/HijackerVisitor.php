<?php

namespace BeraniDigitalID\FilamentAccess\Hijacker;

use BeraniDigitalID\FilamentAccess\Analyzer\AnalyzerResult;
use Illuminate\Support\Facades\Log;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class HijackerVisitor extends NodeVisitorAbstract
{
    public function __construct(private readonly AnalyzerResult $arg) {}

    public function enterNode(Node $node)
    {

        if ($node instanceof Node\Stmt\Class_) {

            if (class_basename($this->arg->class) === $node->name->name) {
                $handler = BaseHijacker::$handlers[$this->arg->type] ?? null;
                if ($handler) {
                    $handler::hijack($node);
                } else {
                    Log::warning('No handler for ' . $this->arg->type);
                }
            }
        }

        return null;
    }
}
