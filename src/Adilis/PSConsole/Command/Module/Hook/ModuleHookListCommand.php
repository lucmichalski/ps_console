<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module\Hook;

use Adilis\PSConsole\Command\Module\ModuleAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Module;

/**
 * Commande qui permet de lister les hooks d'un module
 *
 */
class ModuleHookListCommand extends ModuleAbstract {
    protected function configure() {
        $this
            ->setName('module:hook:list')
            ->setDescription('Get modules list')
            ->addModuleNameArgument();
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        if ($module = Module::getInstanceByName($this->_moduleName)) {
            //Possible hook list
            $possibleHooksList = $module->getPossibleHooksList();
            $moduleHooks = [];

            foreach ($possibleHooksList as $hook) {
                $isHooked = (int) $module->getPosition($hook['id_hook']);
                if ($isHooked != 0) {
                    $moduleHooks[] = $hook['name'];
                }
            }

            if (sizeof($moduleHooks)) {
                $output->writeln('<info>The module ' . $this->_moduleName . ' is linked on the folowing hooks :' . rtrim(implode(', ', $moduleHooks), ', ') . '</info>');
            } else {
                $output->writeln('<info>The module is not hooked</info>');
            }
        } else {
            $output->writeln('<error>Error the module ' . $this->_moduleName . ' doesn\'t exists</error>');
            return 1;
        }
    }
}
