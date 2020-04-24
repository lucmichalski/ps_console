<?php

namespace Adilis\PSConsole\PhpParser\Builder;

use PhpParser\Node;

class ModuleBuilder extends AbstractBuilder {
    protected $_name;
    protected $_author;
    protected $_displayName;
    protected $_description;
    protected $_hookList;
    protected $_implementWidget;
    protected $_generateTemplate;

    public function __construct(string $name, string $author = '', string $displayName = '', string $description = '', array $hookList = [], bool $implementWidget = false, bool $generateTemplate = false) {
        parent::__construct();

        $this->_name = strtolower($name);
        $this->_author = $author;
        $this->_displayName = $displayName;
        $this->_description = $description;
        $this->_hookList = $hookList;
        $this->_implementWidget = $implementWidget;
        $this->_generateTemplate = $generateTemplate;
    }

    public function getFilePath() {
        return _PS_MODULE_DIR_ . $this->_name . DIRECTORY_SEPARATOR . $this->_name . '.php';
    }

    protected function buildNodes() {
        return [
            ($this->_implementWidget) ? $this->_builder->use('PrestaShop\PrestaShop\Core\Module\WidgetInterface')->getNode() : null,
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
        $hooks = ['displayHeader', 'backOfficeHeader'];
        foreach ($this->_hookList as $hook) {
            $hooks[] = $hook;
        }
        $hooks = array_unique($hooks);

        $this->_classNode = $this->_builder->class($this->_name);
        $this->_classNode->extend('Module')
            ->addStmt($this->_builder->property('hooks')->makePrivate()->setDefault($hooks))
            ->addStmt(
                $this->_builder->method('__construct')
                    ->makePublic()
                    ->addStmts([
                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'name'
                            ),
                            new Node\Scalar\String_($this->_name)
                        ),
                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'author'
                            ),
                            new Node\Scalar\String_($this->_author)
                        ),
                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'need_instance'
                            ),
                            new Node\Scalar\LNumber(0)
                        ),
                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'tab'
                            ),
                            new Node\Scalar\String_('tab')
                        ),
                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'version'
                            ),
                            new Node\Scalar\String_('0.1.0')
                        ),
                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'bootstrap'
                            ),
                            new Node\Expr\ConstFetch(new Node\Name('true'))
                        ),
                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'displayName'
                            ),
                            new Node\Expr\MethodCall(
                                new Node\Expr\Variable('this'),
                                'l',
                                [new Node\Scalar\String_($this->_displayName)]
                            )
                        ),
                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'description'
                            ),
                            new Node\Expr\MethodCall(
                                new Node\Expr\Variable('this'),
                                'l',
                                [new Node\Scalar\String_($this->_description)]
                            )
                        ),

                        new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'confirmUninstall'
                            ),
                            new Node\Expr\MethodCall(
                                new Node\Expr\Variable('this'),
                                'l',
                                [new Node\Scalar\String_('Are you sure ?')]
                            )
                        ),

                        new Node\Expr\StaticCall(
                            new Node\Name('parent'),
                            '__construct'
                        )
                        /*,
                        */
                    ])
            )
            ->addStmt(
                $this->_builder->method('install')
                    ->makePublic()
                    ->addStmt(
                        new Node\Stmt\Return_(
                            new Node\Expr\BinaryOp\LogicalAnd(
                                new Node\Expr\StaticCall(
                                    new Node\Name('parent'),
                                    'install'
                                ),
                                new Node\Expr\MethodCall(
                                    new Node\Expr\Variable('this'),
                                    'registerHook',
                                    [
                                        new Node\Expr\PropertyFetch(
                                            new Node\Expr\Variable('this'),
                                            'hooks'
                                        )
                                    ]
                                )
                            )
                        )
                    )
            )
            ->addStmt(
                $this->_builder->method('uninstall')
                    ->makePublic()
                    ->addStmt(new Node\Stmt\Return_(
                        new Node\Expr\StaticCall(
                            new Node\Name('parent'),
                            'uninstall'
                        )
                    ))
            )
            ->addStmt($this->addHookMethod('hookDisplayHeader'))
            ->addStmt(
                $this->addHookMethod('hookBackOfficeHeader', [
                    new Node\Stmt\If_(
                        new Node\Expr\BinaryOp\Equal(
                            new Node\Expr\StaticCall(
                                new Node\Name('Tools'),
                                'getValue',
                                [new Node\Scalar\String_('configure')]
                            ),
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'name'
                            )
                        )
                    )
                ])
            );

        if ($this->_implementWidget) {
            $this->_classNode
                ->implement('WidgetInterface')
                ->addStmt(
                    $this->_builder->method('renderWidget')
                        ->makePublic()
                        ->addParam($this->_builder->param('hookName')->setDefault(null))
                        ->addParam($this->_builder->param('configuration')->setDefault([]))
                )
                ->addStmt(
                    $this->_builder->method('getWidgetVariables')
                        ->makePublic()
                        ->addParam($this->_builder->param('hookName')->setDefault(null))
                        ->addParam($this->_builder->param('configuration')->setDefault([]))
                );
        }

        foreach ($this->_hookList as $hook) {
            $this->_classNode->addStmt(
                $this->addHookMethod($hook)
            );
        }

        return $this->_classNode->getNode();
    }

    private function addHookMethod($hook, array $stmts = []) {
        $method_name = 'hook' . ucfirst($hook);
        $method_node = $this->_builder->method($method_name)
            ->makePublic()
            ->addParam($this->_builder->param('params')->setDefault([]))
            ->addStmts($stmts);

        if ($this->_generateTemplate && preg_match('#^display#', $hook)) {
            $method_node->addStmt(
                new Node\Stmt\Return_(
                    new Node\Expr\MethodCall(
                        new Node\Expr\Variable('this'),
                        'display',
                        [
                            new Node\Scalar\MagicConst\File,
                            new Node\Scalar\String_('views/templates/hook/' . strtolower($hook) . '.tpl')
                        ]
                    )
                )
            );
        }

        return $method_node;
    }
}
