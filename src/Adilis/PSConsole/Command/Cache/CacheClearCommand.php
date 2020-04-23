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

/**
 * Clear all caches commands
 */
class CacheClearCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clear all cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            $cacheClearer = new \PrestaShop\PrestaShop\Core\Cache\Clearer\CacheClearerChain();
            $cacheClearer->clear();
            $output->writeln("<info>All cache cleared with success</info>");
        } elseif (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $cacheClearer = new \PrestaShop\PrestaShop\Adapter\Cache\CacheClearer();
            $cacheClearer->clearAllCaches();
            $output->writeln("<info>All cache cleared with success</info>");
        } else {
            $output->writeln("<error>This command is only available for Prestashop > 1.7.0.0 </error>");
            return 1;
        }
    }
}
