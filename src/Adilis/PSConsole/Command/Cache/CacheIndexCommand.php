<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Cache;

use PrestaShop\PrestaShop\Adapter\Cache\Clearer\ClassIndexCacheClearer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheIndexCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:index')
            ->setDescription('Clear classes index');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        ClassIndexCacheClearer::clear();
        $output->writeln('<info>Classes index cache done !</info>');
    }
}
