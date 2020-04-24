<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Configuration;

use Configuration;
use Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class all
 * Command sample description
 */
class ConfigurationListCommand extends Command {
    const MAX_LENGTH_CONFIGURATION_VALUE = 50;

    /**
     * @inheritDoc
     */
    protected function configure() {
        $this
            ->setName('configuration:list')
            ->setDescription('List all configurations')
            ->setAliases(['config:list', 'cfg:list']);
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        //Load All Configurations
        Configuration::loadConfiguration();

        //Get All Configuration names (except xml configuration)
        $configurationNames = Db::getInstance()->executeS('SELECT name FROM ' . _DB_PREFIX_ . "configuration WHERE name <> 'PS_INSTALL_XML_LOADERS_ID'");

        $table = new Table($output);
        $table->setHeaders(['Name', 'Value']);
        foreach ($configurationNames as $configuration_name) {
            $configuration_value = Configuration::get($configuration_name['name']);
            if (strlen($configuration_value) > self::MAX_LENGTH_CONFIGURATION_VALUE) {
                $configuration_value = substr($configuration_value, 0, self::MAX_LENGTH_CONFIGURATION_VALUE) . ' (*)';
            }
            $table->addRow([$configuration_name['name'], $configuration_value]);
        }

        $table->render();
        $output->writeln('(*) : Value truncated');
    }
}
