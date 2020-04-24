<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module;

use Module;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Reset
 * Command sample description
 */
class ModuleResetCommand extends ModuleAbstract {
    protected function configure() {
        $this
            ->setName('module:reset')
            ->setDescription('Reset module')
            ->addModuleNameArgument()
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'hard|soft(default)');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $type = $input->getOption('type');

        if ($module = Module::getInstanceByName($this->_moduleName)) {
            if (Module::isInstalled($module->name)) {
                try {
                    $error = false;
                    if (method_exists($module, 'reset') && $type != 'hard') {
                        if (!$module->reset()) {
                            $output->writeln("<error>Cannot reset module: '$this->_moduleName'</error>");
                            $error = true;
                        }
                    } else {
                        if ($module->uninstall()) {
                            if (!$module->install()) {
                                $output->writeln("<error>Cannot install module: '$this->_moduleName'</error>");
                                $error = true;
                            }
                        } else {
                            $output->writeln("<error>Cannot uninstall module: '$this->_moduleName'</error>");
                            $error = true;
                        }
                    }
                } catch (\Exception $e) {
                    $output->writeln("<error>Module: '$this->_moduleName' $e->getMessage()</error>");
                    $error = true;
                }
                if (!$error) {
                    $output->writeln("<info>Module '$this->_moduleName' reset with success</info>");
                } else {
                    return 1;
                }
            } else {
                $output->writeln("<comment>Module '$this->_moduleName' is uninstalled</comment>");
            }
        } else {
            $output->writeln("<error>Unknow module name '$this->_moduleName' </error>");
            return 1;
        }
    }
}
