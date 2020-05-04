<?php

namespace Adilis\PSConsole\Template\Builder;

class AdminControllerOverrideTemplateBuilder extends AbstractClassOverrideTemplateBuilder {
    protected function getFilePath() {
        return _PS_OVERRIDE_DIR_ . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . $this->_path;
    }
}
