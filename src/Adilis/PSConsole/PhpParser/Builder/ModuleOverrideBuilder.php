<?php

namespace Adilis\PSConsole\PhpParser\Builder;

use PhpParser\Node;

class ModuleOverrideBuilder extends AbstractBuilder
{

    protected $_name;

    public function __construct(string $name)
    {
        parent::__construct();

        $this->_name = $name;
    }

    public function getFilePath()
    {
        return _PS_OVERRIDE_DIR_ . 'modules' . DIRECTORY_SEPARATOR . $this->_name . DIRECTORY_SEPARATOR . $this->_name . '.php';
    }

    protected function buildNodes()
    {
        return [
            $this->buildSecurityNode(),
            $this->buildClassNode()
        ];
    }

    private function buildSecurityNode()
    {
        return new Node\Stmt\If_(
            new Node\Expr\BooleanNot(
                new Node\Expr\FuncCall(
                    new Node\Name('defined'),
                    [new Node\Scalar\String_('_PS_VERSION_')]
                )
            ),
            ['stmts' => [new Node\Expr\Exit_]]
        );
    }
    private function buildClassNode()
    {
        return $this->_builder
            ->class($this->_name . 'Override')
            ->extend($this->_name)
            ->getNode();
    }
}
