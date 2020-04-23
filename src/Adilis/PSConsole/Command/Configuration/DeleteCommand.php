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
 * Class delete
 * Command sample description
 */
class DeleteCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('configuration:delete')
            ->setDescription('Delete configuration by name')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'configuration name'
            )
            ->setAliases(['config:delete', 'cfg:delete']);;
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $value = Configuration::deleteByName($name);
        $output->writeln('<info>' . $value . '</info>');
    }
}
