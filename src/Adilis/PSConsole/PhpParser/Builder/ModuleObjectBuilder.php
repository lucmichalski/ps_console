<?php

namespace Adilis\PSConsole\PhpParser\Builder;

use PhpParser\Node;

class ModuleObjectBuilder extends AbstractBuilder {
    protected $_moduleName;
    protected $_className;
    protected $_tableName;
    protected $_primary;
    protected $_multishop;
    protected $_multilang = false;
    protected $_multilang_shop = false;
    protected $_fields;

    private $_classNode;

    public function __construct(string $moduleName, string $className, string $tableName, string $primary, bool $multishop, array $fields = []) {
        parent::__construct();

        $this->_moduleName = $moduleName;
        $this->_className = $className;
        $this->_tableName = $tableName;
        $this->_primary = $primary;
        $this->_multishop = (bool)$multishop;
        $this->_fields = $fields;

        foreach ($this->_fields as $field) {
            if ($field['lang']) {
                $this->_multilang = true;
                $this->_multilang_shop = $this->_multishop;
                break;
            }
        }
    }

    public function getFilePath() {
        return _PS_MODULE_DIR_ . $this->_moduleName . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $this->_className . '.php';
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
            ->class($this->_className)->extend('ObjectModel')
            ->addStmt($this->_builder->property('id'))
            ->addStmts($this->buildProperties())
            ->addStmt($this->buildDefinitionNode())
            ->getNode();
    }

    private function buildDefinitionNode() {
        return $this->_builder->property('definition')->makeStatic()->setDefault([
            'table' => $this->_tableName,
            'primary' => $this->_primary,
            'multishop' => $this->_multishop,
            'multilang' => $this->_multilang,
            'multilang_shop' => $this->_multilang_shop,
            'fields' => $this->buildFieldsDefinition()
        ]);
    }

    private function buildFieldsDefinition() {
        $definitions = [];

        foreach ($this->_fields as $field) {
            $definitions[$field['name']] = ['type' => new Node\Expr\ClassConstFetch(
                new Node\Name('self'),
                'TYPE_' . strtoupper($field['type'])
            )];
            if ($field['validate']) {
                $definitions[$field['name']]['validate'] = $field['validate'];
            }
            if ($field['length']) {
                $definitions[$field['name']]['length'] = (int)$field['length'];
            }
            if ($field['lang']) {
                $definitions[$field['name']]['lang'] = true;
            }
            if ($field['shop'] == true) {
                $definitions[$field['name']]['shop'] = true;
            }
        }

        return $definitions;
    }

    private function buildProperties() {
        $properties = [];

        foreach ($this->_fields as $field) {
            $node = $this->_builder->property($field['name']);
            switch ($field['type']) {
                case 'float':
                case 'int':
                    if ($field['required']) {
                        $node->setDefault(0);
                    }
                    break;
                case 'bool':
                    $node->setDefault(0);
                    break;
                case 'string':
                    if (!$field['lang'] && $field['required']) {
                        $node->setDefault('');
                    }
                    break;
            }
            $properties[] = $node;
        }

        return $properties;
    }
}
