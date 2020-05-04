<?php

namespace Adilis\PSConsole\Template\Builder;

abstract class AbstractTemplateBuilder {
    abstract protected function getFilePath();

    abstract protected function getTemplate();

    abstract protected function getTemplateVars();

    public function getContent() {
        $content = $this->getTemplate();
        $templateVars = $this->getTemplateVars();
        if (!is_array($templateVars)) {
            throw new \Exception('getTemplateVars must return an array');
        }
        $content = str_replace(array_keys($templateVars), array_values($templateVars), $content);
        return $content;
    }

    public function writeFile() {
        $filesystem = new \Symfony\Component\Filesystem\Filesystem;
        try {
            $filesystem->dumpFile($this->getFilePath(), $this->getContent());
        } catch (\Symfony\Component\Filesystem\Exception\IOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
