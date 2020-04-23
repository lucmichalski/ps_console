<?php

namespace Adilis\PSConsole\PhpParser\Builder;

use PhpParser\Node;

class ModuleUpgradeBuilder extends AbstractBuilder
{

    protected $_name;
    protected $_version;

    public function __construct(string $name, string $version = '')
    {
        parent::__construct();

        $this->_name = strtolower($name);
        $this->_version = $version;
    }

    public function getFilePath()
    {
        return _PS_MODULE_DIR_ . $this->_name . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR . 'upgrade-' . $this->_version . '.php';
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

        $functionName = 'upgrade_module_' . str_replace('.', '_', $this->_version);
        return $this->_builder
            ->function($functionName)
            ->addParam($this->_builder->param('module'))
            ->getNode();
    }
}
