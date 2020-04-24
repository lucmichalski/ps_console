<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cache;

class CacheFlushCommand extends Command {
    protected function configure() {
        $this
            ->setName('cache:flush')
            ->setDescription('Flush cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $cache = Cache::getInstance();
        $cache->flush();

        //Specific cacheFS
        if (get_class($cache) == 'cacheFs') {
            $cache::deleteCacheDirectory();
            $cache::createCacheDirectories();
        }

        $output->writeln('<info>Cache flushed</info>');
    }
}
