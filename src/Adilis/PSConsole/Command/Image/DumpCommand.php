<?php

namespace Adilis\PSConsole\Command\Image;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    protected function configure()
    {
        $this->setName('image:dump');
        $this->setDescription('Dump images');
    }
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('it works');
    }
}
