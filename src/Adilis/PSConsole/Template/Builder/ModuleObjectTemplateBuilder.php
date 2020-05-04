<?php

namespace Adilis\PSConsole\Template\Builder;

class ModuleObjectTemplateBuilder extends AbstractTemplateBuilder {
    protected $_moduleName;
    protected $_className;
    protected $_tableName;
    protected $_primary;
    protected $_multishop;
    protected $_multilang = false;
    protected $_multilang_shop = false;
    protected $_fields;

    public function __construct(string $moduleName, string $className, string $tableName, string $primary, bool $multishop, array $fields = []) {
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

    protected function getFilePath() {
        return _PS_MODULE_DIR_ . $this->_moduleName . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $this->_className . '.php';
    }

    protected function getTemplateVars() {
        return [
            '{class_name}' => $this->_className,
            '{fields_properties}' => $this->buildFieldsProperties(),
            '{table_name}' => $this->_tableName,
            '{primary}' => $this->_primary,
            '{multilang}' => $this->_multilang ? 'true' : 'false',
            '{multishop}' => $this->_multishop ? 'true' : 'false',
            '{multilang_shop}' => $this->_multilang_shop ? 'true' : 'false',
            '{fields_definitions}' => $this->buildFieldsDefinition()
        ];
    }

    private function buildFieldsDefinition() {
        $strFlieldsDefinitions = '';

        foreach ($this->_fields as $field) {
            $strFlieldsDefinitions .= "\t\t\t'" . $field['name'] . "' => [";
            $strFlieldsDefinitions .= "'type' => self::TYPE_" . strtoupper($field['type']) . ', ';
            if ($field['validate']) {
                $strFlieldsDefinitions .= "'validate' => '" . $field['validate'] . "', ";
            }
            if ($field['required']) {
                $strFlieldsDefinitions .= "'required' => true, ";
            }
            if ($field['length']) {
                $strFlieldsDefinitions .= "'length' => " . (int)$field['length'] . ', ';
            }
            if ($field['lang']) {
                $strFlieldsDefinitions .= "'lang' => true, ";
            }
            if ($field['shop'] == true) {
                $strFlieldsDefinitions .= "'shop' => true, ";
            }
            $strFlieldsDefinitions = rtrim($strFlieldsDefinitions, ' ,');
            $strFlieldsDefinitions .= "],\n";
        }

        return rtrim($strFlieldsDefinitions, "\n,");
    }

    private function buildFieldsProperties() {
        $strFlieldsProperties = '';

        foreach ($this->_fields as $field) {
            $strFlieldsProperties .= "\tpublic \$" . $field['name'];
            switch ($field['type']) {
                case 'float':
                case 'int':
                    if ($field['required']) {
                        $strFlieldsProperties .= ' = 0';
                    }
                    break;
                case 'bool':
                    $strFlieldsProperties .= ' = 0';
                    break;
                case 'string':
                    if (!$field['lang'] && $field['required']) {
                        $strFlieldsProperties .= " = ''";
                    }
                    break;
            }
            $strFlieldsProperties .= ";\n";
        }

        return $strFlieldsProperties;
    }

    protected function getTemplate() {
        return
"<?php

if (!defined('_PS_VERSION')) {
    exit;
}

class {class_name} extends ObjectModel {
    public \$id;
{fields_properties}
    public static \$definition = [
        'table' => '{table_name}',
        'primary' => '{primary}',
        'multishop' => {multishop},
        'multilang' => {multilang},
        'multilang_shop' => {multilang_shop},
        'fields' => [
{fields_definitions}
        ]
    ];
}";
    }
}
