<?php

namespace Adilis\PSConsole\PhpParser\Builder;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;

class ModuleObjectInstallBuilder extends AbstractBuilder {
    protected $_moduleName;
    protected $_tableName;
    protected $_primary;
    protected $_multishop;
    protected $_multilang = false;
    protected $_multilang_shop = false;
    protected $_fields;

    public function __construct(string $moduleName, string $tableName, string $primary, bool $multishop, array $fields = []) {
        parent::__construct();

        $this->_moduleName = $moduleName;
        $this->_tableName = strtolower($tableName);
        $this->_primary = strtolower($primary);
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
        return _PS_MODULE_DIR_ . $this->_moduleName . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'install.php';
    }

    protected function buildNodes() {
        return [
            $this->buildSecurityNode(),
            $this->buildArrayQueriesNode(),
            $this->generateTableQuery(),
            $this->generateTableLangQuery(),
            $this->generateTableShopQuery(),
            $this->buildForeachNode()
        ];
    }

    private function buildSecurityNode() {
        return new If_(
            new BooleanNot(
                new FuncCall(
                    new Name('defined'),
                    [new String_('_PS_VERSION_')]
                )
            ),
            ['stmts' => [new Exit_]]
        );
    }

    private function buildArrayQueriesNode() {
        return new Assign(
            new Variable('sql'),
            new Array_
        );
    }

    private function buildForeachNode() {
        return new Foreach_(
            new Variable('sql'),
            new Variable('query'),
            [
                'stmts' => [
                    new If_(
                        new Equal(
                            new StaticCall(
                                new Name('Db'),
                                'getInstance',
                                [new Variable('query')]
                            ),
                            new ConstFetch(new Name('false'))
                        ),
                        [
                            'stmts' => [
                                new Return_(
                                    new ConstFetch(new Name('false'))
                                )
                            ]
                        ]
                    ),
                ]
            ]
        );
    }

    private function generateTableQuery() {
        $fields = array_filter($this->_fields, function ($field) {
            return $field['lang'] != true;
        });
        return new Assign(
            new ArrayDimFetch(
                new Variable('sql'),
                new String_($this->_tableName)
            ),
            new Concat(
                new Concat(
                    new Concat(
                        new Concat(
                            new String_('CREATE TABLE IF NOT EXISTS ' . chr(96)),
                            new ConstFetch(new Name('_DB_PREFIX_'))
                        ),
                        new String_(
                            $this->_tableName . chr(96) . ' (' . "\n" .
                            "\t`" . $this->_primary . "` int(11) unsigned NOT NULL AUTO_INCREMENT,\n" .
                            $this->generateFieldsQuery($fields) . "\n" .
                            "\tPRIMARY KEY (`" . $this->_primary . "`)\n" .
                            ') ENGINE='
                        )
                    ),
                    new ConstFetch(new Name('_MYSQL_ENGINE_'))
                ),
                new String_(' DEFAULT CHARSET=utf8;')
            )
        );
    }

    private function generateTableLangQuery() {
        if (!$this->_multilang) {
            return;
        }

        $fields = array_filter($this->_fields, function ($field) {
            return $field['lang'] == true;
        });
        return new Assign(
            new ArrayDimFetch(
                new Variable('sql'),
                new String_($this->_tableName . '_lang')
            ),
            new Concat(
                new Concat(
                    new Concat(
                        new Concat(
                            new String_('CREATE TABLE IF NOT EXISTS ' . chr(96)),
                            new ConstFetch(new Name('_DB_PREFIX_'))
                        ),
                        new String_(
                            $this->_tableName . '_lang' . chr(96) . ' (' . "\n" .
                            "\t`" . $this->_primary . "` int(11) unsigned NOT NULL,\n" .
                            "\t`id_lang` int(11) unsigned NOT NULL,\n" .
                            ($this->_multishop ? "\t`id_shop` int(11) unsigned NOT NULL DEFAULT \"1\",\n" : '') .
                            $this->generateFieldsQuery($fields) . "\n" .
                            "\tPRIMARY KEY (`" . $this->_primary . '`, `id_lang`' . ($this->_multishop ? ', `id_shop`' : '') . ")\n" .
                            ') ENGINE='
                        )
                    ),
                    new ConstFetch(new Name('_MYSQL_ENGINE_'))
                ),
                new String_(' DEFAULT CHARSET=utf8;')
            )
        );
    }

    private function generateTableShopQuery() {
        if (!$this->_multishop) {
            return;
        }
        $fields = array_filter($this->_fields, function ($field) {
            return $field['shop'] == true;
        });
        return new Assign(
            new ArrayDimFetch(
                new Variable('sql'),
                new String_($this->_tableName . '_shop')
            ),
            new Concat(
                new Concat(
                    new Concat(
                        new Concat(
                            new String_('CREATE TABLE IF NOT EXISTS ' . chr(96)),
                            new ConstFetch(new Name('_DB_PREFIX_'))
                        ),
                        new String_(
                            $this->_tableName . '_shop' . chr(96) . ' (' . "\n" .
                            "\t`" . $this->_primary . "` int(11) unsigned NOT NULL,\n" .
                            "\t`id_shop` int(11) unsigned NOT NULL,\n" .
                            $this->generateFieldsQuery($fields) . "\n" .
                            "\tPRIMARY KEY (`" . $this->_primary . "`, `id_shop`)\n" .
                            ') ENGINE='
                        )
                    ),
                    new ConstFetch(new Name('_MYSQL_ENGINE_'))
                ),
                new String_(' DEFAULT CHARSET=utf8;')
            )
        );
    }

    private function generateFieldsQuery($fields) {
        $sqlQueryString = '';
        foreach ($fields as $field) {
            $required = ($field['required'] !== false) ? 'NOT NULL' : 'DEFAULT NULL';
            $sqlQueryString .= "\t`" . $field['name'] . '`';

            switch ($field['type']) {
                case 'int':
                    $fieldLength = $field['length'] ? $field['length'] : 11;
                    $sqlQueryString .= ' INT(' . (int) $fieldLength . ') unsigned' . $required;
                    if ($field['required'] === false) {
                        $sqlQueryString .= ' DEFAULT "0"';
                    }
                    break;
                case 'bool':
                    $sqlQueryString .= ' TINYINT(1) NOT NULL unsigned DEFAULT "0"';
                    break;
                case 'string':
                    $fieldLength = $field['length'] ? $field['length'] : 255;
                    $sqlQueryString .= ' VARCHAR (' . (int) $fieldLength . ') ' . $required . '';
                    break;
                case 'float':
                    $fieldLength = (int) ($field['length'] ? $field['length'] : 20);
                    $fieldLengthAfter = (int) (isset($field['length_after']) && $field['length_after'] ? $field['length_after'] : 6);
                    $sqlQueryString .= ' DECIMAL(' . (int) $fieldLength . ',' . (int) $fieldLengthAfter . ') ' . $required;
                    if ($field['required'] === false) {
                        $sqlQueryString .= ' DEFAULT "0.' . str_pad('', $fieldLengthAfter, '0') . '"';
                    }
                    break;
                case 'date':
                    $sqlQueryString .= ' datetime NOT NULL';
                    break;
                case 'html':
                    $sqlQueryString .= ' text';
                    break;
            }
        }
        return $sqlQueryString;
    }
}
