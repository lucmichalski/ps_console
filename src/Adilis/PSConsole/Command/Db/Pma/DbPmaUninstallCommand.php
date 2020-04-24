<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Db\Pma;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DbPmaUninstallCommand extends Command {
    protected function configure() {
        $this
            ->setName('db:pma:uninstall')
            ->setDescription('Uninstall PhpMyAdmin')
            ->setAliases(['pma:uninstall']);
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $filesystem = new Filesystem();

        if (!$filesystem->exists(_PS_ROOT_DIR_ . '/pma')) {
            $output->writeln('<error>PhpMyAdmin directory not exits</error>');
            return;
        }

        $filesystem->remove(_PS_ROOT_DIR_ . '/pma');
        $output->writeln('<info>PhpMyAdmin have been successfully uninstalled</info>');
    }
}
