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

class HookListCommand extends Command {
    protected function configure() {
        $this
            ->setName('hook:list')
            ->setDescription('List all hooks registered in database');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $hooks = array_map(function ($row) {
            return $row['name'];
        }, Hook::getHooks());

        $table = new Table($output);
        $table->setHeaders(['Hook Name']);

        foreach ($hooks as $hook) {
            $table->addRow([$hook]);
        }

        $table->render();
    }
}
