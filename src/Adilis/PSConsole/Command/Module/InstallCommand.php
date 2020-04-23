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
 * Class Install
 * Command sample description
 */
class InstallCommand extends ModuleAbstract
{
    protected function configure()
    {
        $this
            ->setName('module:install')
            ->addModuleNameArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($module = Module::getInstanceByName($this->_moduleName)) {
            if (!Module::isInstalled($module->name)) {
                try {
                    if (!$module->install()) {
                        $output->writeln("<error>Cannot install module: '$this->_moduleName'</error>");
                        return 1;
                    }
                } catch (PrestaShopException $e) {
                    $output->writeln("<error>Module: '$this->_moduleName' $e->displayMessage()</error>");
                    return 1;
                }
                $output->writeln("<info>Module '$this->_moduleName' installed with success</info>");
            } else {
                $output->writeln("<comment>Module '$this->_moduleName' is already installed</comment>");
            }
        } else {
            $output->writeln("<error>Unknow module name '$this->_moduleName' </error>");
            return 1;
        }
    }
}
