<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cache;

class CacheCleanCommand extends Command {
    protected function configure() {
        $this
            ->setName('cache:clean')
            ->setDescription('Clean cache')
            ->addArgument('key', InputArgument::OPTIONAL, 'key name | default *');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $key = $input->getArgument('key');

        if (!$key || $key == '') {
            $key = '*';
        }

        $cache = Cache::getInstance();
        $cache->clean($key);

        $output->writeln('<info>Cache cleaned</info>');
    }
}
