<?php

namespace Adilis\PSConsole\Template\Builder;

class ModuleOverrideTemplateBuilder extends AbstractTemplateBuilder {
    protected $_moduleName;

    public function __construct(string $moduleName) {
        $this->_moduleName = $moduleName;
    }

    public function getFilePath() {
        return _PS_OVERRIDE_DIR_ . 'modules' . DIRECTORY_SEPARATOR . $this->_name . DIRECTORY_SEPARATOR . $this->_name . '.php';
    }

    protected function getTemplateVars() {
        return [
            '{module_name}' => $this->_moduleName
        ];
    }

    protected function getTemplate() {
        return
"<?php

if (!defined('_PS_VERSION')) {
    exit;
}

class {module_name}Override extends {module_name} {
    
}
";
    }
}
