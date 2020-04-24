<?php

namespace Adilis\PSConsole\Command\Configuration;

use Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationGetCommand extends Command {
    protected function configure() {
        $this
            ->setName('configuration:get')
            ->setDescription('get configuration value')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'configuration name'
            )
            ->setAliases(['config:get', 'cfg:get']);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $name = $input->getArgument('name');
        $value = Configuration::get($name);
        $output->writeln('<info>' . $value . '</info>');
    }
}
