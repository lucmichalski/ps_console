<?php

namespace Adilis\PSConsole\Template\Builder;

class ClassOverrideTemplateBuilder extends AbstractClassOverrideTemplateBuilder {
    protected function getFilePath() {
        return _PS_OVERRIDE_DIR_ . 'classes' . DIRECTORY_SEPARATOR . $this->_path;
    }
}
