<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Media;

/**
 * Clear Media cache
 */
class CacheMediaCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:media')
            ->setDescription('Clean media cache directory');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        Media::clearCache();
        $output->writeln('<info>Media cache cleared</info>');
    }
}
