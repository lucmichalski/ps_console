<?php

namespace Adilis\PSConsole\PhpParser\NodeVisitor;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

class ModuleAddHookNodeVisitor extends NodeVisitorAbstract {
    const HOOKS_PROPERTY_NAME = 'hooks';

    protected $_moduleName;
    protected $_methodName;

    public function __construct($moduleName, $hookName) {
        $this->_moduleName = $moduleName;
        $this->_hookName = $hookName;
        $this->_methodName = 'hook' . ucfirst($this->_hookName);
        $this->_finder = new NodeFinder;
        $this->_builder = new BuilderFactory;

        $this->_haveMethod = false;
        $this->_haveHooksProperty = false;
    }

    public function afterTraverse(array $nodes) {
        if ($this->_haveMethod && $this->_haveHooksProperty) {
            return;
        }

        $classNode = $this->_finder->findFirst($nodes, function (Node $node) {
            return
                $node instanceof Class_ &&
                strtolower($node->name) == strtolower($this->_moduleName);
        });

        if ($classNode !== null) {
            if (!$this->_haveMethod) {
                $classNode->stmts[] =
                    $this->_builder
                    ->method($this->_methodName)->makePublic()
                    ->addParam($this->_builder->param('params'))
                    ->getNode();
            }

            if (!$this->_haveHooksProperty) {
                array_unshift(
                    $classNode->stmts,
                    $this->_builder
                        ->property(self::HOOKS_PROPERTY_NAME)
                        ->makePublic()
                        ->setDefault([$this->_hookName])
                        ->getNode()
                );
            }
        }
    }

    public function enterNode(Node $node) {
        dump($node);
        if ($this->_haveMethod && $this->_haveHooksProperty) {
            return;
        }

        if (
            $node instanceof ClassMethod &&
            strtolower($node->name) === strtolower($this->_methodName)
        ) {
            $this->_haveMethod = true;
            return;
        }

        if (
            $node instanceof PropertyProperty &&
            $node->default instanceof Array_ &&
            strtolower($node->name) === self::HOOKS_PROPERTY_NAME
        ) {
            $this->_haveHooksProperty = true;

            $hooks = [$this->_hookName];
            foreach ($node->default->items as $item) {
                $hooks[] = $item->value->value;
            }
            $hooks = array_values(array_unique($hooks));
            return $this->_builder
                ->property(self::HOOKS_PROPERTY_NAME)
                ->makePublic()
                ->setDefault($hooks)
                ->getNode()->props[0];
        }
    }
}
