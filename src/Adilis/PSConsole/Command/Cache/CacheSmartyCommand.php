<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tools;

class CacheSmartyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:smarty')
            ->setDescription('Clear smarty cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Tools::clearSmartyCache();
        $output->writeln('<info>Smarty Cache and compiled dir cleaned</info>');
    }
}
