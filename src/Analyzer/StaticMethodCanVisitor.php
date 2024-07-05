<?php

namespace BeraniDigitalID\FilamentAccess\Analyzer;

use Illuminate\Support\Str;
use PhpParser\NodeVisitorAbstract;

class StaticMethodCanVisitor extends NodeVisitorAbstract
{
    public array $canMethods = [];

    public function enterNode(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            if (Str::startsWith($node->name->name, 'can')) {
                $this->canMethods[] = $node;
            }
        }
    }
}
