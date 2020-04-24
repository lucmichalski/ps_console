<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module\Controller;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class controller
 * Command sample description
 */
class ModuleControllerCreateCommand extends Command {
    /** @var string Module Name */
    protected $_moduleName;

    /** @var string Controller Name */
    protected $_controllerName;

    /** @var string Controller Type */
    protected $_controllerType;

    /** @var bool Generate template or not */
    protected $_template;

    /** @var Filesystem */
    protected $_filesystem;

    /**
     * @inheritDoc
     */
    protected function configure() {
        $this
            ->setName('module:controller:create')
            ->setDescription('Generate module controller file')
            ->addArgument('moduleName', InputArgument::REQUIRED, 'module name');
        ;
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $this->_moduleName = $input->getArgument('moduleName');

        if (!is_dir(_PS_MODULE_DIR_ . $this->_moduleName)) {
            $output->writeln('<error>Module not exists</error>');
            return 1;
        }

        $helper = $this->getHelper('question');
        $this->_filesystem = new Filesystem();

        $fieldQuestion = new Question('<question>Controller type (default is admin) :</question>', 'admin');
        $fieldQuestion->setAutocompleterValues(['admin', 'front']);
        $fieldQuestion->setValidator(function ($answer) {
            if ($answer !== null && !in_array($answer, ['admin', 'front'])) {
                throw new \RuntimeException('The field type must be part of the suggested');
            }
            return $answer;
        });

        $this->_controllerType = $helper->ask($input, $output, $fieldQuestion);

        $fieldQuestion = new Question('<question>Controller name :</question>', 'default');
        $this->_controllerName = $helper->ask($input, $output, $fieldQuestion);

        //Create all module directories
        try {
            $this->_createDirectories();
        } catch (IOException $e) {
            $output->writeln('<error>Unable to creat controller directories</error>');
            return 1;
        }
        $controllerClass = ucfirst($this->_moduleName) . ucfirst($this->_controllerName);
        if ($this->_controllerType == 'admin') {
            $defaultContent = $this->_getAdminControllerContent();
            if ($this->_template === true) {
                $output->writeln('<info>Template cannot be generated for admin controllers</info>');
            }
        } else {
            $defaultContent = $this->_getFrontControllerContent();
            if ($this->_template === true) {
                $this->_generateTemplate();
            }
        }

        $defaultContent = str_replace('{controllerClass}', $controllerClass, $defaultContent);

        try {
            $this->_filesystem->dumpFile(
                _PS_MODULE_DIR_ . $this->_moduleName . '/controllers/' . $this->_controllerType . '/' . strtolower($this->_controllerName) . '.php',
                $defaultContent
            );
        } catch (IOException $e) {
            $output->writeln('<error>Unable to creat controller directories</error>');
            return 1;
        }

        echo $output->writeln('<info>Controller ' . $this->_controllerName . ' created with sucess');
    }

    protected function _createDirectories() {
        if ($this->_controllerType == 'admin') {
            if (!$this->_filesystem->exists(_PS_MODULE_DIR_ . $this->_moduleName . '/controllers/admin')) {
                $this->_filesystem->mkdir(_PS_MODULE_DIR_ . $this->_moduleName . '/controllers/admin', 0775);
            }
        } else {
            if (!$this->_filesystem->exists(_PS_MODULE_DIR_ . $this->_moduleName . '/controllers/front')) {
                $this->_filesystem->mkdir(_PS_MODULE_DIR_ . $this->_moduleName . '/controllers/front', 0775);
            }
            if ($this->_template) {
                if (!$this->_filesystem->exists(_PS_MODULE_DIR_ . $this->_moduleName . '/views/templates/front')) {
                    $this->_filesystem->mkdir(_PS_MODULE_DIR_ . $this->_moduleName . '/views/templates/front', 0775);
                }
            }
        }
        $this->addIndexFiles(_PS_MODULE_DIR_ . $this->_moduleName . '/controllers');
    }

    private function addIndexFiles($dir) {
        try {
            if (!is_dir($dir)) {
                throw new \Exception('directory doesn\'t exists');
            }

            $finder = new Finder();
            $indexFile = $finder->files()->in((string) $dir)->depth('==0')->name('index.php');
            if (!sizeof($indexFile)) {
                copy(_PS_IMG_DIR_ . 'index.php', $dir . DIRECTORY_SEPARATOR . 'index.php');
            }

            $directories = $finder->directories()->in($dir);

            $i = 0;
            foreach ($directories as $directory) {
                ${$i} = new Finder();
                $indexFile = ${$i}->files()->in((string) $directory)->depth('==0')->name('index.php');
                if (!sizeof($indexFile)) {
                    copy(_PS_IMG_DIR_ . 'index.php', $directory . DIRECTORY_SEPARATOR . 'index.php');
                }
                $i++;
            }
        } catch (\Exception $e) {
            $output->writeln('<info>ERROR:' . $e->getMessage() . '</info>');
        }
    }

    protected function _getAdminControllerContent() {
        return
            '<?php
class {controllerClass}Controller extends ModuleAdminController {
 
 
}';
    }
}
