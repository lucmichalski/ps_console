<?php

namespace Adilis\PSConsole\PhpParser\Builder;

use PhpParser\Node;

class FrontControllerOverrideBuilder extends AbstractBuilder {
    protected $_name;
    protected $_path;

    public function __construct(string $name, string $path) {
        parent::__construct();

        $this->_name = $name;
        $this->_path = $path;
    }

    public function getFilePath() {
        return _PS_OVERRIDE_DIR_ . 'controllers' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . $this->_path;
    }

    protected function buildNodes() {
        return [
            $this->buildSecurityNode(),
            $this->buildClassNode()
        ];
    }

    private function buildSecurityNode() {
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

    private function buildClassNode() {
        return $this->_builder
            ->class($this->_name)
            ->extend($this->_name . 'Core')
            ->getNode();
    }
}
