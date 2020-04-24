<?php

namespace Adilis\PSConsole\Command\Image;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageDumpCommand extends Command {
    protected $_types = [
        'all',
        'admin',
        'product',
        'category',
        'cms',
        'tmp',
    ];

    /** @var array Archives Types */
    protected $_archivesFormat = [
        'tar.gz',
        'zip',
    ];

    protected $_type;
    protected $_archiveFormat;

    protected function configure() {
        $this->setName('image:dump')
            ->setDescription('Dump images')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'type of file to export')
            ->addOption('archive', 'a', InputOption::VALUE_OPTIONAL, 'Archive format', 'tar.gz');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        if (!is_dir(_PS_ROOT_DIR_ . '/dumps')) {
            $filesystem = new Filesystem;
            $filesystem->mkdir(_PS_ROOT_DIR_ . '/dumps', 0755);
        }

        $this->_archiveFormat = $input->getOption('archive');
        dump($this->_archiveFormat);
        if (!in_array($this->_archiveFormat, $this->_archivesFormat)) {
            $this->_archiveFormat = 'tar.gz';
        }

        $this->_type = $input->getOption('type');

        switch ($this->_type) {
            case 'admin':
            case 'cms':
            case 'tmp':
                $directory = $this->_type;
                break;

            case 'product':
                $directory = 'p';
                break;

            case 'category':
                $directory = 'c';
                break;

            default:
                $directory = '';
                break;
        }

        $exportPath = _PS_IMG_DIR_ . $directory;

        if (is_dir($exportPath)) {
            $filePath = _PS_ROOT_DIR_ . '/dumps' . DIRECTORY_SEPARATOR . date('YmdHi') . '-images' . $this->_type . '.' . $this->_archiveFormat;

            if ($this->_archiveFormat == 'tar.gz') {
                $command = 'tar czf ' . $filePath . ' *';
            } else {
                $command = 'zip -qr ' . $filePath . ' *';
            }

            $output->writeln('<info>Images export started</info>');
            $export = shell_exec('cd ' . $exportPath . ' && ' . $command);
            $output->writeln($export);
            $output->writeln('<info>Images export ended in path ' . $filePath . '</info>');
        } else {
            $output->writeln('<error>The path ' . $exportPath . ' does not exists</error>');
        }
    }
}
