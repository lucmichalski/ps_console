<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Override;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class List
 * Command sample description
 */
class OverrideListCommand extends Command {
    protected function configure() {
        $this
            ->setName('override:list')
            ->setDescription('List overrides of classes and controllers in the project')
            ->setAliases(['list:override']);
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $outputString = '';
        try {
            $finder = new Finder();
            $finder->files()->in(_PS_OVERRIDE_DIR_)->name('*.php')->notName('index.php');

            foreach ($finder as $file) {
                $outputString .= $file->getRelativePathname() . "\n";
            }
        } catch (\Exception $e) {
            $output->writeln('<info>ERROR:' . $e->getMessage() . '</info>');
            return 1;
        }
        if ($outputString == '') {
            $outputString = 'No class or controllers overrides on this project';
        }
        $output->writeln('<info>' . $outputString . '</info>');
    }
}
