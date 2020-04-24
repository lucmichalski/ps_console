<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Search;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Context;
use Shop;
use Search;
use Db;

/**
 * Commands: Add missing products to the index or re-build the entire index
 *
 */
class SearchIndexCommand extends Command {
    protected function configure() {
        $this
            ->setName('search:index')
            ->setDescription('Add missing products to the index or re-build the entire index (default)')
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'add|rebuild(default)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $type = $input->getArgument('type');

        Context::getContext()->shop->setContext(Shop::CONTEXT_ALL);

        switch ($type) {
            case 'add':
                $output->writeln('<comment>Adding missing products to the index...</comment>');
                Search::indexation();
                break;
            case 'rebuild':
            default:
                $output->writeln('<comment>Re-building the entire index...</comment>');
                Search::indexation(1);
                break;
        }

        list($total, $indexed) = Db::getInstance()->getRow('SELECT COUNT(*) as "0", SUM(product_shop.indexed) as "1" FROM ' . _DB_PREFIX_ . 'product p ' . Shop::addSqlAssociation('product', 'p') . ' WHERE product_shop.`visibility` IN ("both", "search") AND product_shop.`active` = 1');

        $output->writeln('<info>Currently indexed products: ' . (int) $indexed . ' / ' . (int) $total . '</info>');
    }
}
