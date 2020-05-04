<?php

namespace Adilis\PSConsole\Template\Builder;

class FrontControllerOverrideTemplateBuilder extends AbstractClassOverrideTemplateBuilder {
    protected function getFilePath() {
        return _PS_OVERRIDE_DIR_ . 'controllers' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . $this->_path;
    }
}
