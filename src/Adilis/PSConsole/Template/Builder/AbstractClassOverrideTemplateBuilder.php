<?php

namespace Adilis\PSConsole\Template\Builder;

abstract class AbstractClassOverrideTemplateBuilder extends AbstractTemplateBuilder {
    protected $_className;
    protected $_path;

    public function __construct(string $className, string $path) {
        $this->_className = $className;
        $this->_path = $path;
    }

    protected function getTemplateVars() {
        return [
            '{class_name}' => $this->_className
        ];
    }

    protected function getTemplate() {
        return
"<?php

if (!defined('_PS_VERSION')) {
    exit;
}

class {class_name} extends {class_name}Core {
    
}
";
    }
}
