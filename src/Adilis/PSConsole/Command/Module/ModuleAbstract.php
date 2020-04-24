<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module;

use Adilis\PSConsole\Console\Helper\QuestionHelper;
use Adilis\PSConsole\Console\Question\HookQuestion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Validate;

abstract class ModuleAbstract extends Command {
    protected $_moduleName;
    protected $_moduleRelativePath;
    protected $_modulePath;
    protected $_moduleFilePath;
    protected $_hookList = [];
    protected $_filesystem;
    protected $_helper;

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->_moduleName = $input->getArgument('moduleName');
        $this->_filesystem = new Filesystem();
        $this->_helper = new QuestionHelper();

        if ($input->hasArgument('moduleName')) {
            if ($this->_moduleName === null || !Validate::isModuleName($this->_moduleName)) {
                $fieldQuestion = new Question('<question>Module name:</question>');
                $fieldQuestion->setValidator(function ($answer) {
                    if (!Validate::isModuleName($answer)) {
                        throw new \RuntimeException('Module name is incorrect');
                    }
                    return $answer;
                });
                $this->_moduleName = $this->_helper->ask($input, $output, $fieldQuestion);
            }

            $this->_moduleRelativePath = 'modules' . DIRECTORY_SEPARATOR . $this->_moduleName . DIRECTORY_SEPARATOR;
            $this->_modulePath = _PS_MODULE_DIR_ . $this->_moduleName . DIRECTORY_SEPARATOR;
            $this->_moduleFilePath = _PS_MODULE_DIR_ . $this->_moduleName . DIRECTORY_SEPARATOR . $this->_moduleName . '.php';
        }

        if ($input->hasArgument('hooksList')) {
            $this->_hookList = $input->getArgument('hooksList');
            if (!count($this->_hookList)) {
                $fieldQuestion = new HookQuestion('<question>Add hook :</question>');
                $this->_hookList = $this->_helper->askMany($input, $output, $fieldQuestion);
            }
        }
    }

    protected function addModuleNameArgument() {
        $this->addArgument('moduleName', InputArgument::OPTIONAL, 'module name');
        return $this;
    }

    protected function addHookListArgument() {
        $this->addArgument('hooksList', InputArgument::IS_ARRAY, 'hooks name (separate multiple with spaces)');
    }
}
