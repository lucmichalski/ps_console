<?php

namespace Adilis\PSConsole\PhpParser\Printer;

use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Stmt;

class NicePrinter extends Standard {
    protected $nl = "\n";
    private $isFirstClassMethod = true;

    protected function pStmt_ClassMethod(Stmt\ClassMethod $node) {
        $return = ($this->isFirstClassMethod ? $this->nl : '')
            . $this->pModifiers($node->flags)
            . 'function ' . ($node->byRef ? '&' : '') . $node->name
            . '(' . $this->pCommaSeparated($node->params) . ')'
            . (null !== $node->returnType ? ' : ' . $this->p($node->returnType) : '')
            . (null !== $node->stmts
                ? ' {' . $this->pStmts($node->stmts) . $this->nl . '}' . $this->nl
                : ';');

        $this->isFirstClassMethod = false;
        return $return;
    }
}
