<?php

namespace Adilis\PSConsole\Template\Builder;

class ModuleTemplateBuilder extends AbstractTemplateBuilder {
    protected $_moduleName;
    protected $_author;
    protected $_displayName;
    protected $_description;
    protected $_hookList;
    protected $_implementWidget;
    protected $_generateTemplate;

    public function __construct(string $moduleName, string $author = '', string $displayName = '', string $description = '', array $hookList = [], bool $implementWidget = false, bool $generateTemplate = false) {
        $this->_moduleName = $moduleName;
        $this->_author = $author;
        $this->_displayName = $displayName;
        $this->_description = $description;
        $this->_hookList = $hookList;
        $this->_implementWidget = $implementWidget;
        $this->_generateTemplate = $generateTemplate;
    }

    public function getFilePath() {
        return _PS_MODULE_DIR_ . $this->_moduleName . DIRECTORY_SEPARATOR . $this->_moduleName . '.php';
    }

    protected function getTemplateVars() {
        return [
            '{class_name}' => $this->_moduleName,
            '{author}' => $this->_author,
            '{tab}' => '',
            '{display_name}' => $this->_description,
            '{description}' => $this->_displayName,
            '{hook_registers}' => $this->getHookRegisters(),
            '{hook_functions}' => $this->getHookFunctions(),
            '{widgetinterface_functions}' => $this->getWidgetinterfaceFunctions(),
            '{widgetinterface_implement}' => $this->getWidgetinterfaceImplement(),
            '{widgetinterface_namespace}' => $this->getWidgetinterfaceNamespace(),
        ];
    }

    private function getWidgetinterfaceImplement() {
        return $this->_implementWidget ? ' implements WidgetInterface' : '';
    }

    private function getWidgetinterfaceFunctions() {
        if (!$this->_implementWidget) {
            return '';
        }

        return '

    public function renderWidget($hookName = null, array $configuration = [])
    {
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
    }';
    }

    private function getWidgetinterfaceNamespace() {
        if (!$this->_implementWidget) {
            return '';
        }

        return 'use PrestaShop\PrestaShop\Core\Module\WidgetInterface;';
    }

    private function getHookRegisters() {
        $strHookRegisters = '';
        foreach ($this->_hookList as $hook) {
            $strHookRegisters .= "\n\t\t\t&& \$this->registerHook('" . $hook . "')";
        }
        return $strHookRegisters;
    }

    private function getHookFunctions() {
        $strHookFunctions = "\n";
        foreach ($this->_hookList as $hook) {
            $strHookFunctions .= "\n";
            $strHookFunctions .=
                preg_match('#^display#', $hook) ?
                $this->getDisplayHookFunction($hook) :
                $this->getSimpleHookFunction($hook);
        }
        return $strHookFunctions;
    }

    private function getSimpleHookFunction($hook) {
        $methodName = 'hook' . ucfirst($hook);
        return '       
    public function ' . $methodName . '($params = [])
    {
    }';
    }

    private function getDisplayHookFunction($hook) {
        $methodName = 'hook' . ucfirst($hook);
        return '       
    public function ' . $methodName . '($params = [])
    {
        return $this->display(__FILE__, \'views/templates/hook/' . strtolower($hook) . '.tpl\');
    }';
    }

    protected function getTemplate() {
        return
"<?php
{widgetinterface_namespace}
if (!defined('_PS_VERSION')) {
    exit;
}

class {class_name} extends Module{widgetinterface_implement} {
    public function __construct() {

        \$this->name = '{class_name}';
        \$this->author = '{author}';
        \$this->need_instance = 0;
        \$this->tab = '{tab}';
        \$this->version = '0.0.1';
        \$this->bootstrap = true;
        \$this->displayName = \$this->l('{display_name}');
        \$this->description = \$this->l('{description}');
        \$this->confirmUninstall = \$this->l('Are you sure ?');

        parent::__construct();
    }

    public function install() {
        return
            parent::install(){hook_registers}
        ;
    }

    public function hookDisplayHeader() {
    }

    public function hookBackOfficeHeader() {
        if (Tools::getValue('configure') == \$this->name) {
        }
    }{widgetinterface_functions}{hook_functions}
}
";
    }
}
