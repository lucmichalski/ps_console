<?php

namespace Adilis\PSConsole\Template\Builder;

class ModuleObjectInstallTemplateBuilder extends AbstractTemplateBuilder {
    protected $_moduleName;
    protected $_tableName;
    protected $_primary;
    protected $_multishop;
    protected $_multilang = false;
    protected $_multilang_shop = false;
    protected $_fields;

    public function __construct(string $moduleName, string $tableName, string $primary, bool $multishop, array $fields = []) {
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

    protected function getFilePath() {
        return _PS_MODULE_DIR_ . $this->_moduleName . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'install.php';
    }

    protected function getTemplateVars() {
        return [
            '{queries}' => $this->getQueries()
        ];
    }

    private function getQueries() {
        $queries = [];
        $queries[] = $this->getMainQuery();

        if ($this->_multilang) {
            $queries[] = $this->getLangQuery();
        }

        if ($this->_multishop) {
            $queries[] = $this->getShopQuery();
        }

        return implode("\n\n", $queries);
    }

    private function getMainQuery() {
        $fields = array_filter($this->_fields, function ($field) {
            return $field['lang'] != true;
        });

        $strQuery = '$sql[] = \'CREATE TABLE IF NOT EXISTS `' . $this->_tableName . '` (
    `' . $this->_primary . '` int(11) unsigned NOT NULL,
' . $this->generateFieldsQuery($fields) . '
    PRIMARY KEY (`' . $this->_primary . '`)
) ENGINE= \' . _MYSQL_ENGINE_ . \' DEFAULT CHARSET=utf8;\';';

        return $strQuery;
    }

    private function getLangQuery() {
        $fields = array_filter($this->_fields, function ($field) {
            return $field['lang'] == true;
        });

        $strQuery = '$sql[] = \'CREATE TABLE IF NOT EXISTS `' . $this->_tableName . '_lang` (
    `' . $this->_primary . '` int(11) unsigned NOT NULL,
    `id_lang` int(11) unsigned NOT NULL,
    ' . ($this->_multishop ? '`id_shop` int(11) unsigned NOT NULL DEFAULT "1"' : '') . '
' . $this->generateFieldsQuery($fields) . '
    PRIMARY KEY (`' . $this->_primary . '`, `id_lang`' . ($this->_multishop ? ', `id_shop`' : '') . ')
) ENGINE=\' . _MYSQL_ENGINE_ . \' DEFAULT CHARSET=utf8;\';';

        return $strQuery;
    }

    private function getShopQuery() {
        $fields = array_filter($this->_fields, function ($field) {
            return $field['shop'] == true;
        });
        $strQuery = '$sql[] = \'CREATE TABLE IF NOT EXISTS `' . $this->_tableName . '_shop` (
    `' . $this->_primary . '` int(11) unsigned NOT NULL,
    `id_shop` int(11) unsigned NOT NULL,
' . $this->generateFieldsQuery($fields) . '
    PRIMARY KEY (`' . $this->_primary . '`, `id_shop`)
) ENGINE=\' . _MYSQL_ENGINE_ . \' DEFAULT CHARSET=utf8;\';';

        return $strQuery;
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
            $sqlQueryString .= ",\n";
        }
        return rtrim($sqlQueryString, "\n");
    }

    protected function getTemplate() {
        return
"<?php

if (!defined('_PS_VERSION')) {
    exit;
}

\$sql = [];

{queries}

foreach (\$sql as \$query) {
    if (Db::getInstance(\$query) == false) {
        return false;
    }
}
";
    }
}
