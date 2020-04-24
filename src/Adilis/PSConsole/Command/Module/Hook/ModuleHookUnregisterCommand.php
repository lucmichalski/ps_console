<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module\Hook;

use Adilis\PSConsole\Command\Module\ModuleAbstract;
use Adilis\PSConsole\Console\Question\HookQuestion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Module;

class ModuleHookUnregisterCommand extends ModuleAbstract {
    protected function configure() {
        $this
            ->setName('module:hook:unregister')
            ->setDescription('Remove module to one or several hooks')
            ->addModuleNameArgument()
            ->addHookListArgument();
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $hookList = $input->getArgument('hooksList');
        if (!count($hookList)) {
            $fieldQuestion = new HookQuestion('<question>Add hook :</question>');
            $hookList = $this->_helper->askMany($input, $output, $fieldQuestion);
        }

        if ($module = Module::getInstanceByName($this->_moduleName)) {
            if (sizeof($hookList)) {
                foreach ($hookList as $hook) {
                    if (!$module->unregisterHook($hook)) {
                        $output->writeln('<error>Error during hook remove from hook ' . $hook . '</error>');
                        return 1;
                    } else {
                        $output->writeln('<info>Module remove from hook ' . $hook . ' with success</info>');
                    }
                }
            } else {
                $output->writeln('<error>Not hooks given for ' . $this->_moduleName . '</error>');
                return 1;
            }
        } else {
            $output->writeln('<error>Error the module ' . $this->_moduleName . ' doesn\'t exists</error>');
            return 1;
        }
    }
}
