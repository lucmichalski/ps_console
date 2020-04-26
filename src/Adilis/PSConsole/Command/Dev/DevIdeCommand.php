<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ide
 * Command sample description
 */
class DevIdeCommand extends Command {
    const CLASS_NAME_SOURCE = 'https://raw.githubusercontent.com/julienbourdeau/PhpStorm-PrestaShop-Autocomplete/master/autocomplete.php';
    const CLASS_NAME_FILE = 'autocomplete.php';

    /**
     * @inheritDoc
     */
    protected function configure() {
        $this
            ->setName('dev:ide')
            ->setDescription('Download class names index to resolve autocompletion in IDE');
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        //Download content
        $content = file_get_contents(self::CLASS_NAME_SOURCE);
        $fileName = self::CLASS_NAME_FILE;

        if (file_put_contents($fileName, $content) !== false) {
            $output->writeln('<info>File ' . self::CLASS_NAME_FILE . ' download with success</info>');
        } else {
            $output->writeln('<error>Unable to create file' . self::CLASS_NAME_FILE . '</error>');
            return 1;
        }
    }
}
