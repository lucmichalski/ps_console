<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Theme;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Install
 * Command sample description
 */
class ThemeInstallCommand extends Command {
    protected function configure() {
        $this
            ->setName('theme:install')
            ->setDescription('Install a theme');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        // TODO: Generate logic
        $output->writeln('it works');
    }
}
