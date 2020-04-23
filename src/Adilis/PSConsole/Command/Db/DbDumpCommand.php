<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Db;

use Configuration;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DbDumpCommand extends Command
{
    protected $_allowedTypes = [
        'all',
        'customers',
        'orders',
        'catalog',
    ];

    protected function configure()
    {
        $this
            ->setName('db:dump')
            ->setDescription('Create db export ')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'allowed values all|customers|orders|catalog', 'all')
            ->addOption('gzip', 'g', InputOption::VALUE_OPTIONAL, 'gzip', false)
            ->setHelp('This command will export current prestashop database using mysqldump shell command');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_dir(_PS_ROOT_DIR_ . '/dumps')) {
            $filesystem = new Filesystem;
            $filesystem->mkdir(_PS_ROOT_DIR_ . '/dumps', 0755);
        }

        //Shell_exec function is required
        if (!function_exists('shell_exec')) {
            $output->writeln('<error>The function shell_exec is not present</error>');
            return 1;
        }

        $type = $input->getOption('type');
        $gzip = $input->getOption('gzip');

        if (!in_array($type, $this->_allowedTypes)) {
            $output->writeln('<error>Unknow type option for export</error>');
            return 1;
        }

        $output->writeln('<info>Export started</info>');

        $mysql_dump_path = Configuration::get('PSC_MYSQLDUMP_PATH', null, null, null, 'mysqldump');
        $command = $mysql_dump_path;
        if (preg_match('/^(.*):([0-9]+)$/', _DB_SERVER_, $matches)) {
            $command .= " -h " . $matches[1] . ' --port=' . $matches[2];;
        } else {
            $command .= " -h " . _DB_SERVER_;
        }
        $command .= ' -u ' . _DB_USER_ . ' -p' . _DB_PASSWD_ . ' ' . _DB_NAME_ . ' ';

        //Export type management
        if ($type !== 'all') {
            //Get table concerned by export
            $getfunction = '_get' . ucfirst($type) . 'Tables';
            $tables = $this->$getfunction();
            $tables = array_map(function ($item) {
                return _DB_PREFIX_ . $item;
            }, $tables);
            $command .= implode(" ", $tables);
        }

        ($gzip !== false) ? $command .= ' | gzip ' : '';
        $command .= '> ' . _PS_ROOT_DIR_ . '/dumps/' . date('YmdHi') . '-db-dump' . ($type ? '-' . $type : '') . '.sql';
        ($gzip !== false) ? $command .= '.gz' : '';

        exec($command, $exec_outputs);
        foreach ($exec_outputs as $exec_output) {
            $output->writeln('<info>' . $exec_output . '</info>');
        }

        $output->writeln('<info>Export ended</info>');
    }


    /**
     * Récupération des tables du catalogue
     * @return array
     */
    protected function _getCustomersTables()
    {
        return [
            'customer',
            'customer_group',
            'customer_message',
            'customer_message_sync_imap',
            'customer_thread',
            'address',
        ];
    }

    /**
     * Récupération des tables du catalogue
     * @return array
     */
    protected function _getOrdersTables()
    {
        return [
            'cart',
            'cart_product',
            'connections',
            'connections_page',
            'connections_source',
            'guest',
            'message',
            'message_readed',
            'orders',
            'order_carrier',
            'order_cart_rule',
            'order_detail',
            'order_detail_tax',
            'order_history',
            'order_invoice',
            'order_invoice_payment',
            'order_invoice_tax',
            'order_message',
            'order_message_lang',
            'order_payment',
            'order_return',
            'order_return_detail',
            'order_slip',
            'order_slip_detail',
            'page',
            'page_type',
            'page_viewed',
            'product_sale',
            'referrer_cache',
        ];
    }

    /**
     * Récupération des tables du catalogue
     * @return array
     */
    protected function _getCatalogTables()
    {
        return [
            'product',
            'product_shop',
            'product_lang',
            'category_product',
            'product_tag',
            'tag',
            'image',
            'image_lang',
            'image_shop',
            'product_carrier',
            'cart_product',
            'product_attachment',
            'product_country_tax',
            'product_download',
            'product_group_reduction_cache',
            'product_sale',
            'product_supplier',
            'warehouse_product_location',
            'stock',
            'stock_available',
            'stock_mvt',
            'supply_order_detail',
            'product_attribute',
            'product_attribute_shop',
            'product_attribute_combination',
            'product_attribute_image',
            'attribute_impact',
            'attribute_lang',
            'attribute_group',
            'attribute_group_lang',
            'attribute_group_shop',
            'attribute_shop',
            'manufacturer',
            'manufacturer_lang',
            'manufacturer_shop',
            'supplier',
            'supplier_lang',
            'supplier_shop',
            'customization',
            'customization_field',
            'customization_field_lang',
            'customized_data',
            'feature',
            'feature_lang',
            'feature_product',
            'feature_shop',
            'feature_value',
            'feature_value_lang',
            'pack',
            'search_index',
            'search_word',
            'specific_price',
            'specific_price_priority',
            'specific_price_rule',
            'specific_price_rule_condition',
            'specific_price_rule_condition_group',
            'warehouse',
        ];
    }
}
