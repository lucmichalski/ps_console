<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Hook;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Hook;
use Symfony\Component\Console\Helper\TableSeparator;

class HookModulesCommand extends Command {
    protected function configure() {
        $this
            ->setName('hook:modules')
            ->setDescription('List all hooks with hooked modules');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $hooks = array_map(function ($row) {
            return $row['name'];
        }, Hook::getHooks());

        $table = new Table($output);
        $table->setHeaders(['Hook Name', 'Modules hooked']);

        foreach ($hooks as $hook) {
            $hookModules = Hook::getHookModuleExecList($hook);
            if ($hookModules) {
                $hookModulesInformations = '';
                foreach ($hookModules as $index => $hookModule) {
                    $hookModulesInformations .= ($index + 1) . '.' . $hookModule['module'] . "\n";
                }
                $table->addRow([$hook, trim($hookModulesInformations, ', ')]);
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();
    }
}
