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

/**
 * Class Disable
 * Command sample description
 */
class DisableCommand extends ModuleAbstract
{
    protected function configure()
    {
        $this
            ->setName('module:disable')
            ->setDescription('Disable a module')
            ->addModuleNameArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($module = Module::getInstanceByName($this->_moduleName)) {
            if (Module::isInstalled($module->name)) {
                try {
                    $module->disable();
                } catch (PrestaShopException $e) {
                    $outputString = '<error>Error : module ' . $this->_moduleName . ' ' . $e->getMessage() . "<error>";
                    $output->writeln($outputString);
                    return;
                }
                $outputString = '<info>Module ' . $this->_moduleName . ' disable with sucess' . "</info>";
            } else {
                $outputString = '<error>Error : module ' . $this->_moduleName . ' is not installed' . "<error>";
            }
        } else {
            $outputString = '<error>Error : Unknow module name ' . $this->_moduleName . "</error>";
        }
        $output->writeln($outputString);
    }
}
