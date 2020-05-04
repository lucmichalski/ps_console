<?php

namespace Adilis\PSConsole\Template\Builder;

class ModuleUpgradeTemplateBuilder extends AbstractTemplateBuilder {
    protected $_moduleName;
    protected $_version;

    public function __construct(string $moduleName, string $version = '') {
        $this->_moduleName = $moduleName;
        $this->_version = $version;
    }

    public function getFilePath() {
        return _PS_MODULE_DIR_ . $this->_name . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR . 'upgrade-' . $this->_version . '.php';
    }

    protected function getTemplateVars() {
        return [
            '{version}' => str_replace('.', '_', $this->_version)
        ];
    }

    protected function getTemplate() {
        return
"<?php

if (!defined('_PS_VERSION')) {
    exit;
}

function upgrade_module_{version}(\$module) {
    
}
";
    }
}
