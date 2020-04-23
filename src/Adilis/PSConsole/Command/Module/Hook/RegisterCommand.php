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

class RegisterCommand extends ModuleAbstract
{
    protected function configure()
    {
        $this
            ->setName('module:hook:register')
            ->setDescription('Add module to one or several hooks')
            ->addModuleNameArgument()
            ->addHookListArgument();
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($module = Module::getInstanceByName($this->_moduleName)) {
            if (!$module->registerHook($this->_hookList)) {
                $output->writeln('<error>Error during hook assignation</error>');
            } else {
                $output->writeln('<info>Module hooked with success</info>');
            }
        } else {
            $output->writeln('<error>Error the module ' . $this->_moduleName . ' doesn\'t exists</error>');
            return 1;
        }
    }
}
