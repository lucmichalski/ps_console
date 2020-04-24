<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class index
 * Command sample description
 */
class DevIndexCommand extends Command {
    /**
     * @inheritDoc
     */
    protected function configure() {
        $this
            ->setName('dev:add-index-files')
            ->setDescription('Add missing index.php files in directory')
            ->addArgument(
                'dir',
                InputArgument::REQUIRED,
                'directory to fill ( relative to ps root path)'
            );
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $dir = $input->getArgument('dir');
        try {
            $dir = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($dir)) {
                throw new \Exception('directory doesn\'t exists');
            }

            /* Fix current directory */
            if (!file_exists($dir . DIRECTORY_SEPARATOR . 'index.php')) {
                copy(_PS_IMG_DIR_ . 'index.php', $dir . DIRECTORY_SEPARATOR . 'index.php');
            }

            $finder = new Finder();
            $directories = $finder->directories()->in($dir);

            $i = 0;
            foreach ($directories as $directory) {
                ${$i} = new Finder();
                $indexFile = ${$i}->files()->in((string) $directory)->depth('==0')->name('index.php');
                if (!sizeof($indexFile)) {
                    copy(_PS_IMG_DIR_ . 'index.php', $directory . DIRECTORY_SEPARATOR . 'index.php');
                }
                $i++;
            }
        } catch (\Exception $e) {
            $output->writeln('<info>ERROR:' . $e->getMessage() . '</info>');
        }
        $output->writeln('<info>Index files added with success</info>');
    }
}
