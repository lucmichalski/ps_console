<?php
/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module;

use Module;
use PrestaShopException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleEnableCommand extends ModuleAbstract {
    protected function configure() {
        $this
            ->setName('module:enable')
            ->setDescription('Enable a module')
            ->addModuleNameArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($module = Module::getInstanceByName($this->_moduleName)) {
            if (Module::isInstalled($module->name)) {
                try {
                    $module->enable();
                } catch (PrestaShopException $e) {
                    $outputString = '<error>module ' . $this->_moduleName . ' ' . $e->getMessage() . '</error>';
                    $output->writeln($outputString);
                    return;
                }
                $outputString = '<info>Module ' . $this->_moduleName . ' enable with sucess' . '</info>';
            } else {
                $outputString = '<error>module ' . $this->_moduleName . ' is not installed' . '<error>';
            }
        } else {
            $outputString = '<error>Unknow module name ' . $this->_moduleName . '<error>';
        }
        $output->writeln($outputString);
    }
}
