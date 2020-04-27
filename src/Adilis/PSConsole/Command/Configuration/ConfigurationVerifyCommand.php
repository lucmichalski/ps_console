<?php

namespace Adilis\PSConsole\Command\Configuration;

use Configuration;
use Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationVerifyCommand extends Command {
    protected $_table;

    protected function configure() {
        $this
            ->setName('configuration:verify')
            ->setDescription('Verify prestashop configuration')
            ->setAliases(['config:list', 'cfg:list']);
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $this->_table = new Table($output);
        $this->_table->setHeaders(['Configuration verified', 'Status', 'Current value', 'Expected value']);
        $this->_table->addRows([
            [new TableCell('<comment>Preferences</comment>', ['colspan' => 4])],
            new TableSeparator
        ]);
        $this->verify('PS_SSL_ENABLED', '1');
        $this->verify('PS_SSL_ENABLED_EVERYWHERE', '1');
        $this->verify('PS_TOKEN_ENABLE', '0');
        $this->verify('PS_ALLOW_HTML_IFRAME', '1');
        $this->verify('PS_USE_HTMLPURIFIER', '1');
        $this->verify('PS_PRICE_DISPLAY_PRECISION', '2');
        $this->verify('PS_PRODUCT_WEIGHT_PRECISION', '2');
        $this->verify('PS_CUSTOMER_OPTIN', '0');
        $this->_table->addRows([
            new TableSeparator,
            [new TableCell('<comment>URLs</comment>', ['colspan' => 4])],
            new TableSeparator
        ]);

        $this->verify('PS_REWRITING_SETTINGS', '1');
        $this->verify('PS_ALLOW_ACCENTED_CHARS_URL', '0');
        $this->verify('PS_CANONICAL_REDIRECT', '2');

        $this->_table->addRows([
            new TableSeparator,
            [new TableCell('<comment>Performances</comment>', ['colspan' => 4])],
            new TableSeparator
        ]);
        $this->verify('PS_SMARTY_FORCE_COMPILE', '0');
        $this->verify('PS_SMARTY_CACHE', '1');
        $this->verify('PS_CSS_THEME_CACHE', '1');
        $this->verify('PS_JS_THEME_CACHE', '1');
        $this->verify('PS_HTACCESS_CACHE_CONTROL', '1');

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->verify('PS_HTML_THEME_COMPRESSION', '1');
            $this->verify('PS_JS_HTML_THEME_COMPRESSION', '0');
            $this->verify('PS_JS_DEFER', '0');
        }

        $this->_table->render();
        $output->writeln('');

        $this->_table = new Table($output);
        $this->_table->setHeaders(['Host configuration', 'Value']);
        $this->_table->addRows([
            [new TableCell('<comment>PHP configuration</comment>', ['colspan' => 2])],
            new TableSeparator
        ]);
        $this->_table->addRow(['PHP version', phpversion()]);
        $this->_table->addRow(['Interface', php_sapi_name()]);
        $this->_table->addRow(['Memory limit', ini_get('memory_limit')]);
        $this->_table->addRow(['Max execution time', ini_get('max_execution_time')]);
        $this->_table->addRow(['Max execution time', ini_get('max_execution_time')]);
        $this->_table->addRow(['Max upload filesize', ini_get('upload_max_filesize')]);
        $this->_table->addRow(['Max input vars', ini_get('max_input_vars')]);

        $this->_table->addRows([
            new TableSeparator,
            [new TableCell('<comment>Database configuration</comment>', ['colspan' => 2])],
            new TableSeparator
        ]);
        $this->_table->addRow(['Connecteur MySQL', Db::getInstance()->getClass()]);
        $this->_table->addRow(['Version de MySQL', Db::getInstance()->getVersion()]);
        $this->_table->addRow(['Nom MySQL', _DB_NAME_]);
        $this->_table->addRow(['Préfixe des tables', _DB_PREFIX_]);
        $this->_table->addRow(['Connecteur MySQL', _MYSQL_ENGINE_]);

        $this->_table->render();
    }

    private function verify(string $key, $expected_value) {
        $this->_table->addRow([
            $key,
            Configuration::get($key) == $expected_value ? '<info>OK</info>' : '<error>KO</error>',
            Configuration::get($key),
            $expected_value
        ]);
    }
}
