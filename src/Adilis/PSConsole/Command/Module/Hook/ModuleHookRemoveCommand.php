<?php

namespace Adilis\PSConsole\Command\Module\Hook;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleHookRemoveCommand extends Command
{
    protected function configure()
    {
        $this->setName('module:hook:remove');
        $this->setDescription('cool');
    }
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('it works');
    }
}
