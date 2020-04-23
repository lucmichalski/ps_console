<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Configuration;

use Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class set
 * Command sample description
 */
class ConfigurationSetCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('configuration:set')
            ->setDescription('set configuration value')
            ->addArgument('name', InputArgument::REQUIRED, 'configuration name')
            ->addArgument('value', InputArgument::REQUIRED, 'configuration value')
            ->setAliases(['config:set', 'cfg:set']);;
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $value = $input->getArgument('value');
        Configuration::updateValue($name, $value);
        $output->writeln("<info>Update configuration " . $name . " with " . $value . "</info>");
    }
}
