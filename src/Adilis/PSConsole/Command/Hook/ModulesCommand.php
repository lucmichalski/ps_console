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

/**
 * Class Module
 * List hook with registered modules
 */
class ModulesCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('hook:modules')
            ->setDescription('List all hooks with hooked modules');
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        //Get Hooks list
        $hooks = Hook::getHooks();

        //Extract only hooks name
        $hooks = array_map(function ($row) {
            return $row['name'];
        }, $hooks);

        //Sort hooks by name
        usort($hooks, array($this, "cmp"));

        //Init Table
        $table = new Table($output);
        $table->setHeaders(['Hook Name', 'Modules hooked']);

        foreach ($hooks as $hook) {

            //Get Modules hooked
            $hookModules = Hook::getHookModuleExecList($hook);

            if ($hookModules) {

                //Add module information on hook
                $hookModulesInformations = '';
                foreach ($hookModules as $index => $hookModule) {
                    $hookModulesInformations .= ($index + 1) . "." . $hookModule['module'] . "\n";
                }
                $table->addRow([$hook, trim($hookModulesInformations, ', ')]);
                $table->addRow(new TableSeparator());
            }
        }

        //Display result
        $table->render();
    }

    /**
     * Function to sort hook by name
     * @param $a
     * @param $b
     * @return int|\lt
     */
    private function cmp($a, $b)
    {
        return strcmp($a, $b);
    }
}
